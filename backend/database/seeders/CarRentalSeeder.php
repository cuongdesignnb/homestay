<?php

namespace Database\Seeders;

use App\Models\CarRental;
use App\Models\CarRentalCategory;
use Illuminate\Database\Seeder;

class CarRentalSeeder extends Seeder
{
    public function run(): void
    {
        $categoryMap = CarRentalCategory::query()
            ->get()
            ->keyBy(fn ($category) => $category->slug)
            ->map(fn ($category) => $category->id)
            ->toArray();

        $cars = [
            [
                'name' => 'Honda Air Blade 125',
                'brand' => 'Honda',
                'model' => 'Air Blade',
                'type' => 'motorbike',
                'car_rental_category_id' => $categoryMap['motorbike'] ?? null,
                'seats' => 2,
                'transmission' => 'automatic',
                'fuel_type' => 'petrol',
                'price_per_day' => 160000,
                'location' => 'Cat Ba',
                'short_description' => 'Xe tay ga tiết kiệm, dễ đi trong phố và đảo.',
                'description' => 'Xe tay ga 125cc, cốp rộng, phù hợp di chuyển ngắn ngày.',
                'features' => ['air_conditioning'],
                'images' => ['/images/cars/airblade-1.jpg'],
                'status' => 'active',
                'is_available' => true,
                'average_rating' => 4.5,
                'total_reviews' => 18,
                'contact_phone' => '0123456789',
                'contact_whatsapp' => '84123456789',
            ],
            [
                'name' => 'Yamaha Sirius 110',
                'brand' => 'Yamaha',
                'model' => 'Sirius',
                'type' => 'motorbike',
                'car_rental_category_id' => $categoryMap['motorbike'] ?? null,
                'seats' => 2,
                'transmission' => 'manual',
                'fuel_type' => 'petrol',
                'price_per_day' => 120000,
                'location' => 'Cat Ba',
                'short_description' => 'Xe số bền bỉ, phù hợp đi xa và tiết kiệm xăng.',
                'description' => 'Xe số 110cc, máy khoẻ, dễ bảo dưỡng, phù hợp di chuyển đường dài.',
                'features' => ['bluetooth'],
                'images' => ['/images/cars/sirius-1.jpg'],
                'status' => 'active',
                'is_available' => true,
                'average_rating' => 4.3,
                'total_reviews' => 12,
                'contact_phone' => '0123456789',
                'contact_whatsapp' => '84123456789',
            ],
            [
                'name' => 'Toyota Vios 1.5 AT',
                'brand' => 'Toyota',
                'model' => 'Vios',
                'type' => 'sedan',
                'car_rental_category_id' => $categoryMap['sedan'] ?? null,
                'seats' => 5,
                'transmission' => 'automatic',
                'fuel_type' => 'petrol',
                'price_per_day' => 750000,
                'location' => 'Cat Ba',
                'short_description' => 'Sedan tiết kiệm, phù hợp di chuyển trong thành phố.',
                'description' => 'Xe sedan 5 chỗ, điều hòa mát, cốp rộng, thích hợp đi gia đình nhỏ hoặc công tác.',
                'features' => ['air_conditioning', 'bluetooth', 'gps', 'rear_camera'],
                'images' => ['/images/cars/vios-1.jpg'],
                'status' => 'active',
                'is_available' => true,
                'average_rating' => 4.6,
                'total_reviews' => 28,
                'contact_phone' => '0123456789',
                'contact_whatsapp' => '84123456789',
            ],
            [
                'name' => 'Kia Seltos 1.4 Turbo',
                'brand' => 'Kia',
                'model' => 'Seltos',
                'type' => 'suv',
                'car_rental_category_id' => $categoryMap['suv'] ?? null,
                'seats' => 5,
                'transmission' => 'automatic',
                'fuel_type' => 'petrol',
                'price_per_day' => 980000,
                'location' => 'Cat Ba',
                'short_description' => 'SUV gầm cao, phù hợp đi gia đình và đường đồi.',
                'description' => 'SUV 5 chỗ với động cơ mạnh mẽ, nhiều tiện nghi và an toàn.',
                'features' => ['air_conditioning', 'bluetooth', 'gps', 'cruise_control', 'rear_camera'],
                'images' => ['/images/cars/seltos-1.jpg'],
                'status' => 'active',
                'is_available' => true,
                'average_rating' => 4.7,
                'total_reviews' => 35,
                'contact_phone' => '0123456789',
                'contact_whatsapp' => '84123456789',
            ],
            [
                'name' => 'Ford Everest 7 chỗ',
                'brand' => 'Ford',
                'model' => 'Everest',
                'type' => 'suv',
                'car_rental_category_id' => $categoryMap['suv'] ?? null,
                'seats' => 7,
                'transmission' => 'automatic',
                'fuel_type' => 'diesel',
                'price_per_day' => 1500000,
                'location' => 'Hai Phong',
                'short_description' => 'SUV 7 chỗ rộng rãi, phù hợp nhóm đông.',
                'description' => 'SUV 7 chỗ, cốp rộng, phù hợp di chuyển đường dài và địa hình đa dạng.',
                'features' => ['air_conditioning', 'bluetooth', 'gps', 'parking_sensors', 'rear_camera'],
                'images' => ['/images/cars/everest-1.jpg'],
                'status' => 'active',
                'is_available' => true,
                'average_rating' => 4.8,
                'total_reviews' => 19,
                'contact_phone' => '0123456789',
                'contact_whatsapp' => '84123456789',
            ],
            [
                'name' => 'Hyundai Accent MT',
                'brand' => 'Hyundai',
                'model' => 'Accent',
                'type' => 'sedan',
                'car_rental_category_id' => $categoryMap['sedan'] ?? null,
                'seats' => 5,
                'transmission' => 'manual',
                'fuel_type' => 'petrol',
                'price_per_day' => 650000,
                'location' => 'Cat Ba',
                'short_description' => 'Sedan số sàn tiết kiệm, giá tốt.',
                'description' => 'Xe số sàn dễ lái, tiết kiệm nhiên liệu, phù hợp thuê dài ngày.',
                'features' => ['air_conditioning', 'bluetooth'],
                'images' => ['/images/cars/accent-1.jpg'],
                'status' => 'active',
                'is_available' => true,
                'average_rating' => 4.4,
                'total_reviews' => 14,
                'contact_phone' => '0123456789',
                'contact_whatsapp' => '84123456789',
            ],
            [
                'name' => 'Toyota Fortuner 7 chỗ',
                'brand' => 'Toyota',
                'model' => 'Fortuner',
                'type' => 'suv',
                'car_rental_category_id' => $categoryMap['suv'] ?? null,
                'seats' => 7,
                'transmission' => 'automatic',
                'fuel_type' => 'diesel',
                'price_per_day' => 1400000,
                'location' => 'Cat Ba',
                'short_description' => 'SUV 7 chỗ, mạnh mẽ, nội thất rộng.',
                'description' => 'Xe 7 chỗ cao cấp, phù hợp gia đình lớn và du lịch nhóm.',
                'features' => ['air_conditioning', 'bluetooth', 'gps', 'sunroof'],
                'images' => ['/images/cars/fortuner-1.jpg'],
                'status' => 'active',
                'is_available' => false,
                'average_rating' => 4.5,
                'total_reviews' => 22,
                'contact_phone' => '0123456789',
                'contact_whatsapp' => '84123456789',
            ],
            [
                'name' => 'Mitsubishi Xpander',
                'brand' => 'Mitsubishi',
                'model' => 'Xpander',
                'type' => 'mpv',
                'car_rental_category_id' => $categoryMap['mpv'] ?? null,
                'seats' => 7,
                'transmission' => 'automatic',
                'fuel_type' => 'petrol',
                'price_per_day' => 1100000,
                'location' => 'Hai Phong',
                'short_description' => 'MPV 7 chỗ linh hoạt, phù hợp gia đình.',
                'description' => 'Không gian rộng, chạy êm, phù hợp di chuyển trong phố và đường dài.',
                'features' => ['air_conditioning', 'bluetooth', 'rear_camera'],
                'images' => ['/images/cars/xpander-1.jpg'],
                'status' => 'active',
                'is_available' => true,
                'average_rating' => 4.3,
                'total_reviews' => 12,
                'contact_phone' => '0123456789',
                'contact_whatsapp' => '84123456789',
            ],
        ];

        foreach ($cars as $car) {
            $car['name_en'] = $car['name'];
            $car['name_vi'] = $car['name'];
            $car['brand_en'] = $car['brand'];
            $car['brand_vi'] = $car['brand'];
            $car['model_en'] = $car['model'];
            $car['model_vi'] = $car['model'];
            $car['type_en'] = $car['type'];
            $car['type_vi'] = $car['type'];
            $car['transmission_en'] = $car['transmission'];
            $car['transmission_vi'] = $car['transmission'];
            $car['fuel_type_en'] = $car['fuel_type'];
            $car['fuel_type_vi'] = $car['fuel_type'];
            $car['location_en'] = $car['location'];
            $car['location_vi'] = $car['location'];
            $car['short_description_en'] = $car['short_description'];
            $car['short_description_vi'] = $car['short_description'];
            $car['description_en'] = $car['description'];
            $car['description_vi'] = $car['description'];

            CarRental::updateOrCreate(
                [
                    'name' => $car['name'],
                    'location' => $car['location'],
                ],
                $car
            );
        }
    }
}
