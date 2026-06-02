<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'tour_id',
        'booking_id',
        'tour_booking_id',
        'reviewable_type',
        'reviewable_id',
        'reviewer_name',
        'reviewer_email',
        'rating',
        'comment',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function tourBooking()
    {
        return $this->belongsTo(TourBooking::class);
    }

    // Polymorphic relationship
    public function reviewable()
    {
        return $this->morphTo();
    }
}
