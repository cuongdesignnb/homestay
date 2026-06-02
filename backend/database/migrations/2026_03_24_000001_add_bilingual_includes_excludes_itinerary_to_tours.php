<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->json('includes_en')->nullable()->after('includes');
            $table->json('includes_vi')->nullable()->after('includes_en');
            $table->json('excludes_en')->nullable()->after('excludes');
            $table->json('excludes_vi')->nullable()->after('excludes_en');
            $table->json('itinerary_en')->nullable()->after('itinerary');
            $table->json('itinerary_vi')->nullable()->after('itinerary_en');
        });

        // Copy existing data to _en columns
        DB::statement("UPDATE tours SET includes_en = includes, excludes_en = excludes, itinerary_en = itinerary WHERE includes IS NOT NULL OR excludes IS NOT NULL OR itinerary IS NOT NULL");
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn([
                'includes_en', 'includes_vi',
                'excludes_en', 'excludes_vi',
                'itinerary_en', 'itinerary_vi',
            ]);
        });
    }
};
