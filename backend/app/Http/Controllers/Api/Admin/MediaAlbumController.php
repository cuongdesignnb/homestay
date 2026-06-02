<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaAlbum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MediaAlbumController extends Controller
{
    public function index(Request $request)
    {
        $query = MediaAlbum::query()->with('coverMedia');

        if ($search = $request->query('q')) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $albums = $query
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        $albums->getCollection()->transform(function (MediaAlbum $album) {
            return $this->transformAlbum($album);
        });

        return response()->json($albums);
    }

    public function store(Request $request)
    {
        $data = $this->validateAlbum($request);

        $album = MediaAlbum::create($data);

        return response()->json([
            'message' => 'Album created',
            'album' => $this->transformAlbum($album)
        ], 201);
    }

    public function show(MediaAlbum $mediaAlbum)
    {
        return response()->json($this->transformAlbum($mediaAlbum));
    }

    public function update(Request $request, MediaAlbum $mediaAlbum)
    {
        $data = $this->validateAlbum($request, $mediaAlbum->id);
        $mediaAlbum->update($data);

        return response()->json([
            'message' => 'Album updated',
            'album' => $this->transformAlbum($mediaAlbum)
        ]);
    }

    public function destroy(MediaAlbum $mediaAlbum)
    {
        $mediaAlbum->delete();

        return response()->json([
            'message' => 'Album deleted'
        ]);
    }

    protected function validateAlbum(Request $request, ?int $albumId = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('media_albums', 'slug')->ignore($albumId),
            ],
            'description' => 'nullable|string',
            'cover_media_id' => 'nullable|exists:media,id',
            'media_ids' => 'required|array|min:1',
            'media_ids.*' => 'integer|exists:media,id',
        ]);

        $slug = $validated['slug'] ?? null;
        if (empty($slug)) {
            $slug = Str::slug($validated['name']);
        }

        $validated['slug'] = $this->generateUniqueSlug($slug, $albumId);

        return $validated;
    }

    protected function generateUniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = Str::slug($baseSlug) ?: Str::random(6);
        $original = $slug;
        $counter = 1;

        while (
            MediaAlbum::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original . '-' . $counter++;
        }

        return $slug;
    }

    protected function transformAlbum(MediaAlbum $album): array
    {
        $mediaIds = collect($album->media_ids ?? [])
            ->unique()
            ->values();

        $items = collect();

        if ($mediaIds->isNotEmpty()) {
            $orderMap = $mediaIds->flip();
            $items = Media::whereIn('id', $mediaIds)
                ->get()
                ->sortBy(fn (Media $media) => $orderMap[$media->id] ?? PHP_INT_MAX)
                ->values()
                ->map(fn (Media $media) => [
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
            'cover_media' => $coverItem,
            'cover_image' => $coverItem['url'] ?? null,
            'media_ids' => $mediaIds,
            'media_items' => $items,
            'media_count' => count($mediaIds),
            'created_at' => $album->created_at,
            'updated_at' => $album->updated_at,
        ];
    }
}
