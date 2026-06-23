<?php

namespace Database\Seeders;

use App\Models\JobPricingPlan;
use Illuminate\Database\Seeder;

class JobPricingPlansSeeder extends Seeder
{
    public function run()
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'price' => 0,
                'currency' => 'USD',
                'period' => 'one-time',
                'features' => json_encode([
                    'Standard job posting',
                    '30 days visibility',
                    'Basic applicant tracking',
                ]),
                'recommended' => false,
                'active' => true,
            ],
            [
                'name' => 'Promoted',
                'slug' => 'promoted',
                'price' => 29.99,
                'currency' => 'USD',
                'period' => '30 days',
                'features' => json_encode([
                    'Standard job posting',
                    '60 days visibility',
                    'Featured in search results',
                    'Priority applicant tracking',
                    'Email alerts to matching candidates',
                ]),
                'recommended' => true,
                'active' => true,
            ],
            [
                'name' => 'Featured',
                'slug' => 'featured',
                'price' => 79.99,
                'currency' => 'USD',
                'period' => '30 days',
                'features' => json_encode([
                    'Standard job posting',
                    '90 days visibility',
                    'Homepage featured placement',
                    'Top of search results',
                    'Premium applicant tracking',
                    'Social media promotion',
                    'Urgent badge',
                ]),
                'recommended' => false,
                'active' => true,
            ],
            [
                'name' => 'Sponsored',
                'slug' => 'sponsored',
                'price' => 149.99,
                'currency' => 'USD',
                'period' => '30 days',
                'features' => json_encode([
                    'Standard job posting',
                    '120 days visibility',
                    'Homepage featured placement',
                    'Top of search results',
                    'Premium applicant tracking',
                    'Social media promotion',
                    'Urgent badge',
                    'Featured in weekly newsletter',
                    'Dedicated email blast',
                ]),
                'recommended' => false,
                'active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            JobPricingPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
