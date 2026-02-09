<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::take(3)->get();

        if ($customers->count() === 0) {
            $this->command->warn('No customers found. Skipping CampaignSeeder.');
            return;
        }

        $campaigns = [
            [
                'customer_id' => $customers[0]->customer_id,
                'code' => 'CAMP-' . Str::upper(Str::random(6)),
                'slug' => Str::slug('Education for All'),
                'title' => 'Education for All',
                'thumbnail' => 'campaigns/education-for-all.jpg',
                'description' => 'Help provide quality education to underprivileged children around the world.',
                'target' => 50000,
                'collected' => 12500,
                'donors' => 45,
                'views' => 1250,
                'location' => 'Global',
                'target_date' => now()->addMonths(6),
                'status' => 'active',
            ],
            [
                'customer_id' => $customers->count() > 1 ? $customers[1]->customer_id : $customers[0]->customer_id,
                'code' => 'CAMP-' . Str::upper(Str::random(6)),
                'slug' => Str::slug('Clean Water Initiative'),
                'title' => 'Clean Water Initiative',
                'thumbnail' => 'campaigns/clean-water.jpg',
                'description' => 'Bringing clean and safe drinking water to communities in need.',
                'target' => 75000,
                'collected' => 30000,
                'donors' => 120,
                'views' => 3200,
                'location' => 'Africa',
                'target_date' => now()->addMonths(8),
                'status' => 'active',
            ],
            [
                'customer_id' => $customers->count() > 2 ? $customers[2]->customer_id : $customers[0]->customer_id,
                'code' => 'CAMP-' . Str::upper(Str::random(6)),
                'slug' => Str::slug('Food for Families'),
                'title' => 'Food for Families',
                'thumbnail' => 'campaigns/food-for-families.jpg',
                'description' => 'Supporting families in need with essential food supplies.',
                'target' => 30000,
                'collected' => 8500,
                'donors' => 32,
                'views' => 890,
                'location' => 'Asia',
                'target_date' => now()->addMonths(4),
                'status' => 'active',
            ],
        ];

        foreach ($campaigns as $campaign) {
            Campaign::create($campaign);
        }
    }
}

