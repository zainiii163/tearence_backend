<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $expiresAt = now()->addYears(1);
        $title = '%Forklift%Training%';
        $email = 'hanzoali96@gmail.com';

        // 1. Flag in the listing table
        $listing = DB::table('listing')
            ->where('title', 'LIKE', $title)
            ->orWhere('email', $email)
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
        }

        // Summary for artisan output
        $found = collect([
            'listing'           => $listing,
            'promoted_adverts'  => $promoted,
            'sponsored_adverts' => $sponsored,
            'featured_adverts'  => $featured,
            'vehicles_adverts'  => $vehicles,
        ])->filter()->count();

        if ($found === 0) {
            $this->command->warn('No advert found matching "Forklift Training" or ' . $email . '. Check the title/email and adjust the migration.');
        } else {
            $this->command->info("Forklifts Training advert flagged as sponsored, promoted & featured in {$found} table(s).");
        }
    }

    public function down(): void
    {
        $title = '%Forklift%Training%';
        $email = 'hanzoali96@gmail.com';

        DB::table('listing')
            ->where('title', 'LIKE', $title)
            ->orWhere('email', $email)
            ->update([
                'is_featured'  => false,
                'is_promoted'  => false,
                'is_sponsored' => false,
            ]);

        DB::table('promoted_adverts')
            ->where('title', 'LIKE', $title)
            ->orWhere('email', $email)
            ->update([
                'is_featured'    => false,
                'promotion_tier' => 'promoted_basic',
            ]);

        DB::table('sponsored_adverts')
            ->where('title', 'LIKE', $title)
            ->orWhere('email', $email)
            ->update([
                'sponsored_tier' => 'basic',
            ]);

        DB::table('featured_adverts')
            ->where('title', 'LIKE', $title)
            ->orWhere('contact_email', $email)
            ->update([
                'upsell_tier' => 'promoted',
            ]);

        DB::table('vehicles_adverts')
            ->where('title', 'LIKE', $title)
            ->orWhere('email', $email)
            ->update([
                'promotion_tier' => 'standard',
            ]);
    }
};
