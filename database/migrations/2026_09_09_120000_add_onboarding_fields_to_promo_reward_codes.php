<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Onboarding promo codes grant free promoted / featured / sponsored post credits
 * for WWA and CarServices business signups (shared API).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('promo_reward_codes')) {
            return;
        }

        // Allow free_posts reward type alongside checkout discounts
        try {
            DB::statement(
                "ALTER TABLE promo_reward_codes MODIFY COLUMN type ENUM(
                    'percent',
                    'fixed',
                    'points',
                    'free_posts'
                ) NOT NULL"
            );
        } catch (\Throwable $e) {
            try {
                DB::statement(
                    "ALTER TABLE promo_reward_codes MODIFY COLUMN type VARCHAR(32) NOT NULL"
                );
            } catch (\Throwable $e2) {
                // ignore
            }
        }

        Schema::table('promo_reward_codes', function (Blueprint $table) {
            if (! Schema::hasColumn('promo_reward_codes', 'purpose')) {
                $table->string('purpose', 32)->default('checkout')->after('type');
                // checkout | onboarding | both
            }
            if (! Schema::hasColumn('promo_reward_codes', 'platform')) {
                $table->string('platform', 32)->default('both')->after('purpose');
                // wwa | carservices | both
            }
            if (! Schema::hasColumn('promo_reward_codes', 'grant_tier')) {
                $table->string('grant_tier', 32)->nullable()->after('platform');
                // promoted | featured | sponsored | all
            }
            if (! Schema::hasColumn('promo_reward_codes', 'grant_quantity')) {
                $table->unsignedInteger('grant_quantity')->default(0)->after('grant_tier');
            }
            if (! Schema::hasColumn('promo_reward_codes', 'grant_duration_days')) {
                $table->unsignedInteger('grant_duration_days')->default(7)->after('grant_quantity');
            }
            if (! Schema::hasColumn('promo_reward_codes', 'max_redemptions_per_user')) {
                $table->unsignedInteger('max_redemptions_per_user')->default(1)->after('grant_duration_days');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('promo_reward_codes')) {
            return;
        }

        Schema::table('promo_reward_codes', function (Blueprint $table) {
            foreach ([
                'purpose',
                'platform',
                'grant_tier',
                'grant_quantity',
                'grant_duration_days',
                'max_redemptions_per_user',
            ] as $col) {
                if (Schema::hasColumn('promo_reward_codes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        try {
            DB::statement(
                "ALTER TABLE promo_reward_codes MODIFY COLUMN type ENUM(
                    'percent',
                    'fixed',
                    'points'
                ) NOT NULL"
            );
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
