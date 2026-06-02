<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nav_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('nav_menus')->cascadeOnDelete();
            $table->unsignedBigInteger('parent_id')->nullable()->comment('ID cha (self-reference), null nếu là item gốc');
            $table->string('label')->comment('Nhãn hiển thị');
            $table->string('label_en')->nullable()->comment('Nhãn tiếng Anh');
            $table->string('url')->nullable()->comment('URL tùy chỉnh hoặc path route nội bộ');
            $table->string('route_name')->nullable()->comment('Tên route Vue Router');
            $table->string('icon')->nullable()->comment('Emoji hoặc icon class');
            $table->string('target')->default('_self')->comment('_self hoặc _blank');
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('nav_menu_items')->cascadeOnDelete();
            $table->index(['menu_id', 'parent_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_menu_items');
    }
};
