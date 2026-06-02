<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MenuCategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request): JsonResponse
    {
        $query = MenuCategory::withCount('items');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === 'true' || $request->is_active === '1');
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['slug'] = Str::slug($data['name']);

        // Ensure unique slug
        $originalSlug = $data['slug'];
        $counter = 1;
        while (MenuCategory::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter++;
        }

        $category = MenuCategory::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Danh mục đã được tạo thành công',
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified category
     */
    public function show(MenuCategory $menuCategory): JsonResponse
    {
        $menuCategory->load('items');

        return response()->json([
            'success' => true,
            'data' => $menuCategory,
        ]);
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, MenuCategory $menuCategory): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Update slug if name changed
        if ($data['name'] !== $menuCategory->name) {
            $data['slug'] = Str::slug($data['name']);
            $originalSlug = $data['slug'];
            $counter = 1;
            while (MenuCategory::where('slug', $data['slug'])->where('id', '!=', $menuCategory->id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter++;
            }
        }

        $menuCategory->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Danh mục đã được cập nhật',
            'data' => $menuCategory,
        ]);
    }

    /**
     * Remove the specified category
     */
    public function destroy(MenuCategory $menuCategory): JsonResponse
    {
        // Check if category has items
        if ($menuCategory->items()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa danh mục còn món ăn. Vui lòng xóa hoặc chuyển các món ăn trước.',
            ], 422);
        }

        $menuCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Danh mục đã được xóa',
        ]);
    }

    /**
     * Update sort order for multiple categories
     */
    public function updateOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:menu_categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->categories as $item) {
            MenuCategory::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thứ tự đã được cập nhật',
        ]);
    }
}
