<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tour_id',
        'booking_number',
        'tour_date',
        'participants',
        'price_per_person',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'final_amount',
        'status',
        'payment_status',
        'payment_method',
        'special_requests',
        'contact_name',
        'contact_email',
        'contact_phone',
        'cancelled_at',
        'cancellation_reason',
        'payment_code',
        'tour_variant_id',
        'tour_variant_name',
        'price_tier_id',
        'pricing_type',
        'base_price',
        'addons_amount',
    ];

    protected $casts = [
        'tour_date' => 'date',
        'participants' => 'integer',
        'price_per_person' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'tour_variant_id' => 'integer',
        'price_tier_id' => 'integer',
        'base_price' => 'decimal:2',
        'addons_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function variant()
    {
        return $this->belongsTo(TourVariant::class, 'tour_variant_id');
    }

    public function addons()
    {
        return $this->hasMany(TourBookingAddon::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function generateBookingNumber()
    {
        return 'TB' . date('Ymd') . strtoupper(substr(uniqid(), -6));
    }

    /**
     * Tạo mã thanh toán cho SePay (chỉ số, 3-10 ký tự)
     */
    public function generatePaymentCode(): string
    {
        $timestamp = substr(time(), -6);
        $random = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        return $timestamp . $random;
    }
}
