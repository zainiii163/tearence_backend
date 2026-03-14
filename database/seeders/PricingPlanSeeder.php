<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PricingPlan;

class PricingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = [
            [
                'name' => 'Basic Listing',
                'price' => 0,
                'features' => [
                    'Standard visibility',
                    '7 days listing',
                    'Basic support'
                ],
                'recommended' => false,
                'duration_days' => 7,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Promoted',
                'price' => 29,
                'features' => [
                    'Enhanced visibility',
                    '30 days listing',
                    'Priority support',
                    'Promoted badge'
                ],
                'recommended' => false,
                'duration_days' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Featured',
                'price' => 79,
                'features' => [
                    'Premium placement',
                    '60 days listing',
                    'Featured badge',
                    'Analytics access'
                ],
                'recommended' => true,
                'duration_days' => 60,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sponsored',
                'price' => 149,
                'features' => [
                    'Homepage placement',
                    '90 days listing',
                    'Sponsored badge',
                    'Advanced analytics',
                    'Social media promotion'
                ],
                'recommended' => false,
                'duration_days' => 90,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($plans as $plan) {
            PricingPlan::create([
                'name' => $plan['name'],
                'price' => $plan['price'],
                'features' => json_encode($plan['features']),
                'recommended' => $plan['recommended'],
                'duration_days' => $plan['duration_days'],
                'is_active' => $plan['is_active'],
                'created_at' => $plan['created_at'],
                'updated_at' => $plan['updated_at'],
            ]);
        }
    }
}
