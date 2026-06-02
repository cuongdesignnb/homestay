<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MediaAlbum extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'cover_media_id',
        'media_ids',
    ];

    protected $casts = [
        'media_ids' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (MediaAlbum $album) {
            if (!$album->slug) {
                $album->slug = static::generateUniqueSlug($album->name ?? 'album');
            }
        });
    }

    protected static function generateUniqueSlug(string $value): string
    {
        $slug = Str::slug($value);
        $original = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $counter++;
        }

        return $slug;
    }

    public function coverMedia()
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function mediaItems()
    {
        return Media::whereIn('id', $this->media_ids ?? [])->get();
    }

    protected function mediaCount(): Attribute
    {
        return Attribute::get(fn () => count($this->media_ids ?? []));
    }
}
