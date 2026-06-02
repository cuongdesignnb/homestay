<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaAlbum;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminRoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::query()
            ->with('roomCategory')
            ->when($request->status, fn ($query) => $query->where('status', $request->status))
            ->when($request->room_category_id, fn ($query) => $query->where('room_category_id', $request->room_category_id))
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json($rooms);
    }

    public function store(Request $request)
    {
        $data = $this->validateRoom($request);
        $data = $this->prepareLocalizedFields($data);
        $data = $this->hydrateImagesFromAlbum($data);
        $data = $this->ensureCoverImageData($data);

        $data['amenities'] = $data['amenities'] ?? [];
        $data['images'] = $data['images'] ?? [];

        $room = Room::create($data);
        $room->load('roomCategory');

        return response()->json([
            'message' => 'Room created successfully',
            'room' => $room,
        ], 201);
    }

    public function show(Room $room)
    {
        return response()->json($room);
    }

    public function update(Request $request, Room $room)
    {
        $data = $this->validateRoom($request, true);
        $data = $this->prepareLocalizedFields($data, $room);
        $data = $this->hydrateImagesFromAlbum($data);
        $data = $this->ensureCoverImageData($data);

        $room->update($data);

        $room->load('roomCategory');

        return response()->json([
            'message' => 'Room updated successfully',
            'room' => $room,
        ]);
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return response()->json([
            'message' => 'Room deleted successfully',
        ]);
    }

    public function uploadImages(Request $request, Room $room)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array',
            'images.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $images = $room->images ?? [];
        $room->images = array_values(array_unique(array_merge($images, $request->images)));
        $room->save();

        return response()->json([
            'message' => 'Images updated successfully',
            'images' => $room->images,
        ]);
    }

    public function deleteImage(Room $room, $imageId)
    {
        $images = $room->images ?? [];

        if (!isset($images[$imageId])) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        unset($images[$imageId]);
        $room->images = array_values($images);
        $room->save();

        return response()->json([
            'message' => 'Image removed successfully',
            'images' => $room->images,
        ]);
    }

    protected function validateRoom(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'name' => 'sometimes|string|max:255',
            'name_en' => ($isUpdate ? 'sometimes' : 'required') . '|string|max:255',
            'name_vi' => ($isUpdate ? 'sometimes' : 'nullable') . '|string|max:255',
            'slug_en' => 'nullable|string|max:255',
            'slug_vi' => 'nullable|string|max:255',
            'description' => 'sometimes|string',
            'description_en' => ($isUpdate ? 'sometimes' : 'required') . '|string',
            'description_vi' => ($isUpdate ? 'sometimes' : 'nullable') . '|string',
            'type' => ($isUpdate ? 'sometimes' : 'required') . '|string|max:100',
            'capacity' => ($isUpdate ? 'sometimes' : 'required') . '|integer|min:1',
            'size' => ($isUpdate ? 'sometimes' : 'required') . '|numeric|min:0',
            'beds' => ($isUpdate ? 'sometimes' : 'required') . '|integer|min:1',
            'bathrooms' => ($isUpdate ? 'sometimes' : 'required') . '|integer|min:1',
            'price_per_night' => ($isUpdate ? 'sometimes' : 'required') . '|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:120',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'cover_image' => 'nullable|string|max:2048',
            'cover_media_id' => 'nullable|exists:media,id',
            'media_album_id' => 'nullable|exists:media_albums,id',
            'status' => ($isUpdate ? 'sometimes' : 'required') . '|in:available,unavailable,maintenance',
            'room_category_id' => 'nullable|exists:room_categories,id',
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
        ];

        return $request->validate($rules);
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

    protected function prepareLocalizedFields(array $data, ?Room $room = null): array
    {
        $current = $room?->toArray() ?? [];

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

        return $data;
    }

    protected function generateSlugSegment(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        return Str::slug($value) . '-' . Str::random(5);
    }
}
