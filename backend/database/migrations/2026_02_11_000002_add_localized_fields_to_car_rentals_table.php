<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car_rentals', function (Blueprint $table) {
            $table->foreignId('car_rental_category_id')->nullable()->after('id')->constrained('car_rental_categories')->nullOnDelete();
            $table->string('name_en')->nullable()->after('name');
            $table->string('name_vi')->nullable()->after('name_en');
            $table->string('brand_en')->nullable()->after('brand');
            $table->string('brand_vi')->nullable()->after('brand_en');
            $table->string('model_en')->nullable()->after('model');
            $table->string('model_vi')->nullable()->after('model_en');
            $table->string('type_en')->nullable()->after('type');
            $table->string('type_vi')->nullable()->after('type_en');
            $table->string('transmission_en')->nullable()->after('transmission');
            $table->string('transmission_vi')->nullable()->after('transmission_en');
            $table->string('fuel_type_en')->nullable()->after('fuel_type');
            $table->string('fuel_type_vi')->nullable()->after('fuel_type_en');
            $table->string('location_en')->nullable()->after('location');
            $table->string('location_vi')->nullable()->after('location_en');
            $table->string('short_description_en', 500)->nullable()->after('short_description');
            $table->string('short_description_vi', 500)->nullable()->after('short_description_en');
            $table->text('description_en')->nullable()->after('description');
            $table->text('description_vi')->nullable()->after('description_en');
            $table->string('cover_image', 2048)->nullable()->after('images');
            $table->foreignId('cover_media_id')->nullable()->after('cover_image')->constrained('media')->nullOnDelete();
            $table->foreignId('media_album_id')->nullable()->after('cover_media_id')->constrained('media_albums')->nullOnDelete();
            $table->index('car_rental_category_id');
            $table->index('media_album_id');
        });
    }

    public function down(): void
    {
        Schema::table('car_rentals', function (Blueprint $table) {
            $table->dropForeign(['car_rental_category_id']);
            $table->dropForeign(['cover_media_id']);
            $table->dropForeign(['media_album_id']);
            $table->dropIndex(['car_rental_category_id']);
            $table->dropIndex(['media_album_id']);
            $table->dropColumn([
                'car_rental_category_id',
                'name_en',
                'name_vi',
                'brand_en',
                'brand_vi',
                'model_en',
                'model_vi',
                'type_en',
                'type_vi',
                'transmission_en',
                'transmission_vi',
                'fuel_type_en',
                'fuel_type_vi',
                'location_en',
                'location_vi',
                'short_description_en',
                'short_description_vi',
                'description_en',
                'description_vi',
                'cover_image',
                'cover_media_id',
                'media_album_id',
            ]);
        });
    }
};
