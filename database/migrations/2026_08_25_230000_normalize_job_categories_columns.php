<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Production may have the older job_categories shape (active/job_count)
 * while Filament expects is_active/sort_order/jobs_count — that causes 500s.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('job_categories')) {
            return;
        }

        Schema::table('job_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('job_categories', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (! Schema::hasColumn('job_categories', 'sort_order')) {
                $table->integer('sort_order')->default(0);
            }
            if (! Schema::hasColumn('job_categories', 'jobs_count')) {
                $table->integer('jobs_count')->default(0);
            }
            if (! Schema::hasColumn('job_categories', 'color')) {
                $table->string('color', 7)->nullable();
            }
            if (! Schema::hasColumn('job_categories', 'icon')) {
                $table->string('icon', 50)->nullable();
            }
            if (! Schema::hasColumn('job_categories', 'description')) {
                $table->text('description')->nullable();
            }
        });

        // Copy legacy columns if present
        if (Schema::hasColumn('job_categories', 'active') && Schema::hasColumn('job_categories', 'is_active')) {
            DB::table('job_categories')->orderBy('id')->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('job_categories')->where('id', $row->id)->update([
                        'is_active' => (bool) ($row->active ?? true),
                    ]);
                }
            });
        }

        if (Schema::hasColumn('job_categories', 'job_count') && Schema::hasColumn('job_categories', 'jobs_count')) {
            DB::table('job_categories')->orderBy('id')->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('job_categories')->where('id', $row->id)->update([
                        'jobs_count' => (int) ($row->job_count ?? 0),
                    ]);
                }
            });
        }
    }

    public function down(): void
    {
        // Keep columns — safer than dropping production data columns.
    }
};
