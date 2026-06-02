<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NavMenu;
use Illuminate\Http\JsonResponse;

class NavMenuController extends Controller
{
    /**
     * Lấy cấu trúc menu theo tên (public endpoint)
     * GET /api/nav-menus/{name}
     */
    public function getByName(string $name): JsonResponse
    {
        $menu = NavMenu::where('name', $name)
            ->where('is_active', true)
            ->with('rootItems')
            ->first();

        if (!$menu) {
            return response()->json(['data' => [], 'message' => 'Menu not found'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $menu->id,
                'name' => $menu->name,
                'label' => $menu->label,
                'items' => $this->buildTree($menu->rootItems),
            ]
        ]);
    }

    /**
     * Đệ quy build tree để đảm bảo chỉ trả về visible items
     */
    private function buildTree($items): array
    {
        $result = [];
        foreach ($items as $item) {
            if (!$item->is_visible) continue;
            $node = [
                'id' => $item->id,
                'label' => $item->label,
                'label_en' => $item->label_en,
                'url' => $item->url,
                'route_name' => $item->route_name,
                'icon' => $item->icon,
                'target' => $item->target,
                'children' => $this->buildTree($item->children),
            ];
            $result[] = $node;
        }
        return $result;
    }
}
