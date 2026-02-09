<?php

namespace Database\Seeders;

use App\Models\CandidateUpsell;
use App\Models\CandidateProfile;
use Illuminate\Database\Seeder;

class CandidateUpsellSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = CandidateProfile::take(2)->get();

        if ($profiles->count() === 0) {
            $this->command->warn('No candidate profiles found. Skipping CandidateUpsellSeeder.');
            return;
        }

        $upsells = [
            [
                'candidate_profile_id' => $profiles[0]->candidate_profile_id,
                'upsell_type' => 'featured_profile',
                'price' => 79.99,
                'duration_days' => 30,
                'starts_at' => now(),
                'expires_at' => now()->addDays(30),
                'status' => 'active',
                'payment_status' => 'completed',
                'payment_transaction_id' => 'TXN-' . strtoupper(uniqid()),
            ],
            [
                'candidate_profile_id' => $profiles->count() > 1 ? $profiles[1]->candidate_profile_id : $profiles[0]->candidate_profile_id,
                'upsell_type' => 'job_alerts_boost',
                'price' => 39.99,
                'duration_days' => 15,
                'starts_at' => now(),
                'expires_at' => now()->addDays(15),
                'status' => 'active',
                'payment_status' => 'completed',
                'payment_transaction_id' => 'TXN-' . strtoupper(uniqid()),
            ],
        ];

        foreach ($upsells as $upsell) {
            CandidateUpsell::create($upsell);
        }
    }
}

