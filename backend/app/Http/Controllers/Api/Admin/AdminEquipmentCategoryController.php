<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EquipmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminEquipmentCategoryController extends Controller
{
    public function index()
    {
        $categories = EquipmentCategory::withCount('equipments')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_vi' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'description_vi' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['name_en'] = $data['name_en'] ?? $data['name'];
        $data['name_vi'] = $data['name_vi'] ?? $data['name'];
        $data['description_en'] = $data['description_en'] ?? $data['description'] ?? null;
        $data['description_vi'] = $data['description_vi'] ?? $data['description'] ?? null;

        $category = EquipmentCategory::create($data);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, EquipmentCategory $equipmentCategory)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_vi' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'description_vi' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $data['name_en'] = $data['name_en'] ?? $equipmentCategory->name_en;
        $data['name_vi'] = $data['name_vi'] ?? $equipmentCategory->name_vi;
        $data['description_en'] = $data['description_en'] ?? $equipmentCategory->description_en;
        $data['description_vi'] = $data['description_vi'] ?? $equipmentCategory->description_vi;

        $equipmentCategory->update($data);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $equipmentCategory,
        ]);
    }

    public function destroy(EquipmentCategory $equipmentCategory)
    {
        $equipmentCategory->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}
