<?php

namespace App\Http\Controllers;

use App\Models\UserAnalytics;
use App\Models\ListingAnalytics;
use App\Models\DashboardPermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class UserAnalyticsController extends Controller
{
    /**
     * Get user's personal analytics dashboard data.
     */
    public function getDashboard(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        // Get user's activity summary
        $activitySummary = UserAnalytics::getEventCountsForUser($user->user_id, $days);
        
        // Get daily activity
        $dailyActivity = UserAnalytics::getDailyActivityForUser($user->user_id, $days);
        
        // Get listing performance if user has listings
        $listingPerformance = [];
        if ($user->listings()->exists()) {
            $listingIds = $user->listings()->pluck('listing_id')->toArray();
            
            $listingPerformance = [
                'total_views' => ListingAnalytics::whereIn('listing_id', $listingIds)
                    ->where('event_date', '>=', $startDate)
                    ->where('event_type', 'view')
                    ->count(),
                'total_clicks' => ListingAnalytics::whereIn('listing_id', $listingIds)
                    ->where('event_date', '>=', $startDate)
                    ->where('event_type', 'click')
                    ->count(),
                'total_favorites' => ListingAnalytics::whereIn('listing_id', $listingIds)
                    ->where('event_date', '>=', $startDate)
                    ->where('event_type', 'favorite')
                    ->count(),
                'total_contacts' => ListingAnalytics::whereIn('listing_id', $listingIds)
                    ->where('event_date', '>=', $startDate)
                    ->where('event_type', 'contact')
                    ->count(),
            ];
        }

        // Get recent activity
        $recentActivity = UserAnalytics::where('user_id', $user->user_id)
            ->with('user')
            ->orderBy('event_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'event_type' => $activity->event_type,
                    'event_date' => $activity->event_date->toISOString(),
                    'ip_address' => $activity->ip_address,
                    'source' => $activity->source,
                    'metadata' => $activity->metadata,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'activity_summary' => $activitySummary,
                'daily_activity' => $dailyActivity,
                'listing_performance' => $listingPerformance,
                'recent_activity' => $recentActivity,
                'period' => [
                    'days' => $days,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => Carbon::now()->toDateString(),
                ],
            ],
        ]);
    }

    /**
     * Get user's listing analytics.
     */
    public function getListingAnalytics(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $days = $request->get('days', 30);
        $listingId = $request->get('listing_id');
        $startDate = Carbon::now()->subDays($days);

        $query = ListingAnalytics::whereHas('listing', function ($q) use ($user) {
            $q->where('customer_id', $user->customer_id);
        })->where('event_date', '>=', $startDate);

        if ($listingId) {
            $query->where('listing_id', $listingId);
        }

        $analytics = $query->get()
            ->groupBy('event_type')
            ->map(function ($events) {
                return [
                    'count' => $events->count(),
                    'daily_breakdown' => $events->groupBy(function ($event) {
                        return $event->event_date->format('Y-m-d');
                    })->map->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'analytics' => $analytics,
                'period' => [
                    'days' => $days,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => Carbon::now()->toDateString(),
                ],
            ],
        ]);
    }

    /**
     * Get user's profile views and interactions.
     */
    public function getProfileAnalytics(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $profileViews = UserAnalytics::where('user_id', $user->user_id)
            ->where('event_type', 'profile_view')
            ->where('event_date', '>=', $startDate)
            ->count();

        $loginActivity = UserAnalytics::where('user_id', $user->user_id)
            ->where('event_type', 'login')
            ->where('event_date', '>=', $startDate)
            ->count();

        $messagesSent = UserAnalytics::where('user_id', $user->user_id)
            ->where('event_type', 'message_sent')
            ->where('event_date', '>=', $startDate)
            ->count();

        $messagesReceived = UserAnalytics::where('user_id', $user->user_id)
            ->where('event_type', 'message_received')
            ->where('event_date', '>=', $startDate)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'profile_views' => $profileViews,
                'login_activity' => $loginActivity,
                'messages_sent' => $messagesSent,
                'messages_received' => $messagesReceived,
                'period' => [
                    'days' => $days,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => Carbon::now()->toDateString(),
                ],
            ],
        ]);
    }

    /**
     * Export user analytics data.
     */
    public function exportAnalytics(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $format = $request->get('format', 'json');
        $days = $request->get('days', 30);
        $type = $request->get('type', 'all');

        $startDate = Carbon::now()->subDays($days);
        $data = [];

        switch ($type) {
            case 'activity':
                $data = UserAnalytics::where('user_id', $user->user_id)
                    ->where('event_date', '>=', $startDate)
                    ->get()
                    ->map(function ($activity) {
                        return [
                            'event_type' => $activity->event_type,
                            'event_date' => $activity->event_date->toISOString(),
                            'ip_address' => $activity->ip_address,
                            'source' => $activity->source,
                            'metadata' => $activity->metadata,
                        ];
                    });
                break;
                
            case 'listings':
                $listingIds = $user->listings()->pluck('listing_id')->toArray();
                $data = ListingAnalytics::whereIn('listing_id', $listingIds)
                    ->where('event_date', '>=', $startDate)
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
                
            default:
                $data = [
                    'activity_summary' => UserAnalytics::getEventCountsForUser($user->user_id, $days),
                    'daily_activity' => UserAnalytics::getDailyActivityForUser($user->user_id, $days),
                ];
        }

        if ($format === 'csv') {
            // For CSV export, you might want to use a Laravel CSV package
            // For now, return JSON which can be easily converted to CSV
            return response()->json([
                'success' => true,
                'data' => $data,
                'export_info' => [
                    'format' => $format,
                    'type' => $type,
                    'period' => [
                        'days' => $days,
                        'start_date' => $startDate->toDateString(),
                        'end_date' => Carbon::now()->toDateString(),
                    ],
                    'exported_at' => Carbon::now()->toISOString(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'export_info' => [
                'format' => $format,
                'type' => $type,
                'period' => [
                    'days' => $days,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => Carbon::now()->toDateString(),
                ],
                'exported_at' => Carbon::now()->toISOString(),
            ],
        ]);
    }

    /**
     * Record user activity (called internally by other parts of the application).
     */
    public static function recordActivity($userId, $eventType, $metadata = [], $source = null)
    {
        return UserAnalytics::create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->header('referer'),
            'source' => $source ?? 'web',
            'metadata' => $metadata,
            'event_date' => now(),
        ]);
    }
}
