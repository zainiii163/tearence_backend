<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class JobSeekerSchema
{
    private static ?array $columns = null;

    public static function columns(): array
    {
        if (self::$columns !== null) {
            return self::$columns;
        }

        self::$columns = [
            'title' => Schema::hasColumn('job_seekers', 'title') ? 'title' : 'profession',
            'photo' => Schema::hasColumn('job_seekers', 'profile_photo') ? 'profile_photo' : 'profile_photo_url',
            'cv' => Schema::hasColumn('job_seekers', 'cv_file') ? 'cv_file' : 'cv_file_url',
            'linkedin' => Schema::hasColumn('job_seekers', 'linkedin_url') ? 'linkedin_url' : 'linkedin_link',
            'github' => Schema::hasColumn('job_seekers', 'github_url') ? 'github_url' : 'github_link',
            'remote' => Schema::hasColumn('job_seekers', 'is_remote_available') ? 'is_remote_available' : 'remote_availability',
            'views' => Schema::hasColumn('job_seekers', 'views_count') ? 'views_count' : 'views',
            'contacts' => Schema::hasColumn('job_seekers', 'profile_contacts_count') ? 'profile_contacts_count' : 'contact_count',
        ];

        return self::$columns;
    }

    public static function column(string $key): string
    {
        return self::columns()[$key] ?? $key;
    }

    public static function filterPayload(array $payload): array
    {
        $filtered = [];

        foreach ($payload as $key => $value) {
            if (Schema::hasColumn('job_seekers', $key)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    public static function usesActiveFlag(): bool
    {
        return Schema::hasColumn('job_seekers', 'is_active');
    }

    public static function usesStatusColumn(): bool
    {
        return Schema::hasColumn('job_seekers', 'status');
    }
}
