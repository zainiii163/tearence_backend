<?php

namespace Database\Seeders;

use App\Models\PromoPricingPlan;
use App\Models\PromoRewardCode;
use App\Services\PromoPricingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PromoPricingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $hasVertical = Schema::hasColumn('promo_pricing_plans', 'vertical');
        $hasPopular = Schema::hasColumn('promo_pricing_plans', 'is_popular');

        // Global Clive matrix
        foreach (PromoPricingService::FALLBACK_PLANS as $plan) {
            $attrs = [
                'name' => $plan['name'],
                'tier' => $plan['tier'],
                'price_usd' => $plan['price_usd'],
                'duration_days' => $plan['duration_days'],
                'description' => $plan['description'],
                'features' => $plan['features'],
                'is_active' => true,
                'sort_order' => $plan['sort_order'],
            ];
            if ($hasVertical) {
                $attrs['vertical'] = $plan['vertical'] ?? 'all';
            }
            if ($hasPopular) {
                $attrs['is_popular'] = $plan['is_popular'] ?? false;
            }

            $keys = $hasVertical
                ? ['vertical' => $attrs['vertical'], 'slug' => $plan['slug']]
                : ['slug' => $plan['slug']];

            PromoPricingPlan::updateOrCreate($keys, array_merge($attrs, ['slug' => $plan['slug']]));
        }

        // Per-vertical listing tiers (editable in Filament — used by post forms)
        if ($hasVertical) {
            foreach (PromoPricingService::LISTING_VERTICALS as $vertical) {
                foreach (PromoPricingService::LISTING_VERTICAL_DEFAULTS as $slug => $plan) {
                    PromoPricingPlan::updateOrCreate(
                        ['vertical' => $vertical, 'slug' => $slug],
                        [
                            'name' => $plan['name'],
                            'tier' => $plan['tier'],
                            'price_usd' => $plan['price_usd'],
                            'duration_days' => $plan['duration_days'],
                            'description' => $plan['description'],
                            'features' => $plan['features'],
                            'is_active' => true,
                            'is_popular' => $hasPopular ? ($plan['is_popular'] ?? false) : false,
                            'sort_order' => $plan['sort_order'],
                        ]
                    );
                }
            }
        }

        // Align affiliate upsell plans to Clive matrix (USD durations)
        if (Schema::hasTable('affiliate_upsell_plans')) {
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
        }

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
    }
}
