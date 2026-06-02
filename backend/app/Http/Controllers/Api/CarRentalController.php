<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarRental;
use App\Models\Media;
use Illuminate\Http\Request;

class CarRentalController extends Controller
{
    /**
     * Display a listing of car rentals with filters.
     */
    public function index(Request $request)
    {
        $locale = $this->normalizeLocale(
            $request->get('lang'),
            $request->header('Accept-Language')
        );

        $query = CarRental::active()->with('carRentalCategory');

        if ($request->filled('car_rental_category_id')) {
            $query->where('car_rental_category_id', $request->input('car_rental_category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->input('brand'));
        }

        if ($request->filled('transmission')) {
            $query->where('transmission', $request->input('transmission'));
        }

        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->input('fuel_type'));
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->input('location') . '%');
        }

        if ($request->filled('min_seats')) {
            $query->where('seats', '>=', (int) $request->input('min_seats'));
        }

        if ($request->filled('max_seats')) {
            $query->where('seats', '<=', (int) $request->input('max_seats'));
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_day', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_day', '<=', (float) $request->input('max_price'));
        }

        if ($request->filled('min_rating')) {
            $query->where('average_rating', '>=', (float) $request->input('min_rating'));
        }

        if ($request->has('available')) {
            $available = filter_var($request->input('available'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (!is_null($available)) {
                $query->where('is_available', $available);
            }
        }

        $features = $request->input('features');
        if (!empty($features)) {
            if (is_string($features)) {
                $features = array_filter(array_map('trim', explode(',', $features)));
            }
            if (is_array($features)) {
                foreach ($features as $feature) {
                    $query->whereJsonContains('features', $feature);
                }
            }
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSort = [
            'price_per_day',
            'average_rating',
            'seats',
            'created_at',
        ];

        if (!in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortDir);

        $perPage = (int) $request->input('per_page', 12);
        $cars = $query->with('mediaAlbum')->paginate($perPage);

        $cars->getCollection()->transform(function ($car) use ($locale) {
            $this->applyCarLocale($car, $locale);
            if ($car->relationLoaded('mediaAlbum')) {
                $this->attachAlbumMedia($car);
            }
            return $car;
        });

        return response()->json([
            'success' => true,
            'data' => $cars,
        ]);
    }

    /**
     * Display the specified car rental.
     */
    public function show($id)
    {
        $car = CarRental::active()
            ->with(['carRentalCategory', 'mediaAlbum'])
            ->find($id);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'Car rental not found',
            ], 404);
        }

        $locale = $this->normalizeLocale(
            request()->get('lang'),
            request()->header('Accept-Language')
        );

        $this->applyCarLocale($car, $locale);
        if ($car->relationLoaded('mediaAlbum')) {
            $this->attachAlbumMedia($car);
        }

        return response()->json([
            'success' => true,
            'data' => $car,
        ]);
    }

    /**
     * Get featured car rentals for homepage.
     */
    public function featured()
    {
        $locale = $this->normalizeLocale(
            request()->get('lang'),
            request()->header('Accept-Language')
        );

        $cars = CarRental::active()
            ->with('carRentalCategory')
            ->orderByDesc('average_rating')
            ->orderBy('price_per_day')
            ->take(6)
            ->get();

        $cars->transform(function ($car) use ($locale) {
            $this->applyCarLocale($car, $locale);
            return $car;
        });

        return response()->json([
            'success' => true,
            'data' => $cars,
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

    protected function applyCarLocale(CarRental $car, string $locale): CarRental
    {
        $isVietnamese = $locale === 'vi';

        $car->name = $isVietnamese ? ($car->name_vi ?? $car->name) : ($car->name_en ?? $car->name);
        $car->brand = $isVietnamese ? ($car->brand_vi ?? $car->brand) : ($car->brand_en ?? $car->brand);
        $car->model = $isVietnamese ? ($car->model_vi ?? $car->model) : ($car->model_en ?? $car->model);
        $car->type = $isVietnamese ? ($car->type_vi ?? $car->type) : ($car->type_en ?? $car->type);
        $car->transmission = $isVietnamese ? ($car->transmission_vi ?? $car->transmission) : ($car->transmission_en ?? $car->transmission);
        $car->fuel_type = $isVietnamese ? ($car->fuel_type_vi ?? $car->fuel_type) : ($car->fuel_type_en ?? $car->fuel_type);
        $car->location = $isVietnamese ? ($car->location_vi ?? $car->location) : ($car->location_en ?? $car->location);
        $car->short_description = $isVietnamese
            ? ($car->short_description_vi ?? $car->short_description)
            : ($car->short_description_en ?? $car->short_description);
        $car->description = $isVietnamese
            ? ($car->description_vi ?? $car->description)
            : ($car->description_en ?? $car->description);

        if ($car->relationLoaded('carRentalCategory') && $car->carRentalCategory) {
            $category = $car->carRentalCategory;
            $category->name = $isVietnamese
                ? ($category->name_vi ?? $category->name)
                : ($category->name_en ?? $category->name);
            $category->description = $isVietnamese
                ? ($category->description_vi ?? $category->description)
                : ($category->description_en ?? $category->description);
        }

        return $car;
    }

    protected function attachAlbumMedia(CarRental $car): void
    {
        $album = $car->getRelation('mediaAlbum');

        if (!$album) {
            return;
        }

        $albumData = $this->transformAlbum($album);
        $car->setAttribute('media_album', $albumData);

        if (empty($car->images) && !empty($albumData['media_items'])) {
            $car->images = collect($albumData['media_items'])->pluck('url')->filter()->values()->all();
        }

        if (empty($car->cover_image) && !empty($albumData['cover_image'])) {
            $car->cover_image = $albumData['cover_image'];
        }

        $car->unsetRelation('mediaAlbum');
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
}
