<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tour_id',
        'name',
        'name_en',
        'name_vi',
        'description',
        'description_en',
        'description_vi',
        'status',
        'sort_order',
        'is_default',
        'min_participants',
        'max_participants',
    ];

    protected $casts = [
        'tour_id' => 'integer',
        'sort_order' => 'integer',
        'is_default' => 'boolean',
        'min_participants' => 'integer',
        'max_participants' => 'integer',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function priceTiers()
    {
        return $this->hasMany(TourVariantPriceTier::class);
    }

    public function activePriceTiers()
    {
        return $this->hasMany(TourVariantPriceTier::class)->where('status', 'active')->orderBy('sort_order');
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
