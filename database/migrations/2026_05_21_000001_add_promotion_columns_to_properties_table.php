<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing promotion columns to properties table
     */
    public function up(): void
    {
        if (!Schema::hasTable('properties')) {
            return;
        }

        Schema::table('properties', function (Blueprint $table) {
            // Add advert_type column if it doesn't exist
            if (!Schema::hasColumn('properties', 'advert_type')) {
                $table->enum('advert_type', ['standard', 'promoted', 'featured', 'sponsored'])->default('standard')->after('verified_agent');
            }

            // Add promoted_until column if it doesn't exist
            if (!Schema::hasColumn('properties', 'promoted_until')) {
                $table->timestamp('promoted_until')->nullable()->after('advert_type');
            }

            // Add featured_until column if it doesn't exist
            if (!Schema::hasColumn('properties', 'featured_until')) {
                $table->timestamp('featured_until')->nullable()->after('promoted_until');
            }

            // Add sponsored_until column if it doesn't exist
            if (!Schema::hasColumn('properties', 'sponsored_until')) {
                $table->timestamp('sponsored_until')->nullable()->after('featured_until');
            }

            // Add index for advert_type if it doesn't exist
            if (!Schema::hasIndex('properties', ['advert_type'])) {
                $table->index(['advert_type']);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('properties')) {
            return;
        }

        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'advert_type')) {
                $table->dropColumn('advert_type');
            }
            if (Schema::hasColumn('properties', 'promoted_until')) {
                $table->dropColumn('promoted_until');
            }
            if (Schema::hasColumn('properties', 'featured_until')) {
                $table->dropColumn('featured_until');
            }
            if (Schema::hasColumn('properties', 'sponsored_until')) {
                $table->dropColumn('sponsored_until');
            }
        });
    }
};
