<?php



use App\Http\Controllers\AdPricingPlanController;

use App\Http\Controllers\AffiliateController;

use App\Http\Controllers\Api\AffiliateProgramController;

use App\Http\Controllers\Api\AffiliateController as ApiAffiliateController;

use App\Http\Controllers\AnalyticsController;

use App\Http\Controllers\AuthController;

use App\Http\Controllers\Api\VerificationController;

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

use App\Http\Controllers\Api\BooksAdvertController;

use App\Http\Controllers\Api\ImagesAdvertController;

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

use App\Http\Controllers\CalculatorController;

use App\Http\Controllers\Api\ResortsTravelCategoryController;

use App\Http\Controllers\Api\BuySellPromotionController;

use App\Http\Controllers\Api\BuySellCategoryController;

use App\Http\Controllers\Api\BuySellItemController;

use App\Http\Controllers\Api\BannerAdController;

use App\Http\Controllers\Api\BannerCategoryController;

use App\Http\Controllers\Api\JobListingController;

use App\Http\Controllers\Api\JobApplicationController;

use App\Http\Controllers\Api\JobSeekerController as ApiJobSeekerController;

use App\Http\Controllers\Api\JobUpsellController as ApiJobUpsellController;

use App\Http\Controllers\Api\JobAlertController as ApiJobAlertController;

use App\Http\Controllers\Api\BannerUploadController;

use App\Http\Controllers\Api\BannerMarketplaceController;

use App\Http\Controllers\Api\BookAdvertController;

use App\Http\Controllers\Api\AuthorController;

use App\Http\Controllers\Api\VehiclesAdvertController;

use App\Http\Controllers\Api\VehicleController;

use App\Http\Controllers\Api\VehicleCategoryController;

use App\Http\Controllers\Api\JobController;

use App\Http\Controllers\Api\JobCategoryController;

use App\Http\Controllers\Api\JobSeekerController;

use App\Http\Controllers\Api\SponsoredAdvertController;

use App\Http\Controllers\Api\SponsoredCategoryController;

use App\Http\Controllers\Api\SponsoredPricingPlanController;

use App\Http\Controllers\Api\FeaturedAdvertController;

use App\Http\Controllers\Api\FeaturedAdvertBannerController;

use App\Http\Controllers\Api\PropertyController;

use App\Http\Controllers\Api\PropertyUpsellController;

use App\Http\Controllers\Api\TwoFactorController;

use App\Http\Controllers\Api\CustomerNotificationController;

use App\Http\Controllers\Api\UserInsightsController;

use App\Http\Controllers\Api\BuySellController;

use App\Http\Controllers\Api\BuySellUploadController;

use App\Http\Controllers\Api\BusinessTemplateController;

use App\Http\Controllers\Api\PromotedAdvertCategoryController;

use App\Http\Controllers\Api\PromotedAdvertController;

use App\Http\Controllers\Api\FundingProjectController;

use App\Http\Controllers\Api\FundingPledgeController;

use App\Http\Controllers\Api\DonationController;

use App\Http\Controllers\AdminAnalyticsController;

use App\Http\Controllers\Api\ServiceReviewController;

use App\Http\Controllers\Api\ProviderController;

use App\Http\Controllers\Api\SearchController;

use App\Http\Controllers\Api\PromotionController;

use App\Http\Controllers\Api\FileUploadController;

use App\Http\Controllers\Api\EventsVenuesController;

use App\Http\Controllers\Api\CommunityController;

use App\Http\Controllers\Api\CommunityPostController;

use App\Http\Controllers\Api\CommentController;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;



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

    Route::group(['prefix' => 'auth', 'middleware' => 'jwt.auth'], function () {

        Route::get('/logout', [AuthController::class, 'logout']);

        Route::get('/user-profile', [AuthController::class, 'userProfile']);

        Route::post('/change-password', [AuthController::class, 'changePassword']);

        Route::get('/2fa/status', [TwoFactorController::class, 'status']);
        Route::post('/2fa/setup', [TwoFactorController::class, 'setup']);
        Route::post('/2fa/confirm', [TwoFactorController::class, 'confirm']);
        Route::post('/2fa/disable', [TwoFactorController::class, 'disable']);

    });



    // no auth

    Route::group(['prefix' => 'auth'], function () {

        Route::post('/register', [AuthController::class, 'register']);

        Route::post('/login', [AuthController::class, 'login']);

        Route::post('/login-admin', [AuthController::class, 'loginAdmin']);

        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

        Route::post('/reset-password', [AuthController::class, 'resetPassword']);

        Route::post('/2fa/verify-login', [TwoFactorController::class, 'verifyLogin']);



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

    // Email / phone / company verification (anti-scam signup & post forms)
    Route::prefix('verification')->group(function () {
        Route::post('/email/send', [VerificationController::class, 'sendEmailOtp']);
        Route::post('/email/verify', [VerificationController::class, 'verifyEmailOtp']);
        Route::post('/phone/send', [VerificationController::class, 'sendPhoneOtp']);
        Route::post('/phone/verify', [VerificationController::class, 'verifyPhoneOtp']);
        Route::post('/company/check', [VerificationController::class, 'checkCompany']);
    });

    // Health check endpoint
    Route::get('/health', function () {
        return response()->json([
            'status' => 'OK',
            'message' => 'API is working',
            'timestamp' => now(),
            'app_url' => config('app.url'),
            'environment' => config('app.env')
        ]);
    });



    // CORS test endpoint
    Route::get('/cors-test', function () {
        return response()->json([
            'message' => 'CORS is working',
            'origin' => request()->header('Origin'),
            'timestamp' => now()->toISOString()
        ]);
    });

    // Test route to verify registration
    Route::get('/test-route', function () {
        return response()->json(['message' => 'Test route works']);
    });

    // Health check endpoint
    Route::get('/health', function () {

        return response()->json([

            'status' => 'OK',

            'message' => 'API is working',

            'timestamp' => now(),

            'app_url' => config('app.url'),

            'environment' => config('app.env')

        ]);

    });

    // Debug authentication endpoint
    Route::get('/debug-auth', function (Request $request) {
        return response()->json([
            'authenticated' => auth('api')->check(),
            'user' => auth('api')->user() ? [
                'id' => auth('api')->user()->customer_id,
                'email' => auth('api')->user()->email,
                'name' => auth('api')->user()->first_name . ' ' . auth('api')->user()->last_name,
                'type' => get_class(auth('api')->user()),
            ] : null,
            'token_present' => $request->bearerToken() ? 'Yes' : 'No',
            'auth_header' => $request->header('Authorization'),
            'guard_check' => [
                'default' => auth()->check(),
                'api' => auth('api')->check(),
            ],
            'guards' => array_keys(config('auth.guards')),
        ]);
    });

    // Debug policy endpoint
    Route::get('/debug-policy', function (Request $request) {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['error' => 'Not authenticated via API guard'], 401);
        }
        
        return response()->json([
            'user_info' => [
                'id' => $user->customer_id,
                'email' => $user->email,
                'type' => get_class($user),
            ],
            'policy_checks' => [
                'can_create_vehicle' => $user->can('create', \App\Models\Vehicle::class),
                'is_authenticated_method' => $user->isAuthenticated(),
                'is_admin_method' => $user->isAdmin(),
            ],
            'auth_checks' => [
                'auth_api_check' => auth('api')->check(),
                'auth_default_check' => auth()->check(),
            ],
        ]);
    })->middleware('auth:api');

    // Test vehicle store authorization
    Route::get('/test-vehicle-store-auth', function (Request $request) {
        // Simulate the StoreVehicleRequest authorization check
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'error' => 'Not authenticated',
                'auth_api_check' => auth('api')->check(),
                'auth_default_check' => auth()->check(),
                'bearer_token' => $request->bearerToken() ? 'Present' : 'Missing',
            ], 401);
        }
        
        // Test the policy check that's failing
        try {
            $canCreate = $user->can('create', \App\Models\Vehicle::class);
            return response()->json([
                'success' => true,
                'can_create_vehicle' => $canCreate,
                'user_type' => get_class($user),
                'user_id' => $user->customer_id,
                'is_authenticated_method' => $user->isAuthenticated(),
                'is_admin_method' => $user->isAdmin(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Policy check failed',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    })->middleware('auth:api');

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

    Route::group(['prefix' => 'listing-approval', 'middleware' => 'jwt.auth'], function () {

        Route::get('/pending', [ListingApprovalController::class, 'pending']);

        Route::get('/harmful', [ListingApprovalController::class, 'harmful']);

        Route::get('/statistics', [ListingApprovalController::class, 'statistics']);

        Route::post('/{listingId}/approve', [ListingApprovalController::class, 'approve']);

        Route::post('/{listingId}/reject', [ListingApprovalController::class, 'reject']);

        Route::post('/{listingId}/mark-harmful', [ListingApprovalController::class, 'markHarmful']);

    });



    // KYC verification

    Route::group(['prefix' => 'kyc', 'middleware' => 'jwt.auth'], function () {

        Route::get('/status', [KycController::class, 'status']);

        Route::post('/submit', [KycController::class, 'submit']);

        Route::get('/pending', [KycController::class, 'pending']);

        Route::post('/{userId}/approve', [KycController::class, 'approve']);

        Route::post('/{userId}/reject', [KycController::class, 'reject']);

        Route::get('/statistics', [KycController::class, 'statistics']);

    });



    // Ad moderation and management

    Route::group(['prefix' => 'ads', 'middleware' => 'jwt.auth'], function () {

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

    Route::group(['prefix' => 'business', 'middleware' => 'jwt.auth'], function () {

        Route::post('/', [BusinessController::class, 'store']);

        Route::put('/{id}', [BusinessController::class, 'update']);

        Route::delete('/{id}', [BusinessController::class, 'destroy']);

        Route::get('/my-business', [BusinessController::class, 'myBusiness']);

    });

    Route::group(['prefix' => 'business'], function () {

        Route::get('/', [BusinessController::class, 'index']);

        Route::get('/{id}', [BusinessController::class, 'show']);

        Route::get('/{slug}', [BusinessController::class, 'getBySlug']);

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



    // vehicles-adverts (new comprehensive system)

    Route::group(['prefix' => 'vehicles-adverts'], function () {

        // Public routes - specific routes must come before dynamic routes

        Route::get('/', [VehiclesAdvertController::class, 'index']);

        Route::get('/featured', [VehiclesAdvertController::class, 'featured']);

        Route::get('/most-viewed', [VehiclesAdvertController::class, 'mostViewed']);

        Route::get('/recent', [VehiclesAdvertController::class, 'recent']);

        Route::get('/vehicle-types', [VehiclesAdvertController::class, 'getVehicleTypes']);

        Route::get('/categories', [VehiclesAdvertController::class, 'getCategories']);

        Route::get('/categories-for-filters', [VehiclesAdvertController::class, 'getCategoriesForFilters']);

        Route::get('/makes', [VehiclesAdvertController::class, 'getVehicleMakes']);

        Route::get('/models/{makeId}', [VehiclesAdvertController::class, 'getVehicleModels']);

        Route::get('/promotion-tiers', [VehiclesAdvertController::class, 'getPromotionTiers']);

        Route::get('/statistics', [VehiclesAdvertController::class, 'getStatistics']);

        Route::post('/upload', [VehiclesAdvertController::class, 'uploadImage']);

        Route::get('/slug/{slug}', [VehiclesAdvertController::class, 'showBySlug']);

        // Authenticated static routes before /{id}
        Route::middleware('jwt.auth')->group(function () {
            Route::get('/my-vehicles', [VehiclesAdvertController::class, 'myVehicles']);
        });

        Route::get('/{id}', [VehiclesAdvertController::class, 'show']);

        Route::post('/{id}/views', [VehiclesAdvertController::class, 'trackViews']);

        Route::post('/{id}/contact', [VehiclesAdvertController::class, 'contactSeller']);



        // Authenticated routes

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [VehiclesAdvertController::class, 'store']);

            Route::put('/{id}', [VehiclesAdvertController::class, 'update']);

            Route::delete('/{id}', [VehiclesAdvertController::class, 'destroy']);

            Route::post('/{id}/save', [VehiclesAdvertController::class, 'saveVehicle']);

            Route::post('/{id}/payment', [VehiclesAdvertController::class, 'processPayment']);

        });

    });



    // images-adverts (Stock Images & Media category)

    Route::group(['prefix' => 'images-adverts'], function () {

        // Public routes

        Route::get('/', [ImagesAdvertController::class, 'index']);

        Route::get('/featured', [ImagesAdvertController::class, 'featuredImages']);

        Route::get('/trending', [ImagesAdvertController::class, 'trendingImages']);

        Route::get('/popular', [ImagesAdvertController::class, 'popularImages']);

        Route::get('/categories', [ImagesAdvertController::class, 'categories']);

        Route::get('/license-types', [ImagesAdvertController::class, 'licenseTypes']);

        Route::get('/promotion-tiers', [ImagesAdvertController::class, 'promotionTiers']);

        Route::get('/statistics', [ImagesAdvertController::class, 'statistics']);

        Route::get('/{slug}', [ImagesAdvertController::class, 'show']);

        Route::post('/upload', [ImagesAdvertController::class, 'uploadImage']);

        Route::post('/upload-multiple', [ImagesAdvertController::class, 'uploadMultipleImages']);

        Route::post('/{id}/views', [ImagesAdvertController::class, 'incrementViews']);

        Route::post('/{id}/save', [ImagesAdvertController::class, 'saveImage']);

        Route::post('/{id}/payment', [ImagesAdvertController::class, 'processPayment']);



        // Authenticated routes

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [ImagesAdvertController::class, 'store']);

            Route::put('/{id}', [ImagesAdvertController::class, 'update']);

            Route::delete('/{id}', [ImagesAdvertController::class, 'destroy']);

            Route::get('/my-images', [ImagesAdvertController::class, 'myImages']);

            Route::post('/{id}/verify', [ImagesAdvertController::class, 'verify']);

        });

    });



    // books

    Route::group(['prefix' => 'books'], function () {

        Route::get('/', [BookController::class, 'index']);

        Route::get('/{id}', [BookController::class, 'show']);

        Route::post('/', [BookController::class, 'store'])->middleware('auth:api');

        Route::post('/{id}/purchase', [BookController::class, 'purchase'])->middleware('auth:api');

        Route::get('/download/{token}', [BookController::class, 'download']);

        Route::get('/my-purchases', [BookController::class, 'myPurchases'])->middleware('auth:api');

        Route::get('/statistics', [BookController::class, 'statistics']);

        Route::get('/trending-genres', [BookController::class, 'trendingGenres']);

        Route::get('/featured', [BookController::class, 'featured']);

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

    Route::group(['prefix' => 'ad-pricing-plans', 'middleware' => 'jwt.auth'], function () {

        Route::get('/', [AdPricingPlanController::class, 'index']);

        Route::post('/', [AdPricingPlanController::class, 'store']);

        Route::put('/{id}', [AdPricingPlanController::class, 'update']);

        Route::delete('/{id}', [AdPricingPlanController::class, 'destroy']);

    });



    // candidate profiles

    Route::group(['prefix' => 'candidate-profile', 'middleware' => 'jwt.auth'], function () {

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

    Route::group(['prefix' => 'job-upsell', 'middleware' => 'jwt.auth'], function () {

        Route::get('/', [JobUpsellController::class, 'index']); // List all job upsells for user

        Route::post('/', [JobUpsellController::class, 'store']);

        Route::post('/{id}/complete-payment', [JobUpsellController::class, 'completePayment']);

        Route::get('/listing/{listingId}', [JobUpsellController::class, 'getByListing']);

    });



    // candidate upsells

    Route::group(['prefix' => 'candidate-upsell', 'middleware' => 'jwt.auth'], function () {

        Route::get('/', [CandidateUpsellController::class, 'index']);

        Route::post('/', [CandidateUpsellController::class, 'store']);

        Route::post('/{id}/complete-payment', [CandidateUpsellController::class, 'completePayment']);

        Route::get('/profile/{profileId}', [CandidateUpsellController::class, 'getByProfile']);

    });



    // job alerts

    Route::group(['prefix' => 'job-alert', 'middleware' => 'jwt.auth'], function () {

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

    Route::group(['prefix' => 'dashboard', 'middleware' => 'jwt.auth'], function () {

        Route::get('/user', [DashboardController::class, 'userDashboard']);

        Route::get('/admin', [DashboardController::class, 'adminDashboard']);

        Route::get('/insights', UserInsightsController::class);

    });

    Route::group(['prefix' => 'notifications', 'middleware' => 'jwt.auth'], function () {
        Route::get('/', [CustomerNotificationController::class, 'index']);
        Route::get('/unread-count', [CustomerNotificationController::class, 'unreadCount']);
        Route::get('/settings', [CustomerNotificationController::class, 'settings']);
        Route::put('/settings', [CustomerNotificationController::class, 'updateSettings']);
        Route::put('/mark-read', [CustomerNotificationController::class, 'markMultipleAsRead']);
        Route::put('/mark-all-read', [CustomerNotificationController::class, 'markAllAsRead']);
        Route::delete('/delete-all', [CustomerNotificationController::class, 'destroyAll']);
        Route::put('/{id}/read', [CustomerNotificationController::class, 'markAsRead']);
        Route::delete('/{id}', [CustomerNotificationController::class, 'destroy']);
    });



    // analytics

    Route::group(['prefix' => 'analytics', 'middleware' => 'jwt.auth'], function () {

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

    Route::group(['prefix' => 'chat', 'middleware' => 'jwt.auth'], function () {

        Route::get('/conversations', [ChatController::class, 'getConversations']);

        Route::get('/unread-count', [ChatController::class, 'getUnreadCount']);

    });



    // staff management

    Route::group(['prefix' => 'staff', 'middleware' => 'jwt.auth'], function () {

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

    Route::group(['prefix' => 'upsell', 'middleware' => 'jwt.auth'], function () {

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

        // Handle OPTIONS preflight request
        Route::options('/', function () {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range,Authorization,X-CSRF-TOKEN');
        });

        Route::get('/', [ServiceController::class, 'index']);

        Route::get('/popular', [ServiceController::class, 'getPopularServices']);

        Route::get('/featured', [ServiceController::class, 'getFeaturedServices']);

        Route::get('/categories', [ServiceController::class, 'getCategories']);

        Route::get('/form-schema', [ServiceController::class, 'getFormSchema']);

        Route::get('/promotion-options', [ServiceController::class, 'getPromotionOptions']);

        Route::get('/my-services', [ServiceController::class, 'myServices'])->middleware('jwt.auth');

        Route::get('/{service}', [ServiceController::class, 'show']);

        Route::post('/{service}/enquiries', [ServiceController::class, 'incrementEnquiries']);



        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [ServiceController::class, 'store']);

            Route::put('/{service}', [ServiceController::class, 'update']);

            Route::delete('/{service}', [ServiceController::class, 'destroy']);

            Route::post('/{service}/toggle-status', [ServiceController::class, 'toggleStatus']);

            Route::post('/{service}/media', [ServiceController::class, 'uploadMedia']);

            Route::delete('/{service}/media/{media}', [ServiceController::class, 'deleteMedia']);

            Route::post('/{service}/purchase-promotion', [ServiceController::class, 'purchasePromotion']);

        });

    });



    // service analytics

    Route::group(['prefix' => 'service-analytics'], function () {

        Route::get('/live-activity', [ServiceAnalyticsController::class, 'getLiveActivityFeed']);

        Route::get('/trending', [ServiceAnalyticsController::class, 'getTrendingServices']);

        Route::get('/marketplace-stats', [ServiceAnalyticsController::class, 'getMarketplaceStats']);

    });

    // services-solutions (alias for frontend compatibility)
    Route::group(['prefix' => 'services-solutions'], function () {

        Route::get('/trending', [ServiceAnalyticsController::class, 'getTrendingServices']);

        Route::get('/search', [ServiceController::class, 'index']);

    });



    // service comparison

    Route::group(['prefix' => 'service-comparison', 'middleware' => 'jwt.auth'], function () {

        Route::post('/compare', [ServiceComparisonController::class, 'compare']);

        Route::post('/save-comparison', [ServiceComparisonController::class, 'saveComparison']);

    });



    // service orders

    Route::group(['prefix' => 'service-orders', 'middleware' => 'jwt.auth'], function () {

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



    // service reviews
    Route::group(['prefix' => 'reviews'], function () {

        Route::get('/service/{serviceId}', [ServiceReviewController::class, 'index']);

        

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/service/{serviceId}', [ServiceReviewController::class, 'store']);

            Route::put('/{reviewId}', [ServiceReviewController::class, 'update']);

            Route::delete('/{reviewId}', [ServiceReviewController::class, 'destroy']);

        });

    });



    // providers
    Route::group(['prefix' => 'providers'], function () {

        Route::get('/{id}', [ProviderController::class, 'show']);

        Route::get('/{id}/services', [ProviderController::class, 'services']);

        Route::get('/{id}/reviews', [ProviderController::class, 'reviews']);

        Route::get('/{id}/followers', [ProviderController::class, 'followers']);

        Route::get('/{id}/following', [ProviderController::class, 'following']);

        

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/{id}/follow', [ProviderController::class, 'follow']);

            Route::delete('/{id}/follow', [ProviderController::class, 'unfollow']);

        });

    });



    // search and filtering
    Route::group(['prefix' => 'search'], function () {

        Route::get('/services', [SearchController::class, 'services']);

        Route::get('/suggestions', [SearchController::class, 'suggestions']);

        Route::get('/popular', [SearchController::class, 'popular']);

        Route::get('/trending', [SearchController::class, 'trending']);

    });



    // promotions
    Route::group(['prefix' => 'promotions'], function () {

        Route::get('/tiers', [PromotionController::class, 'tiers']);

        Route::post('/calculate-total', [PromotionController::class, 'calculateTotal']);

        

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/purchase', [PromotionController::class, 'purchase']);

            Route::get('/my-promotions', [PromotionController::class, 'myPromotions']);

            Route::post('/{id}/cancel', [PromotionController::class, 'cancel']);

        });

    });



    // analytics
    Route::group(['prefix' => 'analytics'], function () {

        Route::get('/dashboard', [ServiceAnalyticsController::class, 'dashboard']);

        Route::get('/provider/{id}', [ServiceAnalyticsController::class, 'provider']);

        Route::get('/service/{id}', [ServiceAnalyticsController::class, 'service']);

    });



    // file upload
    Route::group(['prefix' => 'upload', 'middleware' => 'jwt.auth'], function () {

        Route::post('/service-media', [FileUploadController::class, 'uploadServiceMedia']);

        Route::post('/avatar', [FileUploadController::class, 'uploadAvatar']);

        Route::delete('/{fileId}', [FileUploadController::class, 'delete']);

        Route::get('/{fileId}', [FileUploadController::class, 'getFileInfo']);

    });



    // affiliate programs

    Route::group(['prefix' => 'affiliate-programs'], function () {

        Route::get('/', [AffiliateProgramController::class, 'index']);

        Route::get('/featured', [AffiliateProgramController::class, 'getFeaturedPrograms']);

        Route::get('/networks', [AffiliateProgramController::class, 'getNetworks']);

        Route::get('/{program}', [AffiliateProgramController::class, 'show']);

        Route::post('/{program}/track-click', [AffiliateProgramController::class, 'trackClick']);

        Route::post('/record-conversion', [AffiliateProgramController::class, 'recordConversion']);



        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [AffiliateProgramController::class, 'store']);

            Route::put('/{program}', [AffiliateProgramController::class, 'update']);

            Route::delete('/{program}', [AffiliateProgramController::class, 'destroy']);

            Route::get('/my-programs', [AffiliateProgramController::class, 'myPrograms']);

            Route::get('/stats', [AffiliateProgramController::class, 'getProgramStats']);

            Route::post('/join-our-program', [AffiliateProgramController::class, 'joinOurProgram']);

        });

    }); // affiliate-programs



    // affiliate posts (new comprehensive system)

    Route::group(['prefix' => 'affiliate-posts'], function () {

        // Public routes

        Route::get('/list', [App\Http\Controllers\Api\AffiliatePostController::class, 'index']);

        Route::get('/{id}', [App\Http\Controllers\Api\AffiliatePostController::class, 'show']);

        Route::get('/category/{categoryId}', [App\Http\Controllers\Api\AffiliatePostController::class, 'getByCategory']);

        Route::get('/featured', [App\Http\Controllers\Api\AffiliatePostController::class, 'getFeatured']);

        Route::get('/sponsored', [App\Http\Controllers\Api\AffiliatePostController::class, 'getSponsored']);

        Route::get('/promoted', [App\Http\Controllers\Api\AffiliatePostController::class, 'getPromoted']);



        // Authenticated routes

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [App\Http\Controllers\Api\AffiliatePostController::class, 'store']);

            Route::put('/{id}', [App\Http\Controllers\Api\AffiliatePostController::class, 'update']);

            Route::delete('/{id}', [App\Http\Controllers\Api\AffiliatePostController::class, 'destroy']);

            Route::get('/my-posts', [App\Http\Controllers\Api\AffiliatePostController::class, 'myPosts']);

        });

    });

    // Main affiliate system routes (comprehensive system)
    Route::group(['prefix' => 'affiliates'], function () {

        // Public routes
        Route::get('/categories', [ApiAffiliateController::class, 'categories']);
        Route::get('/business-offers', [ApiAffiliateController::class, 'businessOffers']);
        Route::get('/business-offers/{id}', [ApiAffiliateController::class, 'businessOffer']);
        Route::get('/user-posts', [ApiAffiliateController::class, 'userPosts']);
        Route::get('/user-posts/{id}', [ApiAffiliateController::class, 'userPost']);
        Route::get('/upsell-plans', [ApiAffiliateController::class, 'upsellPlans']);
        Route::get('/search', [ApiAffiliateController::class, 'search']);
        Route::post('/track-click', [ApiAffiliateController::class, 'trackClick']);

        // Authenticated routes
        Route::group(['middleware' => 'jwt.auth'], function () {
            // File upload
            Route::post('/upload-image', [ApiAffiliateController::class, 'uploadImage']);
            
            // Business offer management
            Route::post('/business-offers', [ApiAffiliateController::class, 'createBusinessOffer']);
            Route::put('/business-offers/{id}', [ApiAffiliateController::class, 'updateBusinessOffer']);
            Route::delete('/business-offers/{id}', [ApiAffiliateController::class, 'deleteBusinessOffer']);
            
            // User post management
            Route::post('/user-posts', [ApiAffiliateController::class, 'createUserPost']);
            Route::put('/user-posts/{id}', [ApiAffiliateController::class, 'updateUserPost']);
            Route::delete('/user-posts/{id}', [ApiAffiliateController::class, 'deleteUserPost']);
            
            // Applications
            Route::post('/business-offers/{offerId}/apply', [ApiAffiliateController::class, 'applyToPromote']);
            Route::get('/my-applications', [ApiAffiliateController::class, 'myApplications']);
            
            // User's own content
            Route::get('/my-business-offers', [ApiAffiliateController::class, 'myBusinessOffers']);
            Route::get('/my-user-posts', [ApiAffiliateController::class, 'myUserPosts']);
        });

        Route::get('/plans', [App\Http\Controllers\Api\AffiliateUpsellController::class, 'getPlans']);

        Route::get('/comparison', [App\Http\Controllers\Api\AffiliateUpsellController::class, 'getComparison']);

        Route::get('/recommendation', [App\Http\Controllers\Api\AffiliateUpsellController::class, 'getRecommendation']);

        Route::post('/purchase', [App\Http\Controllers\Api\AffiliateUpsellController::class, 'purchaseUpsell']);

        Route::get('/my-upsells', [App\Http\Controllers\Api\AffiliateUpsellController::class, 'getMyUpsells']);

        Route::get('/post/{postId}', [App\Http\Controllers\Api\AffiliateUpsellController::class, 'getPostUpsells']);

        Route::post('/{id}/cancel', [App\Http\Controllers\Api\AffiliateUpsellController::class, 'cancelUpsell']);

        Route::get('/stats', [App\Http\Controllers\Api\AffiliateUpsellController::class, 'getStats']);

    });

    // Test route to verify API routes are working
    Route::get('/test-api', function() {
        return response()->json([
            'success' => true,
            'message' => 'API routes are working',
            'timestamp' => now()
        ]);
    });

    // stores

    Route::group(['prefix' => 'store'], function () {

        // Authenticated routes first to avoid conflicts

        Route::group(['middleware' => 'jwt.auth'], function () {

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

    Route::group(['prefix' => 'admin/category-posts', 'middleware' => 'jwt.auth'], function () {

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

    Route::group(['prefix' => 'admin/moderation', 'middleware' => 'jwt.auth'], function () {

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

    Route::group(['prefix' => 'admin/notifications', 'middleware' => 'jwt.auth'], function () {

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

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::get('/my', [ReferralController::class, 'getMyReferral']);

            Route::post('/create', [ReferralController::class, 'createReferral']);

            Route::put('/{referral_id}', [ReferralController::class, 'updateReferral']);

            Route::get('/history', [ReferralController::class, 'getMyReferralHistory']);

            Route::get('/{referral_id}/share', [ReferralController::class, 'shareReferral']);

        });

    });



    // User Analytics Dashboard

    Route::group(['prefix' => 'user-analytics', 'middleware' => 'jwt.auth'], function () {

        Route::get('/dashboard', [UserAnalyticsController::class, 'getDashboard']);

        Route::get('/listing-analytics', [UserAnalyticsController::class, 'getListingAnalytics']);

        Route::get('/profile-analytics', [UserAnalyticsController::class, 'getProfileAnalytics']);

        Route::get('/export', [UserAnalyticsController::class, 'exportAnalytics']);

    });



    // Admin Analytics Dashboard (with role-based permissions)

    Route::group(['prefix' => 'admin-analytics', 'middleware' => 'jwt.auth'], function () {

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

        Route::group(['middleware' => 'jwt.auth'], function () {

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

        Route::group(['middleware' => 'jwt.auth'], function () {

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

        Route::group(['middleware' => 'jwt.auth'], function () {

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

    Route::group(['prefix' => 'upsells', 'middleware' => 'jwt.auth'], function () {

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

        // Public routes - specific routes must come before dynamic ones

        Route::get('/', [ResortsTravelController::class, 'index']);

        Route::get('/featured', [ResortsTravelController::class, 'featuredAdverts']);

        Route::get('/advert-types', [ResortsTravelController::class, 'advertTypes']);

        Route::get('/amenities', [ResortsTravelController::class, 'amenities']);

        Route::get('/promotion-tiers', [ResortsTravelController::class, 'promotionTiers']);

        Route::get('/statistics', [ResortsTravelController::class, 'statistics']);

        Route::get('/trending', [ResortsTravelController::class, 'trendingDestinations']);

        Route::get('/nearby', [ResortsTravelController::class, 'nearbyAdverts']);

        Route::get('/my-adverts', [ResortsTravelController::class, 'myAdverts'])->middleware('jwt.auth');

        Route::get('/my-bookings', [ResortsTravelController::class, 'getMyBookings'])->middleware('jwt.auth');

        Route::get('/{id}/availability', [ResortsTravelController::class, 'getAvailability']);

        Route::get('/{id}/check-availability', [ResortsTravelController::class, 'checkAvailabilityPricing']);

        Route::get('/{id}/reviews', [ResortsTravelController::class, 'getReviews']);

        Route::post('/{id}/views', [ResortsTravelController::class, 'incrementViews']);

        // Dynamic slug route must come last
        Route::get('/{slug}', [ResortsTravelController::class, 'show']);



        // Authenticated routes

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [ResortsTravelController::class, 'store']);

            Route::put('/{id}', [ResortsTravelController::class, 'update']);

            Route::delete('/{id}', [ResortsTravelController::class, 'destroy']);

            Route::post('/upload-images', [ResortsTravelController::class, 'uploadImages']);

            Route::post('/upload-logo', [ResortsTravelController::class, 'uploadLogo']);

            Route::post('/{id}/book', [ResortsTravelController::class, 'createBooking']);

            Route::post('/{id}/reviews', [ResortsTravelController::class, 'addReview']);

            Route::post('/{id}/report', [ResortsTravelController::class, 'reportAdvert']);

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

        Route::group(['middleware' => 'jwt.auth'], function () {

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

        Route::get('/promotion-options', [BannerAdController::class, 'promotionOptions']);

        // Authenticated static routes before /{slug}
        Route::middleware('jwt.auth')->group(function () {
            Route::get('/my-banners', [BannerAdController::class, 'myBanners']);
        });

        Route::get('/{slug}', [BannerAdController::class, 'show']);

        Route::post('/{slug}/track-click', [BannerAdController::class, 'trackClick']);



        // Authenticated routes

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [BannerAdController::class, 'store']);

            Route::put('/{id}', [BannerAdController::class, 'update']);

            Route::delete('/{id}', [BannerAdController::class, 'destroy']);

        });

    });



    // Banner Upload System

    Route::group(['prefix' => 'banner-upload', 'middleware' => 'jwt.auth'], function () {

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

        Route::group(['middleware' => 'jwt.auth'], function () {

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

        Route::get('/promotion-options', [PromotedAdvertController::class, 'promotionOptions']);

        Route::get('/statistics', [PromotedAdvertController::class, 'statistics']);

        Route::get('/live-activity', [PromotedAdvertController::class, 'liveActivity']);

        Route::get('/trending-countries', [PromotedAdvertController::class, 'trendingCountries']);

        Route::get('/trending-categories', [PromotedAdvertController::class, 'trendingCategories']);

        Route::get('/{slug}', [PromotedAdvertController::class, 'show'])->where('slug', '^[a-zA-Z0-9-_]+$');

        Route::post('/{slug}/track-click', [PromotedAdvertController::class, 'trackClick'])->where('slug', '^[a-zA-Z0-9-_]+$');



        // Authenticated routes

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [PromotedAdvertController::class, 'store']);

            Route::put('/{id}', [PromotedAdvertController::class, 'update'])->where('id', '^[0-9]+$');

            Route::delete('/{id}', [PromotedAdvertController::class, 'destroy'])->where('id', '^[0-9]+$');

            Route::get('/my-adverts', [PromotedAdvertController::class, 'myAdverts']);

            Route::post('/upload-images', [PromotedAdvertController::class, 'uploadImages']);

            Route::post('/upload-logo', [PromotedAdvertController::class, 'uploadLogo']);

            Route::post('/{id}/toggle-favorite', [PromotedAdvertController::class, 'toggleFavorite'])->where('id', '^[0-9]+$');

        });

    });



    // Promoted Advert Categories

    Route::group(['prefix' => 'promoted-advert-categories'], function () {

        // Public routes

        Route::get('/', [PromotedAdvertCategoryController::class, 'index']);

        Route::get('/popular', [PromotedAdvertCategoryController::class, 'popular']);

        Route::get('/{slug}', [PromotedAdvertCategoryController::class, 'show'])->where('slug', '^[a-zA-Z0-9-_]+$');

        Route::get('/{slug}/adverts', [PromotedAdvertCategoryController::class, 'categoryAdverts'])->where('slug', '^[a-zA-Z0-9-_]+$');



        // Admin routes (require auth)

        Route::group(['middleware' => 'jwt.auth'], function () {

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

        Route::get('/statistics', [BookAdvertController::class, 'getStatistics']);

        Route::get('/trending-genres', [BookAdvertController::class, 'getTrendingGenres']);

        Route::post('/{book}/views', [BookAdvertController::class, 'trackViews']);

        Route::get('/my-books', [BookAdvertController::class, 'myBooks'])->middleware('jwt.auth');

        Route::get('/{slug}', [BookAdvertController::class, 'show']);



        // Authenticated user routes

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [BookAdvertController::class, 'store']);

            Route::put('/{book}', [BookAdvertController::class, 'update']);

            Route::delete('/{book}', [BookAdvertController::class, 'destroy']);

            Route::post('/{book}/save', [BookAdvertController::class, 'saveBook']);

            Route::post('/{book}/payment', [BookAdvertController::class, 'processPayment']);

        });

    });



    // Business templates for sale (pitch decks, grants, plans)
    Route::group(['prefix' => 'business-templates'], function () {
        Route::get('/', [BusinessTemplateController::class, 'index']);
        Route::get('/browse', [BusinessTemplateController::class, 'browse']);
        Route::get('/download/{token}', [BusinessTemplateController::class, 'download']);
        Route::get('/my-templates', [BusinessTemplateController::class, 'myTemplates'])->middleware('jwt.auth');
        Route::get('/my-purchases', [BusinessTemplateController::class, 'myPurchases'])->middleware('jwt.auth');
        Route::post('/purchase', [BusinessTemplateController::class, 'purchase'])->middleware('jwt.auth');
        Route::get('/{slug}', [BusinessTemplateController::class, 'show']);

        Route::middleware('jwt.auth')->group(function () {
            Route::post('/', [BusinessTemplateController::class, 'store']);
            Route::put('/{id}', [BusinessTemplateController::class, 'update'])->whereNumber('id');
            Route::delete('/{id}', [BusinessTemplateController::class, 'destroy'])->whereNumber('id');
        });
    });

    // Buy & Sell Marketplace System

    Route::group(['prefix' => 'buysell'], function () {

        // Public routes - specific routes first

        Route::get('/', [BuySellController::class, 'index']);

        Route::get('/stats', [BuySellController::class, 'stats']);

        Route::get('/statistics', [BuySellController::class, 'statistics']);

        Route::get('/browse', [BuySellController::class, 'browse']);

        Route::get('/featured', [BuySellController::class, 'featured']);

        Route::get('/recent', [BuySellController::class, 'recent']);

        Route::get('/promotion-plans', [BuySellController::class, 'promotionPlans']);

        Route::get('/search', [BuySellController::class, 'search']);

        Route::get('/activities', [BuySellController::class, 'activities']);

        // Authenticated static routes before /{slug}
        Route::middleware('jwt.auth')->group(function () {
            Route::get('/my-adverts', [BuySellController::class, 'myAdverts']);
            Route::get('/saved-adverts', [BuySellController::class, 'savedAdverts']);
        });

        Route::get('/{slug}', [BuySellController::class, 'show']);



        // Authenticated routes

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [BuySellController::class, 'store']);

            Route::put('/{id}', [BuySellController::class, 'update']);

            Route::delete('/{id}', [BuySellController::class, 'destroy']);

            Route::post('/{id}/save', [BuySellController::class, 'saveAdvert']);

            Route::delete('/{id}/unsave', [BuySellController::class, 'unsaveAdvert']);

            Route::post('/{id}/contact', [BuySellController::class, 'contactSeller']);

            Route::get('/{id}/analytics', [BuySellController::class, 'analytics']);

            Route::post('/{id}/report', [BuySellController::class, 'reportAdvert']);

        });

    });



    // Buy & Sell Categories

    Route::group(['prefix' => 'buysell-categories'], function () {

        // Public routes

        Route::get('/', [BuySellCategoryController::class, 'index']);

        Route::get('/featured', [BuySellCategoryController::class, 'featured']);

        Route::get('/popular', [BuySellCategoryController::class, 'popular']);

        Route::get('/tree', [BuySellCategoryController::class, 'tree']);

        Route::get('/{slug}', [BuySellCategoryController::class, 'show']);

        Route::get('/{slug}/adverts', [BuySellCategoryController::class, 'adverts']);

        Route::get('/{id}/subcategories', [BuySellCategoryController::class, 'subcategories']);



        // Admin routes (require auth and permissions)

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [BuySellCategoryController::class, 'store']);

            Route::put('/{id}', [BuySellCategoryController::class, 'update']);

            Route::delete('/{id}', [BuySellCategoryController::class, 'destroy']);

        });

    });



    // Buy & Sell Items (Alternative API)

    Route::group(['prefix' => 'buy-sell-items'], function () {

        // Public routes - specific routes first

        Route::get('/', [BuySellItemController::class, 'index']);

        Route::get('/stats', [BuySellItemController::class, 'stats']);

        Route::get('/featured', [BuySellItemController::class, 'featured']);

        Route::get('/{id}', [BuySellItemController::class, 'show']);



        // Authenticated routes

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [BuySellItemController::class, 'store']);

            Route::put('/{id}', [BuySellItemController::class, 'update']);

            Route::delete('/{id}', [BuySellItemController::class, 'destroy']);

            Route::get('/my-items', [BuySellItemController::class, 'myItems']);

        });

    });



    // Buy & Sell Promotions

    Route::group(['prefix' => 'buysell-promotions'], function () {

        // Public routes

        Route::get('/plans', [BuySellPromotionController::class, 'plans']);

        Route::get('/features', [BuySellPromotionController::class, 'features']);



        // Authenticated routes

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/purchase', [BuySellPromotionController::class, 'purchase']);

            Route::get('/my-promotions', [BuySellPromotionController::class, 'myPromotions']);

            Route::post('/{id}/extend', [BuySellPromotionController::class, 'extend']);

            Route::delete('/{id}/cancel', [BuySellPromotionController::class, 'cancel']);

        });

    });



    // Buy & Sell Upload System

    Route::group(['prefix' => 'buysell-upload', 'middleware' => 'jwt.auth'], function () {

        Route::post('/images', [BuySellUploadController::class, 'uploadImages']);

        Route::post('/image', [BuySellUploadController::class, 'uploadSingleImage']);

        Route::post('/video', [BuySellUploadController::class, 'uploadVideo']);

        Route::delete('/file', [BuySellUploadController::class, 'deleteFile']);

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

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [AuthorController::class, 'store']);

            Route::put('/{id}', [AuthorController::class, 'update']);

            Route::delete('/{id}', [AuthorController::class, 'destroy']);

        });

    });


    // Legacy/Compatibility Vehicle Routes (without v1 prefix)
    Route::get('/vehicles', [VehicleController::class, 'index']);

    // Legacy Vehicle Routes (direct access at v1 level)
    Route::get('/vehicle-makes', [VehicleController::class, 'getMakes']);
    Route::get('/vehicle-makes/{id}/models', [VehicleController::class, 'getModels']);
    Route::get('/vehicle-categories', [VehicleController::class, 'getCategories']);

// Vehicles Adverts System

    Route::group(['prefix' => 'vehicles'], function () {

        // Public routes

        Route::get('/', [VehicleController::class, 'index']);

        Route::get('/categories', [VehicleCategoryController::class, 'index']);

        Route::get('/makes', [VehicleController::class, 'getMakes']);

        Route::get('/featured', [VehicleController::class, 'getFeaturedVehicles']);

        Route::get('/promoted', [VehicleController::class, 'index'])->defaults('promoted', true);

        Route::get('/sponsored', [VehicleController::class, 'index'])->defaults('sponsored', true);

        Route::get('/recent', [VehicleController::class, 'getRecentVehicles']);

        Route::get('/stats', [VehicleController::class, 'getStats']);

        Route::get('/{id}', [VehicleController::class, 'show']);

        Route::get('/{id}/related', [VehicleController::class, 'getRelatedVehicles']);

        // Button functionality endpoints
        Route::post('/{id}/view', [VehicleController::class, 'incrementViews']);
        Route::post('/{id}/click', [VehicleController::class, 'incrementClicks']);
        
        // Favourite endpoints (check is public, toggle requires auth)
        Route::get('/{id}/favourite/check', [VehicleController::class, 'checkFavourite']);


        // Data endpoints

        Route::get('/makes', [VehicleController::class, 'getMakes']);

        Route::get('/makes/{id}/models', [VehicleController::class, 'getModels']);

        Route::get('/vehicle-makes/{id}/models', [VehicleController::class, 'getModels']);

        Route::get('/models/{makeId}', [VehicleController::class, 'getModels']);

        Route::get('/categories', [VehicleController::class, 'getCategories']);

        Route::get('/popular-makes', [VehicleController::class, 'getPopularMakes']);

        // Public favourite check (doesn't require authentication)
        Route::get('/{id}/favourite/check', [VehicleController::class, 'checkFavourite']);

        // Legacy routes for vehicle-makes and vehicle-categories (direct access)
        Route::get('/vehicle-makes', [VehicleController::class, 'getMakes']);
        Route::get('/vehicle-makes/{id}/models', [VehicleController::class, 'getModels']);
        Route::get('/vehicle-categories', [VehicleController::class, 'getCategories']);

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [VehicleController::class, 'store']);

            Route::put('/{id}', [VehicleController::class, 'update']);

            Route::delete('/{id}', [VehicleController::class, 'destroy']);

            Route::get('/my-vehicles', [VehicleController::class, 'myVehicles']);

            Route::get('/saved', [VehicleController::class, 'savedVehicles']);

            Route::post('/{id}/save', [VehicleController::class, 'saveVehicle']);

            Route::post('/{id}/favourite', [VehicleController::class, 'toggleFavourite']);

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

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [VehicleCategoryController::class, 'store']);

            Route::put('/{id}', [VehicleCategoryController::class, 'update']);

            Route::delete('/{id}', [VehicleCategoryController::class, 'destroy']);

            Route::post('/{id}/toggle-status', [VehicleCategoryController::class, 'toggleStatus']);

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

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [JobCategoryController::class, 'store']);

            Route::put('/{id}', [JobCategoryController::class, 'update']);

            Route::delete('/{id}', [JobCategoryController::class, 'destroy']);

        });

    });



    // Job Seekers

    Route::group(['prefix' => 'job-seekers'], function () {

        // Authenticated routes (literal paths must be registered before /{id})

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::get('/my-profile', [JobSeekerController::class, 'myProfile']);

            Route::get('/statistics', [JobSeekerController::class, 'statistics']);

            Route::post('/', [JobSeekerController::class, 'store']);

            Route::put('/{id}', [JobSeekerController::class, 'update'])->where('id', '[0-9]+');

            Route::delete('/{id}', [JobSeekerController::class, 'destroy'])->where('id', '[0-9]+');

        });



        // Public routes

        Route::get('/', [JobSeekerController::class, 'index']);

        Route::get('/featured', [JobSeekerController::class, 'featuredProfiles']);

        Route::get('/sponsored', [JobSeekerController::class, 'sponsoredProfiles']);

        Route::get('/{id}', [JobSeekerController::class, 'show'])->where('id', '[0-9]+');

        Route::post('/{id}/contact', [JobSeekerController::class, 'contactProfile'])->where('id', '[0-9]+');

    });



    // Sponsored Adverts System
    Route::group(['prefix' => 'sponsored-adverts'], function () {
        
        // Public routes
        Route::get('/', [SponsoredAdvertController::class, 'index']);
        Route::get('/featured', [SponsoredAdvertController::class, 'featured']);
        Route::get('/statistics', [SponsoredAdvertController::class, 'statistics']);
        Route::get('/categories', [SponsoredAdvertController::class, 'categories']);
        Route::get('/trending-categories', [SponsoredAdvertController::class, 'trendingCategories']);
        Route::get('/pricing-plans', [SponsoredAdvertController::class, 'pricingPlans']);

        // Authenticated static routes must come before /{slug}
        Route::middleware('jwt.auth')->group(function () {
            Route::get('/my-adverts', [SponsoredAdvertController::class, 'myAdverts']);
            Route::patch('/{id}/status', [SponsoredAdvertController::class, 'updateStatus']);
            Route::post('/upload-image', [SponsoredAdvertController::class, 'uploadImage']);
        });

        Route::post('/{id}/track-view', [SponsoredAdvertController::class, 'trackView']);
        Route::get('/{slug}', [SponsoredAdvertController::class, 'show']);

        // Authenticated dynamic routes
        Route::group(['middleware' => 'jwt.auth'], function () {
            Route::post('/', [SponsoredAdvertController::class, 'store']);
            Route::put('/{id}', [SponsoredAdvertController::class, 'update']);
            Route::delete('/{id}', [SponsoredAdvertController::class, 'destroy']);
            Route::post('/{id}/save', [SponsoredAdvertController::class, 'saveAdvert']);
            Route::post('/{id}/payment', [SponsoredAdvertController::class, 'processPayment']);
        });
    });

    // Simple test route outside any group
    Route::get('/sponsored-adverts-simple-test', function() {
        return response()->json(['message' => 'Simple test working']);
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

        // Public data routes (must come before /{property})

        Route::get('/data/property-types', [PropertyController::class, 'getPropertyTypes']);

        Route::get('/data/categories', [PropertyController::class, 'getCategories']);

        Route::get('/data/commercial-types', [PropertyController::class, 'getCommercialTypes']);

        Route::get('/data/land-types', [PropertyController::class, 'getLandTypes']);

        Route::get('/data/planning-permissions', [PropertyController::class, 'getPlanningPermissions']);

        Route::get('/data/view-types', [PropertyController::class, 'getViewTypes']);

        // Authenticated routes (specific paths must come before /{property})

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [PropertyController::class, 'store']);

            Route::get('/my-properties', [PropertyController::class, 'myProperties']);

            Route::get('/saved-properties', [PropertyController::class, 'savedProperties']);

            Route::put('/{property}', [PropertyController::class, 'update']);

            Route::delete('/{property}', [PropertyController::class, 'destroy']);

            Route::post('/{property}/save', [PropertyController::class, 'saveProperty']);

        });

        // Public show + interaction (catch-all last)

        Route::get('/{property}', [PropertyController::class, 'show']);

        Route::post('/{property}/contact-agent', [PropertyController::class, 'contactAgent']);

        Route::post('/{property}/track-event', [PropertyController::class, 'trackEvent']);

    });



    // Property Upsells System

    Route::group(['prefix' => 'property-upsells', 'middleware' => 'jwt.auth'], function () {

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

        Route::post('/upload-image', [FeaturedAdvertController::class, 'uploadImage']);

        Route::get('/statistics', [FeaturedAdvertController::class, 'statistics']);

        Route::get('/live-activity', [FeaturedAdvertController::class, 'liveActivity']);

        Route::get('/analytics', [FeaturedAdvertController::class, 'analytics']);

        // Authenticated static routes before /{id}
        Route::middleware('jwt.auth')->group(function () {
            Route::get('/my-adverts', [FeaturedAdvertController::class, 'myFeaturedAdverts']);
        });

        Route::get('/{id}', [FeaturedAdvertController::class, 'show']);

        Route::post('/{id}/save', [FeaturedAdvertController::class, 'saveAdvert']);

        Route::post('/{id}/contact', [FeaturedAdvertController::class, 'contactSeller']);



        // Authenticated routes (customer)

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [FeaturedAdvertController::class, 'store']);

            Route::put('/{id}', [FeaturedAdvertController::class, 'update']);

            Route::delete('/{id}', [FeaturedAdvertController::class, 'destroy']);

        });



        // Featured Advert Banner Integration

        Route::group(['prefix' => 'banners'], function () {

            Route::get('/', [FeaturedAdvertBannerController::class, 'index']);

            Route::get('/homepage-slider', [FeaturedAdvertBannerController::class, 'homepageSlider']);

            Route::get('/category/{categoryId}', [FeaturedAdvertBannerController::class, 'categoryBanners']);

            Route::get('/country/{country}', [FeaturedAdvertBannerController::class, 'countryBanners']);

            Route::get('/with-banner-data', [FeaturedAdvertBannerController::class, 'withBannerData']);

            Route::get('/analytics', [FeaturedAdvertBannerController::class, 'bannerAnalytics']);

            

            // Authenticated routes (customer)

            Route::group(['middleware' => 'jwt.auth'], function () {

                Route::post('/from-featured/{featuredAdvertId}', [FeaturedAdvertBannerController::class, 'createFromFeatured']);

            });

        });

    });



    // Funding Projects System

    Route::group(['prefix' => 'funding'], function () {

        // Public routes

        Route::get('/', [FundingProjectController::class, 'index']);

        Route::get('/metadata', [FundingProjectController::class, 'getMetadata']);
        Route::get('/statistics', [FundingProjectController::class, 'getStatistics']);

        Route::get('/featured', [FundingProjectController::class, 'getFeaturedProjects']);

        Route::get('/trending', [FundingProjectController::class, 'getTrendingProjects']);

        Route::get('/ending-soon', [FundingProjectController::class, 'getEndingSoonProjects']);

        Route::get('/{id}', [FundingProjectController::class, 'show']);



        // Authenticated routes

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::post('/', [FundingProjectController::class, 'store']);

            // Upload routes
            Route::post('/upload-cover', [FundingProjectController::class, 'uploadCoverImage']);
            Route::post('/upload-additional', [FundingProjectController::class, 'uploadAdditionalImages']);

            Route::put('/{id}', [FundingProjectController::class, 'update']);

            Route::delete('/{id}', [FundingProjectController::class, 'destroy']);

            Route::get('/my-projects/list', [FundingProjectController::class, 'myProjects']);

            Route::post('/{projectId}/rewards', [FundingProjectController::class, 'addReward']);

            Route::post('/{projectId}/upsell', [FundingProjectController::class, 'purchaseUpsell']);

        });

    });



    // Funding Pledges System

    Route::group(['prefix' => 'funding-pledges', 'middleware' => 'jwt.auth'], function () {

        Route::post('/{projectId}', [FundingPledgeController::class, 'store']);

        Route::get('/{pledgeId}', [FundingPledgeController::class, 'show']);

        Route::get('/my/pledges', [FundingPledgeController::class, 'myPledges']);

        Route::put('/{pledgeId}/status', [FundingPledgeController::class, 'updateStatus']);

        Route::delete('/{pledgeId}', [FundingPledgeController::class, 'destroy']);

        Route::get('/project/{projectId}/backers', [FundingPledgeController::class, 'projectPledges']);

    });



    // Funding Upsells System

    Route::group(['prefix' => 'funding-upsells', 'middleware' => 'jwt.auth'], function () {

        Route::get('/plans', [App\Http\Controllers\Api\FundingUpsellController::class, 'getPlans']);

        Route::get('/comparison', [App\Http\Controllers\Api\FundingUpsellController::class, 'getComparison']);

        Route::get('/recommendation', [App\Http\Controllers\Api\FundingUpsellController::class, 'getRecommendation']);

        Route::post('/purchase', [App\Http\Controllers\Api\FundingUpsellController::class, 'purchaseUpsell']);

        Route::get('/my-upsells', [App\Http\Controllers\Api\FundingUpsellController::class, 'getMyUpsells']);

        Route::get('/post/{projectId}', [App\Http\Controllers\Api\FundingUpsellController::class, 'getPostUpsells']);

        Route::post('/{id}/cancel', [App\Http\Controllers\Api\FundingUpsellController::class, 'cancelUpsell']);

        Route::get('/stats', [App\Http\Controllers\Api\FundingUpsellController::class, 'getStats']);

    });

    // Donations System

    Route::group(['prefix' => 'donations'], function () {

        // Public routes

        Route::get('/', [DonationController::class, 'index']);

        Route::get('/featured', [DonationController::class, 'featured']);

        Route::get('/urgent', [DonationController::class, 'urgent']);

        Route::get('/statistics', [DonationController::class, 'statistics']);

        // Authenticated routes (must be registered before /{id})

        Route::group(['middleware' => 'jwt.auth'], function () {

            Route::get('/my-donations', [DonationController::class, 'myDonations']);

            Route::post('/', [DonationController::class, 'store']);

            Route::put('/{id}', [DonationController::class, 'update']);

            Route::delete('/{id}', [DonationController::class, 'destroy']);

        });

        Route::get('/{id}', [DonationController::class, 'show']);

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
    });

    // Admin Events Management
    Route::group(['prefix' => 'admin/events', 'middleware' => ['auth:api', 'admin']], function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Admin\EventAdminController::class, 'dashboard']);
        
        // Events Management
        Route::get('/', [App\Http\Controllers\Admin\EventAdminController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Admin\EventAdminController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Admin\EventAdminController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Admin\EventAdminController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Admin\EventAdminController::class, 'destroy']);
        
        // Event Status Management
        Route::post('/{id}/approve', [App\Http\Controllers\Admin\EventAdminController::class, 'approve']);
        Route::post('/{id}/reject', [App\Http\Controllers\Admin\EventAdminController::class, 'reject']);
        Route::post('/{id}/toggle-active', [App\Http\Controllers\Admin\EventAdminController::class, 'toggleActive']);
        
        // Promotion Management
        Route::post('/{id}/upgrade-tier', [App\Http\Controllers\Admin\EventAdminController::class, 'upgradeTier']);
        Route::post('/{id}/set-featured', [App\Http\Controllers\Admin\EventAdminController::class, 'setFeatured']);
        Route::post('/{id}/set-sponsored', [App\Http\Controllers\Admin\EventAdminController::class, 'setSponsored']);
        
        // Bulk Actions
        Route::post('/bulk-approve', [App\Http\Controllers\Admin\EventAdminController::class, 'bulkApprove']);
        Route::post('/bulk-reject', [App\Http\Controllers\Admin\EventAdminController::class, 'bulkReject']);
        Route::post('/bulk-update', [App\Http\Controllers\Admin\EventAdminController::class, 'bulkUpdate']);
        Route::post('/bulk-delete', [App\Http\Controllers\Admin\EventAdminController::class, 'bulkDelete']);
        
        // Reports and Analytics
        Route::get('/reports', [App\Http\Controllers\Admin\EventAdminController::class, 'reports']);
        Route::get('/promotion-report', [App\Http\Controllers\Admin\EventAdminController::class, 'promotionReport']);
        Route::get('/analytics', [App\Http\Controllers\Admin\EventAdminController::class, 'analytics']);
        Route::get('/popular', [App\Http\Controllers\Admin\EventAdminController::class, 'popularEvents']);
        Route::get('/trends', [App\Http\Controllers\Admin\EventAdminController::class, 'eventTrends']);
        Route::get('/attendance-analytics', [App\Http\Controllers\Admin\EventAdminController::class, 'attendanceAnalytics']);
        Route::get('/revenue-analytics', [App\Http\Controllers\Admin\EventAdminController::class, 'revenueAnalytics']);
        
        // Export
        Route::get('/export', [App\Http\Controllers\Admin\EventAdminController::class, 'export']);
    });

    // Admin Venues Management
    Route::group(['prefix' => 'admin/venues', 'middleware' => ['auth:api', 'admin']], function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Admin\VenueAdminController::class, 'dashboard']);
        
        // Venues Management
        Route::get('/', [App\Http\Controllers\Admin\VenueAdminController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Admin\VenueAdminController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Admin\VenueAdminController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Admin\VenueAdminController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Admin\VenueAdminController::class, 'destroy']);
        
        // Venue Status Management
        Route::post('/{id}/approve', [App\Http\Controllers\Admin\VenueAdminController::class, 'approve']);
        Route::post('/{id}/reject', [App\Http\Controllers\Admin\VenueAdminController::class, 'reject']);
        Route::post('/{id}/toggle-active', [App\Http\Controllers\Admin\VenueAdminController::class, 'toggleActive']);
        
        // Promotion Management
        Route::post('/{id}/upgrade-tier', [App\Http\Controllers\Admin\VenueAdminController::class, 'upgradeTier']);
        Route::post('/{id}/set-featured', [App\Http\Controllers\Admin\VenueAdminController::class, 'setFeatured']);
        Route::post('/{id}/set-sponsored', [App\Http\Controllers\Admin\VenueAdminController::class, 'setSponsored']);
        
        // Bulk Actions
        Route::post('/bulk-approve', [App\Http\Controllers\Admin\VenueAdminController::class, 'bulkApprove']);
        Route::post('/bulk-reject', [App\Http\Controllers\Admin\VenueAdminController::class, 'bulkReject']);
        Route::post('/bulk-update', [App\Http\Controllers\Admin\VenueAdminController::class, 'bulkUpdate']);
        Route::post('/bulk-delete', [App\Http\Controllers\Admin\VenueAdminController::class, 'bulkDelete']);
        
        // Reports and Analytics
        Route::get('/reports', [App\Http\Controllers\Admin\VenueAdminController::class, 'reports']);
        Route::get('/promotion-report', [App\Http\Controllers\Admin\VenueAdminController::class, 'promotionReport']);
        Route::get('/analytics', [App\Http\Controllers\Admin\VenueAdminController::class, 'analytics']);
        Route::get('/popular', [App\Http\Controllers\Admin\VenueAdminController::class, 'popularVenues']);
        Route::get('/trends', [App\Http\Controllers\Admin\VenueAdminController::class, 'venueTrends']);
        Route::get('/revenue-analytics', [App\Http\Controllers\Admin\VenueAdminController::class, 'revenueAnalytics']);
        
        // Export
        Route::get('/export', [App\Http\Controllers\Admin\VenueAdminController::class, 'export']);
    });

    // Admin Properties Management
    Route::group(['prefix' => 'admin/properties', 'middleware' => ['auth:api', 'admin']], function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Admin\PropertyAdminController::class, 'dashboard']);
        
        // Properties Management
        Route::get('/', [App\Http\Controllers\Admin\PropertyAdminController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Admin\PropertyAdminController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Admin\PropertyAdminController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Admin\PropertyAdminController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Admin\PropertyAdminController::class, 'destroy']);
        
        // Property Status Management
        Route::post('/{id}/approve', [App\Http\Controllers\Admin\PropertyAdminController::class, 'approve']);
        Route::post('/{id}/reject', [App\Http\Controllers\Admin\PropertyAdminController::class, 'reject']);
        Route::post('/{id}/toggle-active', [App\Http\Controllers\Admin\PropertyAdminController::class, 'toggleActive']);
        
        // Promotion Management
        Route::post('/{id}/upgrade-tier', [App\Http\Controllers\Admin\PropertyAdminController::class, 'upgradeTier']);
        Route::post('/{id}/set-featured', [App\Http\Controllers\Admin\PropertyAdminController::class, 'setFeatured']);
        Route::post('/{id}/set-sponsored', [App\Http\Controllers\Admin\PropertyAdminController::class, 'setSponsored']);
        
        // Bulk Actions
        Route::post('/bulk-approve', [App\Http\Controllers\Admin\PropertyAdminController::class, 'bulkApprove']);
        Route::post('/bulk-reject', [App\Http\Controllers\Admin\PropertyAdminController::class, 'bulkReject']);
        Route::post('/bulk-update', [App\Http\Controllers\Admin\PropertyAdminController::class, 'bulkUpdate']);
        Route::post('/bulk-delete', [App\Http\Controllers\Admin\PropertyAdminController::class, 'bulkDelete']);
        
        // Categories Management
        Route::get('/categories', [App\Http\Controllers\Admin\PropertyCategoryAdminController::class, 'index']);
        Route::post('/categories', [App\Http\Controllers\Admin\PropertyCategoryAdminController::class, 'store']);
        Route::put('/categories/{id}', [App\Http\Controllers\Admin\PropertyCategoryAdminController::class, 'update']);
        Route::delete('/categories/{id}', [App\Http\Controllers\Admin\PropertyCategoryAdminController::class, 'destroy']);
        
        // Enquiries Management
        Route::get('/enquiries', [App\Http\Controllers\Admin\PropertyEnquiryAdminController::class, 'index']);
        Route::get('/enquiries/{id}', [App\Http\Controllers\Admin\PropertyEnquiryAdminController::class, 'show']);
        Route::post('/enquiries/{id}/respond', [App\Http\Controllers\Admin\PropertyEnquiryAdminController::class, 'respond']);
        Route::delete('/enquiries/{id}', [App\Http\Controllers\Admin\PropertyEnquiryAdminController::class, 'destroy']);
        
        // Reports and Analytics
        Route::get('/reports', [App\Http\Controllers\Admin\PropertyAdminController::class, 'reports']);
        Route::get('/analytics', [App\Http\Controllers\Admin\PropertyAdminController::class, 'analytics']);
        Route::get('/export', [App\Http\Controllers\Admin\PropertyAdminController::class, 'export']);
    });

    // Admin Funding Projects Management
    Route::group(['prefix' => 'admin/funding', 'middleware' => ['auth:api', 'admin']], function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Api\FundingProjectController::class, 'adminDashboard']);
        
        // Projects Management
        Route::get('/', [App\Http\Controllers\Api\FundingProjectController::class, 'adminIndex']);
        Route::get('/{id}', [App\Http\Controllers\Api\FundingProjectController::class, 'adminShow']);
        Route::put('/{id}', [App\Http\Controllers\Api\FundingProjectController::class, 'adminUpdate']);
        Route::delete('/{id}', [App\Http\Controllers\Api\FundingProjectController::class, 'adminDestroy']);
        
        // Project Status Management
        Route::post('/{id}/approve', [App\Http\Controllers\Api\FundingProjectController::class, 'adminApprove']);
        Route::post('/{id}/reject', [App\Http\Controllers\Api\FundingProjectController::class, 'adminReject']);
        Route::post('/{id}/toggle-active', [App\Http\Controllers\Api\FundingProjectController::class, 'adminToggleActive']);
        
        // Reports and Analytics
        Route::get('/reports', [App\Http\Controllers\Api\FundingProjectController::class, 'adminReports']);
        Route::get('/analytics', [App\Http\Controllers\Api\FundingProjectController::class, 'adminAnalytics']);
        Route::get('/export', [App\Http\Controllers\Api\FundingProjectController::class, 'adminExport']);
    });

    // Buy & Sell API (comprehensive system)

    Route::group(['prefix' => 'buysell'], function () {

        // Public routes (no auth required)

        Route::get('/stats', [BuySellController::class, 'stats']);

        Route::get('/adverts', [BuySellController::class, 'index']);

        Route::get('/adverts/{id}', [BuySellController::class, 'show']);

        Route::get('/categories', [BuySellController::class, 'categories']);

        Route::get('/categories/{categoryId}/subcategories', [BuySellController::class, 'subcategories']);

        Route::get('/promotion-plans', [BuySellController::class, 'promotionPlans']);

        Route::get('/search-suggestions', [BuySellController::class, 'searchSuggestions']);

        Route::get('/trending', [BuySellController::class, 'trending']);

        

        // Protected routes (auth required)

        Route::group(['middleware' => 'jwt.auth'], function () {

            // Advert management

            Route::post('/adverts', [BuySellController::class, 'store']);

            Route::put('/adverts/{id}', [BuySellController::class, 'update']);

            Route::delete('/adverts/{id}', [BuySellController::class, 'destroy']);

            Route::get('/adverts/my', [BuySellController::class, 'myAdverts']);

            

            // User interactions

            Route::post('/adverts/{id}/save', [BuySellController::class, 'saveAdvert']);

            Route::get('/saved-adverts', [BuySellController::class, 'savedAdverts']);

            Route::post('/adverts/{id}/contact', [BuySellController::class, 'contactSeller']);

            Route::post('/adverts/{id}/report', [BuySellController::class, 'reportAdvert']);

            Route::post('/adverts/{id}/view', [BuySellController::class, 'view']);

            Route::get('/recently-viewed', [BuySellController::class, 'recentlyViewed']);

            

            // Promotions

            Route::post('/adverts/{id}/promote', [BuySellController::class, 'promoteAdvert']);

        });

    });

    // Jobs & Vacancies System - Comprehensive API
    Route::group(['prefix' => 'jobs'], function () {
        
        // Public Routes (No Authentication Required)
        Route::group(['prefix' => 'public'], function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\JobController::class, 'index']);
            Route::get('/stats', [\App\Http\Controllers\Api\V1\JobController::class, 'statistics']);
            Route::get('/featured', [\App\Http\Controllers\Api\V1\JobController::class, 'featured']);
            Route::get('/categories', [\App\Http\Controllers\Api\V1\JobController::class, 'categories']);
            Route::get('/pricing-plans', [\App\Http\Controllers\Api\V1\JobController::class, 'pricingPlans']);
            Route::get('/genre/{genre}', [\App\Http\Controllers\Api\V1\JobController::class, 'byCategory']);
            Route::get('/activities', [\App\Http\Controllers\Api\V1\JobController::class, 'activities']);
            Route::get('/trending-searches', [\App\Http\Controllers\Api\V1\JobController::class, 'trendingSearches']);

            // Job Seekers Public Routes
            Route::get('/seekers', [\App\Http\Controllers\Api\V1\JobSeekerController::class, 'index']);
            Route::get('/seekers/stats', [\App\Http\Controllers\Api\V1\JobSeekerController::class, 'statistics']);
            Route::get('/seekers/{seekerId}', [\App\Http\Controllers\Api\V1\JobSeekerController::class, 'show'])->where('seekerId', '[0-9]+');
            Route::post('/seekers/{seekerId}/contact', [\App\Http\Controllers\Api\V1\JobSeekerController::class, 'contactProfile'])->where('seekerId', '[0-9]+');

            Route::get('/{jobId}', [\App\Http\Controllers\Api\V1\JobController::class, 'show']);
        });

        // Protected Routes (Authentication Required)
        Route::group(['middleware' => 'jwt.auth'], function () {

            // File Upload
            Route::post('/upload', [\App\Http\Controllers\Api\V1\JobController::class, 'uploadFile']);

            // Job Management
            Route::post('/', [\App\Http\Controllers\Api\V1\JobController::class, 'store']);
            Route::get('/my-jobs', [\App\Http\Controllers\Api\V1\JobController::class, 'myJobs']);
            Route::get('/saved', [\App\Http\Controllers\Api\V1\JobController::class, 'savedJobs']);
            Route::put('/{id}', [\App\Http\Controllers\Api\V1\JobController::class, 'update']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\V1\JobController::class, 'destroy']);
            Route::post('/{id}/save', [\App\Http\Controllers\Api\V1\JobController::class, 'saveJob']);
            
            // Job Applications
            Route::post('/{jobId}/apply', [\App\Http\Controllers\Api\V1\JobApplicationController::class, 'apply']);
            Route::get('/applications', [\App\Http\Controllers\Api\V1\JobApplicationController::class, 'index']);
            Route::get('/applications/{applicationId}', [\App\Http\Controllers\Api\V1\JobApplicationController::class, 'show']);
            Route::put('/applications/{applicationId}/status', [\App\Http\Controllers\Api\V1\JobApplicationController::class, 'updateStatus']);
            Route::get('/applications/stats', [\App\Http\Controllers\Api\V1\JobApplicationController::class, 'statistics']);
            Route::get('/my-applications', [\App\Http\Controllers\Api\V1\JobApplicationController::class, 'myApplications']);
            Route::post('/applications/{applicationId}/withdraw', [\App\Http\Controllers\Api\V1\JobApplicationController::class, 'withdraw']);
            
            // Job Seeker Profiles (literal paths before /seekers/{id})
            Route::get('/seekers/my-profile', [\App\Http\Controllers\Api\V1\JobSeekerController::class, 'myProfile']);
            Route::get('/seekers/my-applications', [\App\Http\Controllers\Api\V1\JobSeekerController::class, 'myApplications']);
            Route::get('/seekers/my-statistics', [\App\Http\Controllers\Api\V1\JobSeekerController::class, 'myStatistics']);
            Route::post('/seekers', [\App\Http\Controllers\Api\V1\JobSeekerController::class, 'store']);
            Route::put('/seekers/{id}', [\App\Http\Controllers\Api\V1\JobSeekerController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/seekers/{id}', [\App\Http\Controllers\Api\V1\JobSeekerController::class, 'destroy'])->where('id', '[0-9]+');
            
            // Job Alerts
            Route::post('/alerts', [\App\Http\Controllers\Api\V1\JobAlertController::class, 'store']);
            Route::get('/alerts', [\App\Http\Controllers\Api\V1\JobAlertController::class, 'index']);
            Route::get('/alerts/{id}', [\App\Http\Controllers\Api\V1\JobAlertController::class, 'show']);
            Route::put('/alerts/{id}', [\App\Http\Controllers\Api\V1\JobAlertController::class, 'update']);
            Route::delete('/alerts/{id}', [\App\Http\Controllers\Api\V1\JobAlertController::class, 'destroy']);
            Route::post('/alerts/{id}/test', [\App\Http\Controllers\Api\V1\JobAlertController::class, 'test']);
            Route::get('/alerts/stats', [\App\Http\Controllers\Api\V1\JobAlertController::class, 'statistics']);
            Route::get('/alerts/{id}/matching-jobs', [\App\Http\Controllers\Api\V1\JobAlertController::class, 'matchingJobs']);
            
            // Premium Upsells
            Route::get('/upsells/pricing', [\App\Http\Controllers\Api\V1\JobUpsellController::class, 'pricing']);
            Route::post('/upsells', [\App\Http\Controllers\Api\V1\JobUpsellController::class, 'store']);
            Route::get('/upsells', [\App\Http\Controllers\Api\V1\JobUpsellController::class, 'index']);
            Route::get('/upsells/{id}', [\App\Http\Controllers\Api\V1\JobUpsellController::class, 'show']);
            Route::post('/upsells/{id}/activate', [\App\Http\Controllers\Api\V1\JobUpsellController::class, 'activate']);
            Route::post('/upsells/{id}/cancel', [\App\Http\Controllers\Api\V1\JobUpsellController::class, 'cancel']);
            Route::post('/upsells/{id}/pay', [\App\Http\Controllers\Api\V1\JobUpsellController::class, 'pay']);
            Route::get('/upsells/stats', [\App\Http\Controllers\Api\V1\JobUpsellController::class, 'statistics']);
        });
    });

    // Communities API Routes
    Route::group(['prefix' => 'communities'], function () {
        // Public routes
        Route::get('/', [CommunityController::class, 'index']);
        Route::get('/trending', [CommunityController::class, 'trending']);
        Route::get('/featured', [CommunityController::class, 'featured']);
        Route::get('/category/{categoryId}', [CommunityController::class, 'byCategory']);

        // Authenticated routes (static paths before /{id})
        Route::group(['middleware' => 'jwt.auth'], function () {
            Route::get('/my-communities', [CommunityController::class, 'myCommunities']);
            Route::post('/', [CommunityController::class, 'store']);
            Route::put('/{id}', [CommunityController::class, 'update']);
            Route::delete('/{id}', [CommunityController::class, 'destroy']);
            Route::post('/{id}/join', [CommunityController::class, 'join']);
            Route::post('/{id}/leave', [CommunityController::class, 'leave']);
            Route::post('/{id}/follow', [CommunityController::class, 'follow']);
            Route::post('/{id}/unfollow', [CommunityController::class, 'unfollow']);
        });

        Route::get('/{id}', [CommunityController::class, 'show']);
        Route::get('/{id}/members', [CommunityController::class, 'members']);
    });

    // Community Posts API Routes
    Route::group(['prefix' => 'community-posts'], function () {
        // Public routes
        Route::get('/', [CommunityPostController::class, 'index']);

        // Authenticated routes (static paths before /{id})
        Route::group(['middleware' => 'jwt.auth'], function () {
            Route::get('/for-you', [CommunityPostController::class, 'forYou']);
            Route::get('/following', [CommunityPostController::class, 'following']);
            Route::get('/local', [CommunityPostController::class, 'local']);
            Route::get('/saved', [CommunityPostController::class, 'saved']);
            Route::get('/my-posts', [CommunityPostController::class, 'myPosts']);
            Route::post('/upload-media', [CommunityPostController::class, 'uploadMedia']);
            Route::post('/', [CommunityPostController::class, 'store']);
            Route::put('/{id}', [CommunityPostController::class, 'update']);
            Route::delete('/{id}', [CommunityPostController::class, 'destroy']);
            Route::post('/{id}/react', [CommunityPostController::class, 'react']);
            Route::post('/{id}/save', [CommunityPostController::class, 'save']);
            Route::post('/{id}/pin', [CommunityPostController::class, 'pin']);
            Route::post('/{id}/flag', [CommunityPostController::class, 'flag']);
        });

        Route::get('/{id}', [CommunityPostController::class, 'show']);
    });

    // Comments API Routes
    Route::group(['prefix' => 'comments'], function () {
        // Public routes
        Route::get('/post/{postId}', [CommentController::class, 'index']);
        Route::get('/{id}', [CommentController::class, 'show']);
        Route::get('/{id}/replies', [CommentController::class, 'replies']);

        // Authenticated routes
        Route::group(['middleware' => 'jwt.auth'], function () {
            Route::post('/', [CommentController::class, 'store']);
            Route::put('/{id}', [CommentController::class, 'update']);
            Route::delete('/{id}', [CommentController::class, 'destroy']);
            Route::post('/{id}/react', [CommentController::class, 'react']);
            Route::post('/{id}/flag', [CommentController::class, 'flag']);
            Route::post('/{id}/hide', [CommentController::class, 'hide']);
        });
    });

    // Events & Venues API Routes
    Route::group(['prefix' => 'events-venues'], function () {
        // Authenticated routes (must come first to avoid being caught by {slug})
        Route::group(['middleware' => 'jwt.auth'], function () {
            Route::post('/', [EventsVenuesController::class, 'store']);
            Route::put('/{id}', [EventsVenuesController::class, 'update']);
            Route::delete('/{id}', [EventsVenuesController::class, 'destroy']);
            Route::get('/my-adverts', [EventsVenuesController::class, 'myAdverts']);
            Route::post('/{id}/save', [EventsVenuesController::class, 'save']);
            Route::get('/saved', [EventsVenuesController::class, 'savedAdverts']);
        });

        // Public routes
        Route::get('/', [EventsVenuesController::class, 'index']);
        Route::get('/featured', [EventsVenuesController::class, 'featured']);
        Route::get('/sponsored', [EventsVenuesController::class, 'sponsored']);
        Route::get('/categories', [EventsVenuesController::class, 'categories']);
        Route::get('/statistics', [EventsVenuesController::class, 'statistics']);
        Route::get('/live-activity', [EventsVenuesController::class, 'liveActivity']);
        Route::get('/promotion-tiers', [EventsVenuesController::class, 'promotionTiers']);
        Route::post('/upload-image', [EventsVenuesController::class, 'uploadImage']);
        Route::get('/{slug}', [EventsVenuesController::class, 'show']);
    });

});

// Legacy/Compatibility Vehicle Routes (without v1 prefix)
Route::get('/vehicles', [VehicleController::class, 'index']);
Route::post('/vehicles', [VehicleController::class, 'store'])->middleware('auth:api');
Route::get('/vehicles/stats', [VehicleController::class, 'getStats']);
Route::get('/vehicles/featured', [VehicleController::class, 'getFeaturedVehicles']);
Route::get('/vehicles/recent', [VehicleController::class, 'getRecentVehicles']);

// Calculator API Routes
Route::group(['prefix' => 'v1/calculators'], function () {
    // Business Calculators
    Route::post('/business/break-even', [CalculatorController::class, 'breakEven']);
    Route::post('/business/roe', [CalculatorController::class, 'roe']);
    Route::post('/business/operating-margin', [CalculatorController::class, 'operatingMargin']);
    Route::post('/business/gross-margin', [CalculatorController::class, 'grossMargin']);
    Route::post('/business/valuation', [CalculatorController::class, 'businessValuation']);
    Route::post('/business/vat', [CalculatorController::class, 'vat']);
    Route::post('/business/fcff', [CalculatorController::class, 'fcff']);

    // Real Estate Calculators
    Route::post('/real-estate/mortgage', [CalculatorController::class, 'mortgage']);
    Route::post('/real-estate/affordability', [CalculatorController::class, 'affordability']);
    Route::post('/real-estate/roi', [CalculatorController::class, 'roi']);
    Route::post('/real-estate/rent-vs-buy', [CalculatorController::class, 'rentVsBuy']);
    Route::post('/real-estate/property-tax', [CalculatorController::class, 'propertyTax']);
    Route::post('/real-estate/closing-costs', [CalculatorController::class, 'closingCosts']);

    // Vehicle Calculators
    Route::post('/vehicle/auto-loan', [CalculatorController::class, 'autoLoan']);
    Route::post('/vehicle/lease-vs-buy', [CalculatorController::class, 'leaseVsBuy']);
    Route::post('/vehicle/depreciation', [CalculatorController::class, 'depreciation']);
    Route::post('/vehicle/fuel-cost', [CalculatorController::class, 'fuelCost']);
    Route::post('/vehicle/insurance', [CalculatorController::class, 'insurance']);
    Route::post('/vehicle/tco', [CalculatorController::class, 'tco']);
});

// Legacy/Compatibility Sponsored Adverts Routes (without v1 prefix)
Route::group(['prefix' => 'sponsored-adverts'], function () {
    Route::get('/test', function() {
        return response()->json(['message' => 'Sponsored adverts legacy route working']);
    });
    
    Route::get('/categories', function() {
        return response()->json([
            'status' => 'Success',
            'message' => 'Categories retrieved successfully',
            'data' => [
                ['id' => 1, 'name' => 'Real Estate', 'slug' => 'real-estate'],
                ['id' => 2, 'name' => 'Vehicles', 'slug' => 'vehicles'],
                ['id' => 3, 'name' => 'Services', 'slug' => 'services']
            ]
        ]);
    });
    
    Route::get('/homepage-stats', function() {
        return response()->json([
            'status' => 'Success',
            'message' => 'Homepage stats retrieved successfully',
            'data' => [
                'total_adverts' => 1250,
                'active_adverts' => 890,
                'total_categories' => 12,
                'featured_adverts' => 45
            ]
        ]);
    });
    
    Route::get('/live-activity', function() {
        return response()->json([
            'status' => 'Success',
            'message' => 'Live activity retrieved successfully',
            'data' => [
                ['id' => 1, 'title' => 'Sample Advert 1', 'created_at' => now()],
                ['id' => 2, 'title' => 'Sample Advert 2', 'created_at' => now()]
            ]
        ]);
    });
    
    Route::get('/', function() {
        return response()->json([
            'status' => 'Success',
            'message' => 'Sponsored adverts retrieved successfully',
            'data' => []
        ]);
    });
});
