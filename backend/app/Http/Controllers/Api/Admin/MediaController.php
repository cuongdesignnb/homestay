<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::query()->orderByDesc('created_at');

        if ($search = $request->query('q')) {
            $query->where('original_name', 'like', "%{$search}%");
        }

        $perPage = (int) $request->query('per_page', 24);
        $perPage = max(6, min($perPage, 60));

        return response()->json(
            $query->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,bmp,svg,webp,tiff,ico|max:10240',
        ]);

        $manager = new ImageManager(new Driver());
        $disk = Storage::disk('public');
        $directory = 'media/' . now()->format('Y/m');
        $disk->makeDirectory($directory);

        $created = [];
        $errors = [];

        foreach ($request->file('files') as $index => $file) {
            try {
                $image = $manager->read($file->getRealPath());
                $width = $image->width();
                $height = $image->height();

                $fileName = Str::uuid() . '.webp';
                $path = $directory . '/' . $fileName;
                $encoded = $image->encode(new WebpEncoder(quality: 85));
                $disk->put($path, (string) $encoded);

                $media = Media::create([
                    'user_id' => $request->user()?->id,
                    'original_name' => $file->getClientOriginalName(),
                    'file_name' => $fileName,
                    'mime_type' => 'image/webp',
                    'disk' => 'public',
                    'path' => $path,
                    'url' => $disk->url($path),
                    'size' => $encoded->size(),
                    'width' => $width,
                    'height' => $height,
                ]);

                $created[] = $media;
            } catch (\Throwable $e) {
                $errors[] = $file->getClientOriginalName() . ': ' . $e->getMessage();
            }
        }

        $status = count($created) > 0 ? 201 : 422;
        $response = [
            'message' => count($created) > 0 ? 'Uploaded successfully' : 'Upload failed',
            'media' => $created,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    public function destroy(Media $media)
    {
        $disk = Storage::disk($media->disk ?? 'public');
        if ($disk->exists($media->path)) {
            $disk->delete($media->path);
        }

        $media->delete();

        return response()->json([
            'message' => 'Media deleted',
        ]);
    }
}
