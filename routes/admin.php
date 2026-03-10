<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PromotedAdvertAdminController;
use App\Http\Controllers\Admin\AdminDashboardController;

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
    
});

// API Routes for Admin (if needed)
Route::prefix('api/admin')->name('api.admin.')->middleware(['auth:api', 'admin'])->group(function () {
    
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
