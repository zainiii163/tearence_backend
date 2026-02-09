<?php

use App\Http\Controllers\AdPricingPlanController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\Api\AffiliateProgramController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListingUpsellController;
use App\Http\Controllers\Api\ServiceController;
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
        Route::get('/categories', [ServiceController::class, 'getServiceCategories']);
        Route::get('/{service}', [ServiceController::class, 'show']);
        
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [ServiceController::class, 'store']);
            Route::put('/{service}', [ServiceController::class, 'update']);
            Route::delete('/{service}', [ServiceController::class, 'destroy']);
            Route::get('/my-services', [ServiceController::class, 'myServices']);
            Route::post('/{service}/toggle-status', [ServiceController::class, 'toggleStatus']);
            Route::post('/{service}/gallery', [ServiceController::class, 'uploadGallery']);
        });
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
});
