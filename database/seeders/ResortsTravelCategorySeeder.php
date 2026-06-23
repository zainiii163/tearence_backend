<?php

namespace Database\Seeders;

use App\Models\ResortsTravelCategory;
use Illuminate\Database\Seeder;

class ResortsTravelCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Luxury Resorts', 'slug' => 'luxury-resorts', 'type' => 'accommodation', 'description' => 'Premium luxury resort accommodations', 'icon' => 'star', 'sort_order' => 1],
            ['name' => 'Beach Hotels', 'slug' => 'beach-hotels', 'type' => 'accommodation', 'description' => 'Beachfront hotels and coastal properties', 'icon' => 'umbrella-beach', 'sort_order' => 2],
            ['name' => 'City Hotels', 'slug' => 'city-hotels', 'type' => 'accommodation', 'description' => 'Urban hotels and city stays', 'icon' => 'hotel', 'sort_order' => 3],
            ['name' => 'Holiday Homes & Villas', 'slug' => 'holiday-homes-villas', 'type' => 'accommodation', 'description' => 'Self-catering homes and private villas', 'icon' => 'villa', 'sort_order' => 4],
            ['name' => 'Airport Transfers', 'slug' => 'airport-transfers', 'type' => 'transport', 'description' => 'Airport pickup and drop-off services', 'icon' => 'car', 'sort_order' => 5],
            ['name' => 'Car Hire', 'slug' => 'car-hire', 'type' => 'transport', 'description' => 'Vehicle rental and chauffeur services', 'icon' => 'taxi', 'sort_order' => 6],
            ['name' => 'Shuttle & Tour Buses', 'slug' => 'shuttle-tour-buses', 'type' => 'transport', 'description' => 'Group transport and tour buses', 'icon' => 'bus', 'sort_order' => 7],
            ['name' => 'Guided Tours', 'slug' => 'guided-tours', 'type' => 'experience', 'description' => 'Expert-led city and cultural tours', 'icon' => 'map-marked', 'sort_order' => 8],
            ['name' => 'Adventure Experiences', 'slug' => 'adventure-experiences', 'type' => 'experience', 'description' => 'Outdoor and adventure packages', 'icon' => 'mountain', 'sort_order' => 9],
            ['name' => 'Wellness Retreats', 'slug' => 'wellness-retreats', 'type' => 'experience', 'description' => 'Spa, wellness and retreat experiences', 'icon' => 'compass', 'sort_order' => 10],
        ];

        foreach ($categories as $category) {
            ResortsTravelCategory::updateOrCreate(
                ['slug' => $category['slug']],
                array_merge($category, ['is_active' => true])
            );
        }

        $this->command?->info('Resorts & Travel categories seeded successfully.');
    }
}
