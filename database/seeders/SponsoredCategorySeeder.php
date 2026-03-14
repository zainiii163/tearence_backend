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
                'name' => 'Real Estate',
                'slug' => 'real-estate',
                'icon' => '🏠',
                'color' => 'from-blue-500 to-blue-600',
                'description' => 'Properties, houses, apartments, and commercial real estate',
            ],
            [
                'name' => 'Vehicles',
                'slug' => 'vehicles',
                'icon' => '🚗',
                'color' => 'from-green-500 to-green-600',
                'description' => 'Cars, motorcycles, boats, and other vehicles',
            ],
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'icon' => '📱',
                'color' => 'from-purple-500 to-purple-600',
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
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'icon' => '🏡',
                'color' => 'from-yellow-500 to-yellow-600',
                'description' => 'Furniture, appliances, and garden supplies',
            ],
            [
                'name' => 'Business Services',
                'slug' => 'business-services',
                'icon' => '💼',
                'color' => 'from-indigo-500 to-indigo-600',
                'description' => 'Professional services and business opportunities',
            ],
            [
                'name' => 'Education',
                'slug' => 'education',
                'icon' => '📚',
                'color' => 'from-red-500 to-red-600',
                'description' => 'Courses, training, and educational materials',
            ],
        ];

        foreach ($categories as $category) {
            SponsoredCategory::create($category);
        }
    }
}
