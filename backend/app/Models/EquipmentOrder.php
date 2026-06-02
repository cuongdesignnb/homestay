<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'order_type',
        'rental_start_date',
        'rental_days',
        'rental_end_date',
        'items',
        'subtotal',
        'total_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'items' => 'array',
        'rental_start_date' => 'date',
        'rental_end_date' => 'date',
        'rental_days' => 'integer',
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Generate a unique order number.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'EQ';
        $date = now()->format('Ymd');
        $last = static::where('order_number', 'like', "{$prefix}{$date}%")
            ->orderByDesc('order_number')
            ->first();

        if ($last) {
            $lastSeq = (int) substr($last->order_number, -4);
            $nextSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return "{$prefix}{$date}{$nextSeq}";
    }
}
