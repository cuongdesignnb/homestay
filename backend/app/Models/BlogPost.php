<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'title_en',
        'title_vi',
        'slug',
        'slug_en',
        'slug_vi',
        'excerpt',
        'excerpt_en',
        'excerpt_vi',
        'content',
        'content_en',
        'content_vi',
        'featured_image',
        'category_id',
        'author_id',
        'status',
        'published_at',
        'view_count',
        'meta_title',
        'meta_title_en',
        'meta_title_vi',
        'meta_description',
        'meta_description_en',
        'meta_description_vi',
        'meta_keywords',
        'meta_keywords_en',
        'meta_keywords_vi',
        'tags',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'tags' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }
}
