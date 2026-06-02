<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Get full menu with categories and items
     */
    public function index(Request $request): JsonResponse
    {
        $categories = MenuCategory::with(['items' => function ($query) {
            $query->where('is_available', true)->orderBy('sort_order')->orderBy('name');
        }])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get only categories
     */
    public function categories(): JsonResponse
    {
        $categories = MenuCategory::withCount(['items' => function ($query) {
            $query->where('is_available', true);
        }])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get category with items
     */
    public function category(string $slug): JsonResponse
    {
        $category = MenuCategory::with(['items' => function ($query) {
            $query->where('is_available', true)->orderBy('sort_order')->orderBy('name');
        }])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * Get featured items
     */
    public function featured(): JsonResponse
    {
        $items = MenuItem::with('category')
            ->where('is_available', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Get restaurant info from settings
     */
    public function restaurantInfo(): JsonResponse
    {
        $keys = [
            'restaurant_name',
            'restaurant_intro',
            'restaurant_intro_en',
            'restaurant_banner',
            'restaurant_opening_hours',
            'restaurant_phone',
        ];

        $settings = Setting::whereIn('key', $keys)->pluck('value', 'key');

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}
