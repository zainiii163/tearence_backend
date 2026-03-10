<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ServiceCategory;
use Illuminate\Support\Str;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Graphic Design',
                'slug' => 'graphic-design',
                'description' => 'Logo design, branding, illustrations, and visual content creation',
                'icon' => 'fas fa-palette',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Web Development',
                'slug' => 'web-development',
                'description' => 'Website development, web applications, and programming services',
                'icon' => 'fas fa-code',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Writing & Translation',
                'slug' => 'writing-translation',
                'description' => 'Content writing, copywriting, translation, and editing services',
                'icon' => 'fas fa-pen',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Marketing & SEO',
                'slug' => 'marketing-seo',
                'description' => 'Digital marketing, SEO, social media, and advertising services',
                'icon' => 'fas fa-bullhorn',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Business Support',
                'slug' => 'business-support',
                'description' => 'Business consulting, virtual assistance, and administrative services',
                'icon' => 'fas fa-briefcase',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Virtual Assistants',
                'slug' => 'virtual-assistants',
                'description' => 'Administrative support, customer service, and remote assistance',
                'icon' => 'fas fa-user-tie',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Photography & Video',
                'slug' => 'photography-video',
                'description' => 'Professional photography, videography, and photo editing services',
                'icon' => 'fas fa-camera',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Music & Audio',
                'slug' => 'music-audio',
                'description' => 'Music production, audio editing, voice-over, and sound design',
                'icon' => 'fas fa-music',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Lifestyle Services',
                'slug' => 'lifestyle-services',
                'description' => 'Personal coaching, wellness, and lifestyle improvement services',
                'icon' => 'fas fa-heart',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Fitness & Coaching',
                'slug' => 'fitness-coaching',
                'description' => 'Personal training, fitness coaching, and sports instruction',
                'icon' => 'fas fa-dumbbell',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Trades & Repairs',
                'slug' => 'trades-repairs',
                'description' => 'Home repairs, maintenance, and skilled trade services',
                'icon' => 'fas fa-tools',
                'sort_order' => 11,
                'is_active' => true,
            ],
            [
                'name' => 'Cleaning & Domestic Help',
                'slug' => 'cleaning-domestic-help',
                'description' => 'House cleaning, domestic services, and home organization',
                'icon' => 'fas fa-broom',
                'sort_order' => 12,
                'is_active' => true,
            ],
            [
                'name' => 'Event Services',
                'slug' => 'event-services',
                'description' => 'Event planning, catering, and entertainment services',
                'icon' => 'fas fa-calendar-alt',
                'sort_order' => 13,
                'is_active' => true,
            ],
            [
                'name' => 'Transport & Delivery',
                'slug' => 'transport-delivery',
                'description' => 'Transportation, delivery services, and logistics',
                'icon' => 'fas fa-truck',
                'sort_order' => 14,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ServiceCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
