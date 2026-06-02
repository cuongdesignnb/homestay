<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarRental extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'name_en',
        'name_vi',
        'brand',
        'brand_en',
        'brand_vi',
        'model',
        'model_en',
        'model_vi',
        'type',
        'type_en',
        'type_vi',
        'seats',
        'transmission',
        'transmission_en',
        'transmission_vi',
        'fuel_type',
        'fuel_type_en',
        'fuel_type_vi',
        'price_per_day',
        'location',
        'location_en',
        'location_vi',
        'short_description',
        'short_description_en',
        'short_description_vi',
        'description',
        'description_en',
        'description_vi',
        'features',
        'images',
        'cover_image',
        'cover_media_id',
        'media_album_id',
        'car_rental_category_id',
        'status',
        'is_available',
        'average_rating',
        'total_reviews',
        'contact_phone',
        'contact_whatsapp',
    ];

    protected $casts = [
        'features' => 'array',
        'images' => 'array',
        'cover_media_id' => 'integer',
        'media_album_id' => 'integer',
        'car_rental_category_id' => 'integer',
        'seats' => 'integer',
        'price_per_day' => 'decimal:2',
        'is_available' => 'boolean',
        'average_rating' => 'decimal:2',
        'total_reviews' => 'integer',
    ];

    public function carRentalCategory()
    {
        return $this->belongsTo(CarRentalCategory::class);
    }

    public function mediaAlbum()
    {
        return $this->belongsTo(MediaAlbum::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
