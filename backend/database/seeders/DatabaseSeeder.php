<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Room;
use App\Models\Tour;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update admin user
        User::updateOrCreate(
            ['email' => 'admin@homestay.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123456'),
                'phone' => '0123456789',
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Create or update demo user
        User::updateOrCreate(
            ['email' => 'user@homestay.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('user123456'),
                'phone' => '0987654321',
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Create rooms
        $rooms = [
            [
                'name' => 'Deluxe Double Room with Mountain View',
                'type' => 'double',
                'capacity' => 2,
                'beds' => 1,
                'bathrooms' => 1,
                'size' => 25,
                'price_per_night' => 500000,
                'discount_price' => 450000,
                'amenities' => ['WiFi', 'Air Conditioning', 'TV', 'Mini Bar', 'Mountain View'],
                'description' => 'Spacious double room with stunning mountain views and modern amenities.',
            ],
            [
                'name' => 'Family Suite',
                'type' => 'family',
                'capacity' => 4,
                'beds' => 2,
                'bathrooms' => 2,
                'size' => 45,
                'price_per_night' => 900000,
                'amenities' => ['WiFi', 'Air Conditioning', 'TV', 'Kitchen', 'Balcony'],
                'description' => 'Perfect for families with two bedrooms and a living area.',
            ],
            [
                'name' => 'Cozy Single Room',
                'type' => 'single',
                'capacity' => 1,
                'beds' => 1,
                'bathrooms' => 1,
                'size' => 15,
                'price_per_night' => 300000,
                'amenities' => ['WiFi', 'Air Conditioning', 'Desk'],
                'description' => 'Comfortable single room perfect for solo travelers.',
            ],
        ];

        foreach ($rooms as $room) {
            $room['slug'] = Str::slug($room['name']);
            $room['images'] = ['/images/rooms/room-' . rand(1, 5) . '.jpg'];
            $room['status'] = 'available';
            $room['meta_title'] = $room['name'];
            $room['meta_description'] = $room['description'];
            Room::updateOrCreate(
                ['slug' => $room['slug']],
                $room
            );
        }

        // Create tours
        $tours = [
            [
                'name' => 'Mountain Trekking Adventure',
                'duration' => 2,
                'duration_unit' => 'days',
                'price_per_person' => 1200000,
                'max_participants' => 15,
                'min_participants' => 4,
                'difficulty_level' => 'moderate',
                'departure_location' => 'Happy Island Tour Reception',
                'description' => 'Experience the breathtaking mountain trails with experienced guides.',
                'itinerary' => [
                    'Day 1: Trek to base camp, lunch at scenic viewpoint',
                    'Day 2: Summit attempt, return to Happy Island Tour'
                ],
                'includes' => ['Professional guide', 'Meals', 'Equipment', 'Transportation'],
                'excludes' => ['Personal expenses', 'Insurance'],
            ],
            [
                'name' => 'Cultural Village Tour',
                'duration' => 1,
                'duration_unit' => 'days',
                'price_per_person' => 600000,
                'discount_price' => 500000,
                'max_participants' => 20,
                'min_participants' => 2,
                'difficulty_level' => 'easy',
                'departure_location' => 'Happy Island Tour Reception',
                'description' => 'Explore local villages and learn about traditional culture.',
                'itinerary' => [
                    'Morning: Village visit and traditional craft workshops',
                    'Afternoon: Local market and traditional lunch'
                ],
                'includes' => ['Guide', 'Lunch', 'Transportation', 'Entrance fees'],
                'excludes' => ['Personal purchases'],
            ],
        ];

        foreach ($tours as $tour) {
            $tour['slug'] = Str::slug($tour['name']);
            $tour['images'] = ['/images/tours/tour-' . rand(1, 5) . '.jpg'];
            $tour['status'] = 'active';
            $tour['meta_title'] = $tour['name'];
            $tour['meta_description'] = $tour['description'];
            Tour::updateOrCreate(
                ['slug' => $tour['slug']],
                $tour
            );
        }

        // Create blog categories
        $categories = [
            [
                'name_en' => 'Travel Tips',
                'name_vi' => 'Mẹo du lịch',
                'slug' => 'travel-tips',
                'description_en' => 'Helpful tips for travelers',
                'description_vi' => 'Những mẹo hữu ích dành cho du khách',
            ],
            [
                'name_en' => 'Local Culture',
                'name_vi' => 'Văn hóa địa phương',
                'slug' => 'local-culture',
                'description_en' => 'Insights into local traditions',
                'description_vi' => 'Khám phá phong tục và đời sống bản địa',
            ],
            [
                'name_en' => 'Destinations',
                'name_vi' => 'Điểm đến',
                'slug' => 'destinations',
                'description_en' => 'Must-visit places',
                'description_vi' => 'Những điểm tham quan không thể bỏ lỡ',
            ],
            [
                'name_en' => 'Food & Dining',
                'name_vi' => 'Ẩm thực & ăn uống',
                'slug' => 'food-dining',
                'description_en' => 'Local cuisine guide',
                'description_vi' => 'Hành trình khám phá ẩm thực địa phương',
            ],
        ];

        foreach ($categories as $category) {
            $category['name'] = $category['name_en'];
            $category['description'] = $category['description_en'];
            BlogCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        // Create blog posts
        $posts = [
            [
                'title_en' => 'Top 10 Things to Do in Our Region',
                'title_vi' => '10 trải nghiệm không thể bỏ lỡ tại khu vực',
                'category_id' => 3,
                'content_en' => 'Discover the best attractions and activities in our beautiful region...',
                'content_vi' => 'Khám phá những hoạt động và điểm đến hấp dẫn nhất tại vùng đất xinh đẹp của chúng tôi...',
                'excerpt_en' => 'A comprehensive guide to the top experiences in the area.',
                'excerpt_vi' => 'Cẩm nang đầy đủ về những trải nghiệm nổi bật nhất.',
                'tags' => ['travel', 'guide', 'attractions'],
            ],
            [
                'title_en' => 'Traditional Cuisine You Must Try',
                'title_vi' => 'Ẩm thực truyền thống phải thử một lần',
                'category_id' => 4,
                'content_en' => 'Explore the rich flavors of local traditional dishes...',
                'content_vi' => 'Khám phá hương vị đậm đà của những món ăn truyền thống địa phương...',
                'excerpt_en' => 'A culinary journey through traditional Vietnamese cuisine.',
                'excerpt_vi' => 'Hành trình ẩm thực qua những món Việt đặc sắc.',
                'tags' => ['food', 'culture', 'vietnamese'],
            ],
            [
                'title_en' => 'Best Time to Visit for Mountain Views',
                'title_vi' => 'Thời điểm đẹp nhất để săn mây và ngắm núi',
                'category_id' => 1,
                'content_en' => 'Learn about the optimal seasons for stunning mountain scenery...',
                'content_vi' => 'Tìm hiểu mùa lý tưởng nhất để chiêm ngưỡng cảnh núi hùng vĩ...',
                'excerpt_en' => 'Planning your visit for the best weather and views.',
                'excerpt_vi' => 'Lên kế hoạch chuyến đi với thời tiết và cảnh quan đẹp nhất.',
                'tags' => ['travel tips', 'mountains', 'weather'],
            ],
        ];

        foreach ($posts as $post) {
            $post['title'] = $post['title_en'];
            $post['slug'] = Str::slug($post['title_en']);
            $post['slug_en'] = $post['slug'];
            $post['slug_vi'] = Str::slug($post['title_vi']);
            $post['excerpt'] = $post['excerpt_en'];
            $post['content'] = $post['content_en'];
            $post['meta_title_en'] = $post['title_en'];
            $post['meta_title_vi'] = $post['title_vi'];
            $post['meta_title'] = $post['meta_title_en'];
            $post['meta_description_en'] = $post['excerpt_en'];
            $post['meta_description_vi'] = $post['excerpt_vi'];
            $post['meta_description'] = $post['meta_description_en'];
            $post['meta_keywords_en'] = implode(', ', $post['tags']);
            $post['meta_keywords_vi'] = implode(', ', $post['tags']);
            $post['meta_keywords'] = $post['meta_keywords_en'];
            $post['author_id'] = 1;
            $post['status'] = 'published';
            $post['published_at'] = now();
            $post['featured_image'] = '/images/blog/post-' . rand(1, 5) . '.jpg';
            BlogPost::updateOrCreate(
                ['slug' => $post['slug']],
                $post
            );
        }

        // Seed settings
        $this->call(SettingsSeeder::class);
        
        // Seed car rentals
            $this->call(CarRentalCategorySeeder::class);
            $this->call(CarRentalSeeder::class);
    }
}
