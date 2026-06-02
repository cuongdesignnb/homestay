<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('reviewable_type')->nullable()->after('tour_booking_id');
            $table->unsignedBigInteger('reviewable_id')->nullable()->after('reviewable_type');
            $table->string('reviewer_name')->nullable()->after('reviewable_id');
            $table->string('reviewer_email')->nullable()->after('reviewer_name');
            
            $table->index(['reviewable_type', 'reviewable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['reviewable_type', 'reviewable_id']);
            $table->dropColumn(['reviewable_type', 'reviewable_id', 'reviewer_name', 'reviewer_email']);
        });
    }
};
