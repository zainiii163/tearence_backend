<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B15: give `customer` a soft-delete tombstone.
 *
 * Account deletion used to be a hard delete (and was broken besides — it queried
 * a non-existent `id` column). With `deleted_at` a deleted account leaves a
 * record, so login can answer "this account is deleted" instead of a generic
 * "not found", and the row can be restored if a deletion was a mistake.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            if (! Schema::hasColumn('customer', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            if (Schema::hasColumn('customer', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
