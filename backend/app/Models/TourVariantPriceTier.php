<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourVariantPriceTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_variant_id',
        'min_participants',
        'max_participants',
        'pricing_type',
        'price',
        'discount_price',
        'label',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'tour_variant_id' => 'integer',
        'min_participants' => 'integer',
        'max_participants' => 'integer',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function variant()
    {
        return $this->belongsTo(TourVariant::class, 'tour_variant_id');
    }
}
