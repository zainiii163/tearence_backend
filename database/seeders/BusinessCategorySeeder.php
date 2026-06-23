<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class BusinessCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businessCategories = [
            [
                'name' => 'Retail & Shopping',
                'slug' => 'retail-shopping',
                'description' => 'Clothing, electronics, groceries, and specialty stores',
                'icon' => 'shopping-cart',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Restaurants & Food',
                'slug' => 'restaurants-food',
                'description' => 'Restaurants, cafes, bars, and food delivery services',
                'icon' => 'utensils',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Professional Services',
                'slug' => 'professional-services',
                'description' => 'Consulting, legal, financial, and business services',
                'icon' => 'briefcase',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Healthcare & Wellness',
                'slug' => 'healthcare-wellness',
                'description' => 'Hospitals, clinics, fitness, and wellness centers',
                'icon' => 'stethoscope',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Education & Training',
                'slug' => 'education-training',
                'description' => 'Schools, universities, tutoring, and training centers',
                'icon' => 'graduation-cap',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Automotive',
                'slug' => 'automotive',
                'description' => 'Car dealerships, repair shops, and auto parts',
                'icon' => 'car',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Real Estate',
                'slug' => 'real-estate',
                'description' => 'Property sales, rentals, and real estate services',
                'icon' => 'home',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Entertainment & Leisure',
                'slug' => 'entertainment-leisure',
                'description' => 'Movies, gaming, sports, and recreational activities',
                'icon' => 'gamepad',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Travel & Hospitality',
                'slug' => 'travel-hospitality',
                'description' => 'Hotels, travel agencies, and tourism services',
                'icon' => 'plane',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Beauty & Personal Care',
                'slug' => 'beauty-personal-care',
                'description' => 'Salons, spas, and personal care services',
                'icon' => 'heart',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Pet Services',
                'slug' => 'pet-services',
                'description' => 'Pet stores, grooming, and veterinary services',
                'icon' => 'dog',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'description' => 'Home improvement, furniture, and garden supplies',
                'icon' => 'home',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 12,
            ],
            [
                'name' => 'Technology & Electronics',
                'slug' => 'technology-electronics',
                'description' => 'Electronics stores, computer services, and tech support',
                'icon' => 'laptop',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 13,
            ],
            [
                'name' => 'Sports & Fitness',
                'slug' => 'sports-fitness',
                'description' => 'Gyms, sports equipment, and fitness centers',
                'icon' => 'dumbbell',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 14,
            ],
            [
                'name' => 'Industrial & Manufacturing',
                'slug' => 'industrial-manufacturing',
                'description' => 'Manufacturing, warehouses, and industrial services',
                'icon' => 'industry',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 15,
            ],
        ];

        foreach ($businessCategories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('Business categories seeded successfully!');
    }
}
