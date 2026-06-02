<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $locale = $this->normalizeLocale($request->get('lang') ?? $request->header('Accept-Language'));

        $query = BlogPost::published()->with(['category', 'author']);

        // Filter by category
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%")
                    ->orWhere('title_vi', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('content_en', 'like', "%{$search}%")
                    ->orWhere('content_vi', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('excerpt_en', 'like', "%{$search}%")
                    ->orWhere('excerpt_vi', 'like', "%{$search}%");
            });
        }

        // Filter by tags
        if ($request->has('tag')) {
            $query->whereJsonContains('tags', $request->tag);
        }

        $query->orderBy('published_at', 'desc');

        $posts = $query->paginate($request->get('per_page', 10));

        $posts->getCollection()->transform(function ($post) use ($locale) {
            return $this->applyPostLocale($post, $locale);
        });

        return response()->json($posts);
    }

    public function show($slug)
    {
        $locale = $this->normalizeLocale(request()->get('lang') ?? request()->header('Accept-Language'));

        $post = BlogPost::published()
            ->with(['category', 'author'])
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('slug_en', $slug)
                    ->orWhere('slug_vi', $slug);
            })
            ->firstOrFail();

        // Increment view count
        $post->increment('view_count');

        return response()->json($this->applyPostLocale($post, $locale));
    }

    public function categories()
    {
        $locale = $this->normalizeLocale(request()->get('lang') ?? request()->header('Accept-Language'));

        $categories = BlogCategory::withCount('posts')->get()->map(function ($category) use ($locale) {
            return $this->applyCategoryLocale($category, $locale);
        });

        return response()->json($categories);
    }

    protected function normalizeLocale(?string $locale): string
    {
        if ($locale && str_starts_with(strtolower($locale), 'vi')) {
            return 'vi';
        }

        return 'en';
    }

    protected function applyPostLocale(BlogPost $post, string $locale): BlogPost
    {
        $isVietnamese = $locale === 'vi';

        $post->title = $isVietnamese ? ($post->title_vi ?? $post->title) : ($post->title_en ?? $post->title);
        $post->slug = $isVietnamese ? ($post->slug_vi ?? $post->slug) : ($post->slug_en ?? $post->slug);
        $post->excerpt = $isVietnamese ? ($post->excerpt_vi ?? $post->excerpt) : ($post->excerpt_en ?? $post->excerpt);
        $post->content = $isVietnamese ? ($post->content_vi ?? $post->content) : ($post->content_en ?? $post->content);
        $post->meta_title = $isVietnamese ? ($post->meta_title_vi ?? $post->meta_title) : ($post->meta_title_en ?? $post->meta_title);
        $post->meta_description = $isVietnamese ? ($post->meta_description_vi ?? $post->meta_description) : ($post->meta_description_en ?? $post->meta_description);
        $post->meta_keywords = $isVietnamese ? ($post->meta_keywords_vi ?? $post->meta_keywords) : ($post->meta_keywords_en ?? $post->meta_keywords);

        if ($post->relationLoaded('category') && $post->category) {
            $post->category = $this->applyCategoryLocale($post->category, $locale);
        }

        return $post;
    }

    protected function applyCategoryLocale(BlogCategory $category, string $locale): BlogCategory
    {
        $isVietnamese = $locale === 'vi';

        $category->name = $isVietnamese ? ($category->name_vi ?? $category->name) : ($category->name_en ?? $category->name);
        $category->description = $isVietnamese
            ? ($category->description_vi ?? $category->description)
            : ($category->description_en ?? $category->description);

        return $category;
    }
}
