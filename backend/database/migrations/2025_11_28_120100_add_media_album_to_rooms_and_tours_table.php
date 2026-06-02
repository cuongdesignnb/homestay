<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('media_album_id')->nullable()->after('images')->constrained('media_albums')->nullOnDelete();
        });

        Schema::table('tours', function (Blueprint $table) {
            $table->foreignId('media_album_id')->nullable()->after('images')->constrained('media_albums')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('media_album_id');
        });

        Schema::table('tours', function (Blueprint $table) {
            $table->dropConstrainedForeignId('media_album_id');
        });
    }
};
