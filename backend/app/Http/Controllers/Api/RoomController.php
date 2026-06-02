<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $locale = $this->normalizeLocale(
            $request->get('lang'),
            $request->header('Accept-Language')
        );

        $query = Room::query()
            ->with('roomCategory')
            ->where('status', 'available');
        if ($request->filled('room_category_id')) {
            $query->where('room_category_id', $request->room_category_id);
        }

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('capacity') && $request->capacity > 0) {
            $query->where('capacity', '>=', $request->capacity);
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_night', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_night', '<=', $request->max_price);
        }

        // Check availability
        if ($request->filled('check_in') && $request->filled('check_out')) {
            $checkIn = $request->check_in;
            $checkOut = $request->check_out;

            $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->where('status', '!=', 'cancelled')
                    ->where(function ($query) use ($checkIn, $checkOut) {
                        $query->whereBetween('check_in', [$checkIn, $checkOut])
                            ->orWhereBetween('check_out', [$checkIn, $checkOut])
                            ->orWhere(function ($q) use ($checkIn, $checkOut) {
                                $q->where('check_in', '<=', $checkIn)
                                    ->where('check_out', '>=', $checkOut);
                            });
                    });
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->orderBy($sortBy, $sortOrder);

        $rooms = $query
            ->with([
                'reviews',
                'bookings' => function ($relation) {
                    $relation->select([
                        'id',
                        'room_id',
                        'booking_number',
                        'check_in',
                        'check_out',
                        'status',
                        'payment_status',
                        'final_amount',
                        'guest_name',
                    ])
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->whereDate('check_out', '>=', Carbon::now()->toDateString())
                    ->orderBy('check_in');
                },
            ])
            ->paginate($request->get('per_page', 12));

        // Add average rating to each room
        $rooms->getCollection()->transform(function ($room) use ($locale) {
            $room->average_rating = $room->averageRating();
            $room->reviews_count = $room->reviews()->count();
            return $this->transformRoom($room, $locale);
        });

        return response()->json($rooms);
    }

    public function show($id)
    {
        $room = Room::with([
            'reviews.user',
            'roomCategory',
            'mediaAlbum',
            'bookings' => function ($relation) {
                $relation->select([
                    'id',
                    'room_id',
                    'booking_number',
                    'check_in',
                    'check_out',
                    'status',
                    'payment_status',
                    'final_amount',
                    'guest_name',
                ])
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereDate('check_out', '>=', Carbon::now()->subMonths(1)->toDateString())
                ->orderBy('check_in');
            },
        ])->findOrFail($id);
        $locale = $this->normalizeLocale(
            request()->get('lang'),
            request()->header('Accept-Language')
        );
        
        // Increment view count
        $room->increment('view_count');

        $room->average_rating = $room->averageRating();
        $room->reviews_count = $room->reviews()->count();
        $this->transformRoom($room, $locale);

        return response()->json($room);
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
        ]);

        $room = Room::findOrFail($request->room_id);

        $checkIn = $request->check_in;
        $checkOut = $request->check_out;

        $isAvailable = $room->isAvailable($checkIn, $checkOut);

        return response()->json([
            'available' => $isAvailable,
            'room_id' => $room->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
        ]);
    }

    protected function normalizeLocale(?string $locale, ?string $headerLocale = null): string
    {
        $resolvedLocale = $locale ?? $headerLocale;

        if ($resolvedLocale && str_starts_with(strtolower($resolvedLocale), 'vi')) {
            return 'vi';
        }

        return 'en';
    }

    protected function transformRoom(Room $room, string $locale, bool $showFullCalendar = false): Room
    {
        $isVietnamese = $locale === 'vi';

        $room->name = $isVietnamese ? ($room->name_vi ?? $room->name) : ($room->name_en ?? $room->name);
        $room->slug = $isVietnamese ? ($room->slug_vi ?? $room->slug) : ($room->slug_en ?? $room->slug);
        $room->description = $isVietnamese ? ($room->description_vi ?? $room->description) : ($room->description_en ?? $room->description);
        $room->meta_title = $isVietnamese ? ($room->meta_title_vi ?? $room->meta_title) : ($room->meta_title_en ?? $room->meta_title);
        $room->meta_description = $isVietnamese ? ($room->meta_description_vi ?? $room->meta_description) : ($room->meta_description_en ?? $room->meta_description);
        $room->meta_keywords = $isVietnamese ? ($room->meta_keywords_vi ?? $room->meta_keywords) : ($room->meta_keywords_en ?? $room->meta_keywords);

        if ($room->relationLoaded('bookings')) {
            $room->setAttribute('occupancy', $this->buildOccupancyMeta($room));

            if ($showFullCalendar) {
                $room->setAttribute('booking_calendar', $this->buildBookingCalendar($room, $showFullCalendar));
            }

            $room->unsetRelation('bookings');
        }

        if ($room->relationLoaded('mediaAlbum')) {
            $this->attachAlbumMedia($room);
        }

        return $room;
    }

    protected function attachAlbumMedia(Room $room): void
    {
        $album = $room->getRelation('mediaAlbum');

        if (!$album) {
            return;
        }

        $albumData = $this->transformAlbum($album);
        $room->setAttribute('media_album', $albumData);

        if (empty($room->images) && !empty($albumData['media_items'])) {
            $room->images = collect($albumData['media_items'])->pluck('url')->filter()->values()->all();
        }

        if (empty($room->cover_image) && !empty($albumData['cover_image'])) {
            $room->cover_image = $albumData['cover_image'];
        }

        $room->unsetRelation('mediaAlbum');
    }

    protected function transformAlbum($album): array
    {
        $mediaIds = collect($album->media_ids ?? [])
            ->filter()
            ->unique()
            ->values();

        $items = collect();

        if ($mediaIds->isNotEmpty()) {
            $orderMap = $mediaIds->flip();
            $items = Media::whereIn('id', $mediaIds)
                ->get()
                ->sortBy(fn ($media) => $orderMap[$media->id] ?? PHP_INT_MAX)
                ->values()
                ->map(fn ($media) => [
                    'id' => $media->id,
                    'url' => $media->url,
                    'original_name' => $media->original_name,
                    'alt_text' => $media->alt_text,
                    'width' => $media->width,
                    'height' => $media->height,
                ]);
        }

        $coverItem = $items->firstWhere('id', $album->cover_media_id) ?? $items->first();

        return [
            'id' => $album->id,
            'name' => $album->name,
            'slug' => $album->slug,
            'description' => $album->description,
            'cover_media_id' => $album->cover_media_id,
            'cover_image' => $coverItem['url'] ?? null,
            'media_ids' => $mediaIds,
            'media_items' => $items,
            'media_count' => count($album->media_ids ?? []),
        ];
    }

    protected function buildOccupancyMeta(Room $room): array
    {
        $bookings = $room->getRelation('bookings');
        $now = Carbon::now();

        $active = $bookings->filter(function ($booking) use ($now) {
            return $booking->check_in && $booking->check_out && $now->between($booking->check_in, $booking->check_out);
        })->first();

        $next = $bookings->filter(fn ($booking) => $booking->check_in && $booking->check_in->greaterThanOrEqualTo($now))
            ->sortBy('check_in')
            ->first();

        return [
            'is_currently_occupied' => (bool) $active,
            'current_stay' => $active ? [
                'booking_number' => $active->booking_number,
                'guest_name' => $active->guest_name,
                'check_in' => $active->check_in?->toDateString(),
                'check_out' => $active->check_out?->toDateString(),
            ] : null,
            'next_check_in' => $next?->check_in?->toDateString(),
            'next_check_out' => $next?->check_out?->toDateString(),
            'upcoming_count' => $bookings->count(),
        ];
    }

    protected function buildBookingCalendar(Room $room, bool $showFullCalendar = false)
    {
        $bookings = $room->getRelation('bookings');

        $collection = $showFullCalendar ? $bookings : $bookings->take(5);

        return $collection->map(function ($booking) {
            return [
                'id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'status' => $booking->status,
                'payment_status' => $booking->payment_status,
                'guest_name' => $booking->guest_name,
                'check_in' => $booking->check_in?->toDateString(),
                'check_out' => $booking->check_out?->toDateString(),
                'final_amount' => $booking->final_amount,
            ];
        })->values();
    }
}
