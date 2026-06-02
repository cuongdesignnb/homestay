<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavMenu;
use App\Models\NavMenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminNavMenuController extends Controller
{
    // =========================================================
    // DANH SÁCH CÁC ROUTE FRONTEND ĐỂ GỢI Ý (đồng bộ với router/index.js)
    // =========================================================
    private const AVAILABLE_ROUTES = [
        // Công khai
        ['path' => '/', 'name' => 'Home', 'label_vi' => 'Trang chủ', 'label_en' => 'Home', 'group' => 'public'],
        ['path' => '/yolo-ocean-camp', 'name' => 'YoloOceanCamp', 'label_vi' => 'Yolo Ocean Camp', 'label_en' => 'Yolo Ocean Camp', 'group' => 'public'],
        ['path' => '/about', 'name' => 'About', 'label_vi' => 'Giới thiệu', 'label_en' => 'About', 'group' => 'public'],
        ['path' => '/rooms', 'name' => 'RoomList', 'label_vi' => 'Phòng nghỉ', 'label_en' => 'Rooms', 'group' => 'public'],
        ['path' => '/tours', 'name' => 'TourList', 'label_vi' => 'Tours & Dịch vụ', 'label_en' => 'Tours', 'group' => 'public'],
        ['path' => '/car-rentals', 'name' => 'CarRentals', 'label_vi' => 'Cho thuê xe', 'label_en' => 'Car Rentals', 'group' => 'public'],
        ['path' => '/blog', 'name' => 'BlogList', 'label_vi' => 'Blog', 'label_en' => 'Blog', 'group' => 'public'],
        ['path' => '/restaurant', 'name' => 'Restaurant', 'label_vi' => 'Nhà hàng', 'label_en' => 'Restaurant', 'group' => 'public'],
        ['path' => '/booking-form', 'name' => 'BookingForm', 'label_vi' => 'Đặt phòng', 'label_en' => 'Book Now', 'group' => 'public'],
        ['path' => '/booking-lookup', 'name' => 'BookingLookup', 'label_vi' => 'Tra cứu đặt phòng', 'label_en' => 'Booking Lookup', 'group' => 'public'],
        // Xác thực
        ['path' => '/login', 'name' => 'Login', 'label_vi' => 'Đăng nhập', 'label_en' => 'Login', 'group' => 'auth'],
        ['path' => '/register', 'name' => 'Register', 'label_vi' => 'Đăng ký', 'label_en' => 'Register', 'group' => 'auth'],
        ['path' => '/profile', 'name' => 'Profile', 'label_vi' => 'Hồ sơ cá nhân', 'label_en' => 'Profile', 'group' => 'auth'],
        ['path' => '/bookings', 'name' => 'Bookings', 'label_vi' => 'Lịch sử đặt phòng', 'label_en' => 'My Bookings', 'group' => 'auth'],
        // Admin
        ['path' => '/admin', 'name' => 'AdminDashboard', 'label_vi' => 'Tổng quan Admin', 'label_en' => 'Admin Dashboard', 'group' => 'admin'],
        ['path' => '/admin/rooms', 'name' => 'AdminRooms', 'label_vi' => 'QL Phòng', 'label_en' => 'Manage Rooms', 'group' => 'admin'],
        ['path' => '/admin/tours', 'name' => 'AdminTours', 'label_vi' => 'QL Tours', 'label_en' => 'Manage Tours', 'group' => 'admin'],
        ['path' => '/admin/bookings', 'name' => 'AdminBookings', 'label_vi' => 'QL Đặt phòng', 'label_en' => 'Manage Bookings', 'group' => 'admin'],
        ['path' => '/admin/blog', 'name' => 'AdminBlog', 'label_vi' => 'QL Blog', 'label_en' => 'Manage Blog', 'group' => 'admin'],
        ['path' => '/admin/settings', 'name' => 'AdminSettings', 'label_vi' => 'Cài đặt hệ thống', 'label_en' => 'System Settings', 'group' => 'admin'],
        ['path' => '/admin/nav-menus', 'name' => 'AdminNavMenus', 'label_vi' => 'QL Menu điều hướng', 'label_en' => 'Manage Nav Menus', 'group' => 'admin'],
    ];

    // =========================================================
    // INDEX — Danh sách tất cả bộ menu
    // =========================================================
    public function index(): JsonResponse
    {
        $menus = NavMenu::withCount('items')->orderBy('created_at')->get();
        return response()->json(['data' => $menus]);
    }

    // =========================================================
    // SHOW — Chi tiết 1 bộ menu kèm toàn bộ cây items
    // =========================================================
    public function show(NavMenu $navMenu): JsonResponse
    {
        $navMenu->load('rootItems');
        return response()->json([
            'data' => [
                'id' => $navMenu->id,
                'name' => $navMenu->name,
                'label' => $navMenu->label,
                'is_active' => $navMenu->is_active,
                'items' => $this->serializeTree($navMenu->rootItems),
            ]
        ]);
    }

    // =========================================================
    // STORE — Tạo bộ menu mới
    // =========================================================
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:100|unique:nav_menus,name|regex:/^[a-z0-9_-]+$/',
            'label' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $menu = NavMenu::create($validated);
        return response()->json(['data' => $menu, 'message' => 'Đã tạo bộ menu mới'], 201);
    }

    // =========================================================
    // UPDATE — Cập nhật thông tin bộ menu
    // =========================================================
    public function update(Request $request, NavMenu $navMenu): JsonResponse
    {
        $validated = $request->validate([
            'name'  => ['string', 'max:100', 'regex:/^[a-z0-9_-]+$/', Rule::unique('nav_menus', 'name')->ignore($navMenu->id)],
            'label' => 'string|max:255',
            'is_active' => 'boolean',
        ]);

        $navMenu->update($validated);
        return response()->json(['data' => $navMenu, 'message' => 'Đã cập nhật bộ menu']);
    }

    // =========================================================
    // DESTROY — Xóa bộ menu (cascade xóa items)
    // =========================================================
    public function destroy(NavMenu $navMenu): JsonResponse
    {
        $navMenu->delete();
        return response()->json(['message' => 'Đã xóa bộ menu']);
    }

    // =========================================================
    // UPDATE TREE — Nhận cấu trúc cây mới sau drag & drop
    // Body: { items: [ { id, parent_id, sort_order, label, ... } ] }
    // =========================================================
    public function updateTree(Request $request, NavMenu $navMenu): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.parent_id' => 'nullable|integer',
            'items.*.sort_order' => 'required|integer|min:0',
            'items.*.label' => 'required|string|max:255',
            'items.*.label_en' => 'nullable|string|max:255',
            'items.*.url' => 'nullable|string|max:500',
            'items.*.route_name' => 'nullable|string|max:255',
            'items.*.icon' => 'nullable|string|max:100',
            'items.*.target' => 'nullable|in:_self,_blank',
            'items.*.is_visible' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $navMenu) {
            // Lấy danh sách ID hợp lệ thuộc menu này
            $existingIds = $navMenu->items()->pluck('id')->toArray();
            $incomingIds = collect($request->items)->pluck('id')->toArray();

            // Xóa items bị loại bỏ
            $toDelete = array_diff($existingIds, $incomingIds);
            if (!empty($toDelete)) {
                NavMenuItem::whereIn('id', $toDelete)->delete();
            }

            // Cập nhật từng item — cần pass qua 2 lần:
            // Lần 1: set parent_id = null tất cả (tránh FK constraint khi reorder)
            NavMenuItem::whereIn('id', $incomingIds)->update(['parent_id' => null]);

            // Lần 2: update từng item với đầy đủ thông tin
            foreach ($request->items as $itemData) {
                NavMenuItem::where('id', $itemData['id'])
                    ->where('menu_id', $navMenu->id)
                    ->update([
                        'parent_id'  => $itemData['parent_id'] ?? null,
                        'sort_order' => $itemData['sort_order'],
                        'label'      => $itemData['label'],
                        'label_en'   => $itemData['label_en'] ?? null,
                        'url'        => $itemData['url'] ?? null,
                        'route_name' => $itemData['route_name'] ?? null,
                        'icon'       => $itemData['icon'] ?? null,
                        'target'     => $itemData['target'] ?? '_self',
                        'is_visible' => $itemData['is_visible'] ?? true,
                    ]);
            }
        });

        // Trả về cây mới nhất
        $navMenu->load('rootItems');
        return response()->json([
            'data' => $this->serializeTree($navMenu->rootItems),
            'message' => 'Đã lưu cấu trúc menu',
        ]);
    }

    // =========================================================
    // ADD ITEM — Thêm 1 item vào menu
    // =========================================================
    public function addItem(Request $request, NavMenu $navMenu): JsonResponse
    {
        $validated = $request->validate([
            'parent_id'  => 'nullable|integer|exists:nav_menu_items,id',
            'label'      => 'required|string|max:255',
            'label_en'   => 'nullable|string|max:255',
            'url'        => 'nullable|string|max:500',
            'route_name' => 'nullable|string|max:255',
            'icon'       => 'nullable|string|max:100',
            'target'     => 'nullable|in:_self,_blank',
            'is_visible' => 'boolean',
        ]);

        $maxOrder = NavMenuItem::where('menu_id', $navMenu->id)
            ->where('parent_id', $validated['parent_id'] ?? null)
            ->max('sort_order') ?? -1;

        $item = NavMenuItem::create(array_merge($validated, [
            'menu_id'    => $navMenu->id,
            'sort_order' => $maxOrder + 1,
            'is_visible' => $validated['is_visible'] ?? true,
        ]));

        return response()->json(['data' => $item, 'message' => 'Đã thêm mục menu'], 201);
    }

    // =========================================================
    // AVAILABLE ROUTES — Danh sách routes gợi ý
    // =========================================================
    public function availableRoutes(): JsonResponse
    {
        $groups = collect(self::AVAILABLE_ROUTES)->groupBy('group')->map(function ($routes) {
            return $routes->values();
        });

        return response()->json([
            'data' => $groups,
            'all'  => array_values(self::AVAILABLE_ROUTES),
        ]);
    }

    // =========================================================
    // HELPER — Serialize tree thành array
    // =========================================================
    private function serializeTree($items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'id'         => $item->id,
                'parent_id'  => $item->parent_id,
                'label'      => $item->label,
                'label_en'   => $item->label_en,
                'url'        => $item->url,
                'route_name' => $item->route_name,
                'icon'       => $item->icon,
                'target'     => $item->target,
                'is_visible' => $item->is_visible,
                'sort_order' => $item->sort_order,
                'children'   => $this->serializeTree($item->children),
            ];
        }
        return $result;
    }
}
