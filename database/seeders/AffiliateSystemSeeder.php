<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AffiliateCategory;
use App\Models\AffiliateUpsellPlan;

class AffiliateSystemSeeder extends Seeder
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
                'name' => 'Finance & Insurance',
                'slug' => 'finance-insurance',
                'description' => 'Banking, insurance, investment, and financial service affiliate programs.',
                'icon' => 'heroicon-o-banknotes',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Health & Wellness',
                'slug' => 'health-wellness',
                'description' => 'Health supplements, fitness equipment, medical services, and wellness products.',
                'icon' => 'heroicon-o-heart',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Education & Courses',
                'slug' => 'education-courses',
                'description' => 'Online courses, educational platforms, training programs, and learning resources.',
                'icon' => 'heroicon-o-academic-cap',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'description' => 'Home improvement, furniture, garden supplies, and household products.',
                'icon' => 'heroicon-o-home',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Automotive',
                'slug' => 'automotive',
                'description' => 'Car accessories, auto parts, vehicle services, and automotive products.',
                'icon' => 'heroicon-o-truck',
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Real Estate',
                'slug' => 'real-estate',
                'description' => 'Property listings, real estate services, home buying, and rental programs.',
                'icon' => 'heroicon-o-building-office',
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Software & SaaS',
                'slug' => 'software-saas',
                'description' => 'Software as a service, productivity tools, and digital applications.',
                'icon' => 'heroicon-o-computer-desktop',
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Food & Lifestyle',
                'slug' => 'food-lifestyle',
                'description' => 'Food delivery, meal kits, recipes, cooking products, and lifestyle items.',
                'icon' => 'heroicon-o-utensils',
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'Business Services',
                'slug' => 'business-services',
                'description' => 'B2B services, consulting, marketing tools, and business solutions.',
                'icon' => 'heroicon-o-briefcase',
                'is_active' => true,
                'sort_order' => 12,
            ],
        ];

        foreach ($categories as $category) {
            AffiliateCategory::create($category);
        }

        // Create upsell plans
        $plans = [
            [
                'name' => 'Promoted Post',
                'slug' => 'promoted',
                'description' => 'Get your affiliate post highlighted and positioned above standard posts for increased visibility.',
                'price' => 19.99,
                'duration_type' => 'weekly',
                'duration_days' => 7,
                'features' => [
                    'Highlighted background',
                    'Appears above standard posts',
                    '2× more visibility',
                    'Promoted badge',
                ],
                'highlighted_background' => true,
                'above_standard_posts' => true,
                'top_category_placement' => false,
                'larger_card_size' => false,
                'priority_search' => false,
                'homepage_placement' => false,
                'category_top_placement' => false,
                'homepage_slider' => false,
                'social_media_promotion' => false,
                'email_blast_inclusion' => false,
                'badge_text' => 'Promoted',
                'badge_color' => '#3B82F6',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Featured Post',
                'slug' => 'featured',
                'description' => 'Maximum visibility with top placement, larger cards, and inclusion in our weekly email blast.',
                'price' => 49.99,
                'duration_type' => 'monthly',
                'duration_days' => 30,
                'features' => [
                    'Top of category pages',
                    'Larger card size',
                    'Priority in search results',
                    'Featured badge',
                    'Included in weekly email blast',
                    '5× more visibility',
                ],
                'highlighted_background' => true,
                'above_standard_posts' => true,
                'top_category_placement' => true,
                'larger_card_size' => true,
                'priority_search' => true,
                'homepage_placement' => false,
                'category_top_placement' => true,
                'homepage_slider' => false,
                'social_media_promotion' => false,
                'email_blast_inclusion' => true,
                'badge_text' => 'Featured',
                'badge_color' => '#10B981',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Sponsored Post',
                'slug' => 'sponsored',
                'description' => 'Ultimate visibility with homepage placement, social media promotion, and maximum exposure.',
                'price' => 99.99,
                'duration_type' => 'monthly',
                'duration_days' => 30,
                'features' => [
                    'Homepage placement',
                    'Category top placement',
                    'Included in homepage slider',
                    'Social media promotion',
                    'Sponsored badge',
                    'Maximum visibility',
                    '10× more visibility',
                ],
                'highlighted_background' => true,
                'above_standard_posts' => true,
                'top_category_placement' => true,
                'larger_card_size' => true,
                'priority_search' => true,
                'homepage_placement' => true,
                'category_top_placement' => true,
                'homepage_slider' => true,
                'social_media_promotion' => true,
                'email_blast_inclusion' => true,
                'badge_text' => 'Sponsored',
                'badge_color' => '#F59E0B',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            AffiliateUpsellPlan::create($plan);
        }
    }
}
