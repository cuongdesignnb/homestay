<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoatTour extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'short_description',
        'price',
        'duration',
        'included_services',
        'excluded_services',
        'itinerary',
        'departure_location',
        'departure_time',
        'max_participants',
        'image_gallery',
        'contact_whatsapp',
        'status',
        'average_rating',
        'total_reviews'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'included_services' => 'array',
        'excluded_services' => 'array',
        'image_gallery' => 'array',
        'average_rating' => 'decimal:2'
    ];

    // Relationships
    public function reviews()
    {
        return $this->morphMany(GuestReview::class, 'reviewable');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Helper methods
    public function updateRating()
    {
        $avgRating = $this->reviews()->avg('rating');
        $totalReviews = $this->reviews()->count();
        
        $this->update([
            'average_rating' => $avgRating ?: 0,
            'total_reviews' => $totalReviews
        ]);
    }

    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    public function getWhatsAppLinkAttribute()
    {
        // Use tour-specific WhatsApp or fallback to site-wide WhatsApp
        $whatsappNumber = $this->contact_whatsapp ?: \App\Models\Setting::getValue('contact_whatsapp');
        
        if (!$whatsappNumber) {
            return null;
        }
        
        $message = urlencode("Hi! I'm interested in booking the {$this->name} boat tour. Could you provide more details?");
        return "https://wa.me/{$whatsappNumber}?text={$message}";
    }
}
