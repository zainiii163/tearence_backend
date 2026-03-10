<?php

use App\Http\Controllers\AdPricingPlanController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\Api\AffiliateProgramController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListingUpsellController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ServiceAnalyticsController;
use App\Http\Controllers\Api\ServiceComparisonController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\VenueController;
use App\Http\Controllers\Api\VenueServiceController;
use App\Http\Controllers\Api\UpsellController;
use App\Http\Controllers\ServiceOrderController;
use App\Http\Controllers\StaffManagementController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\Admin\CategoryPostController;
use App\Http\Controllers\Admin\PostModerationController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CandidateProfileController;
use App\Http\Controllers\CandidateUpsellController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ClassifiedController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonorController;
use App\Http\Controllers\JobAlertController;
use App\Http\Controllers\JobUpsellController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ListingApprovalController;
use App\Http\Controllers\ListingFavoriteController;
use App\Http\Controllers\ListingPackageController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\UserAnalyticsController;
use App\Http\Controllers\Api\ResortsTravelController;
use App\Http\Controllers\Api\ResortsTravelCategoryController;
use App\Http\Controllers\Api\BannerAdController;
use App\Http\Controllers\Api\BannerCategoryController;
use App\Http\Controllers\Api\BannerUploadController;
use App\Http\Controllers\Api\BannerMarketplaceController;
use App\Http\Controllers\Api\BookAdvertController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VehicleCategoryController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\JobCategoryController;
use App\Http\Controllers\Api\JobSeekerController;
use App\Http\Controllers\Api\SponsoredAdvertController;
use App\Http\Controllers\Api\SponsoredPricingPlanController;
use App\Http\Controllers\Api\FeaturedAdvertController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\PropertyUpsellController;
use App\Http\Controllers\Api\PromotedAdvertController;
use App\Http\Controllers\Api\PromotedAdvertCategoryController;
use App\Http\Controllers\AdminAnalyticsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => 'api',
    'prefix' => 'v1'
], function () {
    // auth
    Route::group(['prefix' => 'auth', 'middleware' => 'auth:api'], function () {
        Route::get('/logout', [AuthController::class, 'logout']);
        Route::get('/user-profile', [AuthController::class, 'userProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    // no auth
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/login-admin', [AuthController::class, 'loginAdmin']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        
        // JWT-based auth for frontend
        Route::post('/web-login', [AuthController::class, 'webLogin']);
        Route::post('/web-logout', [AuthController::class, 'webLogout']);
        Route::get('/web-check', [AuthController::class, 'webCheck']);
        
        // Token refresh - must be outside auth middleware to work with expired tokens
        Route::match(['get', 'post'], '/refresh', [AuthController::class, 'refresh']);
        
        // Debug endpoint for testing
        Route::get('/debug', [AuthController::class, 'debugAuth']);
        // Route::post('/generate-otp', [AuthController::class, 'generateOtp']);
        // Route::post('/validate-otp', [AuthController::class, 'validateOtp']);
    });

    // ads listing
    Route::group(['prefix' => 'listing'], function () {
        Route::get('/', [ListingController::class, 'index']);
        Route::get('/my-listing', [ListingController::class, 'myListing'])->middleware('auth:api');
        Route::get('/{slug}', [ListingController::class, 'show']);
        Route::post('/', [ListingController::class, 'store']);
        Route::put('/{id}', [ListingController::class, 'update']);
        Route::delete('/{id}', [ListingController::class, 'destroy']);
        Route::post('/featured', [ListingController::class, 'featured']);
        Route::post('/new', [ListingController::class, 'new']);
        Route::post('/promoted', [ListingController::class, 'promoted']);
        Route::post('/ebay', [ListingController::class, 'ebay']);
        Route::get('/{slug}/classified', [ListingController::class, 'classified']);
        Route::post('/global', [ListingController::class, 'global']);
    });

    // listing approval
    Route::group(['prefix' => 'listing-approval', 'middleware' => 'auth:api'], function () {
        Route::get('/pending', [ListingApprovalController::class, 'pending']);
        Route::get('/harmful', [ListingApprovalController::class, 'harmful']);
        Route::get('/statistics', [ListingApprovalController::class, 'statistics']);
        Route::post('/{listingId}/approve', [ListingApprovalController::class, 'approve']);
        Route::post('/{listingId}/reject', [ListingApprovalController::class, 'reject']);
        Route::post('/{listingId}/mark-harmful', [ListingApprovalController::class, 'markHarmful']);
    });

    // KYC verification
    Route::group(['prefix' => 'kyc', 'middleware' => 'auth:api'], function () {
        Route::get('/status', [KycController::class, 'status']);
        Route::post('/submit', [KycController::class, 'submit']);
        Route::get('/pending', [KycController::class, 'pending']);
        Route::post('/{userId}/approve', [KycController::class, 'approve']);
        Route::post('/{userId}/reject', [KycController::class, 'reject']);
        Route::get('/statistics', [KycController::class, 'statistics']);
    });

    // Ad moderation and management
    Route::group(['prefix' => 'ads', 'middleware' => 'auth:api'], function () {
        Route::post('/cleanup-old-ads', [ListingApprovalController::class, 'deleteOldAds']);
        Route::get('/pending-approval', [ListingApprovalController::class, 'pending']);
        Route::post('/{adId}/approve', [ListingApprovalController::class, 'approve']);
        Route::post('/{adId}/reject', [ListingApprovalController::class, 'reject']);
        Route::post('/detect-harmful', [ListingApprovalController::class, 'detectHarmful']);
        Route::post('/delete-harmful', [ListingApprovalController::class, 'deleteHarmful']);
        Route::put('/{adId}/poster-role', [ListingApprovalController::class, 'updatePosterRole']);
        Route::post('/{adId}/repost', [ListingApprovalController::class, 'repostAd']);
        Route::get('/moderation-stats', [ListingApprovalController::class, 'statistics']);
    });

    // category
    Route::group(['prefix' => 'category'], function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/tree', [CategoryController::class, 'tree']);
        Route::get('/{id}/filters', [CategoryController::class, 'getFilters']);
        Route::get('/{id}/posting-form', [CategoryController::class, 'getPostingForm']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });

    // customer
    Route::group(['prefix' => 'customer'], function () {
        Route::get('/', [CustomerController::class, 'index']);
        Route::get('/{id}', [CustomerController::class, 'show']);
        Route::post('/', [CustomerController::class, 'store']);
        Route::put('/{id}', [CustomerController::class, 'update']);
        Route::delete('/{id}', [CustomerController::class, 'destroy']);
        Route::post('/upload-avatar/{id}', [CustomerController::class, 'uploadAvatar']);
    });

    // listing-favorite
    Route::group(['prefix' => 'listing-favorite'], function () {
        Route::get('/', [ListingFavoriteController::class, 'index']);
        Route::get('/{id}', [ListingFavoriteController::class, 'show']);
        Route::post('/', [ListingFavoriteController::class, 'store']);
        Route::put('/{id}', [ListingFavoriteController::class, 'update']);
        Route::delete('/{id}', [ListingFavoriteController::class, 'destroy']);
    });

    // listing-package
    Route::group(['prefix' => 'listing-package'], function () {
        Route::get('/', [ListingPackageController::class, 'index']);
        Route::get('/{id}', [ListingPackageController::class, 'show']);
        Route::post('/', [ListingPackageController::class, 'store']);
        Route::put('/{id}', [ListingPackageController::class, 'update']);
        Route::delete('/{id}', [ListingPackageController::class, 'destroy']);
    });

    // master
    Route::group(['prefix' => 'master'], function () {
        Route::get('/currency', [MasterController::class, 'currency']);
        Route::get('/country', [MasterController::class, 'country']);
        Route::get('/zone', [MasterController::class, 'zone']);
    });

    // location
    Route::group(['prefix' => 'location'], function () {
        Route::get('/', [LocationController::class, 'index']);
        Route::get('/{id}', [LocationController::class, 'show']);
        Route::post('/', [LocationController::class, 'store']);
        Route::put('/{id}', [LocationController::class, 'update']);
        Route::delete('/{id}', [LocationController::class, 'destroy']);
    });

    // business
    Route::group(['prefix' => 'business', 'middleware' => 'auth:api'], function () {
        Route::post('/', [BusinessController::class, 'store']);
        Route::put('/{id}', [BusinessController::class, 'update']);
        Route::delete('/{id}', [BusinessController::class, 'destroy']);
        Route::get('/my-business', [BusinessController::class, 'myBusiness']);
    });
    Route::group(['prefix' => 'business'], function () {
        Route::get('/{slug}', [BusinessController::class, 'getBySlug']);
        Route::get('/', [BusinessController::class, 'index']);
        Route::get('/{id}', [BusinessController::class, 'show']);
        Route::get('/{customer_id}/detail', [BusinessController::class, 'detail']);
    });

    // classified
    Route::group(['prefix' => 'classified'], function () {
        Route::get('/', [ClassifiedController::class, 'index']);
        Route::get('/{slug}', [ClassifiedController::class, 'show']);
    });

    // campaign
    Route::group(['prefix' => 'campaign'], function () {
        Route::get('/', [CampaignController::class, 'index']);
        Route::get('/{slug}', [CampaignController::class, 'show']);
        Route::post('/', [CampaignController::class, 'store']);
        Route::put('/{id}', [CampaignController::class, 'update']);
        Route::delete('/{id}', [CampaignController::class, 'destroy']);
    });

    // donor
    Route::group(['prefix' => 'donor'], function () {
        Route::get('/', [DonorController::class, 'index']);
        Route::get('/{id}', [DonorController::class, 'show']);
        Route::post('/', [DonorController::class, 'store']);
        Route::put('/{id}', [DonorController::class, 'update']);
        Route::delete('/{id}', [DonorController::class, 'destroy']);
    });

    // blog
    Route::group(['prefix' => 'blog'], function () {
        Route::get('/', [BlogController::class, 'index']);
        Route::get('/{slug}', [BlogController::class, 'show']);
        Route::post('/', [BlogController::class, 'store']);
        Route::put('/{id}', [BlogController::class, 'update']);
        Route::delete('/{id}', [BlogController::class, 'destroy']);
    });

    // affiliate
    Route::group(['prefix' => 'affiliate'], function () {
        Route::get('/pricing-plans', [AffiliateController::class, 'getPricingPlans']);
        Route::post('/payment', [AffiliateController::class, 'processPayment']);
        Route::get('/my-affiliate', [AffiliateController::class, 'myAffiliate']);
        Route::get('/', [AffiliateController::class, 'index']);
        Route::get('/{id}', [AffiliateController::class, 'show']);
        Route::post('/', [AffiliateController::class, 'store']);
        Route::put('/{id}', [AffiliateController::class, 'update']);
        Route::delete('/{id}', [AffiliateController::class, 'destroy']);
    });

    // books
    Route::group(['prefix' => 'books'], function () {
        Route::get('/', [BookController::class, 'index']);
        Route::get('/{id}', [BookController::class, 'show']);
        Route::post('/', [BookController::class, 'store'])->middleware('auth:customer');
        Route::post('/{id}/purchase', [BookController::class, 'purchase'])->middleware('auth:customer');
        Route::get('/download/{token}', [BookController::class, 'download']);
        Route::get('/my-purchases', [BookController::class, 'myPurchases'])->middleware('auth:customer');
        Route::get('/statistics', [BookController::class, 'statistics'])->middleware('auth:api');
        Route::post('/scrape', [BookController::class, 'scrape']);
    });

    // banner
    Route::group(['prefix' => 'banner'], function () {
        Route::get('/pricing-plans', [BannerController::class, 'getPricingPlans']);
        Route::post('/payment', [BannerController::class, 'processPayment']);
        Route::get('/my-banner', [BannerController::class, 'myBanner']);
        Route::get('/{slug}', [BannerController::class, 'getBySlug']);
        Route::get('/', [BannerController::class, 'index']);
        Route::get('/{id}', [BannerController::class, 'show']);
        Route::post('/', [BannerController::class, 'store']);
        Route::put('/{id}', [BannerController::class, 'update']);
        Route::delete('/{id}', [BannerController::class, 'destroy']);
        Route::post('/upload', [BannerController::class, 'upload']);
    });

    // ad pricing plans
    Route::group(['prefix' => 'ad-pricing-plans', 'middleware' => 'auth:api'], function () {
        Route::get('/', [AdPricingPlanController::class, 'index']);
        Route::post('/', [AdPricingPlanController::class, 'store']);
        Route::put('/{id}', [AdPricingPlanController::class, 'update']);
        Route::delete('/{id}', [AdPricingPlanController::class, 'destroy']);
    });

    // candidate profiles
    Route::group(['prefix' => 'candidate-profile', 'middleware' => 'auth:api'], function () {
        Route::get('/my-profile', [CandidateProfileController::class, 'myProfile']);
        Route::post('/', [CandidateProfileController::class, 'store']);
        Route::put('/{id}', [CandidateProfileController::class, 'update']);
        Route::delete('/{id}', [CandidateProfileController::class, 'destroy']);
    });
    Route::group(['prefix' => 'candidate-profile'], function () {
        Route::get('/', [CandidateProfileController::class, 'index']);
        Route::get('/{id}', [CandidateProfileController::class, 'show']);
    });

    // job upsells
    Route::group(['prefix' => 'job-upsell', 'middleware' => 'auth:api'], function () {
        Route::get('/', [JobUpsellController::class, 'index']); // List all job upsells for user
        Route::post('/', [JobUpsellController::class, 'store']);
        Route::post('/{id}/complete-payment', [JobUpsellController::class, 'completePayment']);
        Route::get('/listing/{listingId}', [JobUpsellController::class, 'getByListing']);
    });

    // candidate upsells
    Route::group(['prefix' => 'candidate-upsell', 'middleware' => 'auth:api'], function () {
        Route::get('/', [CandidateUpsellController::class, 'index']);
        Route::post('/', [CandidateUpsellController::class, 'store']);
        Route::post('/{id}/complete-payment', [CandidateUpsellController::class, 'completePayment']);
        Route::get('/profile/{profileId}', [CandidateUpsellController::class, 'getByProfile']);
    });

    // job alerts
    Route::group(['prefix' => 'job-alert', 'middleware' => 'auth:api'], function () {
        Route::get('/', [JobAlertController::class, 'index']);
        Route::post('/', [JobAlertController::class, 'store']);
        Route::get('/{id}', [JobAlertController::class, 'show']);
        Route::put('/{id}', [JobAlertController::class, 'update']);
        Route::delete('/{id}', [JobAlertController::class, 'destroy']);
        Route::get('/{id}/matching-jobs', [JobAlertController::class, 'getMatchingJobs']);
        Route::post('/{id}/toggle-active', [JobAlertController::class, 'toggleActive']);
    });

    // job alerts notification endpoints (for cron jobs)
    Route::group(['prefix' => 'job-alert-notifications'], function () {
        Route::get('/ready', [JobAlertController::class, 'getAlertsForNotification']);
        Route::post('/{id}/notified', [JobAlertController::class, 'markAsNotified']);
    });

    // dashboard
    Route::group(['prefix' => 'dashboard', 'middleware' => 'auth:api'], function () {
        Route::get('/user', [DashboardController::class, 'userDashboard']);
        Route::get('/admin', [DashboardController::class, 'adminDashboard']);
    });

    // analytics
    Route::group(['prefix' => 'analytics', 'middleware' => 'auth:api'], function () {
        Route::get('/revenue', [AnalyticsController::class, 'revenue']);
        Route::get('/jobs', [AnalyticsController::class, 'jobs']);
        Route::get('/candidates', [AnalyticsController::class, 'candidates']);
        Route::get('/upsells', [AnalyticsController::class, 'upsells']);
        Route::get('/overview', [AnalyticsController::class, 'overview']);
        Route::get('/user-posts', [AnalyticsController::class, 'userPosts']);
    });
    // Track event can be accessed without auth for views
    Route::post('/analytics/track-event', [AnalyticsController::class, 'trackEvent']);

    // chat
    Route::group(['prefix' => 'chat', 'middleware' => 'auth:api'], function () {
        Route::get('/conversations', [ChatController::class, 'getConversations']);
        Route::get('/unread-count', [ChatController::class, 'getUnreadCount']);
    });

    // staff management
    Route::group(['prefix' => 'staff', 'middleware' => 'auth:api'], function () {
        Route::get('/', [StaffManagementController::class, 'index']);
        Route::get('/my-memberships', [StaffManagementController::class, 'myStaffMemberships']);
        Route::post('/', [StaffManagementController::class, 'store']);
        Route::put('/{id}', [StaffManagementController::class, 'update']);
        Route::delete('/{id}', [StaffManagementController::class, 'destroy']);
        
        // New endpoints for user validation and search
        Route::post('/search-users', [StaffManagementController::class, 'searchUsers']);
        Route::post('/check-and-invite', [StaffManagementController::class, 'checkAndInviteUser']);
        Route::post('/add-staff-member', [StaffManagementController::class, 'addStaffMember']);
    });

    // listing upsells
    Route::group(['prefix' => 'upsell', 'middleware' => 'auth:api'], function () {
        Route::get('/options', [ListingUpsellController::class, 'getUpsellOptions']);
        Route::post('/purchase', [ListingUpsellController::class, 'purchaseUpsell']);
        Route::get('/my-upsells', [ListingUpsellController::class, 'getUserUpsells']);
        Route::get('/stats', [ListingUpsellController::class, 'getUpsellStats']);
        Route::post('/{upsellId}/cancel', [ListingUpsellController::class, 'cancelUpsell']);
    });

    // search with priority ordering
    Route::group(['prefix' => 'search'], function () {
        Route::get('/listings', [ListingUpsellController::class, 'getSearchResults']);
    });

    // services (Fiverr-like marketplace)
    Route::group(['prefix' => 'services'], function () {
        Route::get('/', [ServiceController::class, 'index']);
        Route::get('/popular', [ServiceController::class, 'getPopularServices']);
        Route::get('/featured', [ServiceController::class, 'getFeaturedServices']);
        Route::get('/categories', [ServiceController::class, 'getCategories']);
        Route::get('/{service}', [ServiceController::class, 'show']);
        Route::post('/{service}/enquiries', [ServiceController::class, 'incrementEnquiries']);
        Route::get('/promotion-options', [ServiceController::class, 'getPromotionOptions']);
        
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [ServiceController::class, 'store']);
            Route::put('/{service}', [ServiceController::class, 'update']);
            Route::delete('/{service}', [ServiceController::class, 'destroy']);
            Route::get('/my-services', [ServiceController::class, 'myServices']);
            Route::post('/{service}/toggle-status', [ServiceController::class, 'toggleStatus']);
            Route::post('/{service}/media', [ServiceController::class, 'uploadMedia']);
            Route::post('/{service}/purchase-promotion', [ServiceController::class, 'purchasePromotion']);
        });
    });

    // service analytics
    Route::group(['prefix' => 'service-analytics'], function () {
        Route::get('/live-activity', [ServiceAnalyticsController::class, 'getLiveActivityFeed']);
        Route::get('/trending', [ServiceAnalyticsController::class, 'getTrendingServices']);
        Route::get('/marketplace-stats', [ServiceAnalyticsController::class, 'getMarketplaceStats']);
        
        Route::group(['middleware' => 'auth:api'], function () {
            Route::get('/service/{service}', [ServiceAnalyticsController::class, 'getServiceAnalytics']);
        });
    });

    // service comparison
    Route::group(['prefix' => 'service-comparison', 'middleware' => 'auth:api'], function () {
        Route::post('/compare', [ServiceComparisonController::class, 'compare']);
        Route::post('/save-comparison', [ServiceComparisonController::class, 'saveComparison']);
    });

    // service orders
    Route::group(['prefix' => 'service-orders', 'middleware' => 'auth:api'], function () {
        Route::get('/', [ServiceOrderController::class, 'index']);
        Route::get('/seller', [ServiceOrderController::class, 'getSellerOrders']);
        Route::get('/buyer', [ServiceOrderController::class, 'getBuyerOrders']);
        Route::get('/stats', [ServiceOrderController::class, 'getOrderStats']);
        Route::post('/', [ServiceOrderController::class, 'store']);
        Route::get('/{order}', [ServiceOrderController::class, 'show']);
        Route::put('/{order}/status', [ServiceOrderController::class, 'updateStatus']);
        Route::post('/{order}/accept', [ServiceOrderController::class, 'acceptOrder']);
        Route::post('/{order}/reject', [ServiceOrderController::class, 'rejectOrder']);
        Route::post('/{order}/complete', [ServiceOrderController::class, 'completeOrder']);
        Route::post('/{order}/refund', [ServiceOrderController::class, 'requestRefund']);
        Route::post('/{order}/review', [ServiceOrderController::class, 'addReview']);
    });

    // affiliate programs
    Route::group(['prefix' => 'affiliate-programs'], function () {
        Route::get('/', [AffiliateProgramController::class, 'index']);
        Route::get('/featured', [AffiliateProgramController::class, 'getFeaturedPrograms']);
        Route::get('/networks', [AffiliateProgramController::class, 'getNetworks']);
        Route::get('/{program}', [AffiliateProgramController::class, 'show']);
        Route::post('/{program}/track-click', [AffiliateProgramController::class, 'trackClick']);
        Route::post('/record-conversion', [AffiliateProgramController::class, 'recordConversion']);
        
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [AffiliateProgramController::class, 'store']);
            Route::put('/{program}', [AffiliateProgramController::class, 'update']);
            Route::delete('/{program}', [AffiliateProgramController::class, 'destroy']);
            Route::get('/my-programs', [AffiliateProgramController::class, 'myPrograms']);
            Route::get('/stats', [AffiliateProgramController::class, 'getProgramStats']);
            Route::post('/{program}/toggle-status', [AffiliateProgramController::class, 'toggleStatus']);
            Route::post('/join-our-program', [AffiliateProgramController::class, 'joinOurProgram']);
        });
    });

    // affiliate posts (new comprehensive system)
    Route::group(['prefix' => 'affiliate-posts'], function () {
        // Public routes
        Route::get('/', [App\Http\Controllers\Api\AffiliatePostController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Api\AffiliatePostController::class, 'show']);
        Route::get('/category/{categoryId}', [App\Http\Controllers\Api\AffiliatePostController::class, 'getByCategory']);
        Route::get('/featured', [App\Http\Controllers\Api\AffiliatePostController::class, 'getFeatured']);
        Route::get('/sponsored', [App\Http\Controllers\Api\AffiliatePostController::class, 'getSponsored']);
        Route::get('/promoted', [App\Http\Controllers\Api\AffiliatePostController::class, 'getPromoted']);
        
        // Authenticated routes
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [App\Http\Controllers\Api\AffiliatePostController::class, 'store']);
            Route::put('/{id}', [App\Http\Controllers\Api\AffiliatePostController::class, 'update']);
            Route::delete('/{id}', [App\Http\Controllers\Api\AffiliatePostController::class, 'destroy']);
            Route::get('/my-posts', [App\Http\Controllers\Api\AffiliatePostController::class, 'myPosts']);
        });
    });

    // affiliate upsell management
    Route::group(['prefix' => 'affiliate-upsells', 'middleware' => 'auth:api'], function () {
        Route::get('/plans', [App\Http\Controllers\Api\AffiliateUpsellController::class, 'getPlans']);
        Route::get('/comparison', [App\Http\Controllers\Api\AffiliateUpsellController::class, 'getComparison']);
        Route::get('/recommendation', [App\Http\Controllers\Api\AffiliateUpsellController::class, 'getRecommendation']);
        Route::post('/purchase', [App\Http\Controllers\Api\AffiliateUpsellController::class, 'purchaseUpsell']);
        Route::get('/my-upsells', [App\Http\Controllers\Api\AffiliateUpsellController::class, 'getMyUpsells']);
        Route::get('/post/{postId}', [App\Http\Controllers\Api\AffiliateUpsellController::class, 'getPostUpsells']);
        Route::post('/{id}/cancel', [App\Http\Controllers\Api\AffiliateUpsellController::class, 'cancelUpsell']);
        Route::get('/stats', [App\Http\Controllers\Api\AffiliateUpsellController::class, 'getStats']);
    });

    // stores
    Route::group(['prefix' => 'store'], function () {
        // Authenticated routes first to avoid conflicts
        Route::group(['middleware' => 'auth:api'], function () {
            Route::get('/my-store', [StoreController::class, 'myStore']);
            Route::post('/', [StoreController::class, 'store']);
            Route::put('/{id}', [StoreController::class, 'update']);
            Route::delete('/{id}', [StoreController::class, 'destroy']);
        });
        
        // Public routes after
        Route::get('/', [StoreController::class, 'index']);
        Route::get('/{id}', [StoreController::class, 'show']);
        Route::get('/{customer_id}/detail', [StoreController::class, 'detail']);
        Route::get('/{customer_id}/my-ads', [StoreController::class, 'myAds']);
    });

    // Admin category post management
    Route::group(['prefix' => 'admin/category-posts', 'middleware' => 'auth:api'], function () {
        Route::get('/category/{categoryId}', [CategoryPostController::class, 'getCategoryPosts']);
        Route::post('/create', [CategoryPostController::class, 'createAdminPost']);
        Route::put('/{postId}', [CategoryPostController::class, 'updatePost']);
        Route::delete('/{postId}', [CategoryPostController::class, 'deletePost']);
        Route::get('/pending', [CategoryPostController::class, 'getPendingPosts']);
        Route::post('/bulk-approve', [CategoryPostController::class, 'bulkApprove']);
        Route::post('/bulk-reject', [CategoryPostController::class, 'bulkReject']);
        Route::get('/stats', [CategoryPostController::class, 'getCategoryStats']);
    });

    // Admin post moderation
    Route::group(['prefix' => 'admin/moderation', 'middleware' => 'auth:api'], function () {
        Route::get('/dashboard', [PostModerationController::class, 'getModerationDashboard']);
        Route::get('/posts-needing-attention', [PostModerationController::class, 'getPostsNeedingAttention']);
        Route::post('/{postId}/quick-approve', [PostModerationController::class, 'quickApprove']);
        Route::post('/{postId}/quick-reject', [PostModerationController::class, 'quickReject']);
        Route::post('/{postId}/mark-harmful', [PostModerationController::class, 'markAsHarmful']);
        Route::post('/{postId}/restore', [PostModerationController::class, 'restoreHarmful']);
        Route::get('/user/{userId}/history', [PostModerationController::class, 'getUserPostHistory']);
        Route::post('/bulk-action', [PostModerationController::class, 'bulkAction']);
        Route::get('/activity-log', [PostModerationController::class, 'getModerationLog']);
    });

    // Admin notifications
    Route::group(['prefix' => 'admin/notifications', 'middleware' => 'auth:api'], function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{notificationId}/mark-read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{notificationId}', [NotificationController::class, 'delete']);
        Route::post('/create', [NotificationController::class, 'createNotification']);
        Route::get('/stats', [NotificationController::class, 'getStats']);
        Route::post('/cleanup', [NotificationController::class, 'cleanup']);
    });

    // Referral system
    Route::group(['prefix' => 'referral'], function () {
        // Public endpoints (no auth required)
        Route::post('/validate', [ReferralController::class, 'validateCode']);
        Route::get('/info', [ReferralController::class, 'getReferralInfo']);
        
        // Protected endpoints (auth required)
        Route::group(['middleware' => 'auth:api'], function () {
            Route::get('/my', [ReferralController::class, 'getMyReferral']);
            Route::post('/create', [ReferralController::class, 'createReferral']);
            Route::put('/{referral_id}', [ReferralController::class, 'updateReferral']);
            Route::get('/history', [ReferralController::class, 'getMyReferralHistory']);
            Route::get('/{referral_id}/share', [ReferralController::class, 'shareReferral']);
        });
    });

    // User Analytics Dashboard
    Route::group(['prefix' => 'user-analytics', 'middleware' => 'auth:api'], function () {
        Route::get('/dashboard', [UserAnalyticsController::class, 'getDashboard']);
        Route::get('/listing-analytics', [UserAnalyticsController::class, 'getListingAnalytics']);
        Route::get('/profile-analytics', [UserAnalyticsController::class, 'getProfileAnalytics']);
        Route::get('/export', [UserAnalyticsController::class, 'exportAnalytics']);
    });

    // Admin Analytics Dashboard (with role-based permissions)
    Route::group(['prefix' => 'admin-analytics', 'middleware' => 'auth:api'], function () {
        Route::get('/dashboard', [AdminAnalyticsController::class, 'getDashboard']);
        Route::get('/user-analytics', [AdminAnalyticsController::class, 'getUserAnalytics']);
        Route::get('/listing-analytics', [AdminAnalyticsController::class, 'getListingAnalytics']);
        Route::get('/export', [AdminAnalyticsController::class, 'exportAnalytics']);
        Route::post('/permissions', [AdminAnalyticsController::class, 'managePermissions']);
    });

    // Maintenance Control Panel (Admin Only)
    Route::group(['prefix' => 'admin/maintenance', 'middleware' => ['auth:api', 'admin']], function () {
        Route::get('/status', [MaintenanceController::class, 'status']);
        Route::post('/down', [MaintenanceController::class, 'down']);
        Route::post('/up', [MaintenanceController::class, 'up']);
        Route::post('/schedule', [MaintenanceController::class, 'schedule']);
        Route::get('/logs', [MaintenanceController::class, 'logs']);
    });

    // Public maintenance status (no auth required)
    Route::get('/maintenance/status', [MaintenanceController::class, 'status']);

    // Events & Venues System
    Route::group(['prefix' => 'events'], function () {
        // Public routes
        Route::get('/', [EventController::class, 'index']);
        Route::get('/featured', [EventController::class, 'featuredEvents']);
        Route::get('/categories', [EventController::class, 'categories']);
        Route::get('/{slug}', [EventController::class, 'show']);
        
        // Authenticated routes
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [EventController::class, 'store']);
            Route::put('/{id}', [EventController::class, 'update']);
            Route::delete('/{id}', [EventController::class, 'destroy']);
            Route::get('/my-events', [EventController::class, 'myEvents']);
            Route::post('/upload-images', [EventController::class, 'uploadImages']);
        });
    });

    Route::group(['prefix' => 'venues'], function () {
        // Public routes
        Route::get('/', [VenueController::class, 'index']);
        Route::get('/featured', [VenueController::class, 'featuredVenues']);
        Route::get('/types', [VenueController::class, 'venueTypes']);
        Route::get('/amenities', [VenueController::class, 'amenities']);
        Route::get('/{slug}', [VenueController::class, 'show']);
        
        // Authenticated routes
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [VenueController::class, 'store']);
            Route::put('/{id}', [VenueController::class, 'update']);
            Route::delete('/{id}', [VenueController::class, 'destroy']);
            Route::get('/my-venues', [VenueController::class, 'myVenues']);
            Route::post('/upload-images', [VenueController::class, 'uploadImages']);
            Route::post('/upload-floor-plan', [VenueController::class, 'uploadFloorPlan']);
        });
    });

    Route::group(['prefix' => 'venue-services'], function () {
        // Public routes
        Route::get('/', [VenueServiceController::class, 'index']);
        Route::get('/featured', [VenueServiceController::class, 'featuredServices']);
        Route::get('/categories', [VenueServiceController::class, 'serviceCategories']);
        Route::get('/{slug}', [VenueServiceController::class, 'show']);
        
        // Authenticated routes
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [VenueServiceController::class, 'store']);
            Route::put('/{id}', [VenueServiceController::class, 'update']);
            Route::delete('/{id}', [VenueServiceController::class, 'destroy']);
            Route::get('/my-services', [VenueServiceController::class, 'myServices']);
            Route::post('/upload-portfolio-images', [VenueServiceController::class, 'uploadPortfolioImages']);
            Route::post('/event/{eventId}/add-service', [VenueServiceController::class, 'addToEvent']);
            Route::delete('/event/{eventId}/service/{serviceId}', [VenueServiceController::class, 'removeFromEvent']);
            Route::put('/event/{eventId}/service/{serviceId}/status', [VenueServiceController::class, 'updateEventServiceStatus']);
        });
    });

    // Upsell/Promotion System
    Route::group(['prefix' => 'upsells', 'middleware' => 'auth:api'], function () {
        Route::get('/promotion-tiers', [UpsellController::class, 'getPromotionTiers']);
        Route::get('/network-wide-boost', [UpsellController::class, 'getNetworkWideBoost']);
        Route::post('/network-wide-boost/purchase', [UpsellController::class, 'purchaseNetworkWideBoost']);
        
        // Event upgrades
        Route::post('/event/{eventId}/upgrade', [UpsellController::class, 'upgradeEvent']);
        Route::get('/event/{eventId}/stats', [UpsellController::class, 'getPromotionStats']);
        
        // Venue upgrades
        Route::post('/venue/{venueId}/upgrade', [UpsellController::class, 'upgradeVenue']);
        Route::get('/venue/{venueId}/stats', [UpsellController::class, 'getPromotionStats']);
        
        // Venue service upgrades
        Route::post('/venue-service/{serviceId}/upgrade', [UpsellController::class, 'upgradeVenueService']);
        Route::get('/venue-service/{serviceId}/stats', [UpsellController::class, 'getPromotionStats']);
    });

    // Resorts & Travel System
    Route::group(['prefix' => 'resorts-travel'], function () {
        // Public routes
        Route::get('/', [ResortsTravelController::class, 'index']);
        Route::get('/featured', [ResortsTravelController::class, 'featuredAdverts']);
        Route::get('/advert-types', [ResortsTravelController::class, 'advertTypes']);
        Route::get('/amenities', [ResortsTravelController::class, 'amenities']);
        Route::get('/promotion-tiers', [ResortsTravelController::class, 'promotionTiers']);
        Route::get('/categories', [ResortsTravelCategoryController::class, 'index']);
        Route::get('/{slug}', [ResortsTravelController::class, 'show']);
        
        // Authenticated routes
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [ResortsTravelController::class, 'store']);
            Route::put('/{id}', [ResortsTravelController::class, 'update']);
            Route::delete('/{id}', [ResortsTravelController::class, 'destroy']);
            Route::get('/my-adverts', [ResortsTravelController::class, 'myAdverts']);
            Route::post('/upload-images', [ResortsTravelController::class, 'uploadImages']);
            Route::post('/upload-logo', [ResortsTravelController::class, 'uploadLogo']);
        });
    });

    // Resorts & Travel Categories
    Route::group(['prefix' => 'resorts-travel-categories'], function () {
        // Public routes
        Route::get('/', [ResortsTravelCategoryController::class, 'index']);
        Route::get('/types', [ResortsTravelCategoryController::class, 'categoryTypes']);
        Route::get('/popular', [ResortsTravelCategoryController::class, 'popularCategories']);
        Route::get('/{slug}', [ResortsTravelCategoryController::class, 'show']);
        Route::get('/{slug}/adverts', [ResortsTravelCategoryController::class, 'categoryAdverts']);
        
        // Admin routes (require auth)
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [ResortsTravelCategoryController::class, 'store']);
            Route::put('/{id}', [ResortsTravelCategoryController::class, 'update']);
            Route::delete('/{id}', [ResortsTravelCategoryController::class, 'destroy']);
        });
    });

    // Banner Adverts System
    Route::group(['prefix' => 'banner-ads'], function () {
        // Public routes
        Route::get('/', [BannerAdController::class, 'index']);
        Route::get('/featured', [BannerAdController::class, 'featured']);
        Route::get('/most-viewed', [BannerAdController::class, 'mostViewed']);
        Route::get('/recent', [BannerAdController::class, 'recent']);
        Route::get('/{slug}', [BannerAdController::class, 'show']);
        Route::post('/{slug}/track-click', [BannerAdController::class, 'trackClick']);
        Route::get('/promotion-options', [BannerAdController::class, 'promotionOptions']);
        
        // Authenticated routes
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [BannerAdController::class, 'store']);
            Route::put('/{id}', [BannerAdController::class, 'update']);
            Route::delete('/{id}', [BannerAdController::class, 'destroy']);
            Route::get('/my-banners', [BannerAdController::class, 'myBanners']);
        });
    });

    // Banner Upload System
    Route::group(['prefix' => 'banner-upload', 'middleware' => 'auth:api'], function () {
        Route::post('/banner-image', [BannerUploadController::class, 'uploadBannerImage']);
        Route::post('/business-logo', [BannerUploadController::class, 'uploadBusinessLogo']);
        Route::post('/animated-banner', [BannerUploadController::class, 'uploadAnimatedBanner']);
        Route::post('/html5-banner', [BannerUploadController::class, 'uploadHtml5Banner']);
        Route::post('/video-banner', [BannerUploadController::class, 'uploadVideoBanner']);
        Route::delete('/file', [BannerUploadController::class, 'deleteFile']);
    });

    // Banner Categories
    Route::group(['prefix' => 'banner-categories'], function () {
        // Public routes
        Route::get('/', [BannerCategoryController::class, 'index']);
        Route::get('/trending', [BannerCategoryController::class, 'trending']);
        Route::get('/{slug}', [BannerCategoryController::class, 'show']);
        Route::get('/{slug}/banner-ads', [BannerCategoryController::class, 'bannerAds']);
        
        // Admin routes (require auth and permissions)
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [BannerCategoryController::class, 'store']);
            Route::put('/{id}', [BannerCategoryController::class, 'update']);
            Route::delete('/{id}', [BannerCategoryController::class, 'destroy']);
            Route::post('/update-banner-counts', [BannerCategoryController::class, 'updateBannerCounts']);
        });
    });

    // Banner Marketplace
    Route::group(['prefix' => 'banner-marketplace'], function () {
        Route::get('/homepage', [BannerMarketplaceController::class, 'homepage']);
        Route::get('/carousel', [BannerMarketplaceController::class, 'carousel']);
        Route::get('/categories', [BannerMarketplaceController::class, 'categories']);
        Route::get('/analytics', [BannerMarketplaceController::class, 'analytics']);
    });

    // Promoted Adverts System
    Route::group(['prefix' => 'promoted-adverts'], function () {
        // Public routes
        Route::get('/', [PromotedAdvertController::class, 'index']);
        Route::get('/featured', [PromotedAdvertController::class, 'featured']);
        Route::get('/most-viewed', [PromotedAdvertController::class, 'mostViewed']);
        Route::get('/most-saved', [PromotedAdvertController::class, 'mostSaved']);
        Route::get('/recent', [PromotedAdvertController::class, 'recent']);
        Route::get('/{slug}', [PromotedAdvertController::class, 'show']);
        Route::post('/{slug}/track-click', [PromotedAdvertController::class, 'trackClick']);
        Route::get('/promotion-options', [PromotedAdvertController::class, 'promotionOptions']);
        
        // Authenticated routes
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [PromotedAdvertController::class, 'store']);
            Route::put('/{id}', [PromotedAdvertController::class, 'update']);
            Route::delete('/{id}', [PromotedAdvertController::class, 'destroy']);
            Route::get('/my-adverts', [PromotedAdvertController::class, 'myAdverts']);
            Route::post('/upload-images', [PromotedAdvertController::class, 'uploadImages']);
            Route::post('/upload-logo', [PromotedAdvertController::class, 'uploadLogo']);
            Route::post('/{id}/toggle-favorite', [PromotedAdvertController::class, 'toggleFavorite']);
        });
    });

    // Promoted Advert Categories
    Route::group(['prefix' => 'promoted-advert-categories'], function () {
        // Public routes
        Route::get('/', [PromotedAdvertCategoryController::class, 'index']);
        Route::get('/popular', [PromotedAdvertCategoryController::class, 'popular']);
        Route::get('/{slug}', [PromotedAdvertCategoryController::class, 'show']);
        Route::get('/{slug}/adverts', [PromotedAdvertCategoryController::class, 'categoryAdverts']);
        
        // Admin routes (require auth)
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [PromotedAdvertCategoryController::class, 'store']);
            Route::put('/{id}', [PromotedAdvertCategoryController::class, 'update']);
            Route::delete('/{id}', [PromotedAdvertCategoryController::class, 'destroy']);
        });
    });

    // Books Adverts System
    Route::group(['prefix' => 'books-adverts'], function () {
        // Public routes
        Route::get('/', [BookAdvertController::class, 'index']);
        Route::get('/featured', [BookAdvertController::class, 'getFeaturedBooks']);
        Route::get('/genre/{genre}', [BookAdvertController::class, 'getBooksByGenre']);
        Route::get('/pricing-plans', [BookAdvertController::class, 'getPricingPlans']);
        Route::get('/{slug}', [BookAdvertController::class, 'show']);
        
        // Authenticated user routes
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [BookAdvertController::class, 'store']);
            Route::put('/{book}', [BookAdvertController::class, 'update']);
            Route::delete('/{book}', [BookAdvertController::class, 'destroy']);
            Route::post('/{book}/save', [BookAdvertController::class, 'saveBook']);
            Route::get('/my-books', [BookAdvertController::class, 'myBooks']);
            Route::post('/{book}/payment', [BookAdvertController::class, 'processPayment']);
            Route::get('/statistics', [BookAdvertController::class, 'getStatistics']);
        });
    });

    // Authors Management
    Route::group(['prefix' => 'authors'], function () {
        // Public routes
        Route::get('/', [AuthorController::class, 'index']);
        Route::get('/spotlight', [AuthorController::class, 'spotlight']);
        Route::get('/search', [AuthorController::class, 'search']);
        Route::get('/{id}', [AuthorController::class, 'show']);
        Route::get('/{id}/books', [AuthorController::class, 'books']);
        
        // Admin routes (require auth and permissions)
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [AuthorController::class, 'store']);
            Route::put('/{id}', [AuthorController::class, 'update']);
            Route::delete('/{id}', [AuthorController::class, 'destroy']);
        });
    });

    // Vehicles Adverts System
    Route::group(['prefix' => 'vehicles'], function () {
        // Public routes
        Route::get('/', [VehicleController::class, 'index']);
        Route::get('/featured', [VehicleController::class, 'getFeaturedVehicles']);
        Route::get('/promoted', [VehicleController::class, 'index'])->defaults('promoted', true);
        Route::get('/sponsored', [VehicleController::class, 'index'])->defaults('sponsored', true);
        Route::get('/recent', [VehicleController::class, 'getRecentVehicles']);
        Route::get('/{id}', [VehicleController::class, 'show']);
        Route::get('/{id}/related', [VehicleController::class, 'getRelatedVehicles']);
        
        // Data endpoints
        Route::get('/makes', [VehicleController::class, 'getMakes']);
        Route::get('/models/{makeId}', [VehicleController::class, 'getModels']);
        Route::get('/categories', [VehicleController::class, 'getCategories']);
        
        // Authenticated user routes
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [VehicleController::class, 'store']);
            Route::put('/{id}', [VehicleController::class, 'update']);
            Route::delete('/{id}', [VehicleController::class, 'destroy']);
            Route::get('/my-vehicles', [VehicleController::class, 'myVehicles']);
            Route::get('/saved', [VehicleController::class, 'savedVehicles']);
            Route::post('/{id}/save', [VehicleController::class, 'saveVehicle']);
            Route::post('/{id}/toggle-status', [VehicleController::class, 'toggleStatus']);
            Route::post('/{id}/mark-sold', [VehicleController::class, 'markAsSold']);
            Route::post('/{id}/enquiry', [VehicleController::class, 'createEnquiry']);
        });
    });

    // Vehicle Categories
    Route::group(['prefix' => 'vehicle-categories'], function () {
        // Public routes
        Route::get('/', [VehicleCategoryController::class, 'index']);
        Route::get('/popular', [VehicleCategoryController::class, 'popularCategories']);
        Route::get('/{id}', [VehicleCategoryController::class, 'show']);
        Route::get('/{id}/vehicles', [VehicleController::class, 'index'])->defaults('category', request()->id);
        
        // Admin routes (require auth and permissions)
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [VehicleCategoryController::class, 'store']);
            Route::put('/{id}', [VehicleCategoryController::class, 'update']);
            Route::delete('/{id}', [VehicleCategoryController::class, 'destroy']);
            Route::post('/{id}/toggle-status', [VehicleCategoryController::class, 'toggleStatus']);
        });
    });

    // Jobs System
    Route::group(['prefix' => 'jobs'], function () {
        // Public routes
        Route::get('/', [JobController::class, 'index']);
        Route::get('/featured', [JobController::class, 'featuredJobs']);
        Route::get('/sponsored', [JobController::class, 'sponsoredJobs']);
        Route::get('/urgent', [JobController::class, 'urgentJobs']);
        Route::get('/remote', [JobController::class, 'remoteJobs']);
        Route::get('/trending', [JobController::class, 'trendingJobs']);
        Route::get('/live-activity', [JobController::class, 'liveActivityFeed']);
        Route::get('/{slug}', [JobController::class, 'show']);
        
        // Authenticated routes
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [JobController::class, 'store']);
            Route::put('/{id}', [JobController::class, 'update']);
            Route::delete('/{id}', [JobController::class, 'destroy']);
            Route::get('/my-jobs', [JobController::class, 'myJobs']);
            Route::get('/statistics', [JobController::class, 'statistics']);
            Route::post('/{jobId}/apply', [JobController::class, 'apply']);
            Route::get('/my-applications', [JobController::class, 'myApplications']);
            Route::get('/{jobId}/applications', [JobController::class, 'jobApplications']);
            Route::put('/applications/{applicationId}/status', [JobController::class, 'updateApplicationStatus']);
        });
    });

    // Job Categories
    Route::group(['prefix' => 'job-categories'], function () {
        // Public routes
        Route::get('/', [JobCategoryController::class, 'index']);
        Route::get('/popular', [JobCategoryController::class, 'popularCategories']);
        Route::get('/{slug}', [JobCategoryController::class, 'show']);
        Route::get('/{slug}/jobs', [JobCategoryController::class, 'categoryWithJobs']);
        
        // Admin routes (require auth and permissions)
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [JobCategoryController::class, 'store']);
            Route::put('/{id}', [JobCategoryController::class, 'update']);
            Route::delete('/{id}', [JobCategoryController::class, 'destroy']);
        });
    });

    // Job Seekers
    Route::group(['prefix' => 'job-seekers'], function () {
        // Public routes
        Route::get('/', [JobSeekerController::class, 'index']);
        Route::get('/featured', [JobSeekerController::class, 'featuredProfiles']);
        Route::get('/sponsored', [JobSeekerController::class, 'sponsoredProfiles']);
        Route::get('/{id}', [JobSeekerController::class, 'show']);
        Route::post('/{id}/contact', [JobSeekerController::class, 'contactProfile']);
        
        // Authenticated routes
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [JobSeekerController::class, 'store']);
            Route::put('/{id}', [JobSeekerController::class, 'update']);
            Route::delete('/{id}', [JobSeekerController::class, 'destroy']);
            Route::get('/my-profile', [JobSeekerController::class, 'myProfile']);
            Route::get('/statistics', [JobSeekerController::class, 'statistics']);
        });
    });

    // Sponsored Adverts System
    Route::group(['prefix' => 'sponsored-adverts'], function () {
        // Public routes
        Route::get('/', [SponsoredAdvertController::class, 'index']);
        Route::get('/featured', [SponsoredAdvertController::class, 'featured']);
        Route::get('/trending', [SponsoredAdvertController::class, 'trending']);
        Route::get('/statistics', [SponsoredAdvertController::class, 'statistics']);
        Route::get('/category/{categoryId}', [SponsoredAdvertController::class, 'byCategory']);
        Route::get('/country/{country}', [SponsoredAdvertController::class, 'byCountry']);
        Route::get('/{slug}', [SponsoredAdvertController::class, 'show']);
        
        // Public interaction routes
        Route::post('/{id}/inquiry', [SponsoredAdvertController::class, 'submitInquiry']);
        Route::post('/{id}/rating', [SponsoredAdvertController::class, 'submitRating']);
        
        // Authenticated routes
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [SponsoredAdvertController::class, 'store']);
            Route::put('/{id}', [SponsoredAdvertController::class, 'update']);
            Route::delete('/{id}', [SponsoredAdvertController::class, 'destroy']);
        });
    });

    // Sponsored Pricing Plans
    Route::group(['prefix' => 'sponsored-pricing-plans'], function () {
        // Public routes
        Route::get('/', [SponsoredPricingPlanController::class, 'index']);
        Route::get('/featured', [SponsoredPricingPlanController::class, 'featured']);
        Route::get('/comparison', [SponsoredPricingPlanController::class, 'comparison']);
        Route::get('/recommendation', [SponsoredPricingPlanController::class, 'recommendation']);
        Route::get('/tier/{tier}', [SponsoredPricingPlanController::class, 'byTier']);
        Route::get('/{id}', [SponsoredPricingPlanController::class, 'show']);
    });

    // Properties System
    Route::group(['prefix' => 'properties'], function () {
        // Public routes
        Route::get('/', [PropertyController::class, 'index']);
        Route::get('/featured', [PropertyController::class, 'featured']);
        Route::get('/promoted', [PropertyController::class, 'promoted']);
        Route::get('/sponsored', [PropertyController::class, 'sponsored']);
        Route::get('/{slug}', [PropertyController::class, 'show']);
        
        // Public data routes
        Route::get('/data/property-types', [PropertyController::class, 'getPropertyTypes']);
        Route::get('/data/categories', [PropertyController::class, 'getCategories']);
        Route::get('/data/commercial-types', [PropertyController::class, 'getCommercialTypes']);
        Route::get('/data/land-types', [PropertyController::class, 'getLandTypes']);
        Route::get('/data/planning-permissions', [PropertyController::class, 'getPlanningPermissions']);
        Route::get('/data/view-types', [PropertyController::class, 'getViewTypes']);
        
        // Public interaction routes
        Route::post('/{id}/contact-agent', [PropertyController::class, 'contactAgent']);
        Route::post('/{id}/track-event', [PropertyController::class, 'trackEvent']);
        
        // Authenticated routes
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [PropertyController::class, 'store']);
            Route::put('/{id}', [PropertyController::class, 'update']);
            Route::delete('/{id}', [PropertyController::class, 'destroy']);
            Route::get('/my-properties', [PropertyController::class, 'myProperties']);
            Route::post('/{id}/save', [PropertyController::class, 'saveProperty']);
            Route::get('/saved-properties', [PropertyController::class, 'savedProperties']);
        });
    });

    // Property Upsells System
    Route::group(['prefix' => 'property-upsells', 'middleware' => 'auth:api'], function () {
        Route::get('/', [PropertyUpsellController::class, 'index']);
        Route::post('/', [PropertyUpsellController::class, 'store']);
        Route::get('/options', [PropertyUpsellController::class, 'getUpsellOptions']);
        Route::get('/stats', [PropertyUpsellController::class, 'getStats']);
        Route::get('/{id}', [PropertyUpsellController::class, 'show']);
        Route::post('/{id}/complete-payment', [PropertyUpsellController::class, 'completePayment']);
        Route::post('/{id}/cancel', [PropertyUpsellController::class, 'cancel']);
        Route::get('/property/{propertyId}', [PropertyUpsellController::class, 'getPropertyUpsells']);
    });

    // Featured Adverts System
    Route::group(['prefix' => 'featured-adverts'], function () {
        // Public routes
        Route::get('/', [FeaturedAdvertController::class, 'index']);
        Route::get('/carousel', [FeaturedAdvertController::class, 'carousel']);
        Route::get('/category-grid', [FeaturedAdvertController::class, 'categoryGrid']);
        Route::get('/trending-countries', [FeaturedAdvertController::class, 'trendingCountries']);
        Route::get('/trending-categories', [FeaturedAdvertController::class, 'trendingCategories']);
        Route::get('/pricing', [FeaturedAdvertController::class, 'getPricing']);
        Route::get('/home', [FeaturedAdvertController::class, 'homeListing']);
        Route::get('/category/{categoryId}', [FeaturedAdvertController::class, 'byCategory']);
        Route::get('/country/{country}', [FeaturedAdvertController::class, 'byCountry']);
        Route::get('/type/{type}', [FeaturedAdvertController::class, 'byType']);
        Route::get('/{id}/related', [FeaturedAdvertController::class, 'related']);
        Route::get('/search', [FeaturedAdvertController::class, 'advancedSearch']);
        Route::get('/statistics', [FeaturedAdvertController::class, 'statistics']);
        Route::get('/live-activity', [FeaturedAdvertController::class, 'liveActivity']);
        Route::get('/analytics', [FeaturedAdvertController::class, 'analytics']);
        Route::get('/{id}', [FeaturedAdvertController::class, 'show']);
        Route::post('/{id}/save', [FeaturedAdvertController::class, 'saveAdvert']);
        Route::post('/{id}/contact', [FeaturedAdvertController::class, 'contactSeller']);
        
        // Authenticated routes (customer)
        Route::group(['middleware' => 'auth:customer'], function () {
            Route::post('/', [FeaturedAdvertController::class, 'store']);
            Route::put('/{id}', [FeaturedAdvertController::class, 'update']);
            Route::delete('/{id}', [FeaturedAdvertController::class, 'destroy']);
            Route::get('/my-adverts', [FeaturedAdvertController::class, 'myFeaturedAdverts']);
        });
    });

    // Funding Projects System
    Route::group(['prefix' => 'funding'], function () {
        // Public routes
        Route::get('/', [App\Http\Controllers\Api\FundingProjectController::class, 'index']);
        Route::get('/categories', [App\Http\Controllers\Api\FundingProjectController::class, 'getCategories']);
        Route::get('/featured', [App\Http\Controllers\Api\FundingProjectController::class, 'getFeaturedProjects']);
        Route::get('/trending', [App\Http\Controllers\Api\FundingProjectController::class, 'getTrendingProjects']);
        Route::get('/ending-soon', [App\Http\Controllers\Api\FundingProjectController::class, 'getEndingSoonProjects']);
        Route::get('/{slug}', [App\Http\Controllers\Api\FundingProjectController::class, 'show']);
        
        // Authenticated routes
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [App\Http\Controllers\Api\FundingProjectController::class, 'store']);
            Route::put('/{id}', [App\Http\Controllers\Api\FundingProjectController::class, 'update']);
            Route::delete('/{id}', [App\Http\Controllers\Api\FundingProjectController::class, 'destroy']);
            Route::get('/my-projects', [App\Http\Controllers\Api\FundingProjectController::class, 'myProjects']);
            Route::post('/{id}/publish', [App\Http\Controllers\Api\FundingProjectController::class, 'publish']);
            Route::post('/{id}/back', [App\Http\Controllers\Api\FundingProjectController::class, 'backProject']);
        });
    });

    // Funding Upsells System
    Route::group(['prefix' => 'funding-upsells', 'middleware' => 'auth:api'], function () {
        Route::get('/plans', [App\Http\Controllers\Api\FundingUpsellController::class, 'getPlans']);
        Route::get('/comparison', [App\Http\Controllers\Api\FundingUpsellController::class, 'getComparison']);
        Route::get('/recommendation', [App\Http\Controllers\Api\FundingUpsellController::class, 'getRecommendation']);
        Route::post('/purchase', [App\Http\Controllers\Api\FundingUpsellController::class, 'purchaseUpsell']);
        Route::get('/my-upsells', [App\Http\Controllers\Api\FundingUpsellController::class, 'getMyUpsells']);
        Route::get('/post/{projectId}', [App\Http\Controllers\Api\FundingUpsellController::class, 'getPostUpsells']);
        Route::post('/{id}/cancel', [App\Http\Controllers\Api\FundingUpsellController::class, 'cancelUpsell']);
        Route::get('/stats', [App\Http\Controllers\Api\FundingUpsellController::class, 'getStats']);
    });

    // Admin Service Management
    Route::group(['prefix' => 'admin/services', 'middleware' => ['auth:api', 'admin']], function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Admin\ServiceManagementController::class, 'dashboard']);
        
        // Services Management
        Route::get('/', [App\Http\Controllers\Admin\ServiceManagementController::class, 'index']);
        Route::get('/{service}', [App\Http\Controllers\Admin\ServiceManagementController::class, 'show']);
        Route::put('/{service}', [App\Http\Controllers\Admin\ServiceManagementController::class, 'update']);
        Route::delete('/{service}', [App\Http\Controllers\Admin\ServiceManagementController::class, 'destroy']);
        Route::post('/bulk-action', [App\Http\Controllers\Admin\ServiceManagementController::class, 'bulkAction']);
        
        // Categories Management
        Route::get('/categories', [App\Http\Controllers\Admin\ServiceManagementController::class, 'categoriesIndex']);
        Route::post('/categories', [App\Http\Controllers\Admin\ServiceManagementController::class, 'categoriesStore']);
        Route::put('/categories/{category}', [App\Http\Controllers\Admin\ServiceManagementController::class, 'categoriesUpdate']);
        Route::delete('/categories/{category}', [App\Http\Controllers\Admin\ServiceManagementController::class, 'categoriesDestroy']);
        
        // Promotions Management
        Route::get('/promotions', [App\Http\Controllers\Admin\ServiceManagementController::class, 'promotionsIndex']);
        Route::post('/promotions', [App\Http\Controllers\Admin\ServiceManagementController::class, 'promotionsStore']);
        Route::put('/promotions/{promotion}', [App\Http\Controllers\Admin\ServiceManagementController::class, 'promotionsUpdate']);
        Route::delete('/promotions/{promotion}', [App\Http\Controllers\Admin\ServiceManagementController::class, 'promotionsDestroy']);
        Route::get('/promotions/pricing', [App\Http\Controllers\Admin\ServiceManagementController::class, 'promotionPricing']);
        
        // Analytics
        Route::get('/analytics', [App\Http\Controllers\Admin\ServiceManagementController::class, 'analytics']);
    });
});
