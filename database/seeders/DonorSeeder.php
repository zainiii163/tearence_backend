<?php

namespace Database\Seeders;

use App\Models\Donor;
use App\Models\Campaign;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DonorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaigns = Campaign::all();
        $customers = Customer::all();

        if ($campaigns->count() === 0) {
            $this->command->warn('No campaigns found. Skipping DonorSeeder.');
            return;
        }

        $donors = [];

        foreach ($campaigns as $campaign) {
            // Create 2-3 donors per campaign
            for ($i = 0; $i < 3; $i++) {
                $customer = $customers->random();
                $amount = rand(50, 500) * 100; // Random amount between $50 and $500
                $fee = (int)($amount * 0.03); // 3% fee

                $donors[] = [
                    'customer_id' => $customer->customer_id,
                    'campaign_id' => $campaign->id,
                    'anonymous' => rand(0, 1) === 1,
                    'amount' => $amount,
                    'fee' => $fee,
                    'message' => 'Great cause! Keep up the good work.',
                    'paid' => rand(0, 1) === 1,
                    'uuid' => Str::uuid()->toString(),
                    'ref_id' => 'REF-' . Str::upper(Str::random(10)),
                    'payment_method' => ['paypal', 'stripe', 'bank_transfer'][rand(0, 2)],
                    'expired_at' => now()->addDays(7),
                    'paid_at' => rand(0, 1) === 1 ? now()->subDays(rand(1, 5)) : null,
                ];
            }
        }

        foreach ($donors as $donor) {
            Donor::create($donor);
        }
    }
}

