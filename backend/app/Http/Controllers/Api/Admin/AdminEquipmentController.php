<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Media;
use App\Models\MediaAlbum;
use Illuminate\Http\Request;

class AdminEquipmentController extends Controller
{
    public function index(Request $request)
    {
        $equipments = Equipment::query()
            ->with('equipmentCategory')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->orWhere('name_vi', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('equipment_category_id'), function ($query) use ($request) {
                $query->where('equipment_category_id', $request->equipment_category_id);
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('is_available'), function ($query) use ($request) {
                $available = filter_var($request->input('is_available'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if (!is_null($available)) {
                    $query->where('is_available', $available);
                }
            })
            ->when($request->filled('is_rentable'), function ($query) use ($request) {
                $query->where('is_rentable', filter_var($request->input('is_rentable'), FILTER_VALIDATE_BOOLEAN));
            })
            ->when($request->filled('is_sellable'), function ($query) use ($request) {
                $query->where('is_sellable', filter_var($request->input('is_sellable'), FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json($equipments);
    }

    public function store(Request $request)
    {
        $data = $this->validateEquipment($request);
        $data = $this->prepareLocalizedFields($data);
        $data = $this->hydrateImagesFromAlbum($data);
        $data = $this->ensureCoverImageData($data);
        $data['images'] = $data['images'] ?? [];

        $equipment = Equipment::create($data);

        return response()->json([
            'message' => 'Equipment created successfully',
            'equipment' => $equipment,
        ], 201);
    }

    public function show(Equipment $equipment)
    {
        $equipment->load(['equipmentCategory', 'mediaAlbum']);

        return response()->json($equipment);
    }

    public function update(Request $request, Equipment $equipment)
    {
        $data = $this->validateEquipment($request, true);
        $data = $this->prepareLocalizedFields($data, $equipment);
        $data = $this->hydrateImagesFromAlbum($data);
        $data = $this->ensureCoverImageData($data);
        $equipment->update($data);

        return response()->json([
            'message' => 'Equipment updated successfully',
            'equipment' => $equipment,
        ]);
    }

    public function destroy(Equipment $equipment)
    {
        $equipment->delete();

        return response()->json([
            'message' => 'Equipment deleted successfully',
        ]);
    }

    protected function validateEquipment(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'name' => ($isUpdate ? 'sometimes' : 'required') . '|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_vi' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'short_description_en' => 'nullable|string|max:500',
            'short_description_vi' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_vi' => 'nullable|string',
            'rental_price_per_day' => ($isUpdate ? 'sometimes' : 'required') . '|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'is_rentable' => 'nullable|boolean',
            'is_sellable' => 'nullable|boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'string|max:2048',
            'cover_image' => 'nullable|string|max:2048',
            'cover_media_id' => 'nullable|exists:media,id',
            'media_album_id' => 'nullable|exists:media_albums,id',
            'equipment_category_id' => 'nullable|exists:equipment_categories,id',
            'status' => 'nullable|in:active,inactive',
            'is_available' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];

        return $request->validate($rules);
    }

    protected function prepareLocalizedFields(array $data, ?Equipment $equipment = null): array
    {
        $data['name_en'] = $data['name_en'] ?? $equipment?->name_en ?? $data['name'] ?? $equipment?->name;
        $data['name_vi'] = $data['name_vi'] ?? $equipment?->name_vi ?? $data['name'] ?? $equipment?->name;
        $data['short_description_en'] = $data['short_description_en'] ?? $equipment?->short_description_en ?? $data['short_description'] ?? $equipment?->short_description;
        $data['short_description_vi'] = $data['short_description_vi'] ?? $equipment?->short_description_vi ?? $data['short_description'] ?? $equipment?->short_description;
        $data['description_en'] = $data['description_en'] ?? $equipment?->description_en ?? $data['description'] ?? $equipment?->description;
        $data['description_vi'] = $data['description_vi'] ?? $equipment?->description_vi ?? $data['description'] ?? $equipment?->description;

        if (!isset($data['name'])) {
            $data['name'] = $data['name_en'] ?? $data['name_vi'] ?? $equipment?->name;
        }
        if (!isset($data['short_description'])) {
            $data['short_description'] = $data['short_description_en'] ?? $data['short_description_vi'] ?? $equipment?->short_description;
        }
        if (!isset($data['description'])) {
            $data['description'] = $data['description_en'] ?? $data['description_vi'] ?? $equipment?->description;
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
