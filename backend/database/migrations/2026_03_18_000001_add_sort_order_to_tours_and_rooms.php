<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->integer('sort_order')->nullable()->after('status');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->integer('sort_order')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
