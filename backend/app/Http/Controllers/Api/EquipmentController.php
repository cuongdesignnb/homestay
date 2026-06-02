<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentOrder;
use App\Services\MailNotificationService;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /**
     * List active equipments (public).
     */
    public function index(Request $request)
    {
        $equipments = Equipment::query()
            ->active()
            ->where('is_available', true)
            ->with('equipmentCategory')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->orWhere('name_vi', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('equipment_category_id', $request->category_id);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                if ($request->type === 'rental') {
                    $query->where('is_rentable', true);
                } elseif ($request->type === 'sale') {
                    $query->where('is_sellable', true);
                }
            })
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20));

        return response()->json($equipments);
    }

    /**
     * Show a single equipment (public).
     */
    public function show($id)
    {
        $equipment = Equipment::active()
            ->where('is_available', true)
            ->with('equipmentCategory')
            ->findOrFail($id);

        return response()->json($equipment);
    }

    /**
     * List active equipment categories (public).
     */
    public function categories()
    {
        $categories = EquipmentCategory::where('is_active', true)
            ->withCount(['equipments' => function ($q) {
                $q->active()->where('is_available', true);
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    /**
     * Create an equipment order (public — guest).
     */
    public function createOrder(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'required|string|max:50',
            'order_type' => 'required|in:rental,purchase',
            'rental_start_date' => 'nullable|date|after_or_equal:today',
            'rental_days' => 'nullable|integer|min:1',
            'items' => 'required|array|min:1',
            'items.*.equipment_id' => 'required|exists:equipments,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.type' => 'required|in:rental,purchase',
            'items.*.rental_days' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Build order items and calculate totals
        $orderItems = [];
        $subtotal = 0;

        foreach ($data['items'] as $item) {
            $equipment = Equipment::active()
                ->where('is_available', true)
                ->findOrFail($item['equipment_id']);

            $unitPrice = 0;
            $lineTotal = 0;
            $rentalDays = $item['rental_days'] ?? $data['rental_days'] ?? 1;

            if ($item['type'] === 'rental') {
                $unitPrice = $equipment->rental_price_per_day;
                $lineTotal = $unitPrice * $item['quantity'] * $rentalDays;
            } else {
                $unitPrice = $equipment->sale_price ?? 0;
                $lineTotal = $unitPrice * $item['quantity'];
                $rentalDays = null;
            }

            $orderItems[] = [
                'equipment_id' => $equipment->id,
                'name' => $equipment->name,
                'name_en' => $equipment->name_en,
                'name_vi' => $equipment->name_vi,
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'type' => $item['type'],
                'rental_days' => $rentalDays,
                'line_total' => $lineTotal,
            ];

            $subtotal += $lineTotal;
        }

        // Calculate rental end date
        $rentalEndDate = null;
        if ($data['order_type'] === 'rental' && !empty($data['rental_start_date']) && !empty($data['rental_days'])) {
            $rentalEndDate = date('Y-m-d', strtotime($data['rental_start_date'] . " + {$data['rental_days']} days"));
        }

        $order = EquipmentOrder::create([
            'order_number' => EquipmentOrder::generateOrderNumber(),
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'] ?? null,
            'customer_phone' => $data['customer_phone'],
            'order_type' => $data['order_type'],
            'rental_start_date' => $data['rental_start_date'] ?? null,
            'rental_days' => $data['rental_days'] ?? null,
            'rental_end_date' => $rentalEndDate,
            'items' => $orderItems,
            'subtotal' => $subtotal,
            'total_amount' => $subtotal,
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
        ]);

        // Send email notifications
        try {
            MailNotificationService::sendEquipmentOrderNotification($order);
        } catch (\Exception $e) {
            \Log::error('Equipment Order Email Notification Error: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order,
        ], 201);
    }
}
