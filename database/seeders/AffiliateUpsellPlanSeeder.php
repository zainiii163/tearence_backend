<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AffiliateUpsellPlanSeeder extends Seeder
{
    /**
     * Clive matrix: Promoted $50/21d, Featured $30/14d, Sponsored $100/30d
     */
    public function run(): void
    {
        $table = Schema::hasTable('affiliate_upsell_plans')
            ? 'affiliate_upsell_plans'
            : (Schema::hasTable('ea_affiliate_upsell_plans') ? 'ea_affiliate_upsell_plans' : null);

        if (!$table) {
            return;
        }

        $plans = [
            [
                'name' => 'Promoted Post',
                'slug' => 'promoted',
                'description' => 'Highlighted promotion for 3 weeks',
                'price' => 50.00,
                'currency' => 'USD',
                'duration_type' => 'weekly',
                'duration_value' => 3,
                'duration_days' => 21,
                'highlighted_background' => true,
                'appears_above_standard' => true,
                'visibility_multiplier' => 2,
                'top_of_category' => false,
                'larger_card_size' => false,
                'priority_search' => false,
                'homepage_placement' => false,
                'category_top_placement' => false,
                'homepage_slider' => false,
                'social_media_promotion' => false,
                'weekly_email_blast' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Featured Post',
                'slug' => 'featured',
                'description' => 'Top of category for 2 weeks',
                'price' => 30.00,
                'currency' => 'USD',
                'duration_type' => 'weekly',
                'duration_value' => 2,
                'duration_days' => 14,
                'highlighted_background' => true,
                'appears_above_standard' => true,
                'visibility_multiplier' => 3,
                'top_of_category' => true,
                'larger_card_size' => true,
                'priority_search' => true,
                'homepage_placement' => false,
                'category_top_placement' => false,
                'homepage_slider' => false,
                'social_media_promotion' => false,
                'weekly_email_blast' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Sponsored Post',
                'slug' => 'sponsored',
                'description' => 'Maximum visibility for 1 month',
                'price' => 100.00,
                'currency' => 'USD',
                'duration_type' => 'monthly',
                'duration_value' => 1,
                'duration_days' => 30,
                'highlighted_background' => true,
                'appears_above_standard' => true,
                'visibility_multiplier' => 5,
                'top_of_category' => true,
                'larger_card_size' => true,
                'priority_search' => true,
                'homepage_placement' => true,
                'category_top_placement' => true,
                'homepage_slider' => true,
                'social_media_promotion' => true,
                'weekly_email_blast' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            $existing = DB::table($table)->where('slug', $plan['slug'])->first();
            $payload = array_merge($plan, ['updated_at' => now()]);
            // Only set duration_days if column exists
            if (!Schema::hasColumn($table, 'duration_days')) {
                unset($payload['duration_days']);
            }
            if ($existing) {
                DB::table($table)->where('slug', $plan['slug'])->update($payload);
            } else {
                $payload['created_at'] = now();
                DB::table($table)->insert($payload);
            }
        }
    }
}
