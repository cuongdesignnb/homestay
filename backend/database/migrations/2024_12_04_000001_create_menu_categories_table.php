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
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên danh mục (VI)
            $table->string('name_en')->nullable(); // Tên danh mục (EN)
            $table->string('slug')->unique();
            $table->text('description')->nullable(); // Mô tả (VI)
            $table->text('description_en')->nullable(); // Mô tả (EN)
            $table->string('image')->nullable(); // Ảnh đại diện
            $table->integer('sort_order')->default(0); // Thứ tự hiển thị
            $table->boolean('is_active')->default(true); // Trạng thái
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_categories');
    }
};
