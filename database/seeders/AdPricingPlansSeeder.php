<?php

namespace Database\Seeders;

use App\Models\AdPricingPlan;
use Illuminate\Database\Seeder;

class AdPricingPlansSeeder extends Seeder
{
    public function run(): void
    {
        // Banner Ad Pricing Plans
        AdPricingPlan::create([
            'name' => 'Basic Banner',
            'ad_type' => 'banner',
            'price' => 29.99,
            'duration_days' => 7,
            'description' => 'Basic banner placement for 7 days. Perfect for small businesses.',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        AdPricingPlan::create([
            'name' => 'Premium Banner',
            'ad_type' => 'banner',
            'price' => 99.99,
            'duration_days' => 30,
            'description' => 'Premium banner placement for 30 days with enhanced visibility. Ideal for established brands.',
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 2,
        ]);

        AdPricingPlan::create([
            'name' => 'Enterprise Banner',
            'ad_type' => 'banner',
            'price' => 299.99,
            'duration_days' => 90,
            'description' => 'Enterprise banner placement for 90 days with maximum exposure and priority support.',
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 3,
        ]);

        // Affiliate Ad Pricing Plans
        AdPricingPlan::create([
            'name' => 'Starter Affiliate',
            'ad_type' => 'affiliate',
            'price' => 19.99,
            'duration_days' => 14,
            'description' => 'Starter affiliate placement for 14 days. Great for testing affiliate marketing.',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 4,
        ]);

        AdPricingPlan::create([
            'name' => 'Professional Affiliate',
            'ad_type' => 'affiliate',
            'price' => 79.99,
            'duration_days' => 60,
            'description' => 'Professional affiliate placement for 60 days with enhanced tracking and analytics.',
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 5,
        ]);

        AdPricingPlan::create([
            'name' => 'Premium Affiliate',
            'ad_type' => 'affiliate',
            'price' => 199.99,
            'duration_days' => 120,
            'description' => 'Premium affiliate placement for 120 days with maximum exposure and dedicated support.',
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 6,
        ]);

        // Inactive plans for testing
        AdPricingPlan::create([
            'name' => 'Legacy Banner (Deprecated)',
            'ad_type' => 'banner',
            'price' => 15.99,
            'duration_days' => 3,
            'description' => 'Legacy banner plan. No longer available for new purchases.',
            'is_active' => false,
            'is_featured' => false,
            'sort_order' => 10,
        ]);

        AdPricingPlan::create([
            'name' => 'Beta Affiliate Plan',
            'ad_type' => 'affiliate',
            'price' => 9.99,
            'duration_days' => 7,
            'description' => 'Beta testing plan for affiliate marketing. Limited availability.',
            'is_active' => false,
            'is_featured' => false,
            'sort_order' => 11,
        ]);

        $this->command->info('Ad pricing plans created:');
        $this->command->info('- Banner Plans: Basic ($29.99/7d), Premium ($99.99/30d), Enterprise ($299.99/90d)');
        $this->command->info('- Affiliate Plans: Starter ($19.99/14d), Professional ($79.99/60d), Premium ($199.99/120d)');
        $this->command->info('- Inactive Plans: Legacy Banner, Beta Affiliate (for testing admin interface)');
    }
}
