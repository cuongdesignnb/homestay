<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TourCategory;

class TourCategoryController extends Controller
{
    public function index()
    {
        $categories = TourCategory::query()
            ->where('is_active', true)
            ->withCount(['tours' => fn ($query) => $query->where('status', 'active')])
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }
}
