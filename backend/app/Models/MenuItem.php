<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'name_en',
        'description',
        'description_en',
        'price',
        'unit',
        'unit_en',
        'image',
        'note',
        'note_en',
        'is_available',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:0',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the category
     */
    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    /**
     * Scope for available items
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope for featured items
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for ordering
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get localized name
     */
    public function getLocalizedNameAttribute()
    {
        $locale = app()->getLocale();
        if ($locale === 'en' && !empty($this->name_en)) {
            return $this->name_en;
        }
        return $this->name;
    }

    /**
     * Get localized description
     */
    public function getLocalizedDescriptionAttribute()
    {
        $locale = app()->getLocale();
        if ($locale === 'en' && !empty($this->description_en)) {
            return $this->description_en;
        }
        return $this->description;
    }

    /**
     * Get localized note
     */
    public function getLocalizedNoteAttribute()
    {
        $locale = app()->getLocale();
        if ($locale === 'en' && !empty($this->note_en)) {
            return $this->note_en;
        }
        return $this->note;
    }

    /**
     * Get localized unit
     */
    public function getLocalizedUnitAttribute()
    {
        $locale = app()->getLocale();
        if ($locale === 'en' && !empty($this->unit_en)) {
            return $this->unit_en;
        }
        return $this->unit;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', '.') . 'đ';
    }

    /**
     * Get price with unit
     */
    public function getPriceWithUnitAttribute()
    {
        $price = $this->formatted_price;
        $unit = $this->localized_unit;
        
        if ($unit) {
            return $price . '/' . $unit;
        }
        return $price;
    }
}
