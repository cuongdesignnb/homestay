<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = BlogPost::query()
            ->with(['category', 'author'])
            ->when($request->status, fn ($query) => $query->where('status', $request->status))
            ->when($request->category_id, fn ($query) => $query->where('category_id', $request->category_id))
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('title_en', 'like', "%{$search}%")
                        ->orWhere('title_vi', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('excerpt_en', 'like', "%{$search}%")
                        ->orWhere('excerpt_vi', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $data = $this->validatePost($request);
        $data = $this->prepareLocalizedFields($data);
        $data = $this->resolvePublicationState($data);
        $data['tags'] = $this->normalizeTags($data['tags'] ?? null);
        $data['author_id'] = $request->user()->id;

        $post = BlogPost::create($data);
        $post->load(['category', 'author']);

        return response()->json([
            'message' => 'Blog post created successfully',
            'post' => $post,
        ], 201);
    }

    public function show(BlogPost $post)
    {
        $post->load(['category', 'author']);

        return response()->json($post);
    }

    public function update(Request $request, BlogPost $post)
    {
        $data = $this->validatePost($request, true);
        $data = $this->prepareLocalizedFields($data, $post);
        $data = $this->resolvePublicationState($data, $post);
        if (array_key_exists('tags', $data)) {
            $data['tags'] = $this->normalizeTags($data['tags']);
        }

        $post->update($data);
        $post->load(['category', 'author']);

        return response()->json([
            'message' => 'Blog post updated successfully',
            'post' => $post,
        ]);
    }

    public function destroy(BlogPost $post)
    {
        $post->delete();

        return response()->json(['message' => 'Blog post deleted successfully']);
    }

    public function publish(BlogPost $post)
    {
        $post->status = 'published';
        $post->published_at = $post->published_at ?? now();
        $post->save();
        $post->load(['category', 'author']);

        return response()->json([
            'message' => 'Blog post published successfully',
            'post' => $post,
        ]);
    }

    public function unpublish(BlogPost $post)
    {
        $post->status = 'draft';
        $post->published_at = null;
        $post->save();
        $post->load(['category', 'author']);

        return response()->json([
            'message' => 'Blog post unpublished successfully',
            'post' => $post,
        ]);
    }

    public function categories()
    {
        $categories = BlogCategory::withCount('posts')->orderBy('name')->get();

        return response()->json($categories);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_vi' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_categories,slug',
            'description_en' => 'nullable|string',
            'description_vi' => 'nullable|string',
        ]);

        $data['name'] = $data['name_en'] ?? $data['name_vi'];
        $data['description'] = $data['description_en'] ?? $data['description_vi'];
        $data['slug'] = $data['slug'] ?? $this->generateSlug($data['name_en'] ?? $data['name_vi']);

        $category = BlogCategory::create($data)->loadCount('posts');

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }

    protected function validatePost(Request $request, bool $isUpdate = false): array
    {
        $requiredRule = $isUpdate ? 'sometimes' : 'required';

        return $request->validate([
            'title_en' => $requiredRule . '|string|max:255',
            'title_vi' => $requiredRule . '|string|max:255',
            'slug_en' => 'nullable|string|max:255',
            'slug_vi' => 'nullable|string|max:255',
            'excerpt_en' => 'nullable|string',
            'excerpt_vi' => 'nullable|string',
            'content_en' => $requiredRule . '|string',
            'content_vi' => $requiredRule . '|string',
            'featured_image' => 'nullable|string|max:1024',
            'category_id' => $requiredRule . '|exists:blog_categories,id',
            'status' => 'nullable|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'meta_title_en' => 'nullable|string|max:255',
            'meta_title_vi' => 'nullable|string|max:255',
            'meta_description_en' => 'nullable|string',
            'meta_description_vi' => 'nullable|string',
            'meta_keywords_en' => 'nullable|string',
            'meta_keywords_vi' => 'nullable|string',
        ]);
    }

    protected function prepareLocalizedFields(array $data, ?BlogPost $post = null): array
    {
        $current = $post?->toArray() ?? [];

        $data['title_en'] = $data['title_en'] ?? $current['title_en'] ?? $current['title'] ?? null;
        $data['title_vi'] = $data['title_vi'] ?? $current['title_vi'] ?? $current['title'] ?? null;

        $data['slug_en'] = $data['slug_en']
            ?? $current['slug_en']
            ?? $this->generateSlug($data['title_en']);
        $data['slug_vi'] = $data['slug_vi']
            ?? $current['slug_vi']
            ?? $this->generateSlug($data['title_vi']);

        $data['excerpt_en'] = $data['excerpt_en'] ?? $current['excerpt_en'] ?? $current['excerpt'] ?? null;
        $data['excerpt_vi'] = $data['excerpt_vi'] ?? $current['excerpt_vi'] ?? $current['excerpt'] ?? null;
        $data['content_en'] = $data['content_en'] ?? $current['content_en'] ?? $current['content'] ?? null;
        $data['content_vi'] = $data['content_vi'] ?? $current['content_vi'] ?? $current['content'] ?? null;

        $data['meta_title_en'] = $data['meta_title_en'] ?? $current['meta_title_en'] ?? $current['meta_title'] ?? null;
        $data['meta_title_vi'] = $data['meta_title_vi'] ?? $current['meta_title_vi'] ?? $current['meta_title'] ?? null;
        $data['meta_description_en'] = $data['meta_description_en'] ?? $current['meta_description_en'] ?? $current['meta_description'] ?? null;
        $data['meta_description_vi'] = $data['meta_description_vi'] ?? $current['meta_description_vi'] ?? $current['meta_description'] ?? null;
        $data['meta_keywords_en'] = $data['meta_keywords_en'] ?? $current['meta_keywords_en'] ?? $current['meta_keywords'] ?? null;
        $data['meta_keywords_vi'] = $data['meta_keywords_vi'] ?? $current['meta_keywords_vi'] ?? $current['meta_keywords'] ?? null;

        $data['title'] = $data['title_en'] ?? $data['title_vi'] ?? $data['title'] ?? null;
        $data['slug'] = $data['slug_en'] ?? $data['slug_vi'] ?? $data['slug'] ?? $current['slug'] ?? $this->generateSlug($data['title']);
        $data['excerpt'] = $data['excerpt_en'] ?? $data['excerpt_vi'] ?? $data['excerpt'] ?? null;
        $data['content'] = $data['content_en'] ?? $data['content_vi'] ?? $data['content'] ?? null;
        $data['meta_title'] = $data['meta_title_en'] ?? $data['meta_title_vi'] ?? $data['meta_title'] ?? null;
        $data['meta_description'] = $data['meta_description_en'] ?? $data['meta_description_vi'] ?? $data['meta_description'] ?? null;
        $data['meta_keywords'] = $data['meta_keywords_en'] ?? $data['meta_keywords_vi'] ?? $data['meta_keywords'] ?? null;

        return $data;
    }

    protected function resolvePublicationState(array $data, ?BlogPost $post = null): array
    {
        $currentStatus = $post?->status;
        $data['status'] = $data['status'] ?? $currentStatus ?? 'draft';

        if ($data['status'] === 'published') {
            $data['published_at'] = $data['published_at'] ?? $post?->published_at ?? now();
        } elseif (!isset($data['published_at'])) {
            $data['published_at'] = $post?->published_at && $currentStatus === $data['status']
                ? $post->published_at
                : null;
        }

        return $data;
    }

    protected function normalizeTags($tags): array
    {
        if (is_string($tags)) {
            $tags = array_map('trim', explode(',', $tags));
        }

        if (!is_array($tags)) {
            return [];
        }

        $tags = array_filter(array_map(fn ($tag) => trim($tag), $tags), fn ($tag) => $tag !== '');

        return array_values(array_unique($tags));
    }

    protected function generateSlug(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        return Str::slug($value) . '-' . Str::random(5);
    }
}
