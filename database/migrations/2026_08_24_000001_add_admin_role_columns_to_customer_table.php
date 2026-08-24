<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * WWA super-admin management needs role/status flags on the customer
     * table (web app login). The Filament users table already has its own
     * flags; this brings the customer table up to parity.
     */
    public function up(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            if (! Schema::hasColumn('customer', 'role')) {
                $table->string('role', 30)->nullable()->after('user_type');
            }
            if (! Schema::hasColumn('customer', 'status')) {
                $table->string('status', 20)->default('active')->after('role');
            }
            if (! Schema::hasColumn('customer', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            foreach (['is_super_admin', 'status', 'role'] as $column) {
                if (Schema::hasColumn('customer', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
