<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
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
        'room_category_id',
        'type',
        'capacity',
        'size',
        'beds',
        'bathrooms',
        'price_per_night',
        'discount_price',
        'amenities',
        'images',
        'cover_image',
        'cover_media_id',
        'media_album_id',
        'status',
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
        'amenities' => 'array',
        'images' => 'array',
        'cover_media_id' => 'integer',
        'media_album_id' => 'integer',
        'price_per_night' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'capacity' => 'integer',
        'beds' => 'integer',
        'bathrooms' => 'integer',
        'view_count' => 'integer',
        'sort_order' => 'integer',
        'room_category_id' => 'integer',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function roomCategory()
    {
        return $this->belongsTo(RoomCategory::class);
    }

    public function mediaAlbum()
    {
        return $this->belongsTo(MediaAlbum::class);
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating');
    }

    public function isAvailable($checkIn, $checkOut)
    {
        return !$this->bookings()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkIn)
                            ->where('check_out', '>=', $checkOut);
                    });
            })
            ->exists();
    }
}
