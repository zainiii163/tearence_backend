<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class JobSchema
{
    private static ?array $columns = null;

    public static function reset(): void
    {
        self::$columns = null;
    }

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

    public static function mapWorkType(?string $workType): ?string
    {
        if ($workType === null || $workType === '') {
            return $workType;
        }

        return match ($workType) {
            'Full-time', 'full_time' => 'full_time',
            'Part-time', 'part_time' => 'part_time',
            'Contract', 'contract' => 'contract',
            'Freelance', 'freelance' => 'contract',
            'Internship', 'internship' => 'internship',
            'Temporary', 'temporary' => 'temporary',
            'remote' => 'remote',
            default => $workType,
        };
    }

    public static function mapEducationLevel(?string $level): ?string
    {
        if ($level === null || $level === '') {
            return $level;
        }

        return match ($level) {
            'associate' => 'diploma',
            'doctorate' => 'phd',
            default => $level,
        };
    }

    public static function mapApplicationMethod(?string $method): ?string
    {
        if ($method === null || $method === '') {
            return $method;
        }

        return match ($method) {
            'website' => 'link',
            'phone', 'in_person' => 'platform',
            default => in_array($method, ['email', 'link', 'platform'], true) ? $method : 'email',
        };
    }

    /**
     * Map Filament/admin form fields onto whichever jobs columns exist in production.
     */
    public static function normalizeAdminPayload(array $data): array
    {
        foreach (['latitude', 'longitude'] as $coord) {
            if (array_key_exists($coord, $data) && $data[$coord] === '') {
                $data[$coord] = null;
            }
        }

        if (isset($data['work_type'])) {
            $data['work_type'] = self::mapWorkType((string) $data['work_type']);
        }
        if (array_key_exists('education_level', $data)) {
            $data['education_level'] = self::mapEducationLevel($data['education_level']);
        }
        if (isset($data['application_method'])) {
            $data['application_method'] = self::mapApplicationMethod((string) $data['application_method']);
        }

        $aliases = [
            ['job_category_id', 'category_id'],
            ['is_remote', 'remote_available'],
            ['is_verified_employer', 'verified_employer'],
            ['company_logo', 'logo_url'],
            ['contact_email', 'application_email'],
            ['views_count', 'views'],
            ['application_link', 'application_website'],
            ['currency', 'salary_currency'],
        ];

        foreach ($aliases as [$left, $right]) {
            $value = $data[$left] ?? $data[$right] ?? null;
            if ($value === null) {
                continue;
            }
            if (Schema::hasColumn('jobs', $left)) {
                $data[$left] = $value;
            }
            if (Schema::hasColumn('jobs', $right)) {
                $data[$right] = $value;
            }
        }

        return self::filterPayload($data);
    }
}
