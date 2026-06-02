<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarRentalCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCarRentalCategoryController extends Controller
{
    public function index()
    {
        return response()->json(
            CarRentalCategory::query()
                ->withCount('carRentals')
                ->orderBy('name')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $data = $this->prepareLocalizedFields($data);
        $data['slug'] = Str::slug($data['name'] ?? 'car-rental-category');

        $category = CarRentalCategory::create($data);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, CarRentalCategory $carRentalCategory)
    {
        $data = $this->validatePayload($request, true);
        $data = $this->prepareLocalizedFields($data, $carRentalCategory);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $carRentalCategory->update($data);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $carRentalCategory,
        ]);
    }

    public function destroy(CarRentalCategory $carRentalCategory)
    {
        if ($carRentalCategory->carRentals()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with assigned cars',
            ], 422);
        }

        $carRentalCategory->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }

    protected function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'name' => ($isUpdate ? 'sometimes' : 'required') . '|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_vi' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_vi' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ];

        return $request->validate($rules);
    }

    protected function prepareLocalizedFields(array $data, ?CarRentalCategory $category = null): array
    {
        $name = $data['name'] ?? $category?->name;
        $description = $data['description'] ?? $category?->description;

        $data['name_en'] = $data['name_en'] ?? $category?->name_en ?? $name;
        $data['name_vi'] = $data['name_vi'] ?? $category?->name_vi ?? $name;
        $data['description_en'] = $data['description_en'] ?? $category?->description_en ?? $description;
        $data['description_vi'] = $data['description_vi'] ?? $category?->description_vi ?? $description;

        if (!isset($data['name'])) {
            $data['name'] = $data['name_en'] ?? $data['name_vi'] ?? $name;
        }
        if (!isset($data['description'])) {
            $data['description'] = $data['description_en'] ?? $data['description_vi'] ?? $description;
        }

        return $data;
    }
}
