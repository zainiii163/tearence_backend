<?php

namespace Database\Seeders;

use App\Models\JobUpsell;
use App\Models\Listing;
use Illuminate\Database\Seeder;

class JobUpsellSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $listings = Listing::take(3)->get();

        if ($listings->count() === 0) {
            $this->command->warn('No listings found. Skipping JobUpsellSeeder.');
            return;
        }

        $upsells = [
            [
                'listing_id' => $listings[0]->listing_id,
                'upsell_type' => 'featured',
                'price' => 99.99,
                'duration_days' => 30,
                'starts_at' => now(),
                'expires_at' => now()->addDays(30),
                'status' => 'active',
                'payment_status' => 'completed',
                'payment_transaction_id' => 'TXN-' . strtoupper(uniqid()),
            ],
            [
                'listing_id' => $listings->count() > 1 ? $listings[1]->listing_id : $listings[0]->listing_id,
                'upsell_type' => 'suggested',
                'price' => 49.99,
                'duration_days' => 15,
                'starts_at' => now(),
                'expires_at' => now()->addDays(15),
                'status' => 'active',
                'payment_status' => 'completed',
                'payment_transaction_id' => 'TXN-' . strtoupper(uniqid()),
            ],
            [
                'listing_id' => $listings->count() > 2 ? $listings[2]->listing_id : $listings[0]->listing_id,
                'upsell_type' => 'featured',
                'price' => 99.99,
                'duration_days' => 30,
                'starts_at' => now()->addDays(5),
                'expires_at' => now()->addDays(35),
                'status' => 'pending',
                'payment_status' => 'pending',
            ],
        ];

        foreach ($upsells as $upsell) {
            JobUpsell::create($upsell);
        }
    }
}

