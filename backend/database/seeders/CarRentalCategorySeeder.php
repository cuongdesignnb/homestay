<?php

namespace Database\Seeders;

use App\Models\CarRentalCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CarRentalCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Xe máy',
                'name_en' => 'Motorbike',
                'name_vi' => 'Xe máy',
                'description' => 'Xe máy phổ biến cho di chuyển linh hoạt.',
                'description_en' => 'Motorbikes for flexible travel.',
                'description_vi' => 'Xe máy phổ biến cho di chuyển linh hoạt.',
            ],
            [
                'name' => 'Sedan',
                'name_en' => 'Sedan',
                'name_vi' => 'Sedan',
                'description' => 'Sedan cho nhóm nhỏ và di chuyển đô thị.',
                'description_en' => 'Sedans for small groups and city travel.',
                'description_vi' => 'Sedan cho nhóm nhỏ và di chuyển đô thị.',
            ],
            [
                'name' => 'SUV',
                'name_en' => 'SUV',
                'name_vi' => 'SUV',
                'description' => 'SUV cho gia đình và đường dài.',
                'description_en' => 'SUVs for families and long trips.',
                'description_vi' => 'SUV cho gia đình và đường dài.',
            ],
            [
                'name' => 'MPV',
                'name_en' => 'MPV',
                'name_vi' => 'MPV',
                'description' => 'MPV nhiều chỗ ngồi, tiện nghi.',
                'description_en' => 'MPVs with roomy seating and comfort.',
                'description_vi' => 'MPV nhiều chỗ ngồi, tiện nghi.',
            ],
        ];

        foreach ($categories as $category) {
            $slug = Str::slug($category['name_en'] ?? $category['name']);
            CarRentalCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $category['name'],
                    'name_en' => $category['name_en'] ?? $category['name'],
                    'name_vi' => $category['name_vi'] ?? $category['name'],
                    'description' => $category['description'] ?? null,
                    'description_en' => $category['description_en'] ?? $category['description'] ?? null,
                    'description_vi' => $category['description_vi'] ?? $category['description'] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
