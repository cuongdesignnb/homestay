<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Restaurant Settings
        $restaurantSettings = [
            'restaurant_name' => [
                'vi' => 'Nhà hàng Cat Ba Countryside Happy Island Tour',
                'en' => 'Cat Ba Countryside Happy Island Tour Restaurant',
            ],
            'restaurant_intro' => [
                'vi' => 'Chào mừng bạn đến với nhà hàng của chúng tôi! Chúng tôi phục vụ các món ăn truyền thống Việt Nam với nguyên liệu tươi ngon từ địa phương.',
                'en' => 'Welcome to our restaurant! We serve traditional Vietnamese dishes with fresh local ingredients.',
            ],
            'restaurant_opening_hours' => [
                'vi' => '7:00 - 22:00',
                'en' => '7:00 AM - 10:00 PM',
            ],
            'restaurant_phone' => [
                'vi' => '+84 123 456 789',
                'en' => '+84 123 456 789',
            ],
            'restaurant_banner' => [
                'vi' => '',
                'en' => '',
            ],
        ];

        foreach ($restaurantSettings as $key => $values) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'group' => 'restaurant',
                    'value_vi' => $values['vi'],
                    'value_en' => $values['en'],
                    'type' => $key === 'restaurant_intro' ? 'textarea' : 'text',
                    'label' => ucwords(str_replace('_', ' ', $key)),
                ]
            );
        }

        // Create Menu Categories
        $hotPotCategory = MenuCategory::create([
            'name' => 'Lẩu',
            'name_en' => 'Hot Pot',
            'slug' => 'hot-pot',
            'description' => 'Lẩu nóng hổi, nguyên liệu tươi ngon, phục vụ 2-3 người',
            'description_en' => 'Hot pot with fresh ingredients, serves 2-3 people',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $toppingsCategory = MenuCategory::create([
            'name' => 'Topping thêm',
            'name_en' => 'Extra Toppings',
            'slug' => 'extra-toppings',
            'description' => 'Các loại topping thêm cho lẩu',
            'description_en' => 'Extra toppings for hot pot',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // Create Hot Pot Items
        $hotPotItems = [
            [
                'name' => 'Lẩu rau củ',
                'name_en' => 'Hot pot with vegetable',
                'description' => 'Nước dùng, đậu phụ, trứng, nấm, rau, khoai tây, cà rốt...',
                'description_en' => 'Soup broth, tofu, egg, mushroom, vegetable, potato, carrot...',
                'price' => 200000,
                'note' => 'Cho 2-3 người',
                'note_en' => 'For 2-3 people',
                'sort_order' => 1,
            ],
            [
                'name' => 'Lẩu gà',
                'name_en' => 'Hot pot with chicken',
                'description' => 'Nước dùng, đậu phụ, gà, trứng, nấm, rau, khoai tây, cà rốt...',
                'description_en' => 'Soup broth, tofu, chicken, egg, mushroom, vegetable, potato, carrot...',
                'price' => 300000,
                'note' => 'Cho 2-3 người',
                'note_en' => 'For 2-3 people',
                'sort_order' => 2,
            ],
            [
                'name' => 'Lẩu heo',
                'name_en' => 'Hot pot with pork',
                'description' => 'Nước dùng, đậu phụ, thịt heo, trứng, nấm, rau, khoai tây, cà rốt...',
                'description_en' => 'Soup broth, tofu, pork, egg, mushroom, vegetable, potato, carrot...',
                'price' => 300000,
                'note' => 'Cho 2-3 người',
                'note_en' => 'For 2-3 people',
                'sort_order' => 3,
            ],
            [
                'name' => 'Lẩu bò',
                'name_en' => 'Hot pot with beef',
                'description' => 'Nước dùng, đậu phụ, thịt bò, trứng, nấm, rau, khoai tây, cà rốt...',
                'description_en' => 'Soup broth, tofu, beef, egg, mushroom, vegetable, potato, carrot...',
                'price' => 350000,
                'note' => 'Cho 2-3 người',
                'note_en' => 'For 2-3 people',
                'sort_order' => 4,
            ],
            [
                'name' => 'Lẩu hải sản',
                'name_en' => 'Hot pot with sea food',
                'description' => 'Nước dùng, đậu phụ, hải sản tươi, trứng, nấm, rau, khoai tây, cà rốt...',
                'description_en' => 'Soup broth, tofu, fresh seafood, egg, mushroom, vegetable, potato, carrot...',
                'price' => 500000,
                'note' => 'Cho 2-3 người. Vui lòng đặt trước 2 tiếng',
                'note_en' => 'For 2-3 people. Please order 2 hours before',
                'sort_order' => 5,
                'is_featured' => true,
            ],
        ];

        foreach ($hotPotItems as $item) {
            MenuItem::create(array_merge($item, [
                'category_id' => $hotPotCategory->id,
                'is_available' => true,
            ]));
        }

        // Create Toppings Items
        $toppingItems = [
            [
                'name' => 'Thịt heo',
                'name_en' => 'Pork',
                'price' => 100000,
                'unit' => 'đĩa',
                'unit_en' => 'plate',
                'sort_order' => 1,
            ],
            [
                'name' => 'Thịt gà',
                'name_en' => 'Chicken',
                'price' => 100000,
                'unit' => 'đĩa',
                'unit_en' => 'plate',
                'sort_order' => 2,
            ],
            [
                'name' => 'Thịt bò',
                'name_en' => 'Beef',
                'price' => 150000,
                'unit' => 'đĩa',
                'unit_en' => 'plate',
                'sort_order' => 3,
            ],
            [
                'name' => 'Đậu phụ hoặc nấm',
                'name_en' => 'Tofu or mushroom',
                'price' => 40000,
                'unit' => 'đĩa',
                'unit_en' => 'plate',
                'sort_order' => 4,
            ],
            [
                'name' => 'Rau củ',
                'name_en' => 'Vegetable',
                'price' => 30000,
                'unit' => 'đĩa',
                'unit_en' => 'plate',
                'sort_order' => 5,
            ],
        ];

        foreach ($toppingItems as $item) {
            MenuItem::create(array_merge($item, [
                'category_id' => $toppingsCategory->id,
                'is_available' => true,
            ]));
        }

        $this->command->info('Menu seeded successfully!');
    }
}
