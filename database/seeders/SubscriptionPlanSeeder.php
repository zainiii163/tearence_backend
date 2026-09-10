<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'basic',
                'name' => 'Basic',
                'description' => 'Get started with 1 paid post and 1 promoted post per month, plus support.',
                'price' => 30.00,
                'currency' => 'USD',
                'duration_days' => 30,
                'paid_posts' => 1,
                'promoted_posts' => 1,
                'featured_posts' => 0,
                'sponsored_posts' => 0,
                'support_included' => true,
                'marketing_toolkit' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'plus',
                'name' => 'Plus',
                'description' => 'Boost your reach with 1 featured post and 1 sponsored post per month, plus support.',
                'price' => 50.00,
                'currency' => 'USD',
                'duration_days' => 30,
                'paid_posts' => 0,
                'promoted_posts' => 0,
                'featured_posts' => 1,
                'sponsored_posts' => 1,
                'support_included' => true,
                'marketing_toolkit' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'slug' => 'business',
                'name' => 'Business',
                'description' => 'Full package: 1 paid, 1 promoted, 1 featured, 1 sponsored post per month, plus support and marketing toolkits.',
                'price' => 100.00,
                'currency' => 'USD',
                'duration_days' => 30,
                'paid_posts' => 1,
                'promoted_posts' => 1,
                'featured_posts' => 1,
                'sponsored_posts' => 1,
                'support_included' => true,
                'marketing_toolkit' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
