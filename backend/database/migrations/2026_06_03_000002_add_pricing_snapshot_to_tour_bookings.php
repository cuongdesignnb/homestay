<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->foreignId('tour_variant_id')->nullable()->constrained('tour_variants')->onDelete('set null');
            $table->string('tour_variant_name')->nullable();
            $table->unsignedBigInteger('price_tier_id')->nullable(); 
            $table->string('pricing_type')->nullable(); // per_person, flat
            $table->decimal('base_price', 12, 2)->nullable();
            $table->decimal('addons_amount', 12, 2)->default(0);
        });

        Schema::create('tour_booking_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_booking_id')->constrained('tour_bookings')->onDelete('cascade');
            $table->foreignId('tour_addon_id')->nullable()->constrained('tour_addons')->onDelete('set null');
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('name_vi')->nullable();
            $table->decimal('price', 12, 2);
            $table->enum('pricing_type', ['per_person', 'per_booking']);
            $table->integer('quantity');
            $table->decimal('total_amount', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_booking_addons');
        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->dropForeign(['tour_variant_id']);
            $table->dropColumn([
                'tour_variant_id',
                'tour_variant_name',
                'price_tier_id',
                'pricing_type',
                'base_price',
                'addons_amount'
            ]);
        });
    }
};
