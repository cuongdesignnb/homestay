<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaAlbum;
use App\Models\Tour;
use App\Models\TourBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminTourController extends Controller
{
    public function index(Request $request)
    {
        $tours = Tour::query()
            ->with(['tourCategory', 'variants.priceTiers', 'addons'])
            ->when($request->status, fn ($query) => $query->where('status', $request->status))
            ->when($request->tour_category_id, fn ($query) => $query->where('tour_category_id', $request->tour_category_id))
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json($tours);
    }

    public function store(Request $request)
    {
        $data = $this->validateTour($request);

        if (isset($data['variants'])) {
            $this->validatePriceTiersOverlap($data['variants']);
        }

        $data = $this->prepareLocalizedFields($data);
        $data = $this->hydrateImagesFromAlbum($data);
        $data = $this->ensureCoverImageData($data);
        $data['images'] = $data['images'] ?? [];
        $data['includes'] = $data['includes'] ?? $data['includes_en'] ?? [];
        $data['excludes'] = $data['excludes'] ?? $data['excludes_en'] ?? [];
        $data['itinerary'] = $data['itinerary'] ?? $data['itinerary_en'] ?? [];

        $tour = \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $tour = Tour::create($data);
            $this->saveVariantsAndAddons($tour, $data);
            return $tour;
        });

        $tour->load(['tourCategory', 'variants.priceTiers', 'addons']);

        return response()->json([
            'message' => 'Tour created successfully',
            'tour' => $tour,
        ], 201);
    }

    public function show(Tour $tour)
    {
        return response()->json($tour->load(['variants.priceTiers', 'addons']));
    }

    public function update(Request $request, Tour $tour)
    {
        $data = $this->validateTour($request, true);

        if (isset($data['variants'])) {
            $this->validatePriceTiersOverlap($data['variants']);
        }

        $data = $this->prepareLocalizedFields($data, $tour);
        $data = $this->hydrateImagesFromAlbum($data);
        $data = $this->ensureCoverImageData($data);

        \Illuminate\Support\Facades\DB::transaction(function () use ($tour, $data) {
            $tour->update($data);
            $this->saveVariantsAndAddons($tour, $data);
        });

        $tour->load(['tourCategory', 'variants.priceTiers', 'addons']);

        return response()->json([
            'message' => 'Tour updated successfully',
            'tour' => $tour,
        ]);
    }

    public function destroy(Tour $tour)
    {
        $tour->delete();

        return response()->json(['message' => 'Tour deleted successfully']);
    }

    public function bookings(Request $request)
    {
        $bookings = TourBooking::with(['tour', 'user', 'addons'])
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json($bookings);
    }

    public function updateBookingStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed'
        ]);

        $booking = TourBooking::findOrFail($id);
        $booking->status = $data['status'];
        $booking->save();

        return response()->json([
            'message' => 'Booking status updated successfully',
            'booking' => $booking,
        ]);
    }

    protected function validateTour(Request $request, bool $isUpdate = false): array
    {
        $arrayRule = 'nullable|array';

        $rules = [
            'name' => 'sometimes|string|max:255',
            'name_en' => ($isUpdate ? 'sometimes' : 'required') . '|string|max:255',
            'name_vi' => ($isUpdate ? 'sometimes' : 'nullable') . '|string|max:255',
            'slug_en' => 'nullable|string|max:255',
            'slug_vi' => 'nullable|string|max:255',
            'description' => 'sometimes|string',
            'description_en' => ($isUpdate ? 'sometimes' : 'required') . '|string',
            'description_vi' => ($isUpdate ? 'sometimes' : 'nullable') . '|string',
            'duration' => ($isUpdate ? 'sometimes' : 'required') . '|integer|min:1',
            'duration_unit' => ($isUpdate ? 'sometimes' : 'required') . '|in:hours,days',
            'price_per_person' => ($isUpdate ? 'sometimes' : 'required') . '|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'max_participants' => ($isUpdate ? 'sometimes' : 'required') . '|integer|min:1',
            'min_participants' => 'nullable|integer|min:1',
            'departure_location' => ($isUpdate ? 'sometimes' : 'required') . '|string|max:255',
            'destination_type' => 'nullable|string|in:jungle,sea,fusion,historical_culture,experience',
            'difficulty_level' => 'nullable|string|max:50',
            'age_restriction' => 'nullable|string|max:50',
            'status' => ($isUpdate ? 'sometimes' : 'required') . '|in:active,inactive',
            'includes' => $arrayRule,
            'includes.*' => 'string|max:255',
            'includes_en' => $arrayRule,
            'includes_en.*' => 'string|max:255',
            'includes_vi' => $arrayRule,
            'includes_vi.*' => 'string|max:255',
            'excludes' => $arrayRule,
            'excludes.*' => 'string|max:255',
            'excludes_en' => $arrayRule,
            'excludes_en.*' => 'string|max:255',
            'excludes_vi' => $arrayRule,
            'excludes_vi.*' => 'string|max:255',
            'itinerary' => $arrayRule,
            'itinerary.*' => 'string',
            'itinerary_en' => $arrayRule,
            'itinerary_en.*' => 'string',
            'itinerary_vi' => $arrayRule,
            'itinerary_vi.*' => 'string',
            'images' => $arrayRule,
            'images.*' => 'string',
            'cover_image' => 'nullable|string|max:2048',
            'cover_media_id' => 'nullable|exists:media,id',
            'media_album_id' => 'nullable|exists:media_albums,id',
            'tour_category_id' => 'nullable|exists:tour_categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'sometimes|string|max:255',
            'meta_title_en' => 'nullable|string|max:255',
            'meta_title_vi' => 'nullable|string|max:255',
            'meta_description' => 'sometimes|string',
            'meta_description_en' => 'nullable|string',
            'meta_description_vi' => 'nullable|string',
            'meta_keywords' => 'sometimes|string',
            'meta_keywords_en' => 'nullable|string',
            'meta_keywords_vi' => 'nullable|string',
            
            // Variants and Addons validation
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer',
            'variants.*.name' => 'nullable|string|max:255',
            'variants.*.name_en' => 'required_with:variants|string|max:255',
            'variants.*.name_vi' => 'nullable|string|max:255',
            'variants.*.description' => 'nullable|string',
            'variants.*.description_en' => 'nullable|string',
            'variants.*.description_vi' => 'nullable|string',
            'variants.*.status' => 'required_with:variants|in:active,inactive',
            'variants.*.sort_order' => 'nullable|integer',
            'variants.*.is_default' => 'nullable|boolean',
            'variants.*.min_participants' => 'nullable|integer|min:1',
            'variants.*.max_participants' => 'nullable|integer|min:1',
            'variants.*.price_tiers' => 'required_with:variants|array',
            'variants.*.price_tiers.*.id' => 'nullable|integer',
            'variants.*.price_tiers.*.min_participants' => 'required|integer|min:1',
            'variants.*.price_tiers.*.max_participants' => 'required|integer|min:1',
            'variants.*.price_tiers.*.pricing_type' => 'required|in:per_person,flat',
            'variants.*.price_tiers.*.price' => 'required|numeric|min:0',
            'variants.*.price_tiers.*.discount_price' => 'nullable|numeric|min:0',
            'variants.*.price_tiers.*.label' => 'nullable|string|max:255',
            'variants.*.price_tiers.*.sort_order' => 'nullable|integer',
            'variants.*.price_tiers.*.status' => 'required|in:active,inactive',
            
            'addons' => 'nullable|array',
            'addons.*.id' => 'nullable|integer',
            'addons.*.name' => 'nullable|string|max:255',
            'addons.*.name_en' => 'required_with:addons|string|max:255',
            'addons.*.name_vi' => 'nullable|string|max:255',
            'addons.*.description' => 'nullable|string',
            'addons.*.description_en' => 'nullable|string',
            'addons.*.description_vi' => 'nullable|string',
            'addons.*.price' => 'required_with:addons|numeric|min:0',
            'addons.*.pricing_type' => 'required_with:addons|in:per_person,per_booking',
            'addons.*.status' => 'required_with:addons|in:active,inactive',
            'addons.*.sort_order' => 'nullable|integer',
        ];

        return $request->validate($rules);
    }

    protected function validatePriceTiersOverlap(array $variants): void
    {
        foreach ($variants as $vIndex => $variant) {
            $tiers = $variant['price_tiers'] ?? [];
            $activeTiers = collect($tiers)->filter(fn ($t) => ($t['status'] ?? 'active') === 'active')->values();

            for ($i = 0; $i < $activeTiers->count(); $i++) {
                for ($j = $i + 1; $j < $activeTiers->count(); $j++) {
                    $t1 = $activeTiers[$i];
                    $t2 = $activeTiers[$j];

                    $min1 = (int) $t1['min_participants'];
                    $max1 = (int) $t1['max_participants'];
                    $min2 = (int) $t2['min_participants'];
                    $max2 = (int) $t2['max_participants'];

                    if ($min1 <= $max2 && $min2 <= $max1) {
                        $variantName = $variant['name_en'] ?? $variant['name'] ?? "Variant " . ($vIndex + 1);
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "variants.{$vIndex}.price_tiers" => [
                                "Price tiers overlap in variant '{$variantName}': [{$min1}-{$max1}] overlaps with [{$min2}-{$max2}]."
                            ]
                        ]);
                    }
                }
            }
        }
    }

    protected function saveVariantsAndAddons(Tour $tour, array $requestData): void
    {
        // 1. Process Addons
        if (isset($requestData['addons'])) {
            $addonsData = $requestData['addons'];
            $updatedAddonIds = [];

            foreach ($addonsData as $addonData) {
                $addonId = $addonData['id'] ?? null;
                $addonFields = [
                    'name' => $addonData['name_en'] ?? $addonData['name'] ?? '',
                    'name_en' => $addonData['name_en'] ?? null,
                    'name_vi' => $addonData['name_vi'] ?? null,
                    'description' => $addonData['description_en'] ?? $addonData['description'] ?? null,
                    'description_en' => $addonData['description_en'] ?? null,
                    'description_vi' => $addonData['description_vi'] ?? null,
                    'price' => $addonData['price'] ?? 0,
                    'pricing_type' => $addonData['pricing_type'] ?? 'per_person',
                    'sort_order' => $addonData['sort_order'] ?? 0,
                    'status' => $addonData['status'] ?? 'active',
                ];

                if ($addonId) {
                    $addon = $tour->addons()->findOrFail($addonId);
                    $addon->update($addonFields);
                } else {
                    $addon = $tour->addons()->create($addonFields);
                }
                $updatedAddonIds[] = $addon->id;
            }

            $tour->addons()->whereNotIn('id', $updatedAddonIds)->delete();
        }

        // 2. Process Variants
        if (isset($requestData['variants'])) {
            $variantsData = $requestData['variants'];
            $updatedVariantIds = [];

            foreach ($variantsData as $variantData) {
                $variantId = $variantData['id'] ?? null;
                $variantFields = [
                    'name' => $variantData['name_en'] ?? $variantData['name'] ?? '',
                    'name_en' => $variantData['name_en'] ?? null,
                    'name_vi' => $variantData['name_vi'] ?? null,
                    'description' => $variantData['description_en'] ?? $variantData['description'] ?? null,
                    'description_en' => $variantData['description_en'] ?? null,
                    'description_vi' => $variantData['description_vi'] ?? null,
                    'status' => $variantData['status'] ?? 'active',
                    'sort_order' => $variantData['sort_order'] ?? 0,
                    'is_default' => filter_var($variantData['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'min_participants' => $variantData['min_participants'] ?? null,
                    'max_participants' => $variantData['max_participants'] ?? null,
                ];

                if ($variantId) {
                    $variant = $tour->variants()->findOrFail($variantId);
                    $variant->update($variantFields);
                } else {
                    $variant = $tour->variants()->create($variantFields);
                }
                $updatedVariantIds[] = $variant->id;

                if (isset($variantData['price_tiers'])) {
                    $tiersData = $variantData['price_tiers'];
                    $updatedTierIds = [];

                    foreach ($tiersData as $tierData) {
                        $tierId = $tierData['id'] ?? null;
                        $tierFields = [
                            'min_participants' => $tierData['min_participants'],
                            'max_participants' => $tierData['max_participants'],
                            'pricing_type' => $tierData['pricing_type'] ?? 'per_person',
                            'price' => $tierData['price'],
                            'discount_price' => $tierData['discount_price'] ?? null,
                            'label' => $tierData['label'] ?? null,
                            'sort_order' => $tierData['sort_order'] ?? 0,
                            'status' => $tierData['status'] ?? 'active',
                        ];

                        if ($tierId) {
                            $tier = $variant->priceTiers()->findOrFail($tierId);
                            $tier->update($tierFields);
                        } else {
                            $tier = $variant->priceTiers()->create($tierFields);
                        }
                        $updatedTierIds[] = $tier->id;
                    }

                    $variant->priceTiers()->whereNotIn('id', $updatedTierIds)->delete();
                }
            }

            $tour->variants()->whereNotIn('id', $updatedVariantIds)->delete();
            
            if ($tour->variants()->where('is_default', true)->count() > 1) {
                $firstDefaultId = $tour->variants()->where('is_default', true)->orderBy('id')->first()->id;
                $tour->variants()->where('id', '!=', $firstDefaultId)->update(['is_default' => false]);
            }
        }
    }

    protected function prepareLocalizedFields(array $data, ?Tour $tour = null): array
    {
        $current = $tour?->toArray() ?? [];

        $data['name_en'] = $data['name_en'] ?? $current['name_en'] ?? $current['name'] ?? null;
        $data['name_vi'] = $data['name_vi'] ?? $current['name_vi'] ?? $current['name'] ?? $data['name_en'] ?? null;

        $data['slug_en'] = $data['slug_en']
            ?? $current['slug_en']
            ?? $this->generateSlugSegment($data['name_en']);

        $data['slug_vi'] = $data['slug_vi']
            ?? $current['slug_vi']
            ?? $this->generateSlugSegment($data['name_vi'] ?? $data['name_en']);

        $data['description_en'] = $data['description_en'] ?? $current['description_en'] ?? $current['description'] ?? null;
        $data['description_vi'] = $data['description_vi'] ?? $current['description_vi'] ?? $current['description'] ?? $data['description_en'] ?? null;

        $data['meta_title_en'] = $data['meta_title_en'] ?? $current['meta_title_en'] ?? $current['meta_title'] ?? null;
        $data['meta_title_vi'] = $data['meta_title_vi'] ?? $current['meta_title_vi'] ?? $current['meta_title'] ?? $data['meta_title_en'] ?? null;

        $data['meta_description_en'] = $data['meta_description_en'] ?? $current['meta_description_en'] ?? $current['meta_description'] ?? null;
        $data['meta_description_vi'] = $data['meta_description_vi'] ?? $current['meta_description_vi'] ?? $current['meta_description'] ?? $data['meta_description_en'] ?? null;

        $data['meta_keywords_en'] = $data['meta_keywords_en'] ?? $current['meta_keywords_en'] ?? $current['meta_keywords'] ?? null;
        $data['meta_keywords_vi'] = $data['meta_keywords_vi'] ?? $current['meta_keywords_vi'] ?? $current['meta_keywords'] ?? $data['meta_keywords_en'] ?? null;

        $data['name'] = $data['name_en'] ?? $data['name_vi'] ?? $data['name'] ?? null;
        $data['description'] = $data['description_en'] ?? $data['description_vi'] ?? $data['description'] ?? null;
        $data['slug'] = $data['slug_en'] ?? $data['slug'] ?? $current['slug'] ?? $this->generateSlugSegment($data['name']);
        $data['meta_title'] = $data['meta_title_en'] ?? $data['meta_title_vi'] ?? $data['meta_title'] ?? null;
        $data['meta_description'] = $data['meta_description_en'] ?? $data['meta_description_vi'] ?? $data['meta_description'] ?? null;
        $data['meta_keywords'] = $data['meta_keywords_en'] ?? $data['meta_keywords_vi'] ?? $data['meta_keywords'] ?? null;

        // Includes / Excludes / Itinerary bilingual
        $data['includes_en'] = $data['includes_en'] ?? $current['includes_en'] ?? $current['includes'] ?? null;
        $data['includes_vi'] = !empty($data['includes_vi']) ? $data['includes_vi'] : (!empty($current['includes_vi']) ? $current['includes_vi'] : $data['includes_en']);
        $data['includes'] = !empty($data['includes_en']) ? $data['includes_en'] : (!empty($data['includes']) ? $data['includes'] : $current['includes'] ?? null);

        $data['excludes_en'] = $data['excludes_en'] ?? $current['excludes_en'] ?? $current['excludes'] ?? null;
        $data['excludes_vi'] = !empty($data['excludes_vi']) ? $data['excludes_vi'] : (!empty($current['excludes_vi']) ? $current['excludes_vi'] : $data['excludes_en']);
        $data['excludes'] = !empty($data['excludes_en']) ? $data['excludes_en'] : (!empty($data['excludes']) ? $data['excludes'] : $current['excludes'] ?? null);

        $data['itinerary_en'] = $data['itinerary_en'] ?? $current['itinerary_en'] ?? $current['itinerary'] ?? null;
        $data['itinerary_vi'] = !empty($data['itinerary_vi']) ? $data['itinerary_vi'] : (!empty($current['itinerary_vi']) ? $current['itinerary_vi'] : $data['itinerary_en']);
        $data['itinerary'] = !empty($data['itinerary_en']) ? $data['itinerary_en'] : (!empty($data['itinerary']) ? $data['itinerary'] : $current['itinerary'] ?? null);

        return $data;
    }

    protected function generateSlugSegment(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        return Str::slug($value) . '-' . Str::random(5);
    }

    protected function hydrateImagesFromAlbum(array $data): array
    {
        $albumId = $data['media_album_id'] ?? null;

        if (!$albumId) {
            return $data;
        }

        $album = MediaAlbum::with('coverMedia')->find($albumId);

        if (!$album) {
            return $data;
        }

        $mediaIds = collect($album->media_ids ?? [])
            ->filter()
            ->values();

        $albumMedia = collect();

        if ($mediaIds->isNotEmpty()) {
            $albumMedia = $this->getOrderedMediaCollection($mediaIds->all());
            if (empty($data['images'])) {
                $data['images'] = $albumMedia->pluck('url')->toArray();
            }
        }

        if (empty($data['cover_image'])) {
            $coverId = $album->cover_media_id ?? $mediaIds->first();
            if ($coverId) {
                $coverMedia = $albumMedia->firstWhere('id', $coverId) ?? Media::find($coverId);
                if ($coverMedia) {
                    $data['cover_image'] = $coverMedia->url;
                    $data['cover_media_id'] = $coverMedia->id;
                }
            }
        }

        return $data;
    }

    protected function ensureCoverImageData(array $data): array
    {
        if (!empty($data['cover_media_id']) && empty($data['cover_image'])) {
            $media = Media::find($data['cover_media_id']);
            if ($media) {
                $data['cover_image'] = $media->url;
            }
        }

        if (!empty($data['cover_image']) && empty($data['cover_media_id'])) {
            $media = Media::where('url', $data['cover_image'])->first();
            if ($media) {
                $data['cover_media_id'] = $media->id;
            }
        }

        if (empty($data['cover_image']) && !empty($data['images'])) {
            $data['cover_image'] = $data['images'][0];
        }

        return $data;
    }

    protected function getOrderedMediaCollection(array $mediaIds)
    {
        if (empty($mediaIds)) {
            return collect();
        }

        $orderMap = array_flip($mediaIds);

        return Media::whereIn('id', $mediaIds)
            ->get()
            ->sortBy(fn (Media $media) => $orderMap[$media->id] ?? PHP_INT_MAX)
            ->values();
    }
}
