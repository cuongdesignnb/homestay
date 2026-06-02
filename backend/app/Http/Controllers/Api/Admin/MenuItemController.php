<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuItemController extends Controller
{
    /**
     * Display a listing of menu items
     */
    public function index(Request $request): JsonResponse
    {
        $query = MenuItem::with('category');

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        // Filter by availability
        if ($request->has('is_available')) {
            $query->where('is_available', $request->is_available === 'true' || $request->is_available === '1');
        }

        // Filter by featured
        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->is_featured === 'true' || $request->is_featured === '1');
        }

        $items = $query->orderBy('category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Store a newly created menu item
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:menu_categories,id',
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'unit_en' => 'nullable|string|max:50',
            'image' => 'nullable|string',
            'note' => 'nullable|string|max:255',
            'note_en' => 'nullable|string|max:255',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $item = MenuItem::create($validator->validated());
        $item->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Món ăn đã được tạo thành công',
            'data' => $item,
        ], 201);
    }

    /**
     * Display the specified menu item
     */
    public function show(MenuItem $menuItem): JsonResponse
    {
        $menuItem->load('category');

        return response()->json([
            'success' => true,
            'data' => $menuItem,
        ]);
    }

    /**
     * Update the specified menu item
     */
    public function update(Request $request, MenuItem $menuItem): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:menu_categories,id',
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'unit_en' => 'nullable|string|max:50',
            'image' => 'nullable|string',
            'note' => 'nullable|string|max:255',
            'note_en' => 'nullable|string|max:255',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $menuItem->update($validator->validated());
        $menuItem->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Món ăn đã được cập nhật',
            'data' => $menuItem,
        ]);
    }

    /**
     * Remove the specified menu item
     */
    public function destroy(MenuItem $menuItem): JsonResponse
    {
        $menuItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Món ăn đã được xóa',
        ]);
    }

    /**
     * Update sort order for multiple items
     */
    public function updateOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->items as $item) {
            MenuItem::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thứ tự đã được cập nhật',
        ]);
    }

    /**
     * Toggle availability
     */
    public function toggleAvailability(MenuItem $menuItem): JsonResponse
    {
        $menuItem->update(['is_available' => !$menuItem->is_available]);

        return response()->json([
            'success' => true,
            'message' => $menuItem->is_available ? 'Món ăn đã được kích hoạt' : 'Món ăn đã được ẩn',
            'data' => $menuItem,
        ]);
    }

    /**
     * Toggle featured
     */
    public function toggleFeatured(MenuItem $menuItem): JsonResponse
    {
        $menuItem->update(['is_featured' => !$menuItem->is_featured]);

        return response()->json([
            'success' => true,
            'message' => $menuItem->is_featured ? 'Món ăn đã được đánh dấu nổi bật' : 'Đã bỏ đánh dấu nổi bật',
            'data' => $menuItem,
        ]);
    }
}
