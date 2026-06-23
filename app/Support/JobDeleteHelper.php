<?php

namespace App\Support;

use App\Models\Job;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class JobDeleteHelper
{
    public static function deleteJob(Job $job): void
    {
        $jobId = (int) $job->id;

        static::purgeUploadedFiles($job);
        static::deleteRelatedRecords($jobId);
        static::hardDelete($jobId) || static::softDeactivate($jobId);
    }

    protected static function purgeUploadedFiles(Job $job): void
    {
        foreach (['company_logo', 'logo_url'] as $column) {
            if (! Schema::hasColumn('jobs', $column)) {
                continue;
            }

            $path = $job->getAttribute($column);
            if (! $path || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                continue;
            }

            try {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    protected static function deleteRelatedRecords(int $jobId): void
    {
        static::deleteFromTable('job_applications', 'job_id', $jobId);
        static::deleteFromTable('job_applications', 'job_listing_id', $jobId);
        static::deleteFromTable('job_saves', 'job_id', $jobId);
        static::deleteFromTable('job_saved_listings', 'job_listing_id', $jobId);
        static::deleteFromTable('job_views', 'job_id', $jobId);

        if (Schema::hasTable('job_upsells') && Schema::hasColumn('job_upsells', 'upsellable_id')) {
            try {
                DB::table('job_upsells')
                    ->where('upsellable_id', $jobId)
                    ->whereIn('upsellable_type', static::upsellMorphTypes())
                    ->delete();
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    protected static function deleteFromTable(string $table, string $column, int $jobId): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        try {
            DB::table($table)->where($column, $jobId)->delete();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected static function hardDelete(int $jobId): bool
    {
        try {
            if (DB::table('jobs')->where('id', $jobId)->delete() > 0) {
                return true;
            }
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $deleted = DB::table('jobs')->where('id', $jobId)->delete() > 0;
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return $deleted;
        } catch (\Throwable $e) {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Throwable $ignored) {
            }

            report($e);

            return false;
        }
    }

    protected static function softDeactivate(int $jobId): void
    {
        $updates = [];

        if (Schema::hasColumn('jobs', 'is_active')) {
            $updates['is_active'] = false;
        }

        if (Schema::hasColumn('jobs', 'status')) {
            $updates['status'] = 'inactive';
        }

        if ($updates === []) {
            return;
        }

        try {
            DB::table('jobs')->where('id', $jobId)->update($updates);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * @return array<int, string>
     */
    protected static function upsellMorphTypes(): array
    {
        return [
            Job::class,
            'App\\Models\\Job',
            'App\\Models\\JobListing',
        ];
    }
}
