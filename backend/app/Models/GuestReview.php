<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'reviewable_type',
        'reviewable_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'rating',
        'content',
        'images',
        'status',
        'admin_note',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'images' => 'array',
        'rating' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function reviewable()
    {
        return $this->morphTo();
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForRooms($query)
    {
        return $query->where('reviewable_type', Room::class);
    }

    public function scopeForTours($query)
    {
        return $query->where('reviewable_type', Tour::class);
    }

    public function approve(User $admin, ?string $note = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $admin->id,
            'admin_note' => $note,
        ]);
    }

    public function reject(?string $note = null): void
    {
        $this->update([
            'status' => 'rejected',
            'admin_note' => $note,
        ]);
    }
}
