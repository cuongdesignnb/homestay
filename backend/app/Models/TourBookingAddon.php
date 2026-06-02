<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourBookingAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_booking_id',
        'tour_addon_id',
        'name',
        'name_en',
        'name_vi',
        'price',
        'pricing_type',
        'quantity',
        'total_amount',
    ];

    protected $casts = [
        'tour_booking_id' => 'integer',
        'tour_addon_id' => 'integer',
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'total_amount' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(TourBooking::class, 'tour_booking_id');
    }

    public function addon()
    {
        return $this->belongsTo(TourAddon::class, 'tour_addon_id');
    }

    /**
     * Translate name based on locale
     */
    public function getLocalizedName(string $locale): string
    {
        $isVietnamese = $locale === 'vi';
        return $isVietnamese ? ($this->name_vi ?? $this->name) : ($this->name_en ?? $this->name);
    }
}
