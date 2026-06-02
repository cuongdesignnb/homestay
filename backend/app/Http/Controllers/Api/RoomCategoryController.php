<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoomCategory;

class RoomCategoryController extends Controller
{
    public function index()
    {
        $categories = RoomCategory::query()
            ->where('is_active', true)
            ->withCount(['rooms' => fn ($query) => $query->where('status', 'available')])
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }
}
