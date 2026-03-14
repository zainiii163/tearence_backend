<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SponsoredPricingPlan;

class SponsoredPricingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'price' => 0.00,
                'currency' => 'USD',
                'duration_days' => 30,
                'features' => json_encode([
                    'Basic listing on sponsored page',
                    'Standard visibility',
                    'Up to 5 images',
                ]),
                'active' => true,
                'recommended' => false,
                'visibility_multiplier' => 1,
            ],
            [
                'name' => 'Promoted',
                'slug' => 'promoted',
                'price' => 29.99,
                'currency' => 'USD',
                'duration_days' => 30,
                'features' => json_encode([
                    'Enhanced visibility',
                    'Priority in search results',
                    'Larger advert card',
                    'Up to 10 images',
                    'Basic analytics',
                ]),
                'active' => true,
                'recommended' => false,
                'visibility_multiplier' => 2,
            ],
            [
                'name' => 'Featured',
                'slug' => 'featured',
                'price' => 49.99,
                'currency' => 'USD',
                'duration_days' => 30,
                'features' => json_encode([
                    'Top placement in category',
                    'Homepage carousel inclusion',
                    'Featured badge',
                    'Advanced analytics',
                    'Up to 15 images',
                ]),
                'active' => true,
                'recommended' => true,
                'visibility_multiplier' => 3,
            ],
            [
                'name' => 'Sponsored',
                'slug' => 'sponsored',
                'price' => 99.99,
                'currency' => 'USD',
                'duration_days' => 30,
                'features' => json_encode([
                    'Homepage placement',
                    'Maximum visibility across platform',
                    'Social media promotion',
                    'Premium sponsored badge',
                    'Unlimited images',
                    'Comprehensive analytics',
                    'Dedicated support',
                ]),
                'active' => true,
                'recommended' => false,
                'visibility_multiplier' => 5,
            ],
        ];

        foreach ($plans as $plan) {
            SponsoredPricingPlan::create($plan);
        }
    }
}
