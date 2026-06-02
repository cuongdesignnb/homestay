<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->string('title_vi')->nullable()->after('title_en');
            $table->string('slug_en')->nullable()->after('slug');
            $table->string('slug_vi')->nullable()->after('slug_en');
            $table->text('excerpt_en')->nullable()->after('excerpt');
            $table->text('excerpt_vi')->nullable()->after('excerpt_en');
            $table->longText('content_en')->nullable()->after('content');
            $table->longText('content_vi')->nullable()->after('content_en');
            $table->string('meta_title_en')->nullable()->after('meta_title');
            $table->string('meta_title_vi')->nullable()->after('meta_title_en');
            $table->text('meta_description_en')->nullable()->after('meta_description');
            $table->text('meta_description_vi')->nullable()->after('meta_description_en');
            $table->text('meta_keywords_en')->nullable()->after('meta_keywords');
            $table->text('meta_keywords_vi')->nullable()->after('meta_keywords_en');
        });

        Schema::table('blog_categories', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('name_vi')->nullable()->after('name_en');
            $table->text('description_en')->nullable()->after('description');
            $table->text('description_vi')->nullable()->after('description_en');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn([
                'title_en', 'title_vi',
                'slug_en', 'slug_vi',
                'excerpt_en', 'excerpt_vi',
                'content_en', 'content_vi',
                'meta_title_en', 'meta_title_vi',
                'meta_description_en', 'meta_description_vi',
                'meta_keywords_en', 'meta_keywords_vi',
            ]);
        });

        Schema::table('blog_categories', function (Blueprint $table) {
            $table->dropColumn([
                'name_en', 'name_vi',
                'description_en', 'description_vi',
            ]);
        });
    }
};
