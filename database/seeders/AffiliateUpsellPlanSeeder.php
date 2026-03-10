<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AffiliateUpsellPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Promoted Post',
                'slug' => 'promoted',
                'description' => 'Get highlighted background and appear above standard posts with 2x more visibility',
                'price' => 29.99,
                'currency' => 'GBP',
                'duration_type' => 'monthly',
                'duration_value' => 1,
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
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Featured Post',
                'slug' => 'featured',
                'description' => 'Top of category pages, larger card size, priority in search results, and weekly email blast inclusion',
                'price' => 59.99,
                'currency' => 'GBP',
                'duration_type' => 'monthly',
                'duration_value' => 1,
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
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sponsored Post',
                'slug' => 'sponsored',
                'description' => 'Maximum visibility with homepage placement, category top placement, homepage slider, and social media promotion',
                'price' => 99.99,
                'currency' => 'GBP',
                'duration_type' => 'monthly',
                'duration_value' => 1,
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
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('ea_affiliate_upsell_plans')->insert($plans);
    }
}
