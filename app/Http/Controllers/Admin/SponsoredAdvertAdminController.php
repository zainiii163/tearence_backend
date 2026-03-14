<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SponsoredAdvert;
use App\Models\SponsoredAdvertInquiry;
use App\Models\SponsoredAdvertAnalytic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SponsoredAdvertAdminController extends Controller
{
    /**
     * Display admin dashboard for sponsored adverts.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $stats = [
            'total_adverts' => SponsoredAdvert::count(),
            'pending_approval' => SponsoredAdvert::where('status', 'pending')->count(),
            'active_adverts' => SponsoredAdvert::where('status', 'approved')->where('is_active', true)->count(),
            'currently_sponsored' => SponsoredAdvert::active()->currentlySponsored()->count(),
            'expired_adverts' => SponsoredAdvert::where('expires_at', '<', now())->count(),
            'by_tier' => [
                'basic' => SponsoredAdvert::where('sponsored_tier', 'basic')->active()->currentlySponsored()->count(),
                'plus' => SponsoredAdvert::where('sponsored_tier', 'plus')->active()->currentlySponsored()->count(),
                'premium' => SponsoredAdvert::where('sponsored_tier', 'premium')->active()->currentlySponsored()->count(),
            ],
            'by_status' => SponsoredAdvert::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'revenue_this_month' => SponsoredAdvert::where('payment_status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->sum('tier_price'),
            'total_revenue' => SponsoredAdvert::where('payment_status', 'paid')->sum('tier_price'),
            'pending_inquiries' => SponsoredAdvertInquiry::where('status', 'pending')->count(),
            'total_views' => SponsoredAdvert::sum('views_count'),
            'total_clicks' => SponsoredAdvert::sum('clicks_count'),
            'total_saves' => SponsoredAdvert::sum('saves_count'),
        ];

        // Recent activity
        $recentAdverts = SponsoredAdvert::with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentInquiries = SponsoredAdvertInquiry::with(['sponsoredAdvert', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Analytics data for charts
        $last30Days = now()->subDays(30);
        $dailyStats = SponsoredAdvert::where('created_at', '>=', $last30Days)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as adverts, SUM(tier_price) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_adverts' => $recentAdverts,
                'recent_inquiries' => $recentInquiries,
                'daily_stats' => $dailyStats,
            ],
        ]);
    }

    /**
     * Display a listing of sponsored adverts for admin.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SponsoredAdvert::with(['user', 'approver']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by sponsored tier
        if ($request->has('sponsored_tier')) {
            $query->where('sponsored_tier', $request->sponsored_tier);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by country
        if ($request->has('country')) {
            $query->where('country', $request->country);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('seller_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 20);
        $adverts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $adverts,
        ]);
    }

    /**
     * Display the specified sponsored advert for admin.
     */
    public function show($id): JsonResponse
    {
        $advert = SponsoredAdvert::with(['user', 'approver', 'inquiries' => function ($query) {
            $query->latest();
        }, 'analytics' => function ($query) {
            $query->latest()->limit(50);
        }])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $advert,
        ]);
    }

    /**
     * Approve a sponsored advert.
     */
    public function approve(Request $request, $id): JsonResponse
    {
        $advert = SponsoredAdvert::findOrFail($id);

        if ($advert->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending adverts can be approved',
            ], 422);
        }

        $advert->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'is_active' => true,
        ]);

        // Set promotion dates if not set
        if (!$advert->promotion_start) {
            $advert->update(['promotion_start' => now()]);
        }

        if (!$advert->promotion_end) {
            $advert->update(['promotion_end' => now()->addYear()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sponsored advert approved successfully',
            'data' => $advert->fresh(['user', 'approver']),
        ]);
    }

    /**
     * Reject a sponsored advert.
     */
    public function reject(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $advert = SponsoredAdvert::findOrFail($id);

        if ($advert->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending adverts can be rejected',
            ], 422);
        }

        $advert->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'is_active' => false,
        ]);

        // Store rejection reason in analytics or notes
        SponsoredAdvertAnalytic::create([
            'sponsored_advert_id' => $advert->id,
            'event_type' => 'rejection',
            'metadata' => ['reason' => $request->rejection_reason],
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sponsored advert rejected successfully',
            'data' => $advert->fresh(['user', 'approver']),
        ]);
    }

    /**
     * Toggle active status of a sponsored advert.
     */
    public function toggleActive(Request $request, $id): JsonResponse
    {
        $advert = SponsoredAdvert::findOrFail($id);

        if ($advert->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved adverts can be activated/deactivated',
            ], 422);
        }

        $advert->update([
            'is_active' => !$advert->is_active,
        ]);

        $status = $advert->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Sponsored advert {$status} successfully",
            'data' => $advert->fresh(['user', 'approver']),
        ]);
    }

    /**
     * Update sponsored tier of an advert.
     */
    public function updateTier(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sponsored_tier' => 'required|in:basic,plus,premium',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $advert = SponsoredAdvert::findOrFail($id);

        // Update tier price
        $tierPrices = [
            'basic' => 29.99,
            'plus' => 59.99,
            'premium' => 99.99,
        ];

        $advert->update([
            'sponsored_tier' => $request->sponsored_tier,
            'tier_price' => $tierPrices[$request->sponsored_tier],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sponsored tier updated successfully',
            'data' => $advert->fresh(['user', 'approver']),
        ]);
    }

    /**
     * Get analytics for a specific sponsored advert.
     */
    public function analytics($id): JsonResponse
    {
        $advert = SponsoredAdvert::findOrFail($id);

        // Basic stats
        $stats = [
            'views' => $advert->views_count,
            'clicks' => $advert->clicks_count,
            'saves' => $advert->saves_count,
            'inquiries' => $advert->inquiries_count,
        ];

        // Analytics events over time
        $last30Days = now()->subDays(30);
        $analytics = SponsoredAdvertAnalytic::where('sponsored_advert_id', $id)
            ->where('created_at', '>=', $last30Days)
            ->selectRaw('event_type, DATE(created_at) as date, COUNT(*) as count')
            ->groupBy(['event_type', 'date'])
            ->orderBy('date')
            ->get()
            ->groupBy('event_type');

        // Geographic distribution
        $geoStats = SponsoredAdvertAnalytic::where('sponsored_advert_id', $id)
            ->whereNotNull('country')
            ->selectRaw('country, COUNT(*) as count')
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Recent inquiries
        $recentInquiries = SponsoredAdvertInquiry::where('sponsored_advert_id', $id)
            ->with(['user'])
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'advert' => $advert,
                'stats' => $stats,
                'analytics' => $analytics,
                'geo_stats' => $geoStats,
                'recent_inquiries' => $recentInquiries,
            ],
        ]);
    }

    /**
     * Bulk approve sponsored adverts.
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'advert_ids' => 'required|array',
            'advert_ids.*' => 'integer|exists:sponsored_adverts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $count = SponsoredAdvert::whereIn('id', $request->advert_ids)
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'is_active' => true,
                'promotion_start' => now(),
                'promotion_end' => now()->addYear(),
            ]);

        return response()->json([
            'success' => true,
            'message' => "{$count} sponsored adverts approved successfully",
        ]);
    }

    /**
     * Bulk reject sponsored adverts.
     */
    public function bulkReject(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'advert_ids' => 'required|array',
            'advert_ids.*' => 'integer|exists:sponsored_adverts,id',
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $adverts = SponsoredAdvert::whereIn('id', $request->advert_ids)
            ->where('status', 'pending')
            ->get();

        foreach ($adverts as $advert) {
            $advert->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'is_active' => false,
            ]);

            // Store rejection reason
            SponsoredAdvertAnalytic::create([
                'sponsored_advert_id' => $advert->id,
                'event_type' => 'rejection',
                'metadata' => ['reason' => $request->rejection_reason],
                'user_id' => Auth::id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "{$adverts->count()} sponsored adverts rejected successfully",
        ]);
    }

    /**
     * Export sponsored adverts data.
     */
    public function export(Request $request): JsonResponse
    {
        $query = SponsoredAdvert::with(['user', 'approver']);

        // Apply filters same as index method
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('sponsored_tier')) {
            $query->where('sponsored_tier', $request->sponsored_tier);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $adverts = $query->get();

        // Transform data for export
        $exportData = $adverts->map(function ($advert) {
            return [
                'ID' => $advert->id,
                'Title' => $advert->title,
                'Seller' => $advert->seller_name,
                'Email' => $advert->email,
                'Country' => $advert->country,
                'Category' => $advert->category,
                'Tier' => $advert->sponsored_tier,
                'Price' => $advert->tier_price,
                'Payment Status' => $advert->payment_status,
                'Status' => $advert->status,
                'Views' => $advert->views_count,
                'Clicks' => $advert->clicks_count,
                'Saves' => $advert->saves_count,
                'Inquiries' => $advert->inquiries_count,
                'Created At' => $advert->created_at->format('Y-m-d H:i:s'),
                'Approved At' => $advert->approved_at?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $exportData,
        ]);
    }

    /**
     * Get system health information.
     */
    public function systemHealth(): JsonResponse
    {
        $health = [
            'database_connection' => DB::connection()->getPdo() ? 'healthy' : 'error',
            'total_adverts' => SponsoredAdvert::count(),
            'pending_approval' => SponsoredAdvert::where('status', 'pending')->count(),
            'expired_ads' => SponsoredAdvert::where('expires_at', '<', now())->count(),
            'disk_usage' => [
                'total' => disk_total_space('/'),
                'free' => disk_free_space('/'),
                'used' => disk_total_space('/') - disk_free_space('/'),
            ],
            'last_backup' => now()->subDay()->format('Y-m-d H:i:s'), // Placeholder
        ];

        return response()->json([
            'success' => true,
            'data' => $health,
        ]);
    }

    /**
     * Get promotion report.
     */
    public function promotionReport(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->subDays(30));
        $endDate = $request->get('end_date', now());

        $report = [
            'revenue_by_tier' => SponsoredAdvert::where('payment_status', 'paid')
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->selectRaw('sponsored_tier, COUNT(*) as count, SUM(tier_price) as revenue')
                ->groupBy('sponsored_tier')
                ->get(),
            
            'revenue_by_country' => SponsoredAdvert::where('payment_status', 'paid')
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->selectRaw('country, COUNT(*) as count, SUM(tier_price) as revenue')
                ->groupBy('country')
                ->orderBy('revenue', 'desc')
                ->limit(10)
                ->get(),
            
            'daily_revenue' => SponsoredAdvert::where('payment_status', 'paid')
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->selectRaw('DATE(paid_at) as date, SUM(tier_price) as revenue')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            
            'conversion_rates' => [
                'pending_to_approved' => $this->getConversionRate('pending', 'approved'),
                'approved_to_active' => $this->getConversionRate('approved', 'active'),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    private function getConversionRate($fromStatus, $toStatus): float
    {
        $from = SponsoredAdvert::where('status', $fromStatus)->count();
        $to = SponsoredAdvert::where('status', $toStatus)->count();
        
        return $from > 0 ? round(($to / $from) * 100, 2) : 0;
    }
}
