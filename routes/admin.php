<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PromotedAdvertAdminController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\SponsoredAdvertAdminController;
use App\Http\Controllers\Admin\BannerAdminController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    
    // Admin Dashboard (root route)
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });
    
    // Admin Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Featured Adverts Admin Routes
    Route::prefix('featured-adverts')->name('featured-adverts.')->group(function () {
        
        // CRUD Operations
        Route::get('/', [FeaturedAdvertAdminController::class, 'index'])->name('index');
        Route::post('/', [FeaturedAdvertAdminController::class, 'store'])->name('store');
        Route::get('/{id}', [FeaturedAdvertAdminController::class, 'show'])->name('show');
        Route::put('/{id}', [FeaturedAdvertAdminController::class, 'update'])->name('update');
        Route::delete('/{id}', [FeaturedAdvertAdminController::class, 'destroy'])->name('destroy');
        
        // Bulk Operations
        Route::post('/bulk-update', [FeaturedAdvertAdminController::class, 'bulkUpdate'])->name('bulk-update');
        
        // Approval Workflow
        Route::post('/{id}/approve', [FeaturedAdvertAdminController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [FeaturedAdvertAdminController::class, 'reject'])->name('reject');
        
        // Statistics and Analytics
        Route::get('/statistics', [FeaturedAdvertAdminController::class, 'statistics'])->name('statistics');
        
        // Export functionality
        Route::get('/export', [FeaturedAdvertAdminController::class, 'export'])->name('export');
    });
    
    // Promoted Adverts Admin Routes
    Route::prefix('promoted-adverts')->name('promoted-adverts.')->group(function () {
        
        // Dashboard Analytics
        Route::get('/dashboard', [PromotedAdvertAdminController::class, 'dashboard'])->name('dashboard');
        
        // Analytics for specific advert
        Route::get('/{advert}/analytics', [PromotedAdvertAdminController::class, 'analytics'])->where('advert', '^[0-9]+$')->name('analytics');
        
        // Bulk Operations
        Route::post('/bulk-approve', [PromotedAdvertAdminController::class, 'bulkApprove'])->name('bulk.approve');
        Route::post('/bulk-reject', [PromotedAdvertAdminController::class, 'bulkReject'])->name('bulk.reject');
        Route::post('/bulk-feature', [PromotedAdvertAdminController::class, 'bulkFeature'])->name('bulk.feature');
        
        // Export functionality
        Route::get('/export', [PromotedAdvertAdminController::class, 'export'])->name('export');
        
        // System Health
        Route::get('/system-health', [PromotedAdvertAdminController::class, 'systemHealth'])->name('system-health');
        
        // Performance Reports
        Route::get('/promotion-report', [PromotedAdvertAdminController::class, 'promotionReport'])->name('promotion-report');
    });
    
    // Sponsored Adverts Admin Routes
    Route::prefix('sponsored-adverts')->name('sponsored-adverts.')->group(function () {
        
        // Admin API endpoints
        Route::get('/dashboard-stats', [SponsoredAdvertAdminController::class, 'dashboard']);
        Route::get('/', [SponsoredAdvertAdminController::class, 'index']);
        Route::get('/{id}', [SponsoredAdvertAdminController::class, 'show'])->where('id', '^[0-9]+$');
        Route::post('/{id}/approve', [SponsoredAdvertAdminController::class, 'approve'])->where('id', '^[0-9]+$');
        Route::post('/{id}/reject', [SponsoredAdvertAdminController::class, 'reject'])->where('id', '^[0-9]+$');
        Route::post('/{id}/toggle-active', [SponsoredAdvertAdminController::class, 'toggleActive'])->where('id', '^[0-9]+$');
        Route::post('/{id}/update-tier', [SponsoredAdvertAdminController::class, 'updateTier'])->where('id', '^[0-9]+$');
        Route::get('/{id}/analytics', [SponsoredAdvertAdminController::class, 'analytics'])->where('id', '^[0-9]+$');
        Route::post('/bulk-approve', [SponsoredAdvertAdminController::class, 'bulkApprove']);
        Route::post('/bulk-reject', [SponsoredAdvertAdminController::class, 'bulkReject']);
        Route::get('/export', [SponsoredAdvertAdminController::class, 'export']);
        Route::get('/system-health', [SponsoredAdvertAdminController::class, 'systemHealth']);
        Route::get('/promotion-report', [SponsoredAdvertAdminController::class, 'promotionReport']);
    });
    
    // Property System Admin Routes
    Route::prefix('properties')->name('properties.')->group(function () {
        
        // Dashboard and Analytics
        Route::get('/dashboard', [App\Http\Controllers\Admin\PropertyAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/analytics', [App\Http\Controllers\Admin\PropertyAdminController::class, 'analytics'])->name('analytics');
        
        // CRUD Operations
        Route::get('/', [App\Http\Controllers\Admin\PropertyAdminController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Admin\PropertyAdminController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\Admin\PropertyAdminController::class, 'show'])->name('show');
        Route::put('/{id}', [App\Http\Controllers\Admin\PropertyAdminController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\PropertyAdminController::class, 'destroy'])->name('destroy');
        
        // Approval Workflow
        Route::post('/{id}/approve', [App\Http\Controllers\Admin\PropertyAdminController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [App\Http\Controllers\Admin\PropertyAdminController::class, 'reject'])->name('reject');
        Route::post('/{id}/toggle-active', [App\Http\Controllers\Admin\PropertyAdminController::class, 'toggleActive'])->name('toggle-active');
        
        // Bulk Operations
        Route::post('/bulk-approve', [App\Http\Controllers\Admin\PropertyAdminController::class, 'bulkApprove'])->name('bulk-approve');
        Route::post('/bulk-reject', [App\Http\Controllers\Admin\PropertyAdminController::class, 'bulkReject'])->name('bulk-reject');
        Route::post('/bulk-update', [App\Http\Controllers\Admin\PropertyAdminController::class, 'bulkUpdate'])->name('bulk-update');
        
        // Export and Reports
        Route::get('/export', [App\Http\Controllers\Admin\PropertyAdminController::class, 'export'])->name('export');
        Route::get('/reports', [App\Http\Controllers\Admin\PropertyAdminController::class, 'reports'])->name('reports');
        
        // Property Categories Management
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\PropertyCategoryAdminController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Admin\PropertyCategoryAdminController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\Admin\PropertyCategoryAdminController::class, 'show'])->name('show');
            Route::put('/{id}', [App\Http\Controllers\Admin\PropertyCategoryAdminController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Admin\PropertyCategoryAdminController::class, 'destroy'])->name('destroy');
            Route::post('/reorder', [App\Http\Controllers\Admin\PropertyCategoryAdminController::class, 'reorder'])->name('reorder');
        });
        
        // Property Analytics and Insights
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/overview', [App\Http\Controllers\Admin\PropertyAdminController::class, 'analyticsOverview'])->name('overview');
            Route::get('/popular', [App\Http\Controllers\Admin\PropertyAdminController::class, 'popularProperties'])->name('popular');
            Route::get('/trends', [App\Http\Controllers\Admin\PropertyAdminController::class, 'propertyTrends'])->name('trends');
            Route::get('/search-analytics', [App\Http\Controllers\Admin\PropertyAdminController::class, 'searchAnalytics'])->name('search');
        });
        
        // Property Enquiries Management
        Route::prefix('enquiries')->name('enquiries.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\PropertyEnquiryAdminController::class, 'index'])->name('index');
            Route::get('/{id}', [App\Http\Controllers\Admin\PropertyEnquiryAdminController::class, 'show'])->name('show');
            Route::post('/{id}/respond', [App\Http\Controllers\Admin\PropertyEnquiryAdminController::class, 'respond'])->name('respond');
            Route::post('/{id}/mark-read', [App\Http\Controllers\Admin\PropertyEnquiryAdminController::class, 'markRead'])->name('mark-read');
            Route::post('/{id}/mark-important', [App\Http\Controllers\Admin\PropertyEnquiryAdminController::class, 'markImportant'])->name('mark-important');
            Route::delete('/{id}', [App\Http\Controllers\Admin\PropertyEnquiryAdminController::class, 'destroy'])->name('destroy');
        });
    });
    
    // Events System Admin Routes
    Route::prefix('events')->name('events.')->group(function () {
        
        // Dashboard and Analytics
        Route::get('/dashboard', [App\Http\Controllers\Admin\EventAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/analytics', [App\Http\Controllers\Admin\EventAdminController::class, 'analytics'])->name('analytics');
        
        // CRUD Operations
        Route::get('/', [App\Http\Controllers\Admin\EventAdminController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Admin\EventAdminController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\Admin\EventAdminController::class, 'show'])->name('show');
        Route::put('/{id}', [App\Http\Controllers\Admin\EventAdminController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\EventAdminController::class, 'destroy'])->name('destroy');
        
        // Approval Workflow
        Route::post('/{id}/approve', [App\Http\Controllers\Admin\EventAdminController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [App\Http\Controllers\Admin\EventAdminController::class, 'reject'])->name('reject');
        Route::post('/{id}/toggle-active', [App\Http\Controllers\Admin\EventAdminController::class, 'toggleActive'])->name('toggle-active');
        
        // Promotion Management
        Route::post('/{id}/upgrade-tier', [App\Http\Controllers\Admin\EventAdminController::class, 'upgradeTier'])->name('upgrade-tier');
        Route::post('/{id}/set-featured', [App\Http\Controllers\Admin\EventAdminController::class, 'setFeatured'])->name('set-featured');
        Route::post('/{id}/set-sponsored', [App\Http\Controllers\Admin\EventAdminController::class, 'setSponsored'])->name('set-sponsored');
        
        // Bulk Operations
        Route::post('/bulk-approve', [App\Http\Controllers\Admin\EventAdminController::class, 'bulkApprove'])->name('bulk-approve');
        Route::post('/bulk-reject', [App\Http\Controllers\Admin\EventAdminController::class, 'bulkReject'])->name('bulk-reject');
        Route::post('/bulk-update', [App\Http\Controllers\Admin\EventAdminController::class, 'bulkUpdate'])->name('bulk-update');
        Route::post('/bulk-delete', [App\Http\Controllers\Admin\EventAdminController::class, 'bulkDelete'])->name('bulk-delete');
        
        // Export and Reports
        Route::get('/export', [App\Http\Controllers\Admin\EventAdminController::class, 'export'])->name('export');
        Route::get('/reports', [App\Http\Controllers\Admin\EventAdminController::class, 'reports'])->name('reports');
        Route::get('/promotion-report', [App\Http\Controllers\Admin\EventAdminController::class, 'promotionReport'])->name('promotion-report');
        
        // Event Categories Management
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\EventCategoryAdminController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Admin\EventCategoryAdminController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\Admin\EventCategoryAdminController::class, 'show'])->name('show');
            Route::put('/{id}', [App\Http\Controllers\Admin\EventCategoryAdminController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Admin\EventCategoryAdminController::class, 'destroy'])->name('destroy');
        });
        
        // Event Analytics and Insights
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/overview', [App\Http\Controllers\Admin\EventAdminController::class, 'analyticsOverview'])->name('overview');
            Route::get('/popular', [App\Http\Controllers\Admin\EventAdminController::class, 'popularEvents'])->name('popular');
            Route::get('/trends', [App\Http\Controllers\Admin\EventAdminController::class, 'eventTrends'])->name('trends');
            Route::get('/attendance', [App\Http\Controllers\Admin\EventAdminController::class, 'attendanceAnalytics'])->name('attendance');
            Route::get('/revenue', [App\Http\Controllers\Admin\EventAdminController::class, 'revenueAnalytics'])->name('revenue');
        });
        
        // Venue Management
        Route::prefix('venues')->name('venues.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\VenueAdminController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Admin\VenueAdminController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\Admin\VenueAdminController::class, 'show'])->name('show');
            Route::put('/{id}', [App\Http\Controllers\Admin\VenueAdminController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Admin\VenueAdminController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/approve', [App\Http\Controllers\Admin\VenueAdminController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [App\Http\Controllers\Admin\VenueAdminController::class, 'reject'])->name('reject');
        });
    });
    
    // Banner System Admin Routes
    Route::prefix('banners')->name('banners.')->group(function () {
        
        // Dashboard and Analytics
        Route::get('/dashboard', [BannerAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/analytics', [BannerAdminController::class, 'analytics'])->name('analytics');
        
        // CRUD Operations
        Route::get('/', [BannerAdminController::class, 'index'])->name('index');
        Route::get('/create', [BannerAdminController::class, 'create'])->name('create');
        Route::post('/', [BannerAdminController::class, 'store'])->name('store');
        Route::get('/{id}', [BannerAdminController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [BannerAdminController::class, 'edit'])->name('edit');
        Route::put('/{id}', [BannerAdminController::class, 'update'])->name('update');
        Route::delete('/{id}', [BannerAdminController::class, 'destroy'])->name('destroy');
        
        // Approval Workflow
        Route::post('/{id}/approve', [BannerAdminController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [BannerAdminController::class, 'reject'])->name('reject');
        Route::post('/{id}/toggle-active', [BannerAdminController::class, 'toggleActive'])->name('toggle-active');
        
        // Bulk Operations
        Route::post('/bulk-approve', [BannerAdminController::class, 'bulkApprove'])->name('bulk-approve');
        Route::post('/bulk-reject', [BannerAdminController::class, 'bulkReject'])->name('bulk-reject');
        
        // Export and Reports
        Route::get('/export', [BannerAdminController::class, 'export'])->name('export');
        Route::get('/system-health', [BannerAdminController::class, 'systemHealth'])->name('system-health');
    });
    
});

// API Routes for Admin (if needed)
Route::prefix('api/admin')->name('api.admin.')->middleware(['auth:api', 'admin'])->group(function () {
    
    Route::prefix('featured-adverts')->name('featured-adverts.')->group(function () {
        
        // Admin API endpoints
        Route::get('/', [FeaturedAdvertAdminController::class, 'index']);
        Route::post('/', [FeaturedAdvertAdminController::class, 'store']);
        Route::get('/{id}', [FeaturedAdvertAdminController::class, 'show']);
        Route::put('/{id}', [FeaturedAdvertAdminController::class, 'update']);
        Route::delete('/{id}', [FeaturedAdvertAdminController::class, 'destroy']);
        Route::post('/bulk-update', [FeaturedAdvertAdminController::class, 'bulkUpdate']);
        Route::post('/{id}/approve', [FeaturedAdvertAdminController::class, 'approve']);
        Route::post('/{id}/reject', [FeaturedAdvertAdminController::class, 'reject']);
        Route::get('/statistics', [FeaturedAdvertAdminController::class, 'statistics']);
        Route::get('/export', [FeaturedAdvertAdminController::class, 'export']);
    });
    
    Route::prefix('promoted-adverts')->name('promoted-adverts.')->group(function () {
        
        // Admin API endpoints
        Route::get('/dashboard-stats', [PromotedAdvertAdminController::class, 'dashboard']);
        Route::get('/{advert}/analytics', [PromotedAdvertAdminController::class, 'analytics'])->where('advert', '^[0-9]+$');
        Route::post('/bulk-approve', [PromotedAdvertAdminController::class, 'bulkApprove']);
        Route::post('/bulk-reject', [PromotedAdvertAdminController::class, 'bulkReject']);
        Route::post('/bulk-feature', [PromotedAdvertAdminController::class, 'bulkFeature']);
        Route::get('/export', [PromotedAdvertAdminController::class, 'export']);
        Route::get('/system-health', [PromotedAdvertAdminController::class, 'systemHealth']);
        Route::get('/promotion-report', [PromotedAdvertAdminController::class, 'promotionReport']);
    });
    
    Route::prefix('properties')->name('properties.')->group(function () {
        
        // Property API endpoints
        Route::get('/', [App\Http\Controllers\Admin\PropertyAdminController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Admin\PropertyAdminController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Admin\PropertyAdminController::class, 'show'])->where('id', '^[0-9]+$');
        Route::put('/{id}', [App\Http\Controllers\Admin\PropertyAdminController::class, 'update'])->where('id', '^[0-9]+$');
        Route::delete('/{id}', [App\Http\Controllers\Admin\PropertyAdminController::class, 'destroy'])->where('id', '^[0-9]+$');
        Route::post('/{id}/approve', [App\Http\Controllers\Admin\PropertyAdminController::class, 'approve'])->where('id', '^[0-9]+$');
        Route::post('/{id}/reject', [App\Http\Controllers\Admin\PropertyAdminController::class, 'reject'])->where('id', '^[0-9]+$');
        Route::post('/{id}/toggle-active', [App\Http\Controllers\Admin\PropertyAdminController::class, 'toggleActive'])->where('id', '^[0-9]+$');
        Route::post('/bulk-approve', [App\Http\Controllers\Admin\PropertyAdminController::class, 'bulkApprove']);
        Route::post('/bulk-reject', [App\Http\Controllers\Admin\PropertyAdminController::class, 'bulkReject']);
        Route::post('/bulk-update', [App\Http\Controllers\Admin\PropertyAdminController::class, 'bulkUpdate']);
        Route::get('/export', [App\Http\Controllers\Admin\PropertyAdminController::class, 'export']);
        Route::get('/analytics', [App\Http\Controllers\Admin\PropertyAdminController::class, 'analytics']);
        Route::get('/dashboard', [App\Http\Controllers\Admin\PropertyAdminController::class, 'dashboard']);
        
        // Property Categories API
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\PropertyCategoryAdminController::class, 'index']);
            Route::post('/', [App\Http\Controllers\Admin\PropertyCategoryAdminController::class, 'store']);
            Route::get('/{id}', [App\Http\Controllers\Admin\PropertyCategoryAdminController::class, 'show'])->where('id', '^[0-9]+$');
            Route::put('/{id}', [App\Http\Controllers\Admin\PropertyCategoryAdminController::class, 'update'])->where('id', '^[0-9]+$');
            Route::delete('/{id}', [App\Http\Controllers\Admin\PropertyCategoryAdminController::class, 'destroy'])->where('id', '^[0-9]+$');
            Route::post('/reorder', [App\Http\Controllers\Admin\PropertyCategoryAdminController::class, 'reorder']);
        });
        
        // Property Enquiries API
        Route::prefix('enquiries')->name('enquiries.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\PropertyEnquiryAdminController::class, 'index']);
            Route::get('/{id}', [App\Http\Controllers\Admin\PropertyEnquiryAdminController::class, 'show'])->where('id', '^[0-9]+$');
            Route::post('/{id}/respond', [App\Http\Controllers\Admin\PropertyEnquiryAdminController::class, 'respond'])->where('id', '^[0-9]+$');
            Route::post('/{id}/mark-read', [App\Http\Controllers\Admin\PropertyEnquiryAdminController::class, 'markRead'])->where('id', '^[0-9]+$');
            Route::post('/{id}/mark-important', [App\Http\Controllers\Admin\PropertyEnquiryAdminController::class, 'markImportant'])->where('id', '^[0-9]+$');
            Route::delete('/{id}', [App\Http\Controllers\Admin\PropertyEnquiryAdminController::class, 'destroy'])->where('id', '^[0-9]+$');
        });
    });
    
    Route::prefix('banners')->name('banners.')->group(function () {
        
        // Banner API endpoints
        Route::get('/', [BannerAdminController::class, 'index']);
        Route::get('/create', [BannerAdminController::class, 'create']);
        Route::post('/', [BannerAdminController::class, 'store']);
        Route::get('/{id}', [BannerAdminController::class, 'show']);
        Route::get('/{id}/edit', [BannerAdminController::class, 'edit']);
        Route::put('/{id}', [BannerAdminController::class, 'update']);
        Route::delete('/{id}', [BannerAdminController::class, 'destroy']);
        Route::post('/{id}/approve', [BannerAdminController::class, 'approve']);
        Route::post('/{id}/reject', [BannerAdminController::class, 'reject']);
        Route::post('/{id}/toggle-active', [BannerAdminController::class, 'toggleActive']);
        Route::post('/bulk-approve', [BannerAdminController::class, 'bulkApprove']);
        Route::post('/bulk-reject', [BannerAdminController::class, 'bulkReject']);
        Route::get('/export', [BannerAdminController::class, 'export']);
        Route::get('/analytics', [BannerAdminController::class, 'analytics']);
        Route::get('/dashboard', [BannerAdminController::class, 'dashboard']);
        Route::get('/system-health', [BannerAdminController::class, 'systemHealth']);
    });
    
});
