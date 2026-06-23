<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class JobSchema
{
    private static ?array $columns = null;

    public static function columns(): array
    {
        if (self::$columns !== null) {
            return self::$columns;
        }

        self::$columns = [
            'category' => Schema::hasColumn('jobs', 'job_category_id') ? 'job_category_id' : 'category_id',
            'logo' => Schema::hasColumn('jobs', 'company_logo') ? 'company_logo' : 'logo_url',
            'email' => Schema::hasColumn('jobs', 'contact_email') ? 'contact_email' : 'application_email',
            'remote' => Schema::hasColumn('jobs', 'is_remote') ? 'is_remote' : 'remote_available',
            'verified' => Schema::hasColumn('jobs', 'is_verified_employer') ? 'is_verified_employer' : 'verified_employer',
            'views' => Schema::hasColumn('jobs', 'views_count') ? 'views_count' : 'views',
        ];

        return self::$columns;
    }

    public static function column(string $key): string
    {
        return self::columns()[$key] ?? $key;
    }

    /**
     * Only keep attributes that exist on the current jobs table.
     */
    public static function filterPayload(array $payload): array
    {
        $filtered = [];

        foreach ($payload as $key => $value) {
            if (Schema::hasColumn('jobs', $key)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }
}
