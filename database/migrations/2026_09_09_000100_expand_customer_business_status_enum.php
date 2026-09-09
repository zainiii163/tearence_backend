<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Signup was inserting status=pending into customer_business, but the column
 * was ENUM('inactive','active') only — MySQL truncated and returned a 500.
 * Expand allowed values so pending remains valid if moderation needs it later.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_business')) {
            return;
        }

        // MySQL ENUM alter — keep existing rows valid
        DB::statement("ALTER TABLE customer_business MODIFY COLUMN status ENUM('inactive', 'active', 'pending') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_business')) {
            return;
        }

        DB::table('customer_business')->where('status', 'pending')->update(['status' => 'active']);
        DB::statement("ALTER TABLE customer_business MODIFY COLUMN status ENUM('inactive', 'active') NOT NULL DEFAULT 'active'");
    }
};
