<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RestaurantSettingController extends Controller
{
    /**
     * Restaurant settings keys
     */
    protected array $restaurantKeys = [
        'restaurant_name',
        'restaurant_intro',
        'restaurant_intro_en',
        'restaurant_banner',
        'restaurant_opening_hours',
        'restaurant_phone',
    ];

    /**
     * Get restaurant settings
     */
    public function index(): JsonResponse
    {
        $settings = Setting::whereIn('key', $this->restaurantKeys)
            ->pluck('value', 'key')
            ->toArray();

        // Ensure all keys exist
        foreach ($this->restaurantKeys as $key) {
            if (!isset($settings[$key])) {
                $settings[$key] = '';
            }
        }

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Update restaurant settings
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'restaurant_name' => 'nullable|string|max:255',
            'restaurant_intro' => 'nullable|string',
            'restaurant_intro_en' => 'nullable|string',
            'restaurant_banner' => 'nullable|string',
            'restaurant_opening_hours' => 'nullable|string|max:255',
            'restaurant_phone' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        foreach ($data as $key => $value) {
            if (in_array($key, $this->restaurantKeys)) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value ?? '']
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Cài đặt nhà hàng đã được cập nhật',
        ]);
    }
}
