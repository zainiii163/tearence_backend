<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Promo plans now include free listing + affiliate cookie packages.
 * MySQL ENUM must allow: free, paid, promoted, featured, sponsored, cookie.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('promo_pricing_plans')) {
            return;
        }

        if (! Schema::hasColumn('promo_pricing_plans', 'tier')) {
            return;
        }

        // Expand ENUM (MySQL). Safe no-op path if already widened.
        try {
            DB::statement(
                "ALTER TABLE promo_pricing_plans MODIFY COLUMN tier ENUM(
                    'free',
                    'paid',
                    'promoted',
                    'featured',
                    'sponsored',
                    'cookie'
                ) NOT NULL"
            );
        } catch (\Throwable $e) {
            // Fallback: widen to VARCHAR so seeders never truncate again
            DB::statement(
                "ALTER TABLE promo_pricing_plans MODIFY COLUMN tier VARCHAR(32) NOT NULL"
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('promo_pricing_plans') || ! Schema::hasColumn('promo_pricing_plans', 'tier')) {
            return;
        }

        // Remove rows that cannot fit the old enum before shrinking
        DB::table('promo_pricing_plans')
            ->whereNotIn('tier', ['paid', 'promoted', 'featured', 'sponsored'])
            ->delete();

        DB::statement(
            "ALTER TABLE promo_pricing_plans MODIFY COLUMN tier ENUM(
                'paid',
                'promoted',
                'featured',
                'sponsored'
            ) NOT NULL"
        );
    }
};
