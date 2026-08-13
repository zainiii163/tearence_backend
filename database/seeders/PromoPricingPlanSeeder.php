<?php

namespace Database\Seeders;

use App\Models\PromoPricingPlan;
use App\Models\PromoRewardCode;
use App\Services\PromoPricingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Launch promotional advert pricing — editable afterward in Filament → Promo Pricing Plans.
 */
class PromoPricingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $hasVertical = Schema::hasColumn('promo_pricing_plans', 'vertical');
        $hasPopular = Schema::hasColumn('promo_pricing_plans', 'is_popular');

        // Global matrix (free / paid / promoted / featured / sponsored + affiliate cookies)
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

        // Retire old multi-week paid variants from previous matrix
        if ($hasVertical) {
            PromoPricingPlan::whereIn('slug', ['paid_1w', 'paid_2w', 'paid_4w'])
                ->update(['is_active' => false]);
        } else {
            PromoPricingPlan::whereIn('slug', ['paid_1w', 'paid_2w', 'paid_4w'])
                ->update(['is_active' => false]);
        }

        // Ensure canonical "paid" slug is active (replaces paid_1w)
        if ($hasVertical) {
            PromoPricingPlan::where('vertical', 'all')->where('slug', 'paid')->update(['is_active' => true]);
        }

        // Per-vertical listing tiers
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

        // Align affiliate upsell plans to launch promo (1 week packages)
        if (Schema::hasTable('affiliate_upsell_plans')) {
            $affiliateMap = [
                'promoted' => ['price' => 20.00, 'duration_days' => 7, 'duration_type' => 'weekly', 'duration_value' => 1],
                'featured' => ['price' => 30.00, 'duration_days' => 7, 'duration_type' => 'weekly', 'duration_value' => 1],
                'sponsored' => ['price' => 40.00, 'duration_days' => 7, 'duration_type' => 'weekly', 'duration_value' => 1],
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
            ['code' => 'LAUNCH20'],
            [
                'type' => 'fixed',
                'value' => 5,
                'max_uses' => 500,
                'uses_count' => 0,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addYear(),
                'applies_to' => ['paid', 'promoted', 'featured', 'sponsored', 'cookie'],
                'is_active' => true,
                'description' => '$5 off launch promo packages',
            ]
        );
    }
}
