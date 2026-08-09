<?php

namespace App\Filament\Support;

use Illuminate\Support\Facades\Schema;

/**
 * Safe metric helpers for Filament dashboards (tables may be missing on older deploys).
 */
class DashboardMetrics
{
    public static function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    public static function columnExists(string $table, string $column): bool
    {
        try {
            return self::tableExists($table) && Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return false;
        }
    }

    public static function count(string $table, ?callable $query = null): int
    {
        if (! self::tableExists($table)) {
            return 0;
        }

        try {
            $builder = \DB::table($table);
            if ($query) {
                $query($builder);
            }

            return (int) $builder->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    public static function sum(string $table, string $column, ?callable $query = null): float
    {
        if (! self::columnExists($table, $column)) {
            return 0.0;
        }

        try {
            $builder = \DB::table($table);
            if ($query) {
                $query($builder);
            }

            return (float) ($builder->sum($column) ?? 0);
        } catch (\Throwable) {
            return 0.0;
        }
    }

    public static function money(float $amount): string
    {
        return '$' . number_format($amount, 2);
    }

    /**
     * Last N days daily sums for a numeric column.
     *
     * @return array{labels: list<string>, values: list<float>}
     */
    public static function dailySumSeries(string $table, string $column, string $dateColumn, int $days = 14, ?callable $query = null): array
    {
        $labels = [];
        $values = [];

        if (! self::columnExists($table, $column) || ! self::columnExists($table, $dateColumn)) {
            for ($i = $days - 1; $i >= 0; $i--) {
                $labels[] = now()->subDays($i)->format('M j');
                $values[] = 0;
            }

            return compact('labels', 'values');
        }

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('M j');
            try {
                $builder = \DB::table($table)->whereDate($dateColumn, $date->toDateString());
                if ($query) {
                    $query($builder);
                }
                $values[] = (float) ($builder->sum($column) ?? 0);
            } catch (\Throwable) {
                $values[] = 0;
            }
        }

        return compact('labels', 'values');
    }

    /**
     * Last N days daily counts.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public static function dailyCountSeries(string $table, string $dateColumn, int $days = 14, ?callable $query = null): array
    {
        $labels = [];
        $values = [];

        if (! self::columnExists($table, $dateColumn)) {
            for ($i = $days - 1; $i >= 0; $i--) {
                $labels[] = now()->subDays($i)->format('M j');
                $values[] = 0;
            }

            return compact('labels', 'values');
        }

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('M j');
            try {
                $builder = \DB::table($table)->whereDate($dateColumn, $date->toDateString());
                if ($query) {
                    $query($builder);
                }
                $values[] = (int) $builder->count();
            } catch (\Throwable) {
                $values[] = 0;
            }
        }

        return compact('labels', 'values');
    }
}
