<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SponsoredCategory;

class SponsoredCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Technology',
                'slug' => 'technology',
                'icon' => 'laptop',
                'description' => 'Technology sponsored listings including electronics, software, and gadgets',
                'count' => 0,
                'active' => true,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'icon' => 'briefcase',
                'description' => 'Business services and opportunities',
                'count' => 0,
                'active' => true,
            ],
            [
                'name' => 'Real Estate',
                'slug' => 'real-estate',
                'icon' => 'home',
                'description' => 'Property listings and real estate services',
                'count' => 0,
                'active' => true,
            ],
            [
                'name' => 'Vehicles',
                'slug' => 'vehicles',
                'icon' => 'car',
                'description' => 'Cars, motorcycles, and other vehicles',
                'count' => 0,
                'active' => true,
            ],
            [
                'name' => 'Fashion',
                'slug' => 'fashion',
                'icon' => 'shirt',
                'description' => 'Clothing, accessories, and fashion items',
                'count' => 0,
                'active' => true,
            ],
            [
                'name' => 'Education',
                'slug' => 'education',
                'icon' => 'graduation-cap',
                'description' => 'Educational services and courses',
                'count' => 0,
                'active' => true,
            ],
            [
                'name' => 'Health & Fitness',
                'slug' => 'health-fitness',
                'icon' => 'heart',
                'description' => 'Health services, fitness programs, and wellness products',
                'count' => 0,
                'active' => true,
            ],
            [
                'name' => 'Travel & Tourism',
                'slug' => 'travel-tourism',
                'icon' => 'plane',
                'description' => 'Travel packages, tours, and tourism services',
                'count' => 0,
                'active' => true,
            ],
            [
                'name' => 'Food & Dining',
                'slug' => 'food-dining',
                'icon' => 'utensils',
                'description' => 'Restaurants, food products, and dining services',
                'count' => 0,
                'active' => true,
            ],
            [
                'name' => 'Entertainment',
                'slug' => 'entertainment',
                'icon' => 'music',
                'description' => 'Entertainment services, events, and media',
                'count' => 0,
                'active' => true,
            ],
            [
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'icon' => 'house',
                'description' => 'Home improvement, furniture, and garden supplies',
                'count' => 0,
                'active' => true,
            ],
            [
                'name' => 'Sports & Recreation',
                'slug' => 'sports-recreation',
                'icon' => 'football',
                'description' => 'Sports equipment, facilities, and recreational activities',
                'count' => 0,
                'active' => true,
            ],
            [
                'name' => 'Professional Services',
                'slug' => 'professional-services',
                'icon' => 'user-tie',
                'description' => 'Professional and consulting services',
                'count' => 0,
                'active' => true,
            ],
            [
                'name' => 'Shopping',
                'slug' => 'shopping',
                'icon' => 'shopping-bag',
                'description' => 'Retail products and shopping services',
                'count' => 0,
                'active' => true,
            ],
            [
                'name' => 'Jobs & Employment',
                'slug' => 'jobs-employment',
                'icon' => 'briefcase',
                'description' => 'Job opportunities and employment services',
                'count' => 0,
                'active' => true,
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
