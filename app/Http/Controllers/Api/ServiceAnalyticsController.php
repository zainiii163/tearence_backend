<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceActivity;
use App\Models\ServiceSaved;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ServiceAnalyticsController extends Controller
{
    public function getLiveActivityFeed(Request $request): JsonResponse
    {
        $limit = $request->limit ?? 20;
        $activities = ServiceActivity::getLiveActivityFeed($limit);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    public function getTrendingServices(Request $request): JsonResponse
    {
        $timeframe = $request->timeframe ?? 'week'; // day, week, month
        
        $services = Service::with(['user', 'category'])
                           ->active()
                           ->withCount(['activities' => function($query) use ($timeframe) {
                               $query->byType(['view', 'inquiry', 'order'])
                                     ->when($timeframe === 'day', fn($q) => $q->recent(24))
                                     ->when($timeframe === 'week', fn($q) => $q->recent(168))
                                     ->when($timeframe === 'month', fn($q) => $q->recent(720));
                           }])
                           ->orderBy('activities_count', 'desc')
                           ->orderBy('rating', 'desc')
                           ->limit($request->limit ?? 12)
                           ->get();

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function getServiceAnalytics(Service $service): JsonResponse
    {
        if ($service->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $analytics = [
            'views' => $service->activities()->byType('view')->count(),
            'inquiries' => $service->activities()->byType('inquiry')->count(),
            'orders' => $service->activities()->byType('order')->count(),
            'saves' => $service->activities()->byType('save')->count(),
            'shares' => $service->activities()->byType('share')->count(),
            'total_revenue' => $service->orders()->sum('total_price'),
            'avg_rating' => $service->rating,
            'reviews_count' => $service->reviews_count,
            'conversion_rate' => $this->calculateConversionRate($service),
            'daily_stats' => $this->getDailyStats($service),
            'country_breakdown' => $this->getCountryBreakdown($service),
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    public function getMarketplaceStats(): JsonResponse
    {
        $stats = [
            'total_services' => Service::active()->count(),
            'total_providers' => Service::active()->distinct('user_id')->count(),
            'total_categories' => DB::table('categories')->count(),
            'total_orders' => DB::table('service_orders')->count(),
            'total_revenue' => DB::table('service_orders')->sum('total_price'),
            'avg_service_price' => Service::active()->avg('base_price'),
            'top_categories' => $this->getTopCategories(),
            'top_countries' => $this->getTopCountries(),
            'recent_growth' => $this->getRecentGrowth(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function getComparisonData(Request $request): JsonResponse
    {
        $request->validate([
            'service_ids' => 'required|array|min:2|max:4',
            'service_ids.*' => 'exists:services,id',
        ]);

        $services = Service::with(['user', 'category', 'packages', 'reviews'])
                           ->whereIn('id', $request->service_ids)
                           ->active()
                           ->get();

        $comparison = $services->map(function($service) {
            return [
                'id' => $service->id,
                'title' => $service->title,
                'provider' => $service->user->name ?? 'Unknown',
                'category' => $service->category->name ?? 'Uncategorized',
                'base_price' => $service->base_price,
                'rating' => $service->rating,
                'reviews_count' => $service->reviews_count,
                'delivery_time' => $service->delivery_time,
                'skill_level' => $service->skill_level,
                'packages' => $service->packages->map(function($package) {
                    return [
                        'name' => $package->name,
                        'price' => $package->price,
                        'delivery_time' => $package->delivery_time,
                        'revisions' => $package->revisions,
                    ];
                }),
                'featured' => $service->featured,
                'verified' => $service->verified,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $comparison,
        ]);
    }

    private function calculateConversionRate(Service $service): float
    {
        $views = $service->activities()->byType('view')->count();
        $orders = $service->activities()->byType('order')->count();
        
        return $views > 0 ? round(($orders / $views) * 100, 2) : 0;
    }

    private function getDailyStats(Service $service): array
    {
        return $service->activities()
                      ->selectRaw('DATE(created_at) as date, activity_type, COUNT(*) as count')
                      ->where('created_at', '>=', now()->subDays(30))
                      ->groupBy('date', 'activity_type')
                      ->orderBy('date')
                      ->get()
                      ->groupBy('date')
                      ->map(function($day) {
                          return [
                              'views' => $day->where('activity_type', 'view')->sum('count'),
                              'inquiries' => $day->where('activity_type', 'inquiry')->sum('count'),
                              'orders' => $day->where('activity_type', 'order')->sum('count'),
                          ];
                      })
                      ->toArray();
    }

    private function getCountryBreakdown(Service $service): array
    {
        return $service->activities()
                      ->selectRaw('country, COUNT(*) as count')
                      ->where('country', '!=', null)
                      ->groupBy('country')
                      ->orderBy('count', 'desc')
                      ->limit(10)
                      ->get()
                      ->toArray();
    }

    private function getTopCategories(): array
    {
        return DB::table('services')
                 ->select('category_id', DB::raw('count(*) as count'))
                 ->where('is_active', true)
                 ->groupBy('category_id')
                 ->orderBy('count', 'desc')
                 ->limit(10)
                 ->get()
                 ->toArray();
    }

    private function getTopCountries(): array
    {
        return ServiceActivity::select('country', DB::raw('count(*) as count'))
                              ->where('country', '!=', null)
                              ->recent(168) // Last 7 days
                              ->groupBy('country')
                              ->orderBy('count', 'desc')
                              ->limit(10)
                              ->get()
                              ->toArray();
    }

    private function getRecentGrowth(): array
    {
        $lastWeek = Service::where('created_at', '>=', now()->subWeek())->count();
        $previousWeek = Service::whereBetween('created_at', [now()->subWeeks(2), now()->subWeek()])->count();
        
        $growth = $previousWeek > 0 ? round((($lastWeek - $previousWeek) / $previousWeek) * 100, 2) : 0;

        return [
            'last_week' => $lastWeek,
            'previous_week' => $previousWeek,
            'growth_percentage' => $growth,
        ];
    }
}
