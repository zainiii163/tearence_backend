<?php

namespace App\Http\Controllers;

use App\Http\Controllers\APIController;
use App\Models\CandidateProfile;
use App\Models\Customer;
use App\Models\CustomerBusiness;
use App\Models\CustomerStore;
use App\Models\JobAlert;
use App\Models\JobUpsell;
use App\Models\Listing;
use App\Models\ListingAnalytics;
use App\Models\RevenueTracking;
use App\Models\StaffManagement;
use App\Models\Banner;
use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DashboardController extends APIController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get user dashboard data (for job seekers and employers).
     */
    public function userDashboard(Request $request)
    {
        try {
            // Get the authenticated user (User model)
            $authUser = auth('api')->user();
            
            // Check if user is authenticated
            if (!$authUser) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            
            $user_id = $authUser->user_id;
            
            // For User model, we'll use user_id as the identifier for listings
            // Check if there's a corresponding customer record
            $customer = \App\Models\Customer::where('email', $authUser->email)->first();
            $customer_id = $customer ? $customer->customer_id : $user_id;

            // Get user's listings/job posts with pagination
            $myListings = Listing::where('customer_id', $customer_id)
                ->with(['category', 'location', 'jobUpsells', 'currency'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Get user's candidate profile if exists
            $candidateProfile = CandidateProfile::where('customer_id', $customer_id)
                ->with(['location', 'upsells'])
                ->first();

            // Featured jobs (for job seekers) - prioritize active featured jobs
            $featuredJobs = Listing::where('is_featured', true)
                ->where(function($q) {
                    $q->whereNull('featured_expires_at')
                      ->orWhere('featured_expires_at', '>', now());
                })
                ->where('status', 'active')
                ->with(['category', 'location', 'customer', 'currency'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Recommended jobs (suggested jobs)
            $recommendedJobs = Listing::where('is_suggested', true)
                ->where(function($q) {
                    $q->whereNull('suggested_expires_at')
                      ->orWhere('suggested_expires_at', '>', now());
                })
                ->where('status', 'active')
                ->with(['category', 'location', 'customer', 'currency'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Recommended jobs based on candidate profile (if exists)
            $personalizedJobs = collect();
            if ($candidateProfile) {
                // Get jobs matching candidate's location and skills
                $locationId = $candidateProfile->location_id;
                $skills = $candidateProfile->skills ?? [];
                
                // Ensure skills is an array
                if (!is_array($skills) && !($skills instanceof \Countable)) {
                    if (is_string($skills)) {
                        $decoded = json_decode($skills, true);
                        $skills = is_array($decoded) ? $decoded : [];
                    } else {
                        $skills = [];
                    }
                }
                
                $personalizedQuery = Listing::where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    });
                
                if ($locationId) {
                    $personalizedQuery->where('location_id', $locationId);
                }
                
                // If skills exist, try to match job titles/descriptions
                if (is_array($skills) && count($skills) > 0) {
                    $personalizedQuery->where(function($q) use ($skills) {
                        foreach ($skills as $skill) {
                            $q->orWhere('title', 'like', '%' . $skill . '%')
                              ->orWhere('description', 'like', '%' . $skill . '%');
                        }
                    });
                }
                
                $personalizedJobs = $personalizedQuery
                    ->with(['category', 'location', 'customer', 'currency'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            }

            // Recent job applications (if tracking exists)
            $recentApplications = []; // Placeholder for future implementation

            // Statistics
            $stats = [
                'total_listings' => Listing::where('customer_id', $customer_id)->count(),
                'active_listings' => Listing::where('customer_id', $customer_id)->where('status', 'active')->count(),
                'expired_listings' => Listing::where('customer_id', $customer_id)
                    ->where(function($q) {
                        $q->where('status', 'inactive')
                          ->orWhere(function($q2) {
                              $q2->whereNotNull('end_date')
                                 ->where('end_date', '<', now());
                          });
                    })
                    ->count(),
                'featured_listings' => Listing::where('customer_id', $customer_id)
                    ->where('is_featured', true)
                    ->where(function($q) {
                        $q->whereNull('featured_expires_at')
                          ->orWhere('featured_expires_at', '>', now());
                    })
                    ->count(),
                'suggested_listings' => Listing::where('customer_id', $customer_id)
                    ->where('is_suggested', true)
                    ->where(function($q) {
                        $q->whereNull('suggested_expires_at')
                          ->orWhere('suggested_expires_at', '>', now());
                    })
                    ->count(),
                'has_candidate_profile' => $candidateProfile ? true : false,
                'candidate_profile_featured' => $candidateProfile && $candidateProfile->isFeaturedActive() ? true : false,
                'candidate_profile_boost' => $candidateProfile && $candidateProfile->has_job_alerts_boost ? true : false,
                'total_job_alerts' => JobAlert::where('customer_id', $customer_id)->count(),
                'active_job_alerts' => JobAlert::where('customer_id', $customer_id)->where('is_active', true)->count(),
            ];

            // Job alerts summary
            $userJobAlerts = JobAlert::where('customer_id', $customer_id)
                ->where('is_active', true)
                ->with(['location', 'category'])
                ->get();

            $jobAlerts = [
                'total_alerts' => $userJobAlerts->count(),
                'active_alerts' => $userJobAlerts->where('is_active', true)->count(),
                'alerts' => $userJobAlerts->map(function($alert) {
                    $matchingJobs = $alert->findMatchingJobs(1);
                    $matchingJobsCount = ($matchingJobs instanceof \Countable || is_array($matchingJobs)) 
                        ? count($matchingJobs) 
                        : 0;
                    return [
                        'job_alert_id' => $alert->job_alert_id,
                        'name' => $alert->name,
                        'frequency' => $alert->frequency,
                        'matching_jobs_count' => $matchingJobsCount,
                        'last_notified_at' => $alert->last_notified_at,
                    ];
                }),
            ];

            // If user has job alerts boost, show enhanced stats
            if ($candidateProfile && $candidateProfile->has_job_alerts_boost) {
                $jobAlerts['has_boost'] = true;
                $jobAlerts['boost_expires_at'] = $candidateProfile->job_alerts_boost_expires_at;
            } else {
                $jobAlerts['has_boost'] = false;
            }

            // Advert Posts Management - Posted Ads, Paid Ads, Expiring Ads
            $postedAds = Listing::where('customer_id', $customer_id)
                ->with(['category', 'location', 'currency', 'jobUpsells'])
                ->orderBy('created_at', 'desc')
                ->get();

            $paidAds = Listing::where('customer_id', $customer_id)
                ->where(function($q) {
                    $q->where('is_paid', true)
                      ->orWhere('is_featured', true)
                      ->orWhere('is_promoted', true)
                      ->orWhere('is_sponsored', true);
                })
                ->where(function($q) {
                    $q->whereNull('paid_expires_at')
                      ->orWhere('paid_expires_at', '>', now())
                      ->orWhereNull('featured_expires_at')
                      ->orWhere('featured_expires_at', '>', now())
                      ->orWhereNull('promoted_expires_at')
                      ->orWhere('promoted_expires_at', '>', now())
                      ->orWhereNull('sponsored_expires_at')
                      ->orWhere('sponsored_expires_at', '>', now());
                })
                ->with(['category', 'location', 'currency', 'jobUpsells'])
                ->orderBy('created_at', 'desc')
                ->get();

            $expiringAds = Listing::where('customer_id', $customer_id)
                ->where('status', 'active')
                ->where(function($q) {
                    $q->whereNotNull('end_date')
                      ->where('end_date', '<=', now()->addDays(7))
                      ->where('end_date', '>', now())
                      ->orWhere(function($q2) {
                          $q2->whereNotNull('paid_expires_at')
                             ->where('paid_expires_at', '<=', now()->addDays(7))
                             ->where('paid_expires_at', '>', now());
                      })
                      ->orWhere(function($q3) {
                          $q3->whereNotNull('featured_expires_at')
                             ->where('featured_expires_at', '<=', now()->addDays(7))
                             ->where('featured_expires_at', '>', now());
                      })
                      ->orWhere(function($q4) {
                          $q4->whereNotNull('promoted_expires_at')
                             ->where('promoted_expires_at', '<=', now()->addDays(7))
                             ->where('promoted_expires_at', '>', now());
                      });
                })
                ->with(['category', 'location', 'currency', 'jobUpsells'])
                ->orderBy('end_date', 'asc')
                ->get();

            // Get user's business and store
            $business = CustomerBusiness::where('customer_id', $customer_id)->first();
            $store = CustomerStore::where('customer_id', $customer_id)->first();

            // Get user's banner and affiliate ads
            $myBannerAds = Banner::where('user_id', $customer_id)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();

            $myAffiliateAds = Affiliate::where('customer_id', $customer_id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Banner and affiliate statistics
            $bannerStats = [
                'total_banners' => $myBannerAds->count(),
                'active_banners' => $myBannerAds->where('is_active', true)->count(),
                'expired_banners' => $myBannerAds->where('expires_at', '<', now())->count(),
                'pending_payment' => $myBannerAds->where('payment_status', 'pending')->count(),
                'total_spent_banners' => $myBannerAds->where('payment_status', 'paid')->sum('price'),
            ];

            $affiliateStats = [
                'total_affiliates' => $myAffiliateAds->count(),
                'active_affiliates' => $myAffiliateAds->where('is_active', true)->count(),
                'expired_affiliates' => $myAffiliateAds->where('expires_at', '<', now())->count(),
                'pending_payment' => $myAffiliateAds->where('payment_status', 'pending')->count(),
                'total_spent_affiliates' => $myAffiliateAds->where('payment_status', 'paid')->sum('price'),
            ];

            // Revenue tracking for user's ad payments
            $adRevenue = RevenueTracking::where('customer_id', $customer_id)
                ->whereIn('ad_type', ['banner', 'affiliate'])
                ->with(['banner', 'affiliate'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Expiring banner and affiliate ads (next 7 days)
            $expiringBannerAds = Banner::where('user_id', $customer_id)
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->where('expires_at', '<=', now()->addDays(7))
                ->orderBy('expires_at', 'asc')
                ->get();

            $expiringAffiliateAds = Affiliate::where('customer_id', $customer_id)
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->where('expires_at', '<=', now()->addDays(7))
                ->orderBy('expires_at', 'asc')
                ->get();

            // Get staff members if user has business or store
            $staffMembers = collect();
            if ($business) {
                $staffMembers = StaffManagement::where('customer_id', $customer_id)
                    ->where('entity_type', 'business')
                    ->where('entity_id', $business->id)
                    ->where('is_active', true)
                    ->with('staffMember')
                    ->get();
            } elseif ($store) {
                $staffMembers = StaffManagement::where('customer_id', $customer_id)
                    ->where('entity_type', 'store')
                    ->where('entity_id', $store->store_id)
                    ->where('is_active', true)
                    ->with('staffMember')
                    ->get();
            }

            // Get post analytics summary
            $postAnalytics = ListingAnalytics::select(
                'listing_id',
                DB::raw('COUNT(CASE WHEN event_type = "view" THEN 1 END) as total_views'),
                DB::raw('COUNT(CASE WHEN event_type = "click" THEN 1 END) as total_clicks'),
                DB::raw('COUNT(CASE WHEN event_type = "favorite" THEN 1 END) as total_favorites'),
                DB::raw('COUNT(CASE WHEN event_type = "contact" THEN 1 END) as total_contacts'),
                DB::raw('COUNT(CASE WHEN event_type = "application" THEN 1 END) as total_applications')
            )
                ->whereIn('listing_id', Listing::where('customer_id', $customer_id)->pluck('listing_id'))
                ->groupBy('listing_id')
                ->get()
                ->keyBy('listing_id');

            return $this->successResponse([
                'my_listings' => $myListings,
                'candidate_profile' => $candidateProfile,
                'featured_jobs' => $featuredJobs,
                'recommended_jobs' => $recommendedJobs,
                'personalized_jobs' => $personalizedJobs,
                'recent_applications' => $recentApplications,
                'job_alerts' => $jobAlerts,
                'stats' => $stats,
                'advert_posts' => [
                    'posted_ads' => $postedAds,
                    'paid_ads' => $paidAds,
                    'expiring_ads' => $expiringAds,
                ],
                'banner_ads' => [
                    'my_banners' => $myBannerAds,
                    'stats' => $bannerStats,
                    'expiring_soon' => $expiringBannerAds,
                ],
                'affiliate_ads' => [
                    'my_affiliates' => $myAffiliateAds,
                    'stats' => $affiliateStats,
                    'expiring_soon' => $expiringAffiliateAds,
                ],
                'ad_revenue' => $adRevenue,
                'business' => $business,
                'store' => $store,
                'staff_members' => $staffMembers,
                'post_analytics_summary' => $postAnalytics,
            ], 'Dashboard data retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get super admin dashboard data.
     */
    public function adminDashboard(Request $request)
    {
        try {
            // Check if user is admin
            $user = auth('api')->user();
            if (!$user || (!$user->is_super_admin && !$user->can_manage_dashboard)) {
                return $this->errorResponse('Unauthorized. Admin access required.', Response::HTTP_FORBIDDEN);
            }

            // Total statistics
            $stats = [
                'total_jobs' => Listing::count(),
                'active_jobs' => Listing::where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    })
                    ->count(),
                'expired_jobs' => Listing::where(function($q) {
                    $q->where('status', 'inactive')
                      ->orWhere(function($q2) {
                          $q2->whereNotNull('end_date')
                             ->where('end_date', '<', now());
                      });
                })->count(),
                'featured_jobs' => Listing::where('is_featured', true)
                    ->where(function($q) {
                        $q->whereNull('featured_expires_at')
                          ->orWhere('featured_expires_at', '>', now());
                    })
                    ->where('status', 'active')
                    ->count(),
                'suggested_jobs' => Listing::where('is_suggested', true)
                    ->where(function($q) {
                        $q->whereNull('suggested_expires_at')
                          ->orWhere('suggested_expires_at', '>', now());
                    })
                    ->where('status', 'active')
                    ->count(),
                'total_candidates' => CandidateProfile::count(),
                'active_candidates' => CandidateProfile::where('visibility', 'public')->count(),
                'featured_candidates' => CandidateProfile::where('is_featured', true)
                    ->where(function($q) {
                        $q->whereNull('featured_expires_at')
                          ->orWhere('featured_expires_at', '>', now());
                    })
                    ->count(),
                'candidates_with_boost' => CandidateProfile::where('has_job_alerts_boost', true)
                    ->where(function($q) {
                        $q->whereNull('job_alerts_boost_expires_at')
                          ->orWhere('job_alerts_boost_expires_at', '>', now());
                    })
                    ->count(),
                'total_revenue' => RevenueTracking::where('payment_status', 'completed')->sum('amount'),
                'monthly_revenue' => RevenueTracking::where('payment_status', 'completed')
                    ->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year)
                    ->sum('amount'),
                'weekly_revenue' => RevenueTracking::where('payment_status', 'completed')
                    ->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->sum('amount'),
                'today_revenue' => RevenueTracking::where('payment_status', 'completed')
                    ->whereDate('payment_date', today())
                    ->sum('amount'),
                'pending_payments' => RevenueTracking::where('payment_status', 'pending')->count(),
            ];

            // Recent job posts with status
            $recentJobs = Listing::with(['customer', 'category', 'location', 'currency'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function($job) {
                    return [
                        'listing_id' => $job->listing_id,
                        'title' => $job->title,
                        'status' => $job->status,
                        'is_featured' => $job->is_featured,
                        'is_suggested' => $job->is_suggested,
                        'end_date' => $job->end_date,
                        'created_at' => $job->created_at,
                        'customer' => $job->customer,
                        'category' => $job->category,
                        'location' => $job->location,
                    ];
                });

            // Recent upsells
            $recentJobUpsells = JobUpsell::with(['listing'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            // Job status breakdown
            $jobStatusBreakdown = Listing::select(
                'status',
                DB::raw('COUNT(*) as count')
            )
                ->groupBy('status')
                ->get();

            // Revenue breakdown by type
            $revenueBreakdown = RevenueTracking::select(
                'revenue_type',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as total_count')
            )
                ->where('payment_status', 'completed')
                ->groupBy('revenue_type')
                ->get();

            // Revenue by upsell type
            $revenueByUpsellType = RevenueTracking::select(
                'upsell_type',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as total_count')
            )
                ->where('payment_status', 'completed')
                ->whereNotNull('upsell_type')
                ->groupBy('upsell_type')
                ->get();

            // Revenue trends (last 30 days)
            $revenueTrends = RevenueTracking::select(
                DB::raw('DATE(payment_date) as date'),
                DB::raw('SUM(amount) as daily_revenue'),
                DB::raw('COUNT(*) as daily_count')
            )
                ->where('payment_status', 'completed')
                ->where('payment_date', '>=', now()->subDays(30))
                ->groupBy(DB::raw('DATE(payment_date)'))
                ->orderBy('date', 'asc')
                ->get();

            // Top performing categories
            $topCategories = Listing::select(
                'category_id',
                DB::raw('COUNT(*) as job_count')
            )
                ->where('status', 'active')
                ->groupBy('category_id')
                ->orderBy('job_count', 'desc')
                ->limit(10)
                ->with('category')
                ->get();

            // Active upsells count
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

            // User statistics
            $userStats = [
                'total_users' => Customer::count(),
                'total_businesses' => CustomerBusiness::count(),
                'active_businesses' => CustomerBusiness::where('status', 'active')->count(),
                'total_stores' => CustomerStore::count(),
                'users_with_staff' => StaffManagement::distinct('customer_id')->count('customer_id'),
                'total_staff_members' => StaffManagement::where('is_active', true)->count(),
            ];

            // Payment system statistics
            $paymentStats = [
                'total_transactions' => RevenueTracking::count(),
                'completed_transactions' => RevenueTracking::where('payment_status', 'completed')->count(),
                'pending_transactions' => RevenueTracking::where('payment_status', 'pending')->count(),
                'failed_transactions' => RevenueTracking::where('payment_status', 'failed')->count(),
                'refunded_transactions' => RevenueTracking::where('payment_status', 'refunded')->count(),
                'payment_methods' => RevenueTracking::select(
                    'payment_method',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(amount) as total_amount')
                )
                    ->where('payment_status', 'completed')
                    ->whereNotNull('payment_method')
                    ->groupBy('payment_method')
                    ->get(),
            ];

            // Admin staff statistics
            $adminStaffStats = [
                'total_admin_users' => \App\Models\User::where('is_super_admin', true)->count(),
                'admin_users' => \App\Models\User::where('is_super_admin', true)
                    ->orWhere('can_manage_dashboard', true)
                    ->with('group')
                    ->get()
                    ->map(function($user) {
                        $authUser = auth('api')->user();
                        $user_id = $authUser->user_id;
                        
                        // For User model, we'll use user_id as the identifier for listings
                        // Check if there's a corresponding customer record
                        $customer = \App\Models\Customer::where('email', $authUser->email)->first();
                        $customer_id = $customer ? $customer->customer_id : $user_id;
                        
                        return [
                            'user_id' => $user->user_id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'is_super_admin' => $user->is_super_admin,
                            'permissions' => [
                                'manage_users' => $user->can_manage_users,
                                'manage_categories' => $user->can_manage_categories,
                                'manage_listings' => $user->can_manage_listings,
                                'manage_dashboard' => $user->can_manage_dashboard,
                                'view_analytics' => $user->can_view_analytics,
                            ],
                            'group' => $user->group,
                            'is_active' => $user->is_active,
                        ];
                    }),
            ];

            // Recent user registrations
            $recentUsers = Customer::orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            // Platform activity (last 7 days)
            $platformActivity = [
                'new_listings' => Listing::where('created_at', '>=', now()->subDays(7))->count(),
                'new_users' => Customer::where('created_at', '>=', now()->subDays(7))->count(),
                'new_businesses' => CustomerBusiness::where('created_at', '>=', now()->subDays(7))->count(),
                'new_stores' => CustomerStore::where('created_at', '>=', now()->subDays(7))->count(),
                'new_revenue' => RevenueTracking::where('payment_status', 'completed')
                    ->where('payment_date', '>=', now()->subDays(7))
                    ->sum('amount'),
            ];

            return $this->successResponse([
                'stats' => $stats,
                'user_stats' => $userStats,
                'payment_stats' => $paymentStats,
                'admin_staff_stats' => $adminStaffStats,
                'recent_jobs' => $recentJobs,
                'recent_users' => $recentUsers,
                'recent_upsells' => $recentJobUpsells,
                'job_status_breakdown' => $jobStatusBreakdown,
                'revenue_breakdown' => $revenueBreakdown,
                'revenue_by_upsell_type' => $revenueByUpsellType,
                'revenue_trends' => $revenueTrends,
                'top_categories' => $topCategories,
                'active_upsells' => $activeUpsells,
                'platform_activity' => $platformActivity,
            ], 'Admin dashboard data retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

