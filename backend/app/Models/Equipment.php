<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipments';

    protected $fillable = [
        'equipment_category_id',
        'name',
        'name_en',
        'name_vi',
        'short_description',
        'short_description_en',
        'short_description_vi',
        'description',
        'description_en',
        'description_vi',
        'rental_price_per_day',
        'sale_price',
        'is_rentable',
        'is_sellable',
        'stock_quantity',
        'images',
        'cover_image',
        'cover_media_id',
        'media_album_id',
        'status',
        'is_available',
        'sort_order',
    ];

    protected $casts = [
        'images' => 'array',
        'cover_media_id' => 'integer',
        'media_album_id' => 'integer',
        'equipment_category_id' => 'integer',
        'rental_price_per_day' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_rentable' => 'boolean',
        'is_sellable' => 'boolean',
        'is_available' => 'boolean',
        'stock_quantity' => 'integer',
        'sort_order' => 'integer',
    ];

    public function equipmentCategory()
    {
        return $this->belongsTo(EquipmentCategory::class);
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
