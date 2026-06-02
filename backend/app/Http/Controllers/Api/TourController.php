<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\TourBooking;
use App\Services\MailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TourController extends Controller
{
    public function index(Request $request)
    {
        $locale = $this->normalizeLocale(
            $request->get('lang'),
            $request->header('Accept-Language')
        );

        $query = Tour::query()
            ->with(['tourCategory', 'activeVariants.activePriceTiers', 'activeAddons'])
            ->where('status', 'active');
        if ($request->filled('tour_category_id')) {
            $query->where('tour_category_id', $request->tour_category_id);
        }

        // Filters
        if ($request->filled('difficulty_level') || $request->filled('difficulty')) {
            $difficulty = $request->get('difficulty_level') ?: $request->get('difficulty');
            $query->where('difficulty_level', $difficulty);
        }

        if ($request->filled('duration')) {
            $duration = $request->duration;
            
            // Support unit prefix: "hours:1-4", "days:1-3", etc.
            $unit = null;
            if (str_contains($duration, ':')) {
                [$unit, $duration] = explode(':', $duration, 2);
                $query->where('duration_unit', $unit);
            }
            
            // Support range strings: "1-3", "4-7", "8+"
            if (str_contains($duration, '-')) {
                $parts = explode('-', $duration);
                $query->whereBetween('duration', [(int)$parts[0], (int)$parts[1]]);
            } elseif (str_ends_with($duration, '+')) {
                $query->where('duration', '>=', (int)rtrim($duration, '+'));
            } else {
                $query->where('duration', (int)$duration);
            }
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_person', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_person', '<=', $request->max_price);
        }

        // Price range: budget / mid / luxury
        if ($request->filled('price_range')) {
            switch ($request->price_range) {
                case 'budget':
                    $query->where('price_per_person', '<', 500000);
                    break;
                case 'mid':
                    $query->whereBetween('price_per_person', [500000, 2000000]);
                    break;
                case 'luxury':
                    $query->where('price_per_person', '>', 2000000);
                    break;
            }
        }

        // Destination type filter (predefined types: jungle, sea, fusion, etc.)
        if ($request->filled('destination_type')) {
            $query->where('destination_type', $request->destination_type);
        }

        // Destination search (search in departure_location, name, description)
        if ($request->filled('destination')) {
            $dest = $request->destination;
            $query->where(function ($q) use ($dest) {
                $q->where('departure_location', 'like', "%{$dest}%")
                    ->orWhere('name', 'like', "%{$dest}%")
                    ->orWhere('name_en', 'like', "%{$dest}%")
                    ->orWhere('name_vi', 'like', "%{$dest}%")
                    ->orWhere('description', 'like', "%{$dest}%");
            });
        }

        // Search (generic)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $query->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->orderBy('created_at', 'desc');

        $tours = $query->with('reviews')->paginate($request->get('per_page', 12));

        // Add average rating and calculate display price
        $tours->getCollection()->transform(function ($tour) use ($locale) {
            $tour->average_rating = $tour->averageRating();
            $tour->reviews_count = $tour->reviews()->count();
            $this->applyTourLocale($tour, $locale);
            $this->calculateDisplayPrice($tour);
            return $tour;
        });

        return response()->json($tours);
    }

    public function popular(Request $request)
    {
        $locale = $this->normalizeLocale(
            $request->get('lang'),
            $request->header('Accept-Language')
        );

        $limit = min((int) $request->get('limit', 8), 20);

        $tours = Tour::query()
            ->where('status', 'active')
            ->withCount('bookings')
            ->with(['tourCategory', 'activeVariants.activePriceTiers', 'activeAddons'])
            ->orderByDesc('bookings_count')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get();

        $tours->transform(function ($tour) use ($locale) {
            $tour->average_rating = $tour->averageRating();
            $tour->reviews_count = $tour->reviews()->count();
            $this->applyTourLocale($tour, $locale);
            $this->calculateDisplayPrice($tour);
            return $tour;
        });

        return response()->json(['data' => $tours]);
    }

    public function show($id)
    {
        $tour = Tour::with(['reviews.user', 'tourCategory', 'activeVariants.activePriceTiers', 'activeAddons'])->findOrFail($id);
        $locale = $this->normalizeLocale(
            request()->get('lang'),
            request()->header('Accept-Language')
        );
        
        // Increment view count
        $tour->increment('view_count');

        $tour->average_rating = $tour->averageRating();
        $tour->reviews_count = $tour->reviews()->count();
        $this->applyTourLocale($tour, $locale);
        $this->calculateDisplayPrice($tour);

        return response()->json($tour);
    }

    public function bookTour(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tour_id' => 'required|exists:tours,id',
            'tour_date' => 'required|date|after:today',
            'participants' => 'required|integer|min:1',
            'contact_name' => 'required|string',
            'contact_email' => 'required|email',
            'contact_phone' => 'required|string',
            'special_requests' => 'nullable|string',
            'tour_variant_id' => 'nullable|exists:tour_variants,id',
            'addon_ids' => 'nullable|array',
            'addon_ids.*' => 'integer|exists:tour_addons,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tour = Tour::with(['activeVariants.activePriceTiers', 'activeAddons'])->findOrFail($request->tour_id);

        $tourVariant = null;
        $priceTier = null;
        $basePrice = 0;
        $pricePerPerson = 0;
        $pricingType = 'per_person';
        $tourVariantName = null;

        $activeVariants = $tour->activeVariants;
        if ($activeVariants->isNotEmpty()) {
            $variantId = $request->tour_variant_id;
            
            if (empty($variantId)) {
                $defaultVariant = $activeVariants->firstWhere('is_default', true) ?? $activeVariants->first();
                $variantId = $defaultVariant?->id;
            }

            if (empty($variantId)) {
                return response()->json(['message' => 'Tour variants are defined but none are active or selected'], 422);
            }

            $tourVariant = $activeVariants->firstWhere('id', $variantId);
            if (!$tourVariant) {
                return response()->json(['message' => 'Invalid or inactive tour variant selected'], 422);
            }

            $tourVariantName = $tourVariant->name;

            if ($tourVariant->min_participants !== null && $request->participants < $tourVariant->min_participants) {
                return response()->json(['message' => "Minimum participants for this variant is {$tourVariant->min_participants}"], 422);
            }
            if ($tourVariant->max_participants !== null && $request->participants > $tourVariant->max_participants) {
                return response()->json(['message' => "Maximum participants for this variant is {$tourVariant->max_participants}"], 422);
            }

            $priceTier = $tourVariant->activePriceTiers->first(function ($tier) use ($request) {
                return $request->participants >= $tier->min_participants && $request->participants <= $tier->max_participants;
            });

            if (!$priceTier) {
                return response()->json(['message' => 'No price tier found for this number of participants in the selected variant'], 422);
            }

            $pricingType = $priceTier->pricing_type;
            $basePrice = $priceTier->price;
            $pricePerPerson = $priceTier->discount_price ?? $priceTier->price;
        } else {
            if ($request->participants > $tour->max_participants) {
                return response()->json(['message' => 'Number of participants exceeds tour limit'], 422);
            }
            if ($tour->min_participants !== null && $request->participants < $tour->min_participants) {
                return response()->json(['message' => "Number of participants is less than tour minimum limit ({$tour->min_participants})"], 422);
            }

            $basePrice = $tour->price_per_person;
            $pricePerPerson = $tour->discount_price ?? $tour->price_per_person;
            $pricingType = 'per_person';
        }

        $tourBaseCost = 0;
        if ($pricingType === 'per_person') {
            $tourBaseCost = $pricePerPerson * $request->participants;
        } else {
            $tourBaseCost = $pricePerPerson;
        }

        $addonsAmount = 0;
        $selectedAddons = collect();
        if ($request->filled('addon_ids')) {
            $activeAddons = $tour->activeAddons;
            foreach ($request->addon_ids as $addonId) {
                $addon = $activeAddons->firstWhere('id', $addonId);
                if (!$addon) {
                    return response()->json(['message' => "Addon with ID {$addonId} is invalid or not active for this tour"], 422);
                }

                $quantity = $addon->pricing_type === 'per_person' ? $request->participants : 1;
                $addonTotal = $addon->price * $quantity;
                $addonsAmount += $addonTotal;

                $selectedAddons->push([
                    'tour_addon_id' => $addon->id,
                    'name' => $addon->name,
                    'name_en' => $addon->name_en,
                    'name_vi' => $addon->name_vi,
                    'price' => $addon->price,
                    'pricing_type' => $addon->pricing_type,
                    'quantity' => $quantity,
                    'total_amount' => $addonTotal,
                ]);
            }
        }

        $totalAmount = $basePrice * ($pricingType === 'per_person' ? $request->participants : 1) + ($selectedAddons->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        }));

        $finalAmount = $tourBaseCost + $addonsAmount;
        $discountAmount = $totalAmount - $finalAmount;

        $booking = new TourBooking();
        $booking->user_id = $request->user()->id;
        $booking->tour_id = $request->tour_id;
        $booking->booking_number = $booking->generateBookingNumber();
        $booking->tour_date = $request->tour_date;
        $booking->participants = $request->participants;
        $booking->price_per_person = $pricePerPerson;
        $booking->total_amount = $totalAmount;
        $booking->discount_amount = $discountAmount;
        $booking->tax_amount = 0;
        $booking->final_amount = $finalAmount;
        $booking->contact_name = $request->contact_name;
        $booking->contact_email = $request->contact_email;
        $booking->contact_phone = $request->contact_phone;
        $booking->special_requests = $request->special_requests;

        // Snapshots
        $booking->tour_variant_id = $tourVariant?->id;
        $booking->tour_variant_name = $tourVariantName;
        $booking->price_tier_id = $priceTier?->id;
        $booking->pricing_type = $pricingType;
        $booking->base_price = $basePrice;
        $booking->addons_amount = $addonsAmount;
        
        $booking->save();

        foreach ($selectedAddons as $addonData) {
            $bookingAddon = new \App\Models\TourBookingAddon($addonData);
            $booking->addons()->save($bookingAddon);
        }

        // Send email notifications
        try {
            MailNotificationService::sendTourBookingNotification($booking);
        } catch (\Exception $e) {
            \Log::error('Tour Booking Email Notification Error: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Tour booked successfully',
            'booking' => $booking->load(['tour', 'addons']),
        ], 201);
    }

    public function userBookings(Request $request)
    {
        $bookings = $request->user()
            ->tourBookings()
            ->with(['tour', 'addons'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($bookings);
    }

    protected function calculateDisplayPrice(Tour $tour): void
    {
        $activeVariants = $tour->activeVariants;
        
        if ($activeVariants->isEmpty()) {
            $tour->display_price = $tour->discount_price ?? $tour->price_per_person;
            $tour->display_price_type = 'per_person';
            return;
        }

        $minPrice = null;
        $lowestTier = null;
        $prices = [];

        foreach ($activeVariants as $variant) {
            foreach ($variant->activePriceTiers as $tier) {
                $effectivePrice = $tier->discount_price ?? $tier->price;
                $prices[] = (float) $effectivePrice;

                if ($minPrice === null || $effectivePrice < $minPrice) {
                    $minPrice = $effectivePrice;
                    $lowestTier = $tier;
                }
            }
        }

        if ($minPrice === null) {
            $tour->display_price = $tour->discount_price ?? $tour->price_per_person;
            $tour->display_price_type = 'per_person';
            return;
        }

        $uniquePrices = array_unique($prices);
        $hasMultiplePrices = count($uniquePrices) > 1;

        $tour->display_price = $minPrice;
        
        if ($hasMultiplePrices) {
            $tour->display_price_type = 'from';
        } else {
            $tour->display_price_type = $lowestTier->pricing_type;
        }
    }

    protected function normalizeLocale(?string $locale, ?string $headerLocale = null): string
    {
        $resolvedLocale = $locale ?? $headerLocale;

        if ($resolvedLocale && str_starts_with(strtolower($resolvedLocale), 'vi')) {
            return 'vi';
        }

        return 'en';
    }

    protected function applyTourLocale(Tour $tour, string $locale): Tour
    {
        return $tour->applyLocale($locale);
    }
}
