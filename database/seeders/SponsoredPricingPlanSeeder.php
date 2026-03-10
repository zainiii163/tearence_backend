<?php

use Illuminate\Database\Seeder;

class SponsoredPricingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Sponsored Basic',
                'tier' => 'basic',
                'price' => 29.99,
                'currency' => 'GBP',
                'duration_days' => 30,
                'description' => 'Get started with sponsored advertising and reach more customers with enhanced visibility.',
                'features' => \App\Models\SponsoredPricingPlan::getDefaultFeatures()['basic'],
                'visibility_settings' => \App\Models\SponsoredPricingPlan::getDefaultVisibilitySettings()['basic'],
                'badge_settings' => \App\Models\SponsoredPricingPlan::getDefaultBadgeSettings()['basic'],
                'placement_settings' => \App\Models\SponsoredPricingPlan::getDefaultPlacementSettings()['basic'],
                'promotion_settings' => \App\Models\SponsoredPricingPlan::getDefaultPromotionSettings()['basic'],
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Sponsored Plus',
                'tier' => 'plus',
                'price' => 59.99,
                'currency' => 'GBP',
                'duration_days' => 30,
                'description' => 'Enhanced visibility with priority placement and larger advert cards for maximum impact.',
                'features' => \App\Models\SponsoredPricingPlan::getDefaultFeatures()['plus'],
                'visibility_settings' => \App\Models\SponsoredPricingPlan::getDefaultVisibilitySettings()['plus'],
                'badge_settings' => \App\Models\SponsoredPricingPlan::getDefaultBadgeSettings()['plus'],
                'placement_settings' => \App\Models\SponsoredPricingPlan::getDefaultPlacementSettings()['plus'],
                'promotion_settings' => \App\Models\SponsoredPricingPlan::getDefaultPromotionSettings()['plus'],
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Sponsored Premium',
                'tier' => 'premium',
                'price' => 99.99,
                'currency' => 'GBP',
                'duration_days' => 30,
                'description' => 'Maximum visibility across the platform with homepage placement and social media promotion.',
                'features' => \App\Models\SponsoredPricingPlan::getDefaultFeatures()['premium'],
                'visibility_settings' => \App\Models\SponsoredPricingPlan::getDefaultVisibilitySettings()['premium'],
                'badge_settings' => \App\Models\SponsoredPricingPlan::getDefaultBadgeSettings()['premium'],
                'placement_settings' => \App\Models\SponsoredPricingPlan::getDefaultPlacementSettings()['premium'],
                'promotion_settings' => \App\Models\SponsoredPricingPlan::getDefaultPromotionSettings()['premium'],
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            \App\Models\SponsoredPricingPlan::create($plan);
        }
    }
}
