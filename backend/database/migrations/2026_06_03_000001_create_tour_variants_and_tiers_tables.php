<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('name_vi')->nullable();
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_vi')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->integer('min_participants')->nullable();
            $table->integer('max_participants')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tour_variant_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_variant_id')->constrained('tour_variants')->onDelete('cascade');
            $table->integer('min_participants');
            $table->integer('max_participants');
            $table->enum('pricing_type', ['per_person', 'flat'])->default('per_person');
            $table->decimal('price', 12, 2);
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->string('label')->nullable();
            $table->integer('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('tour_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('name_vi')->nullable();
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_vi')->nullable();
            $table->decimal('price', 12, 2);
            $table->enum('pricing_type', ['per_person', 'per_booking'])->default('per_person');
            $table->integer('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_addons');
        Schema::dropIfExists('tour_variant_price_tiers');
        Schema::dropIfExists('tour_variants');
    }
};
