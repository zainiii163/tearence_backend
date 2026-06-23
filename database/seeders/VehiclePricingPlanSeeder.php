<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehiclePricingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'price' => 0,
                'duration_days' => 30,
                'benefits' => json_encode([
                    'Basic listing',
                    'Standard visibility',
                    'Searchable',
                ]),
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Promoted',
                'slug' => 'promoted',
                'price' => 10,
                'duration_days' => 30,
                'benefits' => json_encode([
                    'Highlighted listing',
                    'Appears above standard ads',
                    'Promoted badge',
                    '2x more visibility',
                ]),
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Featured',
                'slug' => 'featured',
                'price' => 25,
                'duration_days' => 30,
                'benefits' => json_encode([
                    'Top of category pages',
                    'Larger vehicle card',
                    'Priority in search results',
                    'Featured badge',
                    '5x more visibility',
                    'Most Popular',
                ]),
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Sponsored',
                'slug' => 'sponsored',
                'price' => 50,
                'duration_days' => 30,
                'benefits' => json_encode([
                    'Homepage placement',
                    'Category top placement',
                    'Homepage slider inclusion',
                    'Social media promotion',
                    'Sponsored badge',
                    'Maximum visibility',
                ]),
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Top of Category',
                'slug' => 'top_of_category',
                'price' => 100,
                'duration_days' => 30,
                'benefits' => json_encode([
                    'Pinned at top of chosen category',
                    'Exclusive Top of Category badge',
                    'Category newsletter inclusion',
                    'Top Picks section',
                    'Priority over all tiers',
                    'Ultimate visibility',
                ]),
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Network Boost',
                'slug' => 'network_boost',
                'price' => 150,
                'duration_days' => 30,
                'benefits' => json_encode([
                    'All Sponsored benefits',
                    'Cross-platform promotion',
                    'Email blast to all users',
                    'Premium placement everywhere',
                    'Network-wide visibility',
                ]),
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        DB::table('vehicle_pricing_plans')->insert($plans);
    }
}
