<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsReport extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The database table used by the model.
     */
    protected $table = 'analytics_reports';
    
    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'report_id';

    protected $casts = [
        'filters' => 'array',
        'report_data' => 'array',
        'is_public' => 'boolean',
        'last_generated_at' => 'datetime',
    ];

    /**
     * Get the user who created the report.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Scope a query to only include reports of a specific type.
     */
    public function scopeReportType($query, $reportType)
    {
        return $query->where('report_type', $reportType);
    }

    /**
     * Scope a query to only include public reports.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to only include reports created by a specific user.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Generate report data based on filters.
     */
    public function generateReportData()
    {
        $data = [];
        $filters = $this->filters;

        switch ($this->report_type) {
            case 'user_activity':
                $data = $this->generateUserActivityReport($filters);
                break;
            case 'listing_performance':
                $data = $this->generateListingPerformanceReport($filters);
                break;
            case 'revenue_analysis':
                $data = $this->generateRevenueAnalysisReport($filters);
                break;
            case 'system_health':
                $data = $this->generateSystemHealthReport($filters);
                break;
            default:
                $data = $this->generateCustomReport($filters);
                break;
        }

        $this->report_data = $data;
        $this->last_generated_at = now();
        $this->save();

        return $data;
    }

    /**
     * Generate user activity report.
     */
    private function generateUserActivityReport($filters)
    {
        $startDate = $filters['start_date'] ?? now()->subDays(30);
        $endDate = $filters['end_date'] ?? now();

        return [
            'total_events' => UserAnalytics::whereBetween('event_date', [$startDate, $endDate])->count(),
            'unique_users' => UserAnalytics::whereBetween('event_date', [$startDate, $endDate])->distinct('user_id')->count('user_id'),
            'event_breakdown' => UserAnalytics::whereBetween('event_date', [$startDate, $endDate])
                ->groupBy('event_type')
                ->selectRaw('event_type, COUNT(*) as count')
                ->pluck('count', 'event_type')
                ->toArray(),
            'daily_activity' => UserAnalytics::whereBetween('event_date', [$startDate, $endDate])
                ->groupByRaw('DATE(event_date)')
                ->selectRaw('DATE(event_date) as date, COUNT(*) as count')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray(),
        ];
    }

    /**
     * Generate listing performance report.
     */
    private function generateListingPerformanceReport($filters)
    {
        $startDate = $filters['start_date'] ?? now()->subDays(30);
        $endDate = $filters['end_date'] ?? now();

        return [
            'total_views' => \App\Models\ListingAnalytics::whereBetween('event_date', [$startDate, $endDate])
                ->where('event_type', 'view')
                ->count(),
            'total_clicks' => \App\Models\ListingAnalytics::whereBetween('event_date', [$startDate, $endDate])
                ->where('event_type', 'click')
                ->count(),
            'total_favorites' => \App\Models\ListingAnalytics::whereBetween('event_date', [$startDate, $endDate])
                ->where('event_type', 'favorite')
                ->count(),
            'top_performing_listings' => \App\Models\ListingAnalytics::whereBetween('event_date', [$startDate, $endDate])
                ->groupBy('listing_id')
                ->selectRaw('listing_id, COUNT(*) as interactions')
                ->orderBy('interactions', 'desc')
                ->limit(10)
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Generate revenue analysis report.
     */
    private function generateRevenueAnalysisReport($filters)
    {
        $startDate = $filters['start_date'] ?? now()->subDays(30);
        $endDate = $filters['end_date'] ?? now();

        return [
            'total_revenue' => SystemAnalytics::where('metric_type', 'daily_revenue')
                ->whereBetween('metric_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->sum('metric_value_decimal'),
            'daily_revenue' => SystemAnalytics::where('metric_type', 'daily_revenue')
                ->whereBetween('metric_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->orderBy('metric_date')
                ->pluck('metric_value_decimal', 'metric_date')
                ->toArray(),
            'revenue_by_source' => SystemAnalytics::where('metric_type', 'daily_revenue')
                ->whereBetween('metric_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get()
                ->groupBy(function ($item) {
                    return $item->breakdown['source'] ?? 'unknown';
                })
                ->map(function ($group) {
                    return $group->sum('metric_value_decimal');
                })
                ->toArray(),
        ];
    }

    /**
     * Generate system health report.
     */
    private function generateSystemHealthReport($filters)
    {
        $date = $filters['date'] ?? today();

        return [
            'total_users' => SystemAnalytics::where('metric_type', 'total_users')
                ->where('metric_date', $date->toDateString())
                ->value('metric_value') ?? 0,
            'active_users' => SystemAnalytics::where('metric_type', 'active_users')
                ->where('metric_date', $date->toDateString())
                ->value('metric_value') ?? 0,
            'total_listings' => SystemAnalytics::where('metric_type', 'total_listings')
                ->where('metric_date', $date->toDateString())
                ->value('metric_value') ?? 0,
            'active_listings' => SystemAnalytics::where('metric_type', 'active_listings')
                ->where('metric_date', $date->toDateString())
                ->value('metric_value') ?? 0,
            'page_views' => SystemAnalytics::where('metric_type', 'page_views')
                ->where('metric_date', $date->toDateString())
                ->value('metric_value') ?? 0,
            'unique_visitors' => SystemAnalytics::where('metric_type', 'unique_visitors')
                ->where('metric_date', $date->toDateString())
                ->value('metric_value') ?? 0,
        ];
    }

    /**
     * Generate custom report based on filters.
     */
    private function generateCustomReport($filters)
    {
        // This can be extended to handle custom report types
        return [
            'message' => 'Custom report generation not implemented yet',
            'filters' => $filters,
        ];
    }
}
