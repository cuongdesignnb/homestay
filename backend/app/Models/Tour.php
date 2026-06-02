<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tour extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_en',
        'name_vi',
        'slug',
        'slug_en',
        'slug_vi',
        'description',
        'description_en',
        'description_vi',
        'tour_category_id',
        'duration',
        'duration_unit',
        'price_per_person',
        'discount_price',
        'max_participants',
        'min_participants',
        'itinerary',
        'itinerary_en',
        'itinerary_vi',
        'includes',
        'includes_en',
        'includes_vi',
        'excludes',
        'excludes_en',
        'excludes_vi',
        'images',
        'cover_image',
        'cover_media_id',
        'media_album_id',
        'status',
        'departure_location',
        'destination_type',
        'difficulty_level',
        'age_restriction',
        'view_count',
        'sort_order',
        'meta_title',
        'meta_title_en',
        'meta_title_vi',
        'meta_description',
        'meta_description_en',
        'meta_description_vi',
        'meta_keywords',
        'meta_keywords_en',
        'meta_keywords_vi',
    ];

    protected $casts = [
        'itinerary' => 'array',
        'itinerary_en' => 'array',
        'itinerary_vi' => 'array',
        'includes' => 'array',
        'includes_en' => 'array',
        'includes_vi' => 'array',
        'excludes' => 'array',
        'excludes_en' => 'array',
        'excludes_vi' => 'array',
        'images' => 'array',
        'cover_media_id' => 'integer',
        'media_album_id' => 'integer',
        'price_per_person' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'max_participants' => 'integer',
        'min_participants' => 'integer',
        'duration' => 'integer',
        'view_count' => 'integer',
        'sort_order' => 'integer',
        'tour_category_id' => 'integer',
    ];

    public function bookings()
    {
        return $this->hasMany(TourBooking::class);
    }

    public function variants()
    {
        return $this->hasMany(TourVariant::class);
    }

    public function activeVariants()
    {
        return $this->hasMany(TourVariant::class)->where('status', 'active')->orderBy('sort_order');
    }

    public function addons()
    {
        return $this->hasMany(TourAddon::class);
    }

    public function activeAddons()
    {
        return $this->hasMany(TourAddon::class)->where('status', 'active')->orderBy('sort_order');
    }

    public function applyLocale(string $locale): self
    {
        $isVietnamese = $locale === 'vi';

        $this->name = $isVietnamese ? ($this->name_vi ?? $this->name) : ($this->name_en ?? $this->name);
        $this->slug = $isVietnamese ? ($this->slug_vi ?? $this->slug) : ($this->slug_en ?? $this->slug);
        $this->description = $isVietnamese ? ($this->description_vi ?? $this->description) : ($this->description_en ?? $this->description);
        $this->meta_title = $isVietnamese ? ($this->meta_title_vi ?? $this->meta_title) : ($this->meta_title_en ?? $this->meta_title);
        $this->meta_description = $isVietnamese ? ($this->meta_description_vi ?? $this->meta_description) : ($this->meta_description_en ?? $this->meta_description);
        $this->meta_keywords = $isVietnamese ? ($this->meta_keywords_vi ?? $this->meta_keywords) : ($this->meta_keywords_en ?? $this->meta_keywords);
        $this->includes = $isVietnamese ? (!empty($this->includes_vi) ? $this->includes_vi : (!empty($this->includes) ? $this->includes : $this->includes_en)) : (!empty($this->includes_en) ? $this->includes_en : $this->includes);
        $this->excludes = $isVietnamese ? (!empty($this->excludes_vi) ? $this->excludes_vi : (!empty($this->excludes) ? $this->excludes : $this->excludes_en)) : (!empty($this->excludes_en) ? $this->excludes_en : $this->excludes);
        $this->itinerary = $isVietnamese ? (!empty($this->itinerary_vi) ? $this->itinerary_vi : (!empty($this->itinerary) ? $this->itinerary : $this->itinerary_en)) : (!empty($this->itinerary_en) ? $this->itinerary_en : $this->itinerary);

        if ($this->relationLoaded('variants')) {
            $this->variants->each(fn ($v) => $v->applyLocale($locale));
        }

        if ($this->relationLoaded('addons')) {
            $this->addons->each(fn ($a) => $a->applyLocale($locale));
        }

        return $this;
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating');
    }

    public function tourCategory()
    {
        return $this->belongsTo(TourCategory::class);
    }

    public function mediaAlbum()
    {
        return $this->belongsTo(MediaAlbum::class);
    }
}
