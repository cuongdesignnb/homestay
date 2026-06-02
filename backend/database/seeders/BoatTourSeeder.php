<?php

namespace Database\Seeders;

use App\Models\BoatTour;
use Illuminate\Database\Seeder;

class BoatTourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $boatTours = [
            [
                'name' => 'Cat Ba Island Discovery',
                'description' => 'Explore the pristine waters around Cat Ba Island with this amazing boat tour. Visit hidden caves, beautiful beaches, and experience the natural beauty of Ha Long Bay. Our experienced guides will take you to the most spectacular spots while ensuring your safety and comfort throughout the journey.',
                'short_description' => 'Discover the stunning Cat Ba Island with caves, beaches, and breathtaking scenery.',
                'price' => 45.00,
                'duration' => '6 hours',
                'included_services' => [
                    'Professional tour guide',
                    'Lunch and refreshments',
                    'Life jackets and safety equipment',
                    'Cave entrance fees',
                    'Round trip boat transfer'
                ],
                'excluded_services' => [
                    'Personal expenses',
                    'Travel insurance',
                    'Alcoholic beverages',
                    'Tips for guide'
                ],
                'itinerary' => "8:00 AM - Departure from main pier\n9:30 AM - First cave exploration\n11:00 AM - Beach swimming and relaxation\n12:30 PM - Lunch on board\n2:00 PM - Second cave visit\n3:30 PM - Scenic cruise back\n4:30 PM - Return to pier",
                'departure_location' => 'Cat Ba Island Main Pier',
                'departure_time' => '8:00 AM',
                'max_participants' => 12,
                'image_gallery' => [
                    'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800',
                    'https://images.unsplash.com/photo-1571847140471-1d7766e825ea?w=800',
                    'https://images.unsplash.com/photo-1580401537113-d6c69267f16c?w=800'
                ],
                'contact_whatsapp' => '+84987654321',
                'status' => 'active'
            ],
            [
                'name' => 'Sunset Cruise Adventure',
                'description' => 'Experience the magical sunset over the waters with our romantic sunset cruise. Perfect for couples or anyone wanting to enjoy the golden hour on the water. Includes welcome drinks, light snacks, and the most beautiful views you can imagine.',
                'short_description' => 'Romantic sunset cruise with drinks, snacks, and stunning golden hour views.',
                'price' => 35.00,
                'duration' => '3 hours',
                'included_services' => [
                    'Welcome drinks',
                    'Light snacks and canapés',
                    'Professional photography',
                    'Sunset viewing spots',
                    'Comfortable seating'
                ],
                'excluded_services' => [
                    'Full dinner',
                    'Alcoholic beverages (available for purchase)',
                    'Personal photography'
                ],
                'itinerary' => "5:00 PM - Boarding and welcome drinks\n5:30 PM - Cruise to sunset point\n6:30 PM - Sunset viewing and photography\n7:30 PM - Light snacks service\n8:00 PM - Return cruise",
                'departure_location' => 'Harbour View Marina',
                'departure_time' => '5:00 PM',
                'max_participants' => 20,
                'image_gallery' => [
                    'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
                    'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800'
                ],
                'contact_whatsapp' => '+84987654321',
                'status' => 'active'
            ],
            [
                'name' => 'Fishing Village Experience',
                'description' => 'Immerse yourself in the local culture with our authentic fishing village tour. Meet local fishermen, learn traditional fishing techniques, enjoy fresh seafood, and experience the daily life of a Vietnamese fishing community.',
                'short_description' => 'Authentic cultural experience in traditional Vietnamese fishing village.',
                'price' => 55.00,
                'duration' => '4 hours',
                'included_services' => [
                    'Cultural guide',
                    'Fresh seafood lunch',
                    'Fishing demonstration',
                    'Village tour',
                    'Boat transportation'
                ],
                'excluded_services' => [
                    'Personal souvenirs',
                    'Additional beverages',
                    'Shopping expenses'
                ],
                'itinerary' => "9:00 AM - Meet at departure point\n9:30 AM - Boat ride to fishing village\n10:30 AM - Village tour and cultural activities\n12:00 PM - Fresh seafood lunch\n1:30 PM - Fishing experience\n2:30 PM - Return journey",
                'departure_location' => 'Local Fishing Port',
                'departure_time' => '9:00 AM',
                'max_participants' => 8,
                'image_gallery' => [
                    'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800',
                    'https://images.unsplash.com/photo-1566649957573-4b079aa7a0b8?w=800'
                ],
                'contact_whatsapp' => '+84987654321',
                'status' => 'active'
            ]
        ];

        foreach ($boatTours as $tourData) {
            BoatTour::create($tourData);
        }
    }
}
