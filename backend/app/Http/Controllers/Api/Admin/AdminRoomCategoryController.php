<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminRoomCategoryController extends Controller
{
    public function index()
    {
        return response()->json(
            RoomCategory::query()
                ->withCount('rooms')
                ->orderBy('name')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        $data['slug'] = Str::slug($data['name']);

        $category = RoomCategory::create($data);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, RoomCategory $roomCategory)
    {
        $data = $this->validatePayload($request, true);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $roomCategory->update($data);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $roomCategory,
        ]);
    }

    public function destroy(RoomCategory $roomCategory)
    {
        if ($roomCategory->rooms()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with assigned rooms',
            ], 422);
        }

        $roomCategory->delete();

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
