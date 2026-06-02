<?php

namespace Database\Seeders;

use App\Models\NavMenu;
use App\Models\NavMenuItem;
use Illuminate\Database\Seeder;

class NavMenuSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================
        // Xóa dữ liệu cũ (nếu có)
        // =============================================
        NavMenuItem::query()->delete();
        NavMenu::query()->delete();

        // =============================================
        // 1. MENU HEADER (menu chính trang khách)
        // =============================================
        $header = NavMenu::create([
            'name'      => 'header',
            'label'     => 'Menu chính (Header)',
            'is_active' => true,
        ]);

        $headerItems = [
            [
                'label'      => 'Trang chủ',
                'label_en'   => 'Home',
                'url'        => '/',
                'route_name' => 'Home',
                'icon'       => '🏠',
                'target'     => '_self',
                'is_visible' => true,
                'sort_order' => 0,
            ],
            [
                'label'      => 'Giới thiệu',
                'label_en'   => 'About',
                'url'        => '/about',
                'route_name' => 'About',
                'icon'       => 'ℹ️',
                'target'     => '_self',
                'is_visible' => true,
                'sort_order' => 1,
            ],
            [
                'label'      => 'Tours & Dịch vụ',
                'label_en'   => 'Tours',
                'url'        => '/tours',
                'route_name' => 'TourList',
                'icon'       => '🧭',
                'target'     => '_self',
                'is_visible' => true,
                'sort_order' => 2,
            ],
            [
                'label'      => 'Phòng nghỉ',
                'label_en'   => 'Rooms',
                'url'        => '/rooms',
                'route_name' => 'RoomList',
                'icon'       => '🏡',
                'target'     => '_self',
                'is_visible' => true,
                'sort_order' => 3,
            ],
            [
                'label'      => 'Cho thuê xe',
                'label_en'   => 'Car Rental',
                'url'        => '/car-rentals',
                'route_name' => 'CarRentals',
                'icon'       => '🚗',
                'target'     => '_self',
                'is_visible' => true,
                'sort_order' => 4,
            ],
            [
                'label'      => 'Nhà hàng',
                'label_en'   => 'Restaurant',
                'url'        => '/restaurant',
                'route_name' => 'Restaurant',
                'icon'       => '🍽️',
                'target'     => '_self',
                'is_visible' => true,
                'sort_order' => 5,
            ],
            [
                'label'      => 'Blog',
                'label_en'   => 'Blog',
                'url'        => '/blog',
                'route_name' => 'BlogList',
                'icon'       => '📝',
                'target'     => '_self',
                'is_visible' => true,
                'sort_order' => 6,
            ],
            [
                'label'      => 'Yolo Ocean Camp',
                'label_en'   => 'Yolo Ocean Camp',
                'url'        => '/yolo-ocean-camp',
                'route_name' => 'YoloOceanCamp',
                'icon'       => '🏖️',
                'target'     => '_self',
                'is_visible' => true,
                'sort_order' => 7,
            ],
        ];

        foreach ($headerItems as $item) {
            NavMenuItem::create(array_merge($item, ['menu_id' => $header->id]));
        }

        // =============================================
        // 2. MENU FOOTER
        // =============================================
        $footer = NavMenu::create([
            'name'      => 'footer',
            'label'     => 'Menu chân trang (Footer)',
            'is_active' => true,
        ]);

        // Footer - Nhóm 1: Khám phá
        $explore = NavMenuItem::create([
            'menu_id'    => $footer->id,
            'label'      => 'Khám phá',
            'label_en'   => 'Explore',
            'url'        => null,
            'icon'       => '🔍',
            'target'     => '_self',
            'is_visible' => true,
            'sort_order' => 0,
        ]);

        $exploreChildren = [
            ['label' => 'Tours & Dịch vụ', 'label_en' => 'Tours',    'url' => '/tours',       'route_name' => 'TourList',   'sort_order' => 0],
            ['label' => 'Phòng nghỉ',      'label_en' => 'Rooms',    'url' => '/rooms',       'route_name' => 'RoomList',   'sort_order' => 1],
            ['label' => 'Nhà hàng',         'label_en' => 'Restaurant', 'url' => '/restaurant', 'route_name' => 'Restaurant', 'sort_order' => 2],
            ['label' => 'Cho thuê xe',      'label_en' => 'Car Rental', 'url' => '/car-rentals', 'route_name' => 'CarRentals', 'sort_order' => 3],
            ['label' => 'Blog',             'label_en' => 'Blog',    'url' => '/blog',        'route_name' => 'BlogList',   'sort_order' => 4],
        ];

        foreach ($exploreChildren as $child) {
            NavMenuItem::create(array_merge($child, [
                'menu_id'    => $footer->id,
                'parent_id'  => $explore->id,
                'icon'       => null,
                'target'     => '_self',
                'is_visible' => true,
            ]));
        }

        // Footer - Nhóm 2: Hỗ trợ
        $support = NavMenuItem::create([
            'menu_id'    => $footer->id,
            'label'      => 'Hỗ trợ',
            'label_en'   => 'Support',
            'url'        => null,
            'icon'       => '💬',
            'target'     => '_self',
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $supportChildren = [
            ['label' => 'Giới thiệu',       'label_en' => 'About',          'url' => '/about',          'route_name' => 'About',         'sort_order' => 0],
            ['label' => 'Đặt phòng',         'label_en' => 'Book Now',       'url' => '/booking-form',   'route_name' => 'BookingForm',   'sort_order' => 1],
            ['label' => 'Tra cứu đặt phòng', 'label_en' => 'Booking Lookup', 'url' => '/booking-lookup', 'route_name' => 'BookingLookup', 'sort_order' => 2],
        ];

        foreach ($supportChildren as $child) {
            NavMenuItem::create(array_merge($child, [
                'menu_id'    => $footer->id,
                'parent_id'  => $support->id,
                'icon'       => null,
                'target'     => '_self',
                'is_visible' => true,
            ]));
        }

        $this->command->info("✅ Đã seed 2 bộ menu: header ({$header->items()->count()} mục) + footer ({$footer->items()->count()} mục)");
    }
}
