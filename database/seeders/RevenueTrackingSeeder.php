<?php

namespace Database\Seeders;

use App\Models\RevenueTracking;
use App\Models\JobUpsell;
use App\Models\CandidateUpsell;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class RevenueTrackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobUpsells = JobUpsell::with('listing')->get();
        $candidateUpsells = CandidateUpsell::with('candidateProfile')->get();
        $customers = Customer::all();

        if ($jobUpsells->count() === 0 && $candidateUpsells->count() === 0) {
            $this->command->warn('No upsells found. Skipping RevenueTrackingSeeder.');
            return;
        }

        $revenues = [];

        // Track job upsell revenues
        foreach ($jobUpsells as $upsell) {
            $listing = $upsell->listing;
            if ($listing && $listing->customer_id) {
                $revenues[] = [
                    'revenue_type' => 'job_upsell',
                    'related_id' => $upsell->job_upsell_id,
                    'customer_id' => $listing->customer_id,
                    'upsell_type' => $upsell->upsell_type,
                    'amount' => $upsell->price,
                    'currency' => 'USD',
                    'payment_method' => 'stripe',
                    'payment_transaction_id' => $upsell->payment_transaction_id,
                    'payment_status' => $upsell->payment_status,
                    'payment_date' => $upsell->payment_status === 'completed' ? now()->subDays(rand(1, 30)) : null,
                ];
            }
        }

        // Track candidate upsell revenues
        foreach ($candidateUpsells as $upsell) {
            $profile = $upsell->candidateProfile;
            if ($profile && $profile->customer_id) {
                $revenues[] = [
                    'revenue_type' => 'candidate_upsell',
                    'related_id' => $upsell->candidate_upsell_id,
                    'customer_id' => $profile->customer_id,
                    'upsell_type' => $upsell->upsell_type,
                    'amount' => $upsell->price,
                    'currency' => 'USD',
                    'payment_method' => 'paypal',
                    'payment_transaction_id' => $upsell->payment_transaction_id,
                    'payment_status' => $upsell->payment_status,
                    'payment_date' => $upsell->payment_status === 'completed' ? now()->subDays(rand(1, 30)) : null,
                ];
            }
        }

        foreach ($revenues as $revenue) {
            RevenueTracking::create($revenue);
        }
    }
}

