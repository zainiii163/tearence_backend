<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BooksPricingPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Promoted Book',
                'ad_type' => 'books',
                'tier_type' => 'promoted',
                'price' => 29.99,
                'duration_days' => 30,
                'description' => 'Get your book highlighted with increased visibility',
                'features' => json_encode([
                    'Highlighted listing',
                    'Appears above standard book ads',
                    '"Promoted" badge',
                    '2× more visibility',
                    'Basic analytics',
                ]),
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Featured Book',
                'ad_type' => 'books',
                'tier_type' => 'featured',
                'price' => 79.99,
                'duration_days' => 30,
                'description' => 'Maximum visibility with premium placement',
                'features' => json_encode([
                    'Top of genre/category pages',
                    'Larger book card',
                    'Priority in search results',
                    'Included in weekly "Featured Books" email',
                    '"Featured" badge',
                    'Advanced analytics',
                    'Social media mentions',
                ]),
                'is_active' => true,
                'is_featured' => true, // Mark as "Most Popular"
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sponsored Book',
                'ad_type' => 'books',
                'tier_type' => 'sponsored',
                'price' => 149.99,
                'duration_days' => 30,
                'description' => 'Premium sponsorship with maximum exposure',
                'features' => json_encode([
                    'Homepage placement',
                    'Category top placement',
                    'Included in homepage slider',
                    'Included in social media promotion',
                    '"Sponsored" badge',
                    'Maximum visibility',
                    'Premium analytics dashboard',
                    'Email campaign inclusion',
                ]),
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Top of Category',
                'ad_type' => 'books',
                'tier_type' => 'top_category',
                'price' => 299.99,
                'duration_days' => 30,
                'description' => 'Ultimate visibility with category dominance',
                'features' => json_encode([
                    'Always pinned at the top of the chosen genre',
                    'Exclusive "Top of Category" badge',
                    'Included in genre newsletters',
                    'Included in "Top Picks of the Month" section',
                    'Priority over all other tiers',
                    'Dedicated social media campaign',
                    'Author interview feature',
                    'Premium analytics with insights',
                    'Personalized marketing support',
                ]),
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('ad_pricing_plans')->insert($plans);
    }
}
