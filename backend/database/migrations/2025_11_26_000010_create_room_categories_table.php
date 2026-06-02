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
        Schema::create('room_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('room_category_id')
                ->nullable()
                ->constrained('room_categories')
                ->nullOnDelete();
            $table->index('room_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['room_category_id']);
            $table->dropIndex(['room_category_id']);
            $table->dropColumn('room_category_id');
        });

        Schema::dropIfExists('room_categories');
    }
};
