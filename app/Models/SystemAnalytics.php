<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemAnalytics extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The database table used by the model.
     */
    protected $table = 'system_analytics';
    
    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'analytics_id';

    protected $casts = [
        'breakdown' => 'array',
        'metric_date' => 'date',
        'recorded_at' => 'datetime',
    ];

    /**
     * Scope a query to only include analytics for a specific metric type.
     */
    public function scopeMetricType($query, $metricType)
    {
        return $query->where('metric_type', $metricType);
    }

    /**
     * Scope a query to only include analytics within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('metric_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include analytics from today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('metric_date', today());
    }

    /**
     * Scope a query to only include analytics from this week.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('metric_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
    }

    /**
     * Scope a query to only include analytics from this month.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('metric_date', now()->month)
                    ->whereYear('metric_date', now()->year);
    }

    /**
     * Get metric trend over time.
     */
    public static function getMetricTrend($metricType, $days = 30)
    {
        return self::where('metric_type', $metricType)
            ->where('metric_date', '>=', now()->subDays($days)->toDateString())
            ->orderBy('metric_date')
            ->pluck('metric_value', 'metric_date')
            ->toArray();
    }

    /**
     * Get current system metrics.
     */
    public static function getCurrentMetrics()
    {
        $metrics = [];
        $metricTypes = [
            'total_users', 'active_users', 'total_listings', 'active_listings',
            'total_revenue', 'daily_revenue', 'page_views', 'unique_visitors',
            'new_registrations', 'kyc_submissions', 'kyc_approvals', 'kyc_rejections'
        ];

        foreach ($metricTypes as $type) {
            $metrics[$type] = self::where('metric_type', $type)
                ->where('metric_date', today())
                ->value('metric_value') ?? 0;
        }

        return $metrics;
    }

    /**
     * Record a metric value.
     */
    public static function recordMetric($metricType, $value, $date = null, $breakdown = null)
    {
        return self::updateOrCreate(
            [
                'metric_type' => $metricType,
                'metric_date' => $date ?? today()->toDateString(),
            ],
            [
                'metric_value' => $value,
                'breakdown' => $breakdown,
                'recorded_at' => now(),
            ]
        );
    }

    /**
     * Get revenue analytics.
     */
    public static function getRevenueAnalytics($days = 30)
    {
        return self::where('metric_type', 'daily_revenue')
            ->where('metric_date', '>=', now()->subDays($days)->toDateString())
            ->orderBy('metric_date')
            ->pluck('metric_value_decimal', 'metric_date')
            ->toArray();
    }

    /**
     * Get user growth analytics.
     */
    public static function getUserGrowthAnalytics($days = 30)
    {
        return self::where('metric_type', 'new_registrations')
            ->where('metric_date', '>=', now()->subDays($days)->toDateString())
            ->orderBy('metric_date')
            ->pluck('metric_value', 'metric_date')
            ->toArray();
    }
}
