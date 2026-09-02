<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ForkliftsTrainingAdvertSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $expiresAt = now()->addYears(1);
        $title = '%Forklift%Training%';
        $email = 'hanzoali96@gmail.com';

        // 1. Flag in the listing table (no email column — link is via customer_id)
        $listing = DB::table('listing')
            ->where('title', 'LIKE', $title)
            ->first();

        if ($listing) {
            DB::table('listing')
                ->where('listing_id', $listing->listing_id)
                ->update([
                    'is_featured'          => true,
                    'is_promoted'          => true,
                    'is_sponsored'         => true,
                    'featured_expires_at'  => $expiresAt,
                    'promoted_expires_at'  => $expiresAt,
                    'sponsored_expires_at' => $expiresAt,
                    'updated_at'           => $now,
                ]);
            $this->command->info("Flagged listing #{$listing->listing_id}");
        }

        // 2. Flag in the promoted_adverts table
        $promoted = DB::table('promoted_adverts')
            ->where('title', 'LIKE', $title)
            ->orWhere('email', $email)
            ->first();

        if ($promoted) {
            DB::table('promoted_adverts')
                ->where('id', $promoted->id)
                ->update([
                    'is_featured'      => true,
                    'is_active'        => true,
                    'status'           => 'active',
                    'promotion_tier'   => 'network_wide_boost',
                    'promotion_start'  => $now->toDateString(),
                    'promotion_end'    => $expiresAt->toDateString(),
                    'approved_at'      => $now,
                    'updated_at'       => $now,
                ]);
            $this->command->info("Flagged promoted_advert #{$promoted->id}");
        }

        // 3. Flag in the sponsored_adverts table
        $sponsored = DB::table('sponsored_adverts')
            ->where('title', 'LIKE', $title)
            ->orWhere('email', $email)
            ->first();

        if ($sponsored) {
            DB::table('sponsored_adverts')
                ->where('id', $sponsored->id)
                ->update([
                    'is_active'         => true,
                    'status'            => 'active',
                    'sponsored_tier'    => 'premium',
                    'promotion_start'   => $now,
                    'promotion_end'     => $expiresAt,
                    'approved_at'       => $now,
                    'updated_at'        => $now,
                ]);
            $this->command->info("Flagged sponsored_advert #{$sponsored->id}");
        }

        // 4. Flag in the featured_adverts table (uses upsell_tier)
        $featured = DB::table('featured_adverts')
            ->where('title', 'LIKE', $title)
            ->orWhere('contact_email', $email)
            ->first();

        if ($featured) {
            DB::table('featured_adverts')
                ->where('id', $featured->id)
                ->update([
                    'is_active'      => true,
                    'upsell_tier'    => 'sponsored',
                    'starts_at'      => $now,
                    'expires_at'     => $expiresAt,
                    'payment_status' => 'paid',
                    'updated_at'     => $now,
                ]);
            $this->command->info("Flagged featured_advert #{$featured->id}");
        }

        // 5. Flag in the vehicles_adverts table (uses promotion_tier)
        $vehicles = DB::table('vehicles_adverts')
            ->where('title', 'LIKE', $title)
            ->orWhere('email', $email)
            ->first();

        if ($vehicles) {
            DB::table('vehicles_adverts')
                ->where('id', $vehicles->id)
                ->update([
                    'is_active'        => true,
                    'status'           => 'active',
                    'promotion_tier'   => 'sponsored',
                    'promotion_start'  => $now->toDateString(),
                    'promotion_end'    => $expiresAt->toDateString(),
                    'updated_at'       => $now,
                ]);
            $this->command->info("Flagged vehicles_advert #{$vehicles->id}");
        }

        // Summary
        $found = collect([
            $listing, $promoted, $sponsored, $featured, $vehicles,
        ])->filter()->count();

        if ($found === 0) {
            $this->command->warn('No advert found matching "Forklift Training" or ' . $email . '.');
        } else {
            $this->command->info("Done — flagged in {$found} table(s).");
        }
    }
}
