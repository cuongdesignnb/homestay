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
        Schema::create('tour_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('tours', function (Blueprint $table) {
            $table->foreignId('tour_category_id')
                ->nullable()
                ->constrained('tour_categories')
                ->nullOnDelete();
            $table->index('tour_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropForeign(['tour_category_id']);
            $table->dropIndex(['tour_category_id']);
            $table->dropColumn('tour_category_id');
        });

        Schema::dropIfExists('tour_categories');
    }
};
