<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminTourCategoryController extends Controller
{
    public function index()
    {
        return response()->json(
            TourCategory::query()
                ->withCount('tours')
                ->orderBy('name')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $data['slug'] = Str::slug($data['name']);

        $category = TourCategory::create($data);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, TourCategory $tourCategory)
    {
        $data = $this->validatePayload($request, true);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $tourCategory->update($data);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $tourCategory,
        ]);
    }

    public function destroy(TourCategory $tourCategory)
    {
        if ($tourCategory->tours()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with assigned tours',
            ], 422);
        }

        $tourCategory->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }

    protected function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'name' => ($isUpdate ? 'sometimes' : 'required') . '|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ];

        return $request->validate($rules);
    }
}
