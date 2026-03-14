<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\OTPController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\Frontend\BooksDashboardController;
use App\Http\Controllers\Frontend\BannerDashboardController;
use App\Http\Controllers\Frontend\BuySellDashboardController;
use App\Http\Controllers\BuySellController;
use App\Http\Controllers\SponsoredDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

// Authentication Routes
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Registration Routes
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

// Password Reset Routes
Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');

// Email Verification Routes
Route::get('/email/verify', [EmailVerificationController::class, 'show'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->name('verification.send');

// OTP Routes
Route::get('/otp/send', [OTPController::class, 'show'])->name('otp.send');
Route::post('/otp/send', [OTPController::class, 'send'])->name('otp.send.post');
Route::get('/otp/verify', [OTPController::class, 'show'])->name('otp.verify');
Route::post('/otp/verify', [OTPController::class, 'verify'])->name('otp.verify.post');

// Welcome Route
Route::get('/welcome', function () {
    return view('auth.welcome');
})->name('welcome');

// Vehicle Routes
Route::get('/vehicles', function () {
    return view('vehicles.index');
})->name('vehicles.index');

Route::get('/vehicles/create', function () {
    return view('vehicles.create');
})->middleware('auth')->name('vehicles.create');

Route::get('/vehicles/{id}', function ($id) {
    return view('vehicles.show', ['vehicleId' => $id]);
})->name('vehicles.show');

Route::get('/vehicles/my-vehicles', function () {
    return view('vehicles.my-vehicles');
})->middleware('auth')->name('vehicles.my-vehicles');

Route::get('/', function () {
    return view('landing');
});

// KYC and User Dashboard Routes
Route::get('/kyc-submission', function () {
    return view('kyc-submission');
})->middleware('auth');

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/create-listing', function () {
    return view('create-listing');
})->middleware('auth');

Route::get('/listing/{slug}', function ($slug) {
    return view('listing-detail', ['slug' => $slug]);
});

Route::get('/profile', function () {
    return view('profile');
})->middleware('auth');

Route::get('/settings', function () {
    return view('settings');
})->middleware('auth');

// Books Routes
Route::get('/books', function () {
    return view('books.index');
})->name('books.index');

Route::get('/books/create', function () {
    return view('books.create');
})->middleware('auth')->name('books.create');

Route::get('/books/{slug}', function ($slug) {
    return view('books.show', ['slug' => $slug]);
})->name('books.show');

// Resorts & Travel Routes
Route::get('/resorts-travel', function () {
    return view('resorts-travel');
})->name('resorts-travel.index');

Route::get('/resorts-travel/create', function () {
    return view('resorts-travel.create');
})->middleware('auth')->name('resorts-travel.create');

Route::get('/resorts-travel/{slug}', function ($slug) {
    return view('resorts-travel.show', ['slug' => $slug]);
})->name('resorts-travel.show');

// Promoted Adverts Routes
Route::get('/promoted-adverts', function () {
    return view('promoted-adverts');
})->name('promoted-adverts.index');

Route::get('/promoted-adverts/create', function () {
    return view('create-promoted-advert');
})->middleware('auth')->name('promoted-adverts.create');

Route::get('/promoted-adverts/{slug}', function ($slug) {
    return view('promoted-advert-detail', ['slug' => $slug]);
})->name('promoted-adverts.show');

// Jobs & Vacancies Routes
Route::get('/jobs', function () {
    return view('jobs');
})->name('jobs.index');

Route::get('/jobs/create', function () {
    return view('jobs.create');
})->middleware('auth')->name('jobs.create');

Route::get('/job-seekers', function () {
    return view('job-seekers');
})->name('job-seekers.index');

Route::get('/job-alerts', function () {
    return view('job-alerts');
})->middleware('auth')->name('job-alerts.index');

Route::get('/dashboard/jobs', function () {
    return view('dashboard.jobs');
})->middleware('auth')->name('dashboard.jobs');

// Property Routes
Route::get('/property', [PropertyController::class, 'index'])->name('property.index');
Route::get('/property/search', [PropertyController::class, 'search'])->name('property.search');
Route::get('/property/post', [PropertyController::class, 'create'])->middleware('auth')->name('property.create');
Route::post('/property', [PropertyController::class, 'store'])->middleware('auth')->name('property.store');
Route::get('/property/{id}', [PropertyController::class, 'show'])->name('property.show');
Route::get('/property/{id}/edit', [PropertyController::class, 'edit'])->middleware('auth')->name('property.edit');
Route::put('/property/{id}', [PropertyController::class, 'update'])->middleware('auth')->name('property.update');
Route::delete('/property/{id}', [PropertyController::class, 'destroy'])->middleware('auth')->name('property.destroy');
Route::get('/my-properties', [PropertyController::class, 'myProperties'])->middleware('auth')->name('property.my');
Route::post('/property/{id}/save', [PropertyController::class, 'save'])->middleware('auth')->name('property.save');
Route::get('/saved-properties', [PropertyController::class, 'saved'])->middleware('auth')->name('property.saved');
Route::post('/property/{id}/contact', [PropertyController::class, 'contact'])->name('property.contact');

// Events & Venues Routes
Route::get('/events-venues', function () {
    return view('events-venues.index');
})->name('events-venues.index');

Route::get('/events', function () {
    return view('events.index');
})->name('events.index');

Route::get('/events/create', function () {
    return view('events.create');
})->middleware('auth')->name('events.create');

Route::get('/events/{slug}', function ($slug) {
    return view('events.show', ['slug' => $slug]);
})->name('events.show');

Route::get('/my-events', function () {
    return view('events.my-events');
})->middleware('auth')->name('events.my-events');

Route::get('/venues', function () {
    return view('venues.index');
})->name('venues.index');

Route::get('/venues/create', function () {
    return view('venues.create');
})->middleware('auth')->name('venues.create');

Route::get('/venues/{slug}', function ($slug) {
    return view('venues.show', ['slug' => $slug]);
})->name('venues.show');

Route::get('/my-venues', function () {
    return view('venues.my-venues');
})->middleware('auth')->name('venues.my-venues');

Route::get('/venue-services', function () {
    return view('venue-services.index');
})->name('venue-services.index');

Route::get('/venue-services/create', function () {
    return view('venue-services.create');
})->middleware('auth')->name('venue-services.create');

Route::get('/venue-services/{slug}', function ($slug) {
    return view('venue-services.show', ['slug' => $slug]);
})->name('venue-services.show');

Route::get('/my-venue-services', function () {
    return view('venue-services.my-services');
})->middleware('auth')->name('venue-services.my-services');

// Affiliate Hub Routes
Route::get('/affiliate-dashboard', function () {
    return view('affiliate-dashboard');
})->middleware('auth')->name('affiliate.dashboard');

Route::get('/affiliates', function () {
    return view('affiliates.index');
})->name('affiliates.index');

Route::group(['prefix' => 'sponsored', 'middleware' => ['auth']], function () {
        
        // Public routes
        Route::get('/stats', [SponsoredAdvertController::class, 'stats']);
        Route::get('/activity', [SponsoredAdvertController::class, 'activity']);
        Route::get('/categories', [SponsoredCategoryController::class, 'index']);
        Route::get('/adverts', [SponsoredAdvertController::class, 'index']);
        Route::get('/adverts/search', [SponsoredAdvertController::class, 'search']);
        Route::get('/adverts/featured', [SponsoredAdvertController::class, 'featured']);
        Route::get('/adverts/category/{slug}', [SponsoredAdvertController::class, 'byCategory']);
        Route::get('/adverts/{id}', [SponsoredAdvertController::class, 'show']);
        Route::get('/categories/{slug}', [SponsoredCategoryController::class, 'show']);

        // Authenticated routes
        Route::post('/adverts', [SponsoredAdvertController::class, 'store']);
        Route::put('/adverts/{id}', [SponsoredAdvertController::class, 'update']);
        Route::delete('/adverts/{id}', [SponsoredAdvertController::class, 'destroy']);
        Route::get('/adverts/my-adverts', [SponsoredAdvertController::class, 'userAdverts']);
        Route::post('/adverts/{advertId}/save', [SponsoredAdvertController::class, 'save']);
        Route::get('/adverts/saved', [SponsoredAdvertController::class, 'savedAdverts']);
        Route::post('/adverts/{advertId}/track', [SponsoredAdvertController::class, 'track']);
        Route::get('/adverts/{advertId}/analytics', [SponsoredAdvertController::class, 'analytics']);
        
        // Category management (admin only)
        Route::post('/categories', [SponsoredCategoryController::class, 'store']);
        Route::put('/categories/{id}', [SponsoredCategoryController::class, 'update']);
        Route::delete('/categories/{id}', [SponsoredCategoryController::class, 'destroy']);
});

// Books Marketplace Frontend Routes
Route::group(['prefix' => 'books'], function () {
    // Public routes
    Route::get('/', function () {
        return view('frontend.books.browse');
    })->name('books.browse');
    
    Route::get('/{slug}', function ($slug) {
        return view('frontend.books.show', compact('slug'));
    })->name('books.show');
    
    Route::get('/genre/{genre}', function ($genre) {
        return view('frontend.books.genre', compact('genre'));
    })->name('books.genre');
});

// Books Marketplace Authenticated Routes
Route::group(['prefix' => 'books', 'middleware' => ['auth']], function () {
    Route::get('/dashboard', [BooksDashboardController::class, 'dashboard'])->name('books.dashboard');
    Route::get('/my-books', [BooksDashboardController::class, 'myBooks'])->name('books.my');
    Route::get('/create', [BooksDashboardController::class, 'create'])->name('books.create');
    Route::get('/{id}/edit', [BooksDashboardController::class, 'edit'])->name('books.edit');
    Route::get('/{id}/analytics', [BooksDashboardController::class, 'analytics'])->name('books.analytics');
    Route::get('/payments', [BooksDashboardController::class, 'payments'])->name('books.payments');
});

// Banner Adverts Frontend Routes
Route::group(['prefix' => 'banner-adverts'], function () {
    // Public routes
    Route::get('/', function () {
        return view('frontend.banners.browse');
    })->name('banners.browse');
    
    Route::get('/{slug}', function ($slug) {
        return view('frontend.banners.show', compact('slug'));
    })->name('banners.show');
    
    Route::get('/category/{category}', function ($category) {
        return view('frontend.banners.category', compact('category'));
    })->name('banners.category');
});

// Banner Adverts Authenticated Routes
Route::group(['prefix' => 'banner-adverts', 'middleware' => ['auth']], function () {
    Route::get('/dashboard', [BannerDashboardController::class, 'index'])->name('banners.dashboard');
    Route::get('/my-banners', [BannerDashboardController::class, 'myBanners'])->name('banners.my');
    Route::get('/create', [BannerDashboardController::class, 'create'])->name('banners.create');
    Route::get('/{id}/edit', [BannerDashboardController::class, 'edit'])->name('banners.edit');
    Route::get('/{id}/analytics', [BannerDashboardController::class, 'analytics'])->name('banners.analytics');
});

// Buy & Sell Routes
Route::group(['prefix' => 'buy-sell'], function () {
    // Public routes
    Route::get('/', [BuySellController::class, 'index'])->name('buysell.index');
    Route::get('/browse', [BuySellController::class, 'browse'])->name('buysell.browse');
    Route::get('/{slug}', [BuySellController::class, 'show'])->name('buysell.show');
    Route::get('/promotion-plans', [BuySellController::class, 'promotionPlans'])->name('buysell.promotion-plans');
    
    // Authenticated routes
    Route::group(['middleware' => ['auth']], function () {
        Route::get('/dashboard', [BuySellDashboardController::class, 'dashboard'])->name('buysell.dashboard');
        Route::get('/create', [BuySellDashboardController::class, 'create'])->name('buysell.create');
        Route::post('/create', [BuySellDashboardController::class, 'store'])->name('buysell.store');
        Route::get('/{id}/edit', [BuySellDashboardController::class, 'edit'])->name('buysell.edit');
        Route::put('/{id}', [BuySellDashboardController::class, 'update'])->name('buysell.update');
        Route::delete('/{id}', [BuySellDashboardController::class, 'destroy'])->name('buysell.destroy');
        Route::get('/my-adverts', [BuySellDashboardController::class, 'myAdverts'])->name('buysell.my-adverts');
        Route::get('/saved-adverts', [BuySellDashboardController::class, 'savedAdverts'])->name('buysell.saved-adverts');
        Route::get('/{id}/analytics', [BuySellDashboardController::class, 'analytics'])->name('buysell.analytics');
    });
});
