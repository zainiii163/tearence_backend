<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PromotedAdvertAdminController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\SponsoredAdvertAdminController;

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
        Route::get('/{advert}/analytics', [PromotedAdvertAdminController::class, 'analytics'])->name('analytics');
        
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
        Route::get('/{id}', [SponsoredAdvertAdminController::class, 'show']);
        Route::post('/{id}/approve', [SponsoredAdvertAdminController::class, 'approve']);
        Route::post('/{id}/reject', [SponsoredAdvertAdminController::class, 'reject']);
        Route::post('/{id}/toggle-active', [SponsoredAdvertAdminController::class, 'toggleActive']);
        Route::post('/{id}/update-tier', [SponsoredAdvertAdminController::class, 'updateTier']);
        Route::get('/{id}/analytics', [SponsoredAdvertAdminController::class, 'analytics']);
        Route::post('/bulk-approve', [SponsoredAdvertAdminController::class, 'bulkApprove']);
        Route::post('/bulk-reject', [SponsoredAdvertAdminController::class, 'bulkReject']);
        Route::get('/export', [SponsoredAdvertAdminController::class, 'export']);
        Route::get('/system-health', [SponsoredAdvertAdminController::class, 'systemHealth']);
        Route::get('/promotion-report', [SponsoredAdvertAdminController::class, 'promotionReport']);
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
        Route::get('/{advert}/analytics', [PromotedAdvertAdminController::class, 'analytics']);
        Route::post('/bulk-approve', [PromotedAdvertAdminController::class, 'bulkApprove']);
        Route::post('/bulk-reject', [PromotedAdvertAdminController::class, 'bulkReject']);
        Route::post('/bulk-feature', [PromotedAdvertAdminController::class, 'bulkFeature']);
        Route::get('/export', [PromotedAdvertAdminController::class, 'export']);
        Route::get('/system-health', [PromotedAdvertAdminController::class, 'systemHealth']);
        Route::get('/promotion-report', [PromotedAdvertAdminController::class, 'promotionReport']);
    });
    
});
