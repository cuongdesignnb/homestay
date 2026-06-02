<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_rentals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('type')->nullable();
            $table->unsignedTinyInteger('seats')->default(4);
            $table->string('transmission')->nullable();
            $table->string('fuel_type')->nullable();
            $table->decimal('price_per_day', 10, 2)->default(0);
            $table->string('location')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->json('images')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_available')->default(true);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->string('contact_phone')->nullable();
            $table->string('contact_whatsapp')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_available']);
            $table->index('price_per_day');
            $table->index('location');
            $table->index('seats');
            $table->index('average_rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_rentals');
    }
};
