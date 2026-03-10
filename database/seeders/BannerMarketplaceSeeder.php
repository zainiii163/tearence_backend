<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BannerCategory;
use App\Models\AdPricingPlan;
use Illuminate\Support\Facades\DB;

class BannerMarketplaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedBannerCategories();
            $this->seedPricingPlans();
        });
    }

    /**
     * Seed banner categories.
     */
    private function seedBannerCategories(): void
    {
        $categories = [
            [
                'name' => 'Real Estate',
                'slug' => 'real-estate',
                'description' => 'Property listings, real estate services, and housing advertisements',
                'color' => '#3B82F6',
                'icon' => null,
                'is_active' => true,
                'sort_order' => 1,
                'sample_banners' => 'Property listings, real estate agencies, housing developments, rental properties',
            ],
            [
                'name' => 'Vehicles',
                'slug' => 'vehicles',
                'description' => 'Car dealerships, auto services, and vehicle-related businesses',
                'color' => '#EF4444',
                'icon' => null,
                'is_active' => true,
                'sort_order' => 2,
                'sample_banners' => 'Car dealerships, auto repair shops, vehicle parts, motorcycle sales',
            ],
            [
                'name' => 'Travel & Resorts',
                'slug' => 'travel-resorts',
                'description' => 'Travel agencies, hotels, resorts, and tourism services',
                'color' => '#10B981',
                'icon' => null,
                'is_active' => true,
                'sort_order' => 3,
                'sample_banners' => 'Hotels, travel packages, vacation rentals, tourism boards',
            ],
            [
                'name' => 'Jobs & Recruitment',
                'slug' => 'jobs-recruitment',
                'description' => 'Job postings, recruitment agencies, and career services',
                'color' => '#F59E0B',
                'icon' => null,
                'is_active' => true,
                'sort_order' => 4,
                'sample_banners' => 'Job boards, recruitment agencies, career training, HR services',
            ],
            [
                'name' => 'Books & Authors',
                'slug' => 'books-authors',
                'description' => 'Book promotions, author services, and publishing companies',
                'color' => '#8B5CF6',
                'icon' => null,
                'is_active' => true,
                'sort_order' => 5,
                'sample_banners' => 'Book launches, author promotions, publishing services, bookstores',
            ],
            [
                'name' => 'Services',
                'slug' => 'services',
                'description' => 'Professional services, consulting, and business services',
                'color' => '#6366F1',
                'icon' => null,
                'is_active' => true,
                'sort_order' => 6,
                'sample_banners' => 'Consulting services, professional services, business solutions',
            ],
            [
                'name' => 'Events',
                'slug' => 'events',
                'description' => 'Event promotions, conferences, and entertainment venues',
                'color' => '#EC4899',
                'icon' => null,
                'is_active' => true,
                'sort_order' => 7,
                'sample_banners' => 'Conferences, concerts, festivals, event venues',
            ],
            [
                'name' => 'Food & Hospitality',
                'slug' => 'food-hospitality',
                'description' => 'Restaurants, food services, and hospitality businesses',
                'color' => '#F97316',
                'icon' => null,
                'is_active' => true,
                'sort_order' => 8,
                'sample_banners' => 'Restaurants, cafes, catering services, hotels',
            ],
            [
                'name' => 'Fashion & Beauty',
                'slug' => 'fashion-beauty',
                'description' => 'Fashion brands, beauty products, and style services',
                'color' => '#EC4899',
                'icon' => null,
                'is_active' => true,
                'sort_order' => 9,
                'sample_banners' => 'Clothing brands, beauty products, fashion retailers, salons',
            ],
            [
                'name' => 'Tech & Electronics',
                'slug' => 'tech-electronics',
                'description' => 'Technology products, electronics, and IT services',
                'color' => '#06B6D4',
                'icon' => null,
                'is_active' => true,
                'sort_order' => 10,
                'sample_banners' => 'Software products, electronics, IT services, gadgets',
            ],
            [
                'name' => 'Health & Wellness',
                'slug' => 'health-wellness',
                'description' => 'Healthcare services, fitness, and wellness products',
                'color' => '#10B981',
                'icon' => null,
                'is_active' => true,
                'sort_order' => 11,
                'sample_banners' => 'Medical services, fitness centers, wellness products, health supplements',
            ],
            [
                'name' => 'Business & Finance',
                'slug' => 'business-finance',
                'description' => 'Financial services, business opportunities, and investments',
                'color' => '#1F2937',
                'icon' => null,
                'is_active' => true,
                'sort_order' => 12,
                'sample_banners' => 'Financial services, business opportunities, investment platforms',
            ],
        ];

        foreach ($categories as $category) {
            BannerCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('✅ Banner categories seeded successfully');
    }

    /**
     * Seed pricing plans for banner ads.
     */
    private function seedPricingPlans(): void
    {
        $plans = [
            [
                'name' => 'Standard Banner',
                'slug' => 'banner-standard',
                'description' => 'Basic banner advertisement with standard visibility',
                'price' => 25.00,
                'duration_days' => 30,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 1,
                'features' => json_encode([
                    'Standard banner placement',
                    'Basic visibility',
                    '30 days duration',
                    'Basic analytics',
                ]),
                'ad_type' => 'banner',
            ],
            [
                'name' => 'Promoted Banner',
                'slug' => 'banner-promoted',
                'description' => 'Enhanced visibility with highlighted placement',
                'price' => 50.00,
                'duration_days' => 30,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 2,
                'features' => json_encode([
                    'Highlighted banner',
                    'Appears above standard banners',
                    'Promoted badge',
                    '2× more visibility',
                    'Enhanced analytics',
                ]),
                'ad_type' => 'banner',
            ],
            [
                'name' => 'Featured Banner',
                'slug' => 'banner-featured',
                'description' => 'Premium placement with maximum visibility in categories',
                'price' => 100.00,
                'duration_days' => 30,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 3,
                'features' => json_encode([
                    'Top of category pages',
                    'Larger banner preview',
                    'Priority in search results',
                    'Included in weekly Featured Banners email',
                    'Featured badge',
                    '4× more visibility',
                    'Advanced analytics',
                ]),
                'ad_type' => 'banner',
            ],
            [
                'name' => 'Sponsored Banner',
                'slug' => 'banner-sponsored',
                'description' => 'Premium sponsorship with homepage placement',
                'price' => 200.00,
                'duration_days' => 30,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 4,
                'features' => json_encode([
                    'Homepage placement',
                    'Category top placement',
                    'Included in homepage slider',
                    'Included in social media promotion',
                    'Sponsored badge',
                    'Maximum visibility',
                    'Premium analytics',
                    'Dedicated support',
                ]),
                'ad_type' => 'banner',
            ],
            [
                'name' => 'Network-Wide Boost',
                'slug' => 'banner-network-boost',
                'description' => 'Ultimate visibility across the entire platform',
                'price' => 500.00,
                'duration_days' => 30,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 5,
                'features' => json_encode([
                    'Appears across multiple pages',
                    'Banner Ads page',
                    'Homepage',
                    'Category pages',
                    'Related search pages',
                    'Included in email newsletters',
                    'Included in push notifications',
                    'Top Spotlight badge',
                    'Ultimate visibility',
                    'Enterprise analytics',
                    'Priority support',
                ]),
                'ad_type' => 'banner',
            ],
        ];

        foreach ($plans as $plan) {
            AdPricingPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('✅ Banner pricing plans seeded successfully');
    }
}
