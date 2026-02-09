<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\OTPController;

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

