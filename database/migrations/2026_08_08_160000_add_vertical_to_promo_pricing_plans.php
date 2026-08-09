<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('promo_pricing_plans')) {
            return;
        }

        Schema::table('promo_pricing_plans', function (Blueprint $table) {
            if (! Schema::hasColumn('promo_pricing_plans', 'vertical')) {
                $table->string('vertical', 40)->default('all')->after('slug')->index();
            }
            if (! Schema::hasColumn('promo_pricing_plans', 'is_popular')) {
                $table->boolean('is_popular')->default(false)->after('is_active');
            }
        });

        // Drop unique on slug alone if present; use composite unique (vertical, slug)
        try {
            Schema::table('promo_pricing_plans', function (Blueprint $table) {
                $table->dropUnique(['slug']);
            });
        } catch (\Throwable $e) {
            // index name may differ
            try {
                DB::statement('ALTER TABLE promo_pricing_plans DROP INDEX promo_pricing_plans_slug_unique');
            } catch (\Throwable $e2) {
                // ignore
            }
        }

        try {
            Schema::table('promo_pricing_plans', function (Blueprint $table) {
                $table->unique(['vertical', 'slug'], 'promo_pricing_plans_vertical_slug_unique');
            });
        } catch (\Throwable $e) {
            // already exists
        }

        // Existing rows become global defaults
        DB::table('promo_pricing_plans')->whereNull('vertical')->orWhere('vertical', '')->update(['vertical' => 'all']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('promo_pricing_plans')) {
            return;
        }

        Schema::table('promo_pricing_plans', function (Blueprint $table) {
            try {
                $table->dropUnique('promo_pricing_plans_vertical_slug_unique');
            } catch (\Throwable $e) {
            }
            if (Schema::hasColumn('promo_pricing_plans', 'is_popular')) {
                $table->dropColumn('is_popular');
            }
            if (Schema::hasColumn('promo_pricing_plans', 'vertical')) {
                $table->dropColumn('vertical');
            }
        });
    }
};
