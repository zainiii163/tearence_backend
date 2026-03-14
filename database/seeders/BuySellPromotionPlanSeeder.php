<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BuySellPromotionPlan;

class BuySellPromotionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Standard listing with basic visibility',
                'price' => 0.00,
                'duration_days' => 90,
                'features' => [
                    'Standard listing',
                    '90 days duration',
                    'Basic search visibility',
                    'Image uploads (up to 5)',
                ],
                'visibility_multiplier' => 1.0,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Promoted',
                'slug' => 'promoted',
                'description' => 'Enhanced visibility with promotional features',
                'price' => 19.99,
                'duration_days' => 30,
                'features' => [
                    'Promoted badge',
                    'Higher search ranking',
                    '30 days duration',
                    'Image uploads (up to 10)',
                    'Highlighted in search results',
                    'Social media promotion',
                ],
                'visibility_multiplier' => 2.0,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Featured',
                'slug' => 'featured',
                'description' => 'Premium placement with maximum visibility',
                'price' => 49.99,
                'duration_days' => 30,
                'features' => [
                    'Featured badge',
                    'Top search placement',
                    '30 days duration',
                    'Unlimited image uploads',
                    'Video support',
                    'Homepage featured section',
                    'Priority customer support',
                    'Social media promotion',
                    'Email newsletter inclusion',
                ],
                'visibility_multiplier' => 3.0,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Sponsored',
                'slug' => 'sponsored',
                'description' => 'Ultimate promotion package with exclusive benefits',
                'price' => 99.99,
                'duration_days' => 30,
                'features' => [
                    'Sponsored badge',
                    'Guaranteed top placement',
                    '30 days duration',
                    'Unlimited image uploads',
                    'Video support',
                    'Dedicated promotional banner',
                    'Priority customer support',
                    'Social media promotion',
                    'Email newsletter inclusion',
                    'Analytics dashboard',
                    'Verified seller badge',
                    'Direct messaging with buyers',
                ],
                'visibility_multiplier' => 5.0,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Urgent',
                'slug' => 'urgent',
                'description' => 'Quick sale promotion for time-sensitive listings',
                'price' => 9.99,
                'duration_days' => 7,
                'features' => [
                    'Urgent badge',
                    'Quick sale highlighting',
                    '7 days duration',
                    'Image uploads (up to 8)',
                    'Fast-track approval',
                    'Mobile app promotion',
                ],
                'visibility_multiplier' => 1.5,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Weekend Special',
                'slug' => 'weekend-special',
                'description' => 'Weekend boost for increased weekend traffic',
                'price' => 4.99,
                'duration_days' => 3,
                'features' => [
                    'Weekend special badge',
                    'Weekend traffic boost',
                    '3 days duration (Fri-Sun)',
                    'Image uploads (up to 6)',
                    'Weekend featured section',
                ],
                'visibility_multiplier' => 1.8,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($plans as $plan) {
            BuySellPromotionPlan::create($plan);
        }
    }
}
