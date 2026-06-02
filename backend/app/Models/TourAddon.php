<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id',
        'name',
        'name_en',
        'name_vi',
        'description',
        'description_en',
        'description_vi',
        'price',
        'pricing_type',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'tour_id' => 'integer',
        'price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Translate name and description based on locale
     */
    public function applyLocale(string $locale): self
    {
        $isVietnamese = $locale === 'vi';
        $this->name = $isVietnamese ? ($this->name_vi ?? $this->name) : ($this->name_en ?? $this->name);
        $this->description = $isVietnamese ? ($this->description_vi ?? $this->description) : ($this->description_en ?? $this->description);
        return $this;
    }
}
