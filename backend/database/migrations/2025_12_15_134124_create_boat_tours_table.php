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
        Schema::create('boat_tours', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->text('short_description');
            $table->decimal('price', 10, 2);
            $table->string('duration');
            $table->json('included_services')->nullable();
            $table->json('excluded_services')->nullable();
            $table->text('itinerary')->nullable();
            $table->string('departure_location');
            $table->string('departure_time');
            $table->integer('max_participants');
            $table->json('image_gallery')->nullable();
            $table->string('contact_whatsapp');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boat_tours');
    }
};
