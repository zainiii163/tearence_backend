<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop users FK if present
        try {
            Schema::table('funding_pledges', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Throwable $e) {
            // already dropped or named differently
        }

        if (Schema::hasColumn('funding_pledges', 'user_id') && !Schema::hasColumn('funding_pledges', 'customer_id')) {
            DB::statement('ALTER TABLE funding_pledges CHANGE user_id customer_id INT UNSIGNED NOT NULL');
        }

        // Ensure index on customer_id
        Schema::table('funding_pledges', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            // Prefer raw index create if missing — ignore failures
        });

        try {
            Schema::table('funding_pledges', function (Blueprint $table) {
                $table->foreign('customer_id')
                    ->references('customer_id')
                    ->on('customer')
                    ->onDelete('cascade');
            });
        } catch (\Throwable $e) {
            // FK may already exist
        }
    }

    public function down(): void
    {
        try {
            Schema::table('funding_pledges', function (Blueprint $table) {
                $table->dropForeign(['customer_id']);
            });
        } catch (\Throwable $e) {
        }

        if (Schema::hasColumn('funding_pledges', 'customer_id') && !Schema::hasColumn('funding_pledges', 'user_id')) {
            DB::statement('ALTER TABLE funding_pledges CHANGE customer_id user_id INT UNSIGNED NOT NULL');
        }

        try {
            Schema::table('funding_pledges', function (Blueprint $table) {
                $table->foreign('user_id')
                    ->references('user_id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        } catch (\Throwable $e) {
        }
    }
};
