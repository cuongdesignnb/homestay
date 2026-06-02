<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('type'); // single, double, suite, family, etc.
            $table->integer('capacity');
            $table->decimal('size', 8, 2)->nullable(); // in square meters
            $table->integer('beds')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->decimal('price_per_night', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->json('amenities')->nullable(); // WiFi, TV, AC, etc.
            $table->json('images')->nullable();
            $table->enum('status', ['available', 'unavailable', 'maintenance'])->default('available');
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
        Schema::dropIfExists('rooms');
    }
};
