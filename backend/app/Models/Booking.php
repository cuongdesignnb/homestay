<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'booking_number',
        'check_in',
        'check_in_time',
        'check_out',
        'check_out_time',
        'guests',
        'total_nights',
        'room_price',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'final_amount',
        'status',
        'payment_status',
        'payment_method',
        'special_requests',
        'guest_name',
        'guest_email',
        'guest_phone',
        'cancelled_at',
        'cancellation_reason',
        'payment_code',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'total_nights' => 'integer',
        'guests' => 'integer',
        'room_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function generateBookingNumber()
    {
        // Format: BK + YmdHis + random 4 số (giữ cho hiển thị)
        return 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -6));
    }

    /**
     * Tạo mã thanh toán cho SePay (chỉ số, 3-10 ký tự)
     * Format: Lấy 8 số cuối của timestamp + 2 số random = 10 số
     */
    public function generatePaymentCode(): string
    {
        // Lấy 6 số cuối timestamp + 4 số random = 10 số
        $timestamp = substr(time(), -6);
        $random = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        return $timestamp . $random;
    }
}
