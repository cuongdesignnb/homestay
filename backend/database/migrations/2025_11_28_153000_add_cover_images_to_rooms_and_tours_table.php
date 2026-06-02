<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('cover_image')->nullable()->after('images');
            $table->foreignId('cover_media_id')->nullable()->after('cover_image')->constrained('media')->nullOnDelete();
        });

        Schema::table('tours', function (Blueprint $table) {
            $table->string('cover_image')->nullable()->after('images');
            $table->foreignId('cover_media_id')->nullable()->after('cover_image')->constrained('media')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'cover_media_id')) {
                $table->dropForeign(['cover_media_id']);
                $table->dropColumn('cover_media_id');
            }
            if (Schema::hasColumn('rooms', 'cover_image')) {
                $table->dropColumn('cover_image');
            }
        });

        Schema::table('tours', function (Blueprint $table) {
            if (Schema::hasColumn('tours', 'cover_media_id')) {
                $table->dropForeign(['cover_media_id']);
                $table->dropColumn('cover_media_id');
            }
            if (Schema::hasColumn('tours', 'cover_image')) {
                $table->dropColumn('cover_image');
            }
        });
    }
};
