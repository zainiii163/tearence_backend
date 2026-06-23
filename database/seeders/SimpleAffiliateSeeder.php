<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AffiliateCategory;

class SimpleAffiliateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create affiliate categories
        $categories = [
            [
                'name' => 'Technology & Gadgets',
                'slug' => 'technology-gadgets',
                'description' => 'Latest technology products, gadgets, electronics, and software affiliate offers.',
                'icon' => 'heroicon-o-cpu-chip',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Fashion & Beauty',
                'slug' => 'fashion-beauty',
                'description' => 'Clothing, accessories, cosmetics, and personal care affiliate programs.',
                'icon' => 'heroicon-o-sparkles',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Travel & Tourism',
                'slug' => 'travel-tourism',
                'description' => 'Hotels, flights, vacation packages, and travel-related affiliate offers.',
                'icon' => 'heroicon-o-airplane',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Health & Wellness',
                'slug' => 'health-wellness',
                'description' => 'Health supplements, fitness equipment, medical services, and wellness products.',
                'icon' => 'heroicon-o-heart',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Education & Courses',
                'slug' => 'education-courses',
                'description' => 'Online courses, educational platforms, training programs, and learning resources.',
                'icon' => 'heroicon-o-academic-cap',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            AffiliateCategory::create($category);
        }

        echo "✓ Created " . count($categories) . " affiliate categories\n";
    }
}
