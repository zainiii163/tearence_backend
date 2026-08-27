<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin /admin/jobs/create 500s when the form writes columns/FKs that do not
 * match production (category_id vs job_category_id, user_id FK to users.id).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jobs')) {
            return;
        }

        try {
            Schema::table('jobs', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Throwable $e) {
            // Named differently or already dropped — jobs.user_id stores customer_id.
        }

        Schema::table('jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('jobs', 'slug')) {
                $table->string('slug', 250)->nullable();
            }
            if (! Schema::hasColumn('jobs', 'job_category_id') && Schema::hasColumn('jobs', 'category_id')) {
                $table->unsignedBigInteger('job_category_id')->nullable();
            }
            if (! Schema::hasColumn('jobs', 'category_id') && Schema::hasColumn('jobs', 'job_category_id')) {
                $table->unsignedBigInteger('category_id')->nullable();
            }
            if (! Schema::hasColumn('jobs', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (! Schema::hasColumn('jobs', 'status')) {
                $table->string('status', 50)->default('active');
            }
            if (! Schema::hasColumn('jobs', 'posted_at')) {
                $table->timestamp('posted_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Keep columns / dropped FK — safer on production.
    }
};
