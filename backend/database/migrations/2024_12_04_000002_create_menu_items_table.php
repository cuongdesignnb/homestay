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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('menu_categories')->onDelete('cascade');
            $table->string('name'); // Tên món (VI)
            $table->string('name_en')->nullable(); // Tên món (EN)
            $table->text('description')->nullable(); // Mô tả (VI)
            $table->text('description_en')->nullable(); // Mô tả (EN)
            $table->decimal('price', 12, 0)->default(0); // Giá (VNĐ)
            $table->string('unit')->nullable(); // Đơn vị (VD: "plate", "portion", "bowl")
            $table->string('unit_en')->nullable(); // Đơn vị (EN)
            $table->string('image')->nullable(); // Ảnh món ăn
            $table->string('note')->nullable(); // Ghi chú (VD: "for 2-3 people")
            $table->string('note_en')->nullable(); // Ghi chú (EN)
            $table->boolean('is_available')->default(true); // Còn phục vụ
            $table->boolean('is_featured')->default(false); // Món nổi bật
            $table->integer('sort_order')->default(0); // Thứ tự
            $table->timestamps();

            $table->index('category_id');
            $table->index('is_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
