<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Operational fleet status for dealer / hire businesses and super-admin board.
     */
    public function up(): void
    {
        if (! Schema::hasTable('vehicles')) {
            return;
        }

        Schema::table('vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('vehicles', 'fleet_status')) {
                $table->string('fleet_status', 32)
                    ->default('available')
                    ->after('status')
                    ->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('vehicles')) {
            return;
        }

        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'fleet_status')) {
                $table->dropIndex(['fleet_status']);
                $table->dropColumn('fleet_status');
            }
        });
    }
};
