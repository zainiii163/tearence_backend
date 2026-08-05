<?php

namespace Database\Seeders;

use App\Models\PromoPricingPlan;
use App\Models\PromoRewardCode;
use App\Services\PromoPricingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromoPricingPlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PromoPricingService::FALLBACK_PLANS as $plan) {
            PromoPricingPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                [
                    'name' => $plan['name'],
                    'tier' => $plan['tier'],
                    'price_usd' => $plan['price_usd'],
                    'duration_days' => $plan['duration_days'],
                    'description' => $plan['description'],
                    'features' => $plan['features'],
                    'is_active' => true,
                    'sort_order' => $plan['sort_order'],
                ]
            );
        }

        // Align affiliate upsell plans to Clive matrix (USD durations)
        $affiliateMap = [
            'promoted' => ['price' => 50.00, 'duration_days' => 21, 'duration_type' => 'weekly', 'duration_value' => 3],
            'featured' => ['price' => 30.00, 'duration_days' => 14, 'duration_type' => 'weekly', 'duration_value' => 2],
            'sponsored' => ['price' => 100.00, 'duration_days' => 30, 'duration_type' => 'monthly', 'duration_value' => 1],
        ];

        foreach ($affiliateMap as $slug => $data) {
            DB::table('affiliate_upsell_plans')->where('slug', $slug)->update([
                'price' => $data['price'],
                'currency' => 'USD',
                'duration_days' => $data['duration_days'],
                'duration_type' => $data['duration_type'],
                'duration_value' => $data['duration_value'],
                'updated_at' => now(),
            ]);
        }

        // Sample reward codes for testing
        PromoRewardCode::updateOrCreate(
            ['code' => 'WWA10'],
            [
                'type' => 'percent',
                'value' => 10,
                'max_uses' => 1000,
                'uses_count' => 0,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addYear(),
                'applies_to' => null,
                'is_active' => true,
                'description' => '10% off any advertising promotion',
            ]
        );

        PromoRewardCode::updateOrCreate(
            ['code' => 'CLIVE20'],
            [
                'type' => 'fixed',
                'value' => 20,
                'max_uses' => 500,
                'uses_count' => 0,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addYear(),
                'applies_to' => ['sponsored', 'featured', 'promoted'],
                'is_active' => true,
                'description' => '$20 off sponsored/featured/promoted',
            ]
        );

        PromoRewardCode::updateOrCreate(
            ['code' => 'POINTS50'],
            [
                'type' => 'points',
                'value' => 50,
                'max_uses' => null,
                'uses_count' => 0,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addYear(),
                'applies_to' => null,
                'is_active' => true,
                'description' => 'Award 50 reward points on advertising purchase',
            ]
        );
    }
}
