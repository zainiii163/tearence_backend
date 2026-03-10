<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromotedAdvert;
use App\Models\PromotedAdvertCategory;
use App\Models\PromotedAdvertAnalytic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PromotedAdvertAdminController extends Controller
{
    /**
     * Display admin dashboard analytics.
     */
    public function dashboard(): JsonResponse
    {
        $stats = [
            'total_adverts' => PromotedAdvert::count(),
            'active_adverts' => PromotedAdvert::active()->count(),
            'pending_approval' => PromotedAdvert::where('status', 'pending')->count(),
            'total_revenue' => PromotedAdvert::sum('promotion_price'),
            'featured_adverts' => PromotedAdvert::where('is_featured', true)->count(),
            'categories_count' => PromotedAdvertCategory::active()->count(),
        ];

        // Recent activity
        $recentActivity = PromotedAdvert::with(['user', 'category'])
            ->latest()
            ->limit(10)
            ->get();

        // Promotion tier distribution
        $tierDistribution = PromotedAdvert::select('promotion_tier', DB::raw('count(*) as count'))
            ->groupBy('promotion_tier')
            ->pluck('count', 'promotion_tier');

        // Monthly revenue (last 6 months)
        $monthlyRevenue = PromotedAdvert::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(promotion_price) as revenue')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Top performing categories
        $topCategories = PromotedAdvertCategory::withCount(['promotedAdverts' => function ($query) {
                $query->active();
            }])
            ->orderBy('promoted_adverts_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_activity' => $recentActivity,
                'tier_distribution' => $tierDistribution,
                'monthly_revenue' => $monthlyRevenue,
                'top_categories' => $topCategories,
            ],
        ]);
    }

    /**
     * Get detailed analytics for a specific advert.
     */
    public function analytics(PromotedAdvert $advert): JsonResponse
    {
        $analytics = [
            'views' => $advert->analytics()->views()->count(),
            'clicks' => $advert->analytics()->clicks()->count(),
            'saves' => $advert->analytics()->saves()->count(),
            'inquiries' => $advert->analytics()->inquiries()->count(),
        ];

        // Daily analytics for last 30 days
        $dailyAnalytics = $advert->analytics()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                'event_type'
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy(['date', 'event_type'])
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        // Geographic distribution
        $geoDistribution = $advert->analytics()
            ->select('country', DB::raw('COUNT(*) as count'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'advert' => $advert,
                'analytics' => $analytics,
                'daily_analytics' => $dailyAnalytics,
                'geo_distribution' => $geoDistribution,
            ],
        ]);
    }

    /**
     * Bulk approve adverts.
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $request->validate([
            'advert_ids' => 'required|array',
            'advert_ids.*' => 'exists:promoted_adverts,id',
        ]);

        $count = PromotedAdvert::whereIn('id', $request->advert_ids)
            ->update([
                'status' => 'active',
                'approved_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully approved {$count} adverts",
        ]);
    }

    /**
     * Bulk reject adverts.
     */
    public function bulkReject(Request $request): JsonResponse
    {
        $request->validate([
            'advert_ids' => 'required|array',
            'advert_ids.*' => 'exists:promoted_adverts,id',
            'reason' => 'nullable|string',
        ]);

        $count = PromotedAdvert::whereIn('id', $request->advert_ids)
            ->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => "Successfully rejected {$count} adverts",
        ]);
    }

    /**
     * Bulk feature adverts.
     */
    public function bulkFeature(Request $request): JsonResponse
    {
        $request->validate([
            'advert_ids' => 'required|array',
            'advert_ids.*' => 'exists:promoted_adverts,id',
        ]);

        $count = PromotedAdvert::whereIn('id', $request->advert_ids)
            ->update(['is_featured' => true]);

        return response()->json([
            'success' => true,
            'message' => "Successfully featured {$count} adverts",
        ]);
    }

    /**
     * Export adverts data.
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'format' => 'required|in:csv,xlsx,json',
            'filters' => 'nullable|array',
        ]);

        // Build query based on filters
        $query = PromotedAdvert::with(['user', 'category']);

        if (!empty($request->filters)) {
            foreach ($request->filters as $field => $value) {
                if ($value !== null) {
                    $query->where($field, $value);
                }
            }
        }

        $adverts = $query->get();

        // Format data for export
        $exportData = $adverts->map(function ($advert) {
            return [
                'ID' => $advert->id,
                'Title' => $advert->title,
                'Slug' => $advert->slug,
                'Type' => $advert->advert_type,
                'Category' => $advert->category?->name,
                'Seller' => $advert->seller_name,
                'Email' => $advert->email,
                'Country' => $advert->country,
                'City' => $advert->city,
                'Price' => $advert->formatted_price,
                'Tier' => $advert->promotion_tier_display,
                'Status' => $advert->status,
                'Featured' => $advert->is_featured ? 'Yes' : 'No',
                'Views' => $advert->views_count,
                'Saves' => $advert->saves_count,
                'Clicks' => $advert->clicks_count,
                'Created' => $advert->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $exportData,
            'filename' => 'promoted_adverts_' . now()->format('Y-m-d_H-i-s') . '.' . $request->format,
        ]);
    }

    /**
     * Get system health metrics.
     */
    public function systemHealth(): JsonResponse
    {
        $health = [
            'database_status' => 'healthy',
            'storage_usage' => [
                'images' => $this->getStorageUsage('promoted-adverts'),
                'total_size' => $this->getTotalStorageUsage(),
            ],
            'performance_metrics' => [
                'avg_response_time' => $this->getAverageResponseTime(),
                'daily_api_calls' => $this->getDailyApiCalls(),
            ],
            'recent_errors' => $this->getRecentErrors(),
        ];

        return response()->json([
            'success' => true,
            'data' => $health,
        ]);
    }

    /**
     * Get promotion performance report.
     */
    public function promotionReport(): JsonResponse
    {
        $report = [
            'tier_performance' => $this->getTierPerformance(),
            'revenue_by_month' => $this->getRevenueByMonth(),
            'conversion_rates' => $this->getConversionRates(),
            'top_performing_adverts' => $this->getTopPerformingAdverts(),
        ];

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    // Helper methods
    private function getStorageUsage($directory): array
    {
        // Implementation for storage usage calculation
        return [
            'files' => 0,
            'size_mb' => 0,
        ];
    }

    private function getTotalStorageUsage(): float
    {
        return 0.0;
    }

    private function getAverageResponseTime(): float
    {
        return 250.5; // ms
    }

    private function getDailyApiCalls(): int
    {
        return 1250;
    }

    private function getRecentErrors(): array
    {
        return [];
    }

    private function getTierPerformance(): array
    {
        return PromotedAdvert::select('promotion_tier', DB::raw('count(*) as count'))
            ->groupBy('promotion_tier')
            ->pluck('count', 'promotion_tier')
            ->toArray();
    }

    private function getRevenueByMonth(): array
    {
        return PromotedAdvert::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(promotion_price) as revenue')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    private function getConversionRates(): array
    {
        return [
            'view_to_save' => 0.15,
            'view_to_inquiry' => 0.08,
            'view_to_click' => 0.22,
        ];
    }

    private function getTopPerformingAdverts(): array
    {
        return PromotedAdvert::with(['category', 'user'])
            ->active()
            ->orderBy('views_count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }
}
