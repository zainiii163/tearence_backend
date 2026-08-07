<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SponsoredPricingPlan;

class SponsoredPricingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Aligns with live columns: tier, is_active, is_featured, slug, etc.
     */
    public function run(): void
    {
        $defaults = SponsoredPricingPlan::getDefaultFeatures();
        $visibility = SponsoredPricingPlan::getDefaultVisibilitySettings();
        $badges = SponsoredPricingPlan::getDefaultBadgeSettings();
        $placement = SponsoredPricingPlan::getDefaultPlacementSettings();
        $promotion = SponsoredPricingPlan::getDefaultPromotionSettings();

        $plans = [
            [
                'name' => 'Sponsored Basic',
                'slug' => 'basic',
                'tier' => 'basic',
                'price' => 29.99,
                'currency' => 'USD',
                'duration_days' => 30,
                'description' => 'Listed on Sponsored Adverts with a Sponsored badge and higher visibility.',
                'features' => $defaults['basic'],
                'visibility_settings' => $visibility['basic'],
                'badge_settings' => $badges['basic'],
                'placement_settings' => $placement['basic'],
                'promotion_settings' => $promotion['basic'],
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Sponsored Plus',
                'slug' => 'plus',
                'tier' => 'plus',
                'price' => 59.99,
                'currency' => 'USD',
                'duration_days' => 30,
                'description' => 'Category-top placement, larger card, and weekly highlights.',
                'features' => $defaults['plus'],
                'visibility_settings' => $visibility['plus'],
                'badge_settings' => $badges['plus'],
                'placement_settings' => $placement['plus'],
                'promotion_settings' => $promotion['plus'],
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Sponsored Premium',
                'slug' => 'premium',
                'tier' => 'premium',
                'price' => 99.99,
                'currency' => 'USD',
                'duration_days' => 30,
                'description' => 'Homepage placement and maximum visibility across the platform.',
                'features' => $defaults['premium'],
                'visibility_settings' => $visibility['premium'],
                'badge_settings' => $badges['premium'],
                'placement_settings' => $placement['premium'],
                'promotion_settings' => $promotion['premium'],
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SponsoredPricingPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
