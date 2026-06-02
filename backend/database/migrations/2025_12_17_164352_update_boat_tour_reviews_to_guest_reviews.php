<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate any existing Review records for BoatTour to GuestReview
        DB::statement("
            INSERT INTO guest_reviews (
                reviewable_type, 
                reviewable_id, 
                guest_name, 
                guest_email, 
                guest_phone,
                rating, 
                content, 
                status, 
                created_at, 
                updated_at
            )
            SELECT 
                reviewable_type,
                reviewable_id,
                COALESCE(reviewer_name, 'Anonymous') as guest_name,
                COALESCE(reviewer_email, 'no-email@example.com') as guest_email,
                NULL as guest_phone,
                rating,
                comment as content,
                status,
                created_at,
                updated_at
            FROM reviews 
            WHERE reviewable_type = 'App\\\\Models\\\\BoatTour'
            AND NOT EXISTS (
                SELECT 1 FROM guest_reviews gr 
                WHERE gr.reviewable_type = reviews.reviewable_type 
                AND gr.reviewable_id = reviews.reviewable_id
                AND gr.guest_email = COALESCE(reviews.reviewer_email, 'no-email@example.com')
            )
        ");
        
        // Delete the old Review records for BoatTour
        DB::table('reviews')
            ->where('reviewable_type', 'App\\Models\\BoatTour')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse migration - move GuestReview records back to Review
        DB::statement("
            INSERT INTO reviews (
                reviewable_type, 
                reviewable_id, 
                reviewer_name, 
                reviewer_email,
                rating, 
                comment, 
                status, 
                created_at, 
                updated_at
            )
            SELECT 
                reviewable_type,
                reviewable_id,
                guest_name as reviewer_name,
                guest_email as reviewer_email,
                rating,
                content as comment,
                status,
                created_at,
                updated_at
            FROM guest_reviews 
            WHERE reviewable_type = 'App\\\\Models\\\\BoatTour'
        ");
        
        // Delete GuestReview records for BoatTour
        DB::table('guest_reviews')
            ->where('reviewable_type', 'App\\Models\\BoatTour')
            ->delete();
    }
};
