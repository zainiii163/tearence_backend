<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobPricingPlan;
use Illuminate\Support\Facades\DB;

class JobPricingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing pricing plans
        DB::table('job_pricing_plans')->delete();

        $plans = [
            [
                'slug' => 'basic',
                'name' => 'Basic Listing',
                'description' => 'Standard job posting with 30-day duration',
                'price' => 29.99,
                'currency' => 'USD',
                'period' => 'month',
                'features' => [
                    '30-day posting duration',
                    'Standard visibility',
                    'Basic applicant tracking',
                    'Email notifications',
                ],
                'recommended' => false,
                'active' => true,
                'duration_months' => 1,
                'visibility_multiplier' => 1,
            ],
            [
                'slug' => 'promoted',
                'name' => 'Promoted Listing',
                'description' => 'Enhanced visibility with highlighted placement',
                'price' => 49.99,
                'currency' => 'USD',
                'period' => 'month',
                'features' => [
                    '30-day posting duration',
                    '2x visibility boost',
                    'Highlighted in search results',
                    'Priority in category listings',
                    'Advanced applicant tracking',
                    'Email notifications',
                ],
                'recommended' => true,
                'active' => true,
                'duration_months' => 1,
                'visibility_multiplier' => 2,
            ],
            [
                'slug' => 'featured',
                'name' => 'Featured Listing',
                'description' => 'Premium placement with maximum visibility',
                'price' => 89.99,
                'currency' => 'USD',
                'period' => 'month',
                'features' => [
                    '30-day posting duration',
                    '3x visibility boost',
                    'Featured on homepage',
                    'Top placement in search results',
                    'Featured in category listings',
                    'Advanced applicant tracking',
                    'Priority support',
                    'Email notifications',
                ],
                'recommended' => false,
                'active' => true,
                'duration_months' => 1,
                'visibility_multiplier' => 3,
            ],
            [
                'slug' => 'sponsored',
                'name' => 'Sponsored Listing',
                'description' => 'Maximum exposure with sponsored placement',
                'price' => 149.99,
                'currency' => 'USD',
                'period' => 'month',
                'features' => [
                    '30-day posting duration',
                    '5x visibility boost',
                    'Sponsored banner placement',
                    'Top placement in all listings',
                    'Featured in email newsletters',
                    'Social media promotion',
                    'Premium applicant tracking',
                    'Dedicated support',
                    'Email notifications',
                ],
                'recommended' => false,
                'active' => true,
                'duration_months' => 1,
                'visibility_multiplier' => 5,
            ],
            [
                'slug' => 'network',
                'name' => 'Network-Wide Boost',
                'description' => 'Ultimate visibility across all network platforms',
                'price' => 299.99,
                'currency' => 'USD',
                'period' => 'month',
                'features' => [
                    '60-day posting duration',
                    '10x visibility boost',
                    'Network-wide featured placement',
                    'Cross-platform promotion',
                    'Premium banner advertising',
                    'Featured in email newsletters',
                    'Social media campaign',
                    'Premium applicant tracking',
                    'Dedicated account manager',
                    'Priority support',
                    'Email notifications',
                ],
                'recommended' => false,
                'active' => true,
                'duration_months' => 2,
                'visibility_multiplier' => 10,
            ],
        ];

        foreach ($plans as $plan) {
            JobPricingPlan::create($plan);
        }

        $this->command->info('Job pricing plans seeded successfully!');
    }
}
