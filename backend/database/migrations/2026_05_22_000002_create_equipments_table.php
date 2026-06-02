<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_category_id')->nullable()->constrained('equipment_categories')->nullOnDelete();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('name_vi')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->string('short_description_en', 500)->nullable();
            $table->string('short_description_vi', 500)->nullable();
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_vi')->nullable();
            $table->decimal('rental_price_per_day', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->boolean('is_rentable')->default(true);
            $table->boolean('is_sellable')->default(false);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->json('images')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedBigInteger('cover_media_id')->nullable();
            $table->unsignedBigInteger('media_album_id')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'is_available']);
            $table->index('rental_price_per_day');
            $table->index('sale_price');
            $table->index('sort_order');
            $table->index('equipment_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipments');
    }
};
