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
        // Thêm payment_code vào bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_code', 10)->nullable()->after('booking_number')->index();
        });

        // Thêm payment_code vào tour_bookings
        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->string('payment_code', 10)->nullable()->after('booking_number')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('payment_code');
        });

        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->dropColumn('payment_code');
        });
    }
};
