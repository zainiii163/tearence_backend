<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SponsoredCategory;

class SponsoredCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Property',
                'slug' => 'property',
                'icon' => '🏠',
                'color' => 'from-blue-500 to-blue-600',
                'description' => 'Properties, houses, apartments, and commercial real estate',
            ],
            [
                'name' => 'Cars & Vehicles',
                'slug' => 'cars-vehicles',
                'icon' => '🚗',
                'color' => 'from-green-500 to-green-600',
                'description' => 'Cars, motorcycles, boats, and other vehicles',
            ],
            [
                'name' => 'Jobs & Services',
                'slug' => 'jobs-services',
                'icon' => '💼',
                'color' => 'from-indigo-500 to-indigo-600',
                'description' => 'Job listings, professional services, and business opportunities',
            ],
            [
                'name' => 'Business Opportunities',
                'slug' => 'business-opportunities',
                'icon' => '�',
                'color' => 'from-purple-500 to-purple-600',
                'description' => 'Business investments, partnerships, and opportunities',
            ],
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'icon' => '📱',
                'color' => 'from-gray-500 to-gray-600',
                'description' => 'Computers, phones, gadgets, and electronic devices',
            ],
            [
                'name' => 'Fashion & Beauty',
                'slug' => 'fashion-beauty',
                'icon' => '👗',
                'color' => 'from-pink-500 to-pink-600',
                'description' => 'Clothing, accessories, and beauty products',
            ],
            [
                'name' => 'Travel & Experiences',
                'slug' => 'travel-experiences',
                'icon' => '✈️',
                'color' => 'from-cyan-500 to-cyan-600',
                'description' => 'Travel packages, experiences, and tourism services',
            ],
            [
                'name' => 'Events & Tickets',
                'slug' => 'events-tickets',
                'icon' => '🎫',
                'color' => 'from-orange-500 to-orange-600',
                'description' => 'Concerts, sports, and event tickets',
            ],
            [
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'icon' => '🏡',
                'color' => 'from-yellow-500 to-yellow-600',
                'description' => 'Furniture, appliances, and garden supplies',
            ],
            [
                'name' => 'Health & Wellness',
                'slug' => 'health-wellness',
                'icon' => '🏥',
                'color' => 'from-red-500 to-red-600',
                'description' => 'Healthcare, fitness, and wellness services',
            ],
            [
                'name' => 'Education',
                'slug' => 'education',
                'icon' => '�',
                'color' => 'from-teal-500 to-teal-600',
                'description' => 'Courses, training, and educational materials',
            ],
            [
                'name' => 'Sports & Fitness',
                'slug' => 'sports-fitness',
                'icon' => '⚽',
                'color' => 'from-lime-500 to-lime-600',
                'description' => 'Sports equipment, fitness services, and activities',
            ],
            [
                'name' => 'Food & Dining',
                'slug' => 'food-dining',
                'icon' => '🍔',
                'color' => 'from-amber-500 to-amber-600',
                'description' => 'Restaurants, food delivery, and dining services',
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'icon' => '📦',
                'color' => 'from-gray-400 to-gray-500',
                'description' => 'Other categories not listed above',
            ],
        ];

        foreach ($categories as $category) {
            SponsoredCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
