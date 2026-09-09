<?php

namespace Database\Seeders;

use App\Models\PromoRewardCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Onboarding promo codes for WWA + CarServices business signup.
 * Grants free promoted / featured / sponsored posts after registration.
 */
class OnboardingPromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('promo_reward_codes')) {
            $this->command?->warn('promo_reward_codes missing — skip OnboardingPromoCodeSeeder');

            return;
        }

        $codes = [
            [
                'code' => 'WWA-WELCOME',
                'platform' => 'wwa',
                'grant_tier' => 'all',
                'grant_quantity' => 1,
                'grant_duration_days' => 7,
                'description' => 'WWA business welcome — 1 free promoted + featured + sponsored post (7 days each)',
            ],
            [
                'code' => 'WWA-PROMO',
                'platform' => 'wwa',
                'grant_tier' => 'promoted',
                'grant_quantity' => 1,
                'grant_duration_days' => 7,
                'description' => 'WWA — 1 free promoted post (7 days)',
            ],
            [
                'code' => 'WWA-FEATURED',
                'platform' => 'wwa',
                'grant_tier' => 'featured',
                'grant_quantity' => 1,
                'grant_duration_days' => 7,
                'description' => 'WWA — 1 free featured post (7 days)',
            ],
            [
                'code' => 'WWA-SPONSORED',
                'platform' => 'wwa',
                'grant_tier' => 'sponsored',
                'grant_quantity' => 1,
                'grant_duration_days' => 7,
                'description' => 'WWA — 1 free sponsored post (7 days)',
            ],
            [
                'code' => 'CSL-WELCOME',
                'platform' => 'carservices',
                'grant_tier' => 'all',
                'grant_quantity' => 1,
                'grant_duration_days' => 7,
                'description' => 'CarServices business welcome — 1 free promoted + featured + sponsored post (7 days each)',
            ],
            [
                'code' => 'CSL-PROMO',
                'platform' => 'carservices',
                'grant_tier' => 'promoted',
                'grant_quantity' => 1,
                'grant_duration_days' => 7,
                'description' => 'CarServices — 1 free promoted post (7 days)',
            ],
            [
                'code' => 'CSL-FEATURED',
                'platform' => 'carservices',
                'grant_tier' => 'featured',
                'grant_quantity' => 1,
                'grant_duration_days' => 7,
                'description' => 'CarServices — 1 free featured post (7 days)',
            ],
            [
                'code' => 'CSL-SPONSORED',
                'platform' => 'carservices',
                'grant_tier' => 'sponsored',
                'grant_quantity' => 1,
                'grant_duration_days' => 7,
                'description' => 'CarServices — 1 free sponsored post (7 days)',
            ],
        ];

        foreach ($codes as $row) {
            $payload = [
                'type' => 'free_posts',
                'value' => 0,
                'purpose' => 'onboarding',
                'platform' => $row['platform'],
                'grant_tier' => $row['grant_tier'],
                'grant_quantity' => $row['grant_quantity'],
                'grant_duration_days' => $row['grant_duration_days'],
                'max_redemptions_per_user' => 1,
                'max_uses' => 5000,
                'uses_count' => 0,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addYear(),
                'applies_to' => null,
                'is_active' => true,
                'description' => $row['description'],
            ];

            // Only set new columns if migration ran
            if (! Schema::hasColumn('promo_reward_codes', 'purpose')) {
                unset(
                    $payload['purpose'],
                    $payload['platform'],
                    $payload['grant_tier'],
                    $payload['grant_quantity'],
                    $payload['grant_duration_days'],
                    $payload['max_redemptions_per_user']
                );
            }

            PromoRewardCode::updateOrCreate(['code' => $row['code']], $payload);
        }

        $this->command?->info('Onboarding promo codes seeded (WWA-* and CSL-*).');
    }
}
