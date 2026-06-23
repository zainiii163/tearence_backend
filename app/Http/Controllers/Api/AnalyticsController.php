<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use App\Models\ServiceReview;
use App\Models\ActivityLog;
use App\Models\ServicePromotion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function dashboard(): JsonResponse
    {
        // Overview statistics
        $overview = [
            'total_services' => Service::active()->count(),
            'active_providers' => User::whereHas('services', function ($query) {
                $query->active();
            })->count(),
            'total_orders' => Service::sum('orders'),
            'total_revenue' => Service::sum('starting_price'), // Simplified calculation
            'satisfaction_rate' => ServiceReview::avg('rating') ? round(ServiceReview::avg('rating') * 20, 1) : 0,
        ];

        // Recent activity
        $recentActivity = ActivityLog::with(['user:id,name', 'service:id,title'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'activity_type' => $activity->activity_type,
                    'message' => $this->formatActivityMessage($activity),
                    'country' => $activity->country,
                    'location' => $activity->city,
                    'created_at' => $activity->created_at,
                ];
            });

        // Trending services (last 7 days)
        $trendingServices = Service::with(['category', 'user'])
            ->active()
            ->where('created_at', '>=', now()->subDays(7))
            ->withCount(['activities' => function ($query) {
                $query->where('created_at', '>=', now()->subDays(7));
            }])
            ->orderBy('activities_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($service) {
                $previousViews = ActivityLog::where('service_id', $service->id)
                    ->where('activity_type', 'view')
                    ->where('created_at', '<', now()->subDays(7))
                    ->count();
                
                $currentViews = $service->activities_count;
                $growth = $previousViews > 0 ? (($currentViews - $previousViews) / $previousViews) * 100 : 0;

                return [
                    'id' => $service->id,
                    'title' => $service->title,
                    'category' => [
                        'id' => $service->category->id,
                        'name' => $service->category->name,
                        'icon' => $service->category->icon,
                    ],
                    'provider' => [
                        'name' => $service->user->name,
                    ],
                    'activities_count' => $currentViews,
                    'growth_percentage' => round($growth, 1),
                ];
            });

        // Trending countries
        $trendingCountries = ActivityLog::select('country', DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($country) {
                $previousCount = ActivityLog::where('country', $country->country)
                    ->where('created_at', '<', now()->subDays(30))
                    ->where('created_at', '>=', now()->subDays(60))
                    ->count();
                
                $currentCount = $country->count;
                $growth = $previousCount > 0 ? (($currentCount - $previousCount) / $previousCount) * 100 : 0;

                return [
                    'country' => $country->country,
                    'count' => $currentCount,
                    'growth_percentage' => round($growth, 1),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => $overview,
                'recent_activity' => $recentActivity,
                'trending_services' => $trendingServices,
                'trending_countries' => $trendingCountries,
            ],
        ]);
    }

    public function provider(Request $request, $id): JsonResponse
    {
        $provider = User::findOrFail($id);
        
        // Basic stats
        $stats = [
            'total_services' => $provider->services()->count(),
            'active_services' => $provider->services()->active()->count(),
            'total_views' => $provider->services()->sum('views'),
            'total_enquiries' => $provider->services()->sum('enquiries'),
            'total_orders' => $provider->services()->sum('orders'),
            'average_rating' => $provider->receivedReviews()->avg('rating') ?? 0,
            'total_reviews' => $provider->receivedReviews()->count(),
            'total_revenue' => $provider->services()->sum('starting_price'), // Simplified
        ];

        // Services performance
        $servicesPerformance = $provider->services()
            ->with(['category'])
            ->withCount(['reviews', 'activities'])
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get();

        // Reviews breakdown
        $reviewsBreakdown = $provider->receivedReviews()
            ->select('rating', DB::raw('count(*) as count'))
            ->where('status', 'approved')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get();

        // Monthly performance (last 12 months)
        $monthlyPerformance = $provider->services()
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as services_created'),
                DB::raw('sum(views) as total_views'),
                DB::raw('sum(enquiries) as total_enquiries')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'services_performance' => $servicesPerformance,
                'reviews_breakdown' => $reviewsBreakdown,
                'monthly_performance' => $monthlyPerformance,
            ],
        ]);
    }

    public function service(Request $request, $id): JsonResponse
    {
        $service = Service::with(['category', 'user', 'reviews', 'activities'])
            ->findOrFail($id);

        // Basic stats
        $stats = [
            'views' => $service->views,
            'enquiries' => $service->enquiries,
            'orders' => $service->orders,
            'rating' => $service->rating,
            'review_count' => $service->review_count,
            'conversion_rate' => $service->views > 0 ? round(($service->enquiries / $service->views) * 100, 2) : 0,
        ];

        // Daily views (last 30 days)
        $dailyViews = $service->activities()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as views')
            )
            ->where('activity_type', 'view')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Reviews over time
        $reviewsOverTime = $service->reviews()
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('avg(rating) as avg_rating'),
                DB::raw('count(*) as count')
            )
            ->where('status', 'approved')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Traffic sources (based on user agent patterns)
        $trafficSources = $service->activities()
            ->select('user_agent', DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('user_agent')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'source' => $this->parseUserAgent($item->user_agent),
                    'count' => $item->count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'daily_views' => $dailyViews,
                'reviews_over_time' => $reviewsOverTime,
                'traffic_sources' => $trafficSources,
            ],
        ]);
    }

    private function formatActivityMessage($activity): string
    {
        switch ($activity->activity_type) {
            case 'view':
                $country = $activity->country ? " from {$activity->country}" : '';
                $city = $activity->city ? ", {$activity->city}" : '';
                return "A user{$country}{$city} viewed {$activity->service->title}";
            case 'enquiry':
                return "New enquiry received for {$activity->service->title}";
            case 'order':
                return "New order placed for {$activity->service->title}";
            case 'add':
                return "{$activity->user->name} added new service: {$activity->service->title}";
            case 'update':
                return "{$activity->user->name} updated {$activity->service->title}";
            default:
                return $activity->message ?? 'Unknown activity';
        }
    }

    private function parseUserAgent($userAgent): string
    {
        if (strpos($userAgent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            return 'Edge';
        } elseif (strpos($userAgent, 'Mobile') !== false) {
            return 'Mobile';
        } else {
            return 'Other';
        }
    }
}
