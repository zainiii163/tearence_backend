<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BannerCategory;

class BannerCategorySeeder extends Seeder
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
                'description' => 'Property listings, real estate agencies, and housing services',
                'icon' => 'real-estate-icon.svg',
                'image' => 'real-estate-bg.jpg',
                'color' => '#3B82F6',
                'sort_order' => 1,
            ],
            [
                'name' => 'Vehicles',
                'slug' => 'vehicles',
                'description' => 'Cars, motorcycles, boats, and automotive services',
                'icon' => 'vehicles-icon.svg',
                'image' => 'vehicles-bg.jpg',
                'color' => '#EF4444',
                'sort_order' => 2,
            ],
            [
                'name' => 'Travel & Resorts',
                'slug' => 'travel-resorts',
                'description' => 'Hotels, resorts, travel packages, and tourism services',
                'icon' => 'travel-icon.svg',
                'image' => 'travel-bg.jpg',
                'color' => '#10B981',
                'sort_order' => 3,
            ],
            [
                'name' => 'Jobs & Recruitment',
                'slug' => 'jobs-recruitment',
                'description' => 'Job postings, recruitment services, and career opportunities',
                'icon' => 'jobs-icon.svg',
                'image' => 'jobs-bg.jpg',
                'color' => '#F59E0B',
                'sort_order' => 4,
            ],
            [
                'name' => 'Books & Authors',
                'slug' => 'books-authors',
                'description' => 'Book promotions, author services, and literary events',
                'icon' => 'books-icon.svg',
                'image' => 'books-bg.jpg',
                'color' => '#8B5CF6',
                'sort_order' => 5,
            ],
            [
                'name' => 'Services',
                'slug' => 'services',
                'description' => 'Professional services, consulting, and business solutions',
                'icon' => 'services-icon.svg',
                'image' => 'services-bg.jpg',
                'color' => '#06B6D4',
                'sort_order' => 6,
            ],
            [
                'name' => 'Events',
                'slug' => 'events',
                'description' => 'Conferences, workshops, exhibitions, and special events',
                'icon' => 'events-icon.svg',
                'image' => 'events-bg.jpg',
                'color' => '#EC4899',
                'sort_order' => 7,
            ],
            [
                'name' => 'Food & Hospitality',
                'slug' => 'food-hospitality',
                'description' => 'Restaurants, catering, food services, and hospitality',
                'icon' => 'food-icon.svg',
                'image' => 'food-bg.jpg',
                'color' => '#F97316',
                'sort_order' => 8,
            ],
            [
                'name' => 'Fashion & Beauty',
                'slug' => 'fashion-beauty',
                'description' => 'Fashion brands, beauty products, and style services',
                'icon' => 'fashion-icon.svg',
                'image' => 'fashion-bg.jpg',
                'color' => '#A855F7',
                'sort_order' => 9,
            ],
            [
                'name' => 'Tech & Electronics',
                'slug' => 'tech-electronics',
                'description' => 'Technology products, electronics, and IT services',
                'icon' => 'tech-icon.svg',
                'image' => 'tech-bg.jpg',
                'color' => '#0EA5E9',
                'sort_order' => 10,
            ],
            [
                'name' => 'Health & Wellness',
                'slug' => 'health-wellness',
                'description' => 'Healthcare, fitness, wellness products, and medical services',
                'icon' => 'health-icon.svg',
                'image' => 'health-bg.jpg',
                'color' => '#22C55E',
                'sort_order' => 11,
            ],
            [
                'name' => 'Business & Finance',
                'slug' => 'business-finance',
                'description' => 'Financial services, business consulting, and investment opportunities',
                'icon' => 'business-icon.svg',
                'image' => 'business-bg.jpg',
                'color' => '#6366F1',
                'sort_order' => 12,
            ],
        ];

        foreach ($categories as $category) {
            BannerCategory::create($category);
        }

        $this->command->info('Banner categories seeded successfully!');
    }
}
