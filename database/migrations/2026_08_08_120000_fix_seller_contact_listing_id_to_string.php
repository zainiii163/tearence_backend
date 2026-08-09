<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('seller_contact_messages')) {
            return;
        }

        // Listing IDs across hubs are UUIDs (e.g. buysell_adverts.id) — bigint truncates/fails.
        Schema::table('seller_contact_messages', function (Blueprint $table) {
            $table->string('listing_id', 64)->change();
        });

        // Ensure contacts_count exists on buysell_adverts for increment()
        if (Schema::hasTable('buysell_adverts') && ! Schema::hasColumn('buysell_adverts', 'contacts_count')) {
            Schema::table('buysell_adverts', function (Blueprint $table) {
                $table->unsignedInteger('contacts_count')->default(0)->after('views_count');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('seller_contact_messages')) {
            return;
        }

        // Cannot safely cast UUID strings back to bigint; leave as string.
        Schema::table('seller_contact_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('listing_id')->change();
        });
    }
};
