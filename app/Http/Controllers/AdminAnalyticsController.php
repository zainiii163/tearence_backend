<?php

namespace App\Http\Controllers;

use App\Models\SystemAnalytics;
use App\Models\UserAnalytics;
use App\Models\ListingAnalytics;
use App\Models\DashboardPermission;
use App\Models\AnalyticsReport;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AdminAnalyticsController extends Controller
{
    /**
     * Get admin analytics dashboard with role-based permissions.
     */
    public function getDashboard(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if user has permission to view admin analytics
        if (!$user->hasPermission('view_analytics') && !$user->is_super_admin) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $dashboardData = [];

        // System Overview - only for users with permission
        if ($this->canAccessSection($user->user_id, 'system_overview')) {
            $dashboardData['system_overview'] = [
                'total_users' => SystemAnalytics::getMetricTrend('total_users', $days),
                'active_users' => SystemAnalytics::getMetricTrend('active_users', $days),
                'total_listings' => SystemAnalytics::getMetricTrend('total_listings', $days),
                'active_listings' => SystemAnalytics::getMetricTrend('active_listings', $days),
                'current_metrics' => SystemAnalytics::getCurrentMetrics(),
            ];
        }

        // User Analytics - only for users with permission
        if ($this->canAccessSection($user->user_id, 'user_analytics')) {
            $dashboardData['user_analytics'] = [
                'new_registrations' => SystemAnalytics::getUserGrowthAnalytics($days),
                'user_activity_breakdown' => UserAnalytics::where('event_date', '>=', $startDate)
                    ->groupBy('event_type')
                    ->selectRaw('event_type, COUNT(*) as count')
                    ->pluck('count', 'event_type')
                    ->toArray(),
                'daily_user_activity' => UserAnalytics::where('event_date', '>=', $startDate)
                    ->groupByRaw('DATE(event_date)')
                    ->selectRaw('DATE(event_date) as date, COUNT(*) as count')
                    ->orderBy('date')
                    ->pluck('count', 'date')
                    ->toArray(),
            ];
        }

        // Revenue Analytics - only for users with permission
        if ($this->canAccessSection($user->user_id, 'revenue_analytics')) {
            $dashboardData['revenue_analytics'] = [
                'daily_revenue' => SystemAnalytics::getRevenueAnalytics($days),
                'total_revenue' => SystemAnalytics::where('metric_type', 'daily_revenue')
                    ->where('metric_date', '>=', $startDate->toDateString())
                    ->sum('metric_value_decimal'),
                'revenue_by_source' => $this->getRevenueBySource($startDate),
            ];
        }

        // Listing Analytics - only for users with permission
        if ($this->canAccessSection($user->user_id, 'listing_analytics')) {
            $dashboardData['listing_analytics'] = [
                'total_views' => ListingAnalytics::where('event_date', '>=', $startDate)
                    ->where('event_type', 'view')
                    ->count(),
                'total_clicks' => ListingAnalytics::where('event_date', '>=', $startDate)
                    ->where('event_type', 'click')
                    ->count(),
                'total_favorites' => ListingAnalytics::where('event_date', '>=', $startDate)
                    ->where('event_type', 'favorite')
                    ->count(),
                'top_performing_listings' => $this->getTopPerformingListings($startDate),
                'category_performance' => $this->getCategoryPerformance($startDate),
            ];
        }

        // KYC Analytics - only for users with permission
        if ($this->canAccessSection($user->user_id, 'kyc_analytics')) {
            $dashboardData['kyc_analytics'] = [
                'kyc_submissions' => SystemAnalytics::getMetricTrend('kyc_submissions', $days),
                'kyc_approvals' => SystemAnalytics::getMetricTrend('kyc_approvals', $days),
                'kyc_rejections' => SystemAnalytics::getMetricTrend('kyc_rejections', $days),
                'kyc_status_breakdown' => $this->getKycStatusBreakdown(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $dashboardData,
            'permissions' => [
                'accessible_sections' => DashboardPermission::getUserAccessibleSections($user->user_id),
                'can_export' => [
                    'system_overview' => DashboardPermission::userCanExport($user->user_id, 'system_overview'),
                    'user_analytics' => DashboardPermission::userCanExport($user->user_id, 'user_analytics'),
                    'revenue_analytics' => DashboardPermission::userCanExport($user->user_id, 'revenue_analytics'),
                    'listing_analytics' => DashboardPermission::userCanExport($user->user_id, 'listing_analytics'),
                    'kyc_analytics' => DashboardPermission::userCanExport($user->user_id, 'kyc_analytics'),
                ],
            ],
            'period' => [
                'days' => $days,
                'start_date' => $startDate->toDateString(),
                'end_date' => Carbon::now()->toDateString(),
            ],
        ]);
    }

    /**
     * Get detailed user analytics with filtering.
     */
    public function getUserAnalytics(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user || !$this->canAccessSection($user->user_id, 'user_analytics')) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);
        $filters = $request->get('filters', []);

        $query = UserAnalytics::where('event_date', '>=', $startDate);

        // Apply filters based on user permissions
        $availableFilters = DashboardPermission::getUserFilters($user->user_id, 'user_analytics');
        
        if (in_array('event_type', $availableFilters) && isset($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (in_array('date_range', $availableFilters) && isset($filters['start_date'])) {
            $query->whereDate('event_date', '>=', $filters['start_date']);
            if (isset($filters['end_date'])) {
                $query->whereDate('event_date', '<=', $filters['end_date']);
            }
        }

        $analytics = $query->with('user')
            ->orderBy('event_date', 'desc')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Get detailed listing analytics.
     */
    public function getListingAnalytics(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user || !$this->canAccessSection($user->user_id, 'listing_analytics')) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);
        $filters = $request->get('filters', []);

        $query = ListingAnalytics::where('event_date', '>=', $startDate);

        // Apply filters based on user permissions
        $availableFilters = DashboardPermission::getUserFilters($user->user_id, 'listing_analytics');
        
        if (in_array('event_type', $availableFilters) && isset($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (in_array('listing_id', $availableFilters) && isset($filters['listing_id'])) {
            $query->where('listing_id', $filters['listing_id']);
        }

        $analytics = $query->with('listing')
            ->orderBy('event_date', 'desc')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Export analytics data (with permission checks).
     */
    public function exportAnalytics(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $section = $request->get('section');
        $format = $request->get('format', 'json');
        $days = $request->get('days', 30);

        // Check export permission for the specific section
        if (!DashboardPermission::userCanExport($user->user_id, $section)) {
            return response()->json(['error' => 'Insufficient permissions for export'], 403);
        }

        $startDate = Carbon::now()->subDays($days);
        $data = [];

        switch ($section) {
            case 'user_analytics':
                $data = UserAnalytics::where('event_date', '>=', $startDate)
                    ->with('user')
                    ->get()
                    ->map(function ($activity) {
                        return [
                            'user_id' => $activity->user_id,
                            'user_name' => $activity->user ? $activity->user->name : 'Unknown',
                            'event_type' => $activity->event_type,
                            'event_date' => $activity->event_date->toISOString(),
                            'ip_address' => $activity->ip_address,
                            'source' => $activity->source,
                            'metadata' => $activity->metadata,
                        ];
                    });
                break;
                
            case 'listing_analytics':
                $data = ListingAnalytics::where('event_date', '>=', $startDate)
                    ->with('listing')
                    ->get()
                    ->map(function ($analytics) {
                        return [
                            'listing_id' => $analytics->listing_id,
                            'listing_title' => $analytics->listing->title ?? 'Unknown',
                            'event_type' => $analytics->event_type,
                            'event_date' => $analytics->event_date->toISOString(),
                            'ip_address' => $analytics->ip_address,
                            'source' => $analytics->source,
                        ];
                    });
                break;
                
            case 'system_overview':
                $data = SystemAnalytics::where('metric_date', '>=', $startDate->toDateString())
                    ->orderBy('metric_date')
                    ->get()
                    ->map(function ($metric) {
                        return [
                            'metric_type' => $metric->metric_type,
                            'metric_value' => $metric->metric_value,
                            'metric_value_decimal' => $metric->metric_value_decimal,
                            'currency' => $metric->currency,
                            'metric_date' => $metric->metric_date,
                            'breakdown' => $metric->breakdown,
                        ];
                    });
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'export_info' => [
                'section' => $section,
                'format' => $format,
                'period' => [
                    'days' => $days,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => Carbon::now()->toDateString(),
                ],
                'exported_by' => $user->name,
                'exported_at' => Carbon::now()->toISOString(),
            ],
        ]);
    }

    /**
     * Manage dashboard permissions for users and groups.
     */
    public function managePermissions(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user || (!$user->is_super_admin && !$user->hasPermission('manage_dashboard'))) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        $action = $request->get('action'); // 'set_group', 'set_user', 'get_permissions'
        
        switch ($action) {
            case 'set_group':
                $groupId = $request->get('group_id');
                $section = $request->get('dashboard_section');
                $canView = $request->get('can_view', false);
                $canExport = $request->get('can_export', false);
                $filters = $request->get('filters', []);

                $permission = DashboardPermission::setGroupPermissions($groupId, $section, $canView, $canExport, $filters);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Group permissions updated successfully',
                    'permission' => $permission,
                ]);

            case 'set_user':
                $userId = $request->get('user_id');
                $section = $request->get('dashboard_section');
                $canView = $request->get('can_view', false);
                $canExport = $request->get('can_export', false);
                $filters = $request->get('filters', []);

                $permission = DashboardPermission::setUserPermissions($userId, $section, $canView, $canExport, $filters);
                
                return response()->json([
                    'success' => true,
                    'message' => 'User permissions updated successfully',
                    'permission' => $permission,
                ]);

            case 'get_permissions':
                $permissions = DashboardPermission::with(['group', 'user'])
                    ->get()
                    ->map(function ($permission) {
                        return [
                            'permission_id' => $permission->permission_id,
                            'group_name' => $permission->group ? $permission->group->name : null,
                            'user_name' => $permission->user ? $permission->user->name : null,
                            'dashboard_section' => $permission->dashboard_section,
                            'can_view' => $permission->can_view,
                            'can_export' => $permission->can_export,
                            'filters' => $permission->filters,
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'data' => $permissions,
                ]);

            default:
                return response()->json(['error' => 'Invalid action'], 400);
        }
    }

    /**
     * Check if user can access a specific dashboard section.
     */
    private function canAccessSection($userId, $section): bool
    {
        return DashboardPermission::userCanView($userId, $section);
    }

    /**
     * Get revenue breakdown by source.
     */
    private function getRevenueBySource($startDate): array
    {
        return SystemAnalytics::where('metric_type', 'daily_revenue')
            ->where('metric_date', '>=', $startDate->toDateString())
            ->get()
            ->groupBy(function ($item) {
                return $item->breakdown['source'] ?? 'unknown';
            })
            ->map(function ($group) {
                return $group->sum('metric_value_decimal');
            })
            ->toArray();
    }

    /**
     * Get top performing listings.
     */
    private function getTopPerformingListings($startDate): array
    {
        return ListingAnalytics::where('event_date', '>=', $startDate)
            ->with('listing')
            ->groupBy('listing_id')
            ->selectRaw('listing_id, COUNT(*) as interactions')
            ->orderBy('interactions', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'listing_id' => $item->listing_id,
                    'listing_title' => $item->listing->title ?? 'Unknown',
                    'interactions' => $item->interactions,
                ];
            })
            ->toArray();
    }

    /**
     * Get category performance.
     */
    private function getCategoryPerformance($startDate): array
    {
        return ListingAnalytics::where('event_date', '>=', $startDate)
            ->with('listing.category')
            ->get()
            ->groupBy(function ($item) {
                return $item->listing->category->name ?? 'Unknown';
            })
            ->map(function ($group) {
                return $group->count();
            })
            ->toArray();
    }

    /**
     * Get KYC status breakdown.
     */
    private function getKycStatusBreakdown(): array
    {
        return User::groupBy('kyc_status')
            ->selectRaw('kyc_status, COUNT(*) as count')
            ->pluck('count', 'kyc_status')
            ->toArray();
    }
}
