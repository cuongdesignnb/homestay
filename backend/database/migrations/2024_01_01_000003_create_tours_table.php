<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->integer('duration'); // number of days/hours
            $table->enum('duration_unit', ['hours', 'days'])->default('days');
            $table->decimal('price_per_person', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->integer('max_participants');
            $table->integer('min_participants')->default(1);
            $table->json('itinerary')->nullable(); // day by day schedule
            $table->json('includes')->nullable(); // what's included
            $table->json('excludes')->nullable(); // what's not included
            $table->json('images')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('departure_location')->nullable();
            $table->enum('difficulty_level', ['easy', 'moderate', 'challenging', 'difficult'])->default('easy');
            $table->string('age_restriction')->nullable();
            $table->integer('view_count')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
