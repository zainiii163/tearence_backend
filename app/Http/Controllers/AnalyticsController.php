<?php

namespace App\Http\Controllers;

use App\Http\Controllers\APIController;
use App\Models\CandidateProfile;
use App\Models\JobUpsell;
use App\Models\Listing;
use App\Models\ListingAnalytics;
use App\Models\RevenueTracking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends APIController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get revenue analytics
     */
    public function revenue(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->format('Y-m-d'));
            $groupBy = $request->get('group_by', 'day'); // day, week, month

            $query = RevenueTracking::where('payment_status', 'completed')
                ->whereBetween('payment_date', [$startDate, $endDate]);

            // Group by period
            switch ($groupBy) {
                case 'week':
                    $revenue = $query->select(
                        DB::raw('YEARWEEK(payment_date) as period'),
                        DB::raw('SUM(amount) as total_amount'),
                        DB::raw('COUNT(*) as total_count')
                    )
                        ->groupBy(DB::raw('YEARWEEK(payment_date)'))
                        ->orderBy('period', 'asc')
                        ->get();
                    break;
                case 'month':
                    $revenue = $query->select(
                        DB::raw('DATE_FORMAT(payment_date, "%Y-%m") as period'),
                        DB::raw('SUM(amount) as total_amount'),
                        DB::raw('COUNT(*) as total_count')
                    )
                        ->groupBy(DB::raw('DATE_FORMAT(payment_date, "%Y-%m")'))
                        ->orderBy('period', 'asc')
                        ->get();
                    break;
                default: // day
                    $revenue = $query->select(
                        DB::raw('DATE(payment_date) as period'),
                        DB::raw('SUM(amount) as total_amount'),
                        DB::raw('COUNT(*) as total_count')
                    )
                        ->groupBy(DB::raw('DATE(payment_date)'))
                        ->orderBy('period', 'asc')
                        ->get();
            }

            // Summary statistics
            $summary = [
                'total_revenue' => RevenueTracking::where('payment_status', 'completed')
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->sum('amount'),
                'total_transactions' => RevenueTracking::where('payment_status', 'completed')
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->count(),
                'average_transaction' => RevenueTracking::where('payment_status', 'completed')
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->avg('amount'),
            ];

            // Revenue by type
            $byType = RevenueTracking::select(
                'revenue_type',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as total_count')
            )
                ->where('payment_status', 'completed')
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->groupBy('revenue_type')
                ->get();

            // Revenue by upsell type
            $byUpsellType = RevenueTracking::select(
                'upsell_type',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as total_count')
            )
                ->where('payment_status', 'completed')
                ->whereNotNull('upsell_type')
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->groupBy('upsell_type')
                ->get();

            return $this->successResponse([
                'revenue_data' => $revenue,
                'summary' => $summary,
                'by_type' => $byType,
                'by_upsell_type' => $byUpsellType,
            ], 'Revenue analytics retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get job posting analytics
     */
    public function jobs(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->format('Y-m-d'));

            // Job posting trends
            $jobTrends = Listing::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as job_count')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'asc')
                ->get();

            // Jobs by status
            $byStatus = Listing::select(
                'status',
                DB::raw('COUNT(*) as count')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('status')
                ->get();

            // Jobs by category
            $byCategory = Listing::select(
                'category_id',
                DB::raw('COUNT(*) as count')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('category_id')
                ->with('category')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            // Jobs by location
            $byLocation = Listing::select(
                'location_id',
                DB::raw('COUNT(*) as count')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('location_id')
                ->with('location')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            // Most active job posts (by views or applications - if tracked)
            $mostActive = Listing::where('status', 'active')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->with(['category', 'location', 'customer'])
                ->get();

            // Featured vs regular jobs
            $featuredStats = [
                'featured_count' => Listing::where('is_featured', true)
                    ->where(function($q) {
                        $q->whereNull('featured_expires_at')
                          ->orWhere('featured_expires_at', '>', now());
                    })
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'regular_count' => Listing::where('is_featured', false)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
            ];

            return $this->successResponse([
                'job_trends' => $jobTrends,
                'by_status' => $byStatus,
                'by_category' => $byCategory,
                'by_location' => $byLocation,
                'most_active' => $mostActive,
                'featured_stats' => $featuredStats,
            ], 'Job analytics retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get candidate profile analytics
     */
    public function candidates(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->format('Y-m-d'));

            // Candidate profile trends
            $candidateTrends = CandidateProfile::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as profile_count')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'asc')
                ->get();

            // Candidates by visibility
            $byVisibility = CandidateProfile::select(
                'visibility',
                DB::raw('COUNT(*) as count')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('visibility')
                ->get();

            // Candidates by location
            $byLocation = CandidateProfile::select(
                'location_id',
                DB::raw('COUNT(*) as count')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('location_id')
                ->groupBy('location_id')
                ->with('location')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            // Featured vs regular profiles
            $featuredStats = [
                'featured_count' => CandidateProfile::where('is_featured', true)
                    ->where(function($q) {
                        $q->whereNull('featured_expires_at')
                          ->orWhere('featured_expires_at', '>', now());
                    })
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'regular_count' => CandidateProfile::where('is_featured', false)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
            ];

            // Profiles with job alerts boost
            $boostStats = [
                'with_boost' => CandidateProfile::where('has_job_alerts_boost', true)
                    ->where(function($q) {
                        $q->whereNull('job_alerts_boost_expires_at')
                          ->orWhere('job_alerts_boost_expires_at', '>', now());
                    })
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'without_boost' => CandidateProfile::where('has_job_alerts_boost', false)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
            ];

            return $this->successResponse([
                'candidate_trends' => $candidateTrends,
                'by_visibility' => $byVisibility,
                'by_location' => $byLocation,
                'featured_stats' => $featuredStats,
                'boost_stats' => $boostStats,
            ], 'Candidate analytics retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get upsell analytics
     */
    public function upsells(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->format('Y-m-d'));

            // Job upsells
            $jobUpsells = JobUpsell::select(
                'upsell_type',
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(price) as total_revenue')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('upsell_type', 'status')
                ->get();

            // Active upsells
            $activeUpsells = [
                'job_featured' => JobUpsell::where('upsell_type', 'featured')
                    ->where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    })
                    ->count(),
                'job_suggested' => JobUpsell::where('upsell_type', 'suggested')
                    ->where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    })
                    ->count(),
            ];

            // Upsell conversion rate (pending -> completed)
            $conversionStats = [
                'total_created' => JobUpsell::whereBetween('created_at', [$startDate, $endDate])->count(),
                'completed' => JobUpsell::where('payment_status', 'completed')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'pending' => JobUpsell::where('payment_status', 'pending')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'failed' => JobUpsell::where('payment_status', 'failed')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
            ];

            if ($conversionStats['total_created'] > 0) {
                $conversionStats['conversion_rate'] = round(
                    ($conversionStats['completed'] / $conversionStats['total_created']) * 100,
                    2
                );
            } else {
                $conversionStats['conversion_rate'] = 0;
            }

            return $this->successResponse([
                'job_upsells' => $jobUpsells,
                'active_upsells' => $activeUpsells,
                'conversion_stats' => $conversionStats,
            ], 'Upsell analytics retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get overall platform analytics
     */
    public function overview(Request $request)
    {
        try {
            $overview = [
                'total_jobs' => Listing::count(),
                'active_jobs' => Listing::where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    })
                    ->count(),
                'total_candidates' => CandidateProfile::count(),
                'active_candidates' => CandidateProfile::where('visibility', 'public')->count(),
                'total_revenue' => RevenueTracking::where('payment_status', 'completed')->sum('amount'),
                'monthly_revenue' => RevenueTracking::where('payment_status', 'completed')
                    ->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year)
                    ->sum('amount'),
                'active_upsells' => JobUpsell::where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    })
                    ->count(),
            ];

            return $this->successResponse($overview, 'Platform overview retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user post analytics (for individual user's posts)
     */
    public function userPosts(Request $request)
    {
        try {
            $customer_id = auth()->user()->customer_id;
            $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->format('Y-m-d'));
            $listingId = $request->get('listing_id'); // Optional: specific listing

            // Get user's listings
            $listingsQuery = Listing::where('customer_id', $customer_id);
            if ($listingId) {
                $listingsQuery->where('listing_id', $listingId);
            }
            $listingIds = $listingsQuery->pluck('listing_id');

            if ($listingIds->isEmpty()) {
                return $this->successResponse([
                    'overview' => [],
                    'by_listing' => [],
                    'by_event_type' => [],
                    'trends' => [],
                ], 'No listings found', Response::HTTP_OK);
            }

            // Overall analytics
            $overview = ListingAnalytics::whereIn('listing_id', $listingIds)
                ->whereBetween('event_date', [$startDate, $endDate])
                ->select(
                    DB::raw('COUNT(CASE WHEN event_type = "view" THEN 1 END) as total_views'),
                    DB::raw('COUNT(CASE WHEN event_type = "click" THEN 1 END) as total_clicks'),
                    DB::raw('COUNT(CASE WHEN event_type = "favorite" THEN 1 END) as total_favorites'),
                    DB::raw('COUNT(CASE WHEN event_type = "contact" THEN 1 END) as total_contacts'),
                    DB::raw('COUNT(CASE WHEN event_type = "application" THEN 1 END) as total_applications'),
                    DB::raw('COUNT(CASE WHEN event_type = "share" THEN 1 END) as total_shares'),
                    DB::raw('COUNT(DISTINCT customer_id) as unique_visitors'),
                    DB::raw('COUNT(DISTINCT listing_id) as listings_with_activity')
                )
                ->first();

            // Analytics by listing
            $byListing = ListingAnalytics::whereIn('listing_id', $listingIds)
                ->whereBetween('event_date', [$startDate, $endDate])
                ->select(
                    'listing_id',
                    DB::raw('COUNT(CASE WHEN event_type = "view" THEN 1 END) as views'),
                    DB::raw('COUNT(CASE WHEN event_type = "click" THEN 1 END) as clicks'),
                    DB::raw('COUNT(CASE WHEN event_type = "favorite" THEN 1 END) as favorites'),
                    DB::raw('COUNT(CASE WHEN event_type = "contact" THEN 1 END) as contacts'),
                    DB::raw('COUNT(CASE WHEN event_type = "application" THEN 1 END) as applications'),
                    DB::raw('COUNT(DISTINCT customer_id) as unique_visitors')
                )
                ->groupBy('listing_id')
                ->with('listing:id,listing_id,title,slug,status')
                ->get();

            // Analytics by event type
            $byEventType = ListingAnalytics::whereIn('listing_id', $listingIds)
                ->whereBetween('event_date', [$startDate, $endDate])
                ->select(
                    'event_type',
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('event_type')
                ->get();

            // Daily trends
            $trends = ListingAnalytics::whereIn('listing_id', $listingIds)
                ->whereBetween('event_date', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(event_date) as date'),
                    DB::raw('COUNT(CASE WHEN event_type = "view" THEN 1 END) as views'),
                    DB::raw('COUNT(CASE WHEN event_type = "click" THEN 1 END) as clicks'),
                    DB::raw('COUNT(CASE WHEN event_type = "contact" THEN 1 END) as contacts')
                )
                ->groupBy(DB::raw('DATE(event_date)'))
                ->orderBy('date', 'asc')
                ->get();

            // Top performing listings
            $topListings = ListingAnalytics::whereIn('listing_id', $listingIds)
                ->whereBetween('event_date', [$startDate, $endDate])
                ->select(
                    'listing_id',
                    DB::raw('COUNT(*) as total_events'),
                    DB::raw('COUNT(CASE WHEN event_type = "view" THEN 1 END) as views'),
                    DB::raw('COUNT(CASE WHEN event_type = "contact" THEN 1 END) as contacts')
                )
                ->groupBy('listing_id')
                ->orderBy('total_events', 'desc')
                ->limit(10)
                ->with('listing:id,listing_id,title,slug,status')
                ->get();

            return $this->successResponse([
                'overview' => $overview,
                'by_listing' => $byListing,
                'by_event_type' => $byEventType,
                'trends' => $trends,
                'top_listings' => $topListings,
            ], 'User post analytics retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Track analytics event (for tracking views, clicks, etc.)
     * Note: Views can be tracked without authentication, other events require auth
     */
    public function trackEvent(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'listing_id' => 'required|integer|exists:listing,listing_id',
                'event_type' => 'required|in:view,click,favorite,share,contact,application',
                'source' => 'nullable|string|max:255',
                'referrer' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            // For non-view events, require authentication
            if ($request->event_type !== 'view' && !auth()->check()) {
                return $this->errorResponse('Authentication required for this event type', Response::HTTP_UNAUTHORIZED);
            }

            $customer_id = auth()->check() ? auth()->user()->customer_id : null;

            $analytics = ListingAnalytics::create([
                'listing_id' => $request->listing_id,
                'customer_id' => $customer_id,
                'event_type' => $request->event_type,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referrer' => $request->referrer,
                'source' => $request->get('source'),
                'event_date' => now(),
            ]);

            return $this->successResponse($analytics, 'Event tracked successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

