<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarRental;
use App\Models\Media;
use App\Models\MediaAlbum;
use Illuminate\Http\Request;

class AdminCarRentalController extends Controller
{
    public function index(Request $request)
    {
        $cars = CarRental::query()
            ->with('carRentalCategory')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('car_rental_category_id'), function ($query) use ($request) {
                $query->where('car_rental_category_id', $request->car_rental_category_id);
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('is_available'), function ($query) use ($request) {
                $available = filter_var($request->input('is_available'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if (!is_null($available)) {
                    $query->where('is_available', $available);
                }
            })
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json($cars);
    }

    public function store(Request $request)
    {
        $data = $this->validateCarRental($request);
        $data = $this->prepareLocalizedFields($data);
        $data = $this->hydrateImagesFromAlbum($data);
        $data = $this->ensureCoverImageData($data);
        $data['features'] = $data['features'] ?? [];
        $data['images'] = $data['images'] ?? [];

        $car = CarRental::create($data);

        return response()->json([
            'message' => 'Car rental created successfully',
            'car' => $car,
        ], 201);
    }

    public function show(CarRental $carRental)
    {
        $carRental->load(['carRentalCategory', 'mediaAlbum']);

        return response()->json($carRental);
    }

    public function update(Request $request, CarRental $carRental)
    {
        $data = $this->validateCarRental($request, true);
        $data = $this->prepareLocalizedFields($data, $carRental);
        $data = $this->hydrateImagesFromAlbum($data);
        $data = $this->ensureCoverImageData($data);
        $carRental->update($data);

        return response()->json([
            'message' => 'Car rental updated successfully',
            'car' => $carRental,
        ]);
    }

    public function destroy(CarRental $carRental)
    {
        $carRental->delete();

        return response()->json([
            'message' => 'Car rental deleted successfully',
        ]);
    }

    protected function validateCarRental(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'name' => ($isUpdate ? 'sometimes' : 'required') . '|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_vi' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:120',
            'brand_en' => 'nullable|string|max:120',
            'brand_vi' => 'nullable|string|max:120',
            'model' => 'nullable|string|max:120',
            'model_en' => 'nullable|string|max:120',
            'model_vi' => 'nullable|string|max:120',
            'type' => 'nullable|string|max:120',
            'type_en' => 'nullable|string|max:120',
            'type_vi' => 'nullable|string|max:120',
            'seats' => 'nullable|integer|min:1',
            'transmission' => 'nullable|string|max:120',
            'transmission_en' => 'nullable|string|max:120',
            'transmission_vi' => 'nullable|string|max:120',
            'fuel_type' => 'nullable|string|max:120',
            'fuel_type_en' => 'nullable|string|max:120',
            'fuel_type_vi' => 'nullable|string|max:120',
            'price_per_day' => ($isUpdate ? 'sometimes' : 'required') . '|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'location_en' => 'nullable|string|max:255',
            'location_vi' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'short_description_en' => 'nullable|string|max:500',
            'short_description_vi' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_vi' => 'nullable|string',
            'features' => 'nullable|array',
            'features.*' => 'string|max:120',
            'images' => 'nullable|array',
            'images.*' => 'string|max:2048',
            'cover_image' => 'nullable|string|max:2048',
            'cover_media_id' => 'nullable|exists:media,id',
            'media_album_id' => 'nullable|exists:media_albums,id',
            'status' => 'nullable|in:active,inactive',
            'is_available' => 'nullable|boolean',
            'average_rating' => 'nullable|numeric|min:0|max:5',
            'total_reviews' => 'nullable|integer|min:0',
            'contact_phone' => 'nullable|string|max:50',
            'contact_whatsapp' => 'nullable|string|max:50',
            'car_rental_category_id' => 'nullable|exists:car_rental_categories,id',
        ];

        return $request->validate($rules);
    }

    protected function prepareLocalizedFields(array $data, ?CarRental $carRental = null): array
    {
        $data['name_en'] = $data['name_en'] ?? $carRental?->name_en ?? $data['name'] ?? $carRental?->name;
        $data['name_vi'] = $data['name_vi'] ?? $carRental?->name_vi ?? $data['name'] ?? $carRental?->name;
        $data['brand_en'] = $data['brand_en'] ?? $carRental?->brand_en ?? $data['brand'] ?? $carRental?->brand;
        $data['brand_vi'] = $data['brand_vi'] ?? $carRental?->brand_vi ?? $data['brand'] ?? $carRental?->brand;
        $data['model_en'] = $data['model_en'] ?? $carRental?->model_en ?? $data['model'] ?? $carRental?->model;
        $data['model_vi'] = $data['model_vi'] ?? $carRental?->model_vi ?? $data['model'] ?? $carRental?->model;
        $data['type_en'] = $data['type_en'] ?? $carRental?->type_en ?? $data['type'] ?? $carRental?->type;
        $data['type_vi'] = $data['type_vi'] ?? $carRental?->type_vi ?? $data['type'] ?? $carRental?->type;
        $data['transmission_en'] = $data['transmission_en'] ?? $carRental?->transmission_en ?? $data['transmission'] ?? $carRental?->transmission;
        $data['transmission_vi'] = $data['transmission_vi'] ?? $carRental?->transmission_vi ?? $data['transmission'] ?? $carRental?->transmission;
        $data['fuel_type_en'] = $data['fuel_type_en'] ?? $carRental?->fuel_type_en ?? $data['fuel_type'] ?? $carRental?->fuel_type;
        $data['fuel_type_vi'] = $data['fuel_type_vi'] ?? $carRental?->fuel_type_vi ?? $data['fuel_type'] ?? $carRental?->fuel_type;
        $data['location_en'] = $data['location_en'] ?? $carRental?->location_en ?? $data['location'] ?? $carRental?->location;
        $data['location_vi'] = $data['location_vi'] ?? $carRental?->location_vi ?? $data['location'] ?? $carRental?->location;
        $data['short_description_en'] = $data['short_description_en'] ?? $carRental?->short_description_en ?? $data['short_description'] ?? $carRental?->short_description;
        $data['short_description_vi'] = $data['short_description_vi'] ?? $carRental?->short_description_vi ?? $data['short_description'] ?? $carRental?->short_description;
        $data['description_en'] = $data['description_en'] ?? $carRental?->description_en ?? $data['description'] ?? $carRental?->description;
        $data['description_vi'] = $data['description_vi'] ?? $carRental?->description_vi ?? $data['description'] ?? $carRental?->description;

        if (!isset($data['name'])) {
            $data['name'] = $data['name_en'] ?? $data['name_vi'] ?? $carRental?->name;
        }
        if (!isset($data['brand'])) {
            $data['brand'] = $data['brand_en'] ?? $data['brand_vi'] ?? $carRental?->brand;
        }
        if (!isset($data['model'])) {
            $data['model'] = $data['model_en'] ?? $data['model_vi'] ?? $carRental?->model;
        }
        if (!isset($data['type'])) {
            $data['type'] = $data['type_en'] ?? $data['type_vi'] ?? $carRental?->type;
        }
        if (!isset($data['transmission'])) {
            $data['transmission'] = $data['transmission_en'] ?? $data['transmission_vi'] ?? $carRental?->transmission;
        }
        if (!isset($data['fuel_type'])) {
            $data['fuel_type'] = $data['fuel_type_en'] ?? $data['fuel_type_vi'] ?? $carRental?->fuel_type;
        }
        if (!isset($data['location'])) {
            $data['location'] = $data['location_en'] ?? $data['location_vi'] ?? $carRental?->location;
        }
        if (!isset($data['short_description'])) {
            $data['short_description'] = $data['short_description_en'] ?? $data['short_description_vi'] ?? $carRental?->short_description;
        }
        if (!isset($data['description'])) {
            $data['description'] = $data['description_en'] ?? $data['description_vi'] ?? $carRental?->description;
        }

        return $data;
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
        $orderMap = collect($mediaIds)->flip();
        return Media::whereIn('id', $mediaIds)
            ->get()
            ->sortBy(fn ($media) => $orderMap[$media->id] ?? PHP_INT_MAX)
            ->values();
    }
}
