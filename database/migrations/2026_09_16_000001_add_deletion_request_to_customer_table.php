<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Account deletion is request → admin approval, not immediate.
 *
 * `deletion_requested_at` marks a pending request the user submitted; the
 * account stays fully usable until an admin approves. Approval sets the
 * existing soft-delete `deleted_at` (the "deleted flag" the system reads) —
 * the row is never physically removed, so the system always knows the account
 * was deleted, and login answers "this account is deleted".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            if (! Schema::hasColumn('customer', 'deletion_requested_at')) {
                $table->timestamp('deletion_requested_at')->nullable()->after('avatar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            if (Schema::hasColumn('customer', 'deletion_requested_at')) {
                $table->dropColumn('deletion_requested_at');
            }
        });
    }
};
