<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\Auth\AuthController;
use Modules\Admin\Http\Controllers\DashboardController;
use Modules\Admin\Http\Controllers\UserAdminController;
use Modules\Admin\Http\Controllers\ProfileController;
use Modules\Admin\Http\Controllers\GalleryController;
use Modules\Admin\Http\Controllers\RestaurantAnalyticsController;
use Modules\Admin\Http\Controllers\CustomerAnalyticsController;
use Modules\Admin\Http\Controllers\OrderAnalyticsController;
use Modules\Admin\Http\Controllers\TransactionAnalyticsController;

Route::middleware('web')->group(function () {
    Route::prefix(config('acl::guards.guards.admin.prefix'))->name(config('acl::guards.guards.admin.prefix') . '.')->group(function () {

        // /admin → login page for guests, dashboard for signed-in admins.
        Route::get('/', function () {
            return auth('admin')->check()
                ? redirect()->route('dashboard.index')
                : redirect()->route(config('acl::guards.guards.admin.prefix') . '.login.form');
        })->name('home');

        Route::middleware('guest:admin')->group(function () {
            Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
            Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register.form');
            Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
            Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
        });

        // API routes delegated to ACL Module (handles authentication logic)
        Route::get('/email/verify/{id}/{hash}', \Modules\Acl\Http\Controllers\AdminEmailVerificationController::class)
            ->middleware('throttle:6,1')->whereNumber('id')->name('verification.verify');
        Route::post('/login', [AuthController::class, 'login'])->middleware(['throttle:5,1', 'password.transport'])->name('login');
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])
            ->middleware('throttle:admin-forgot-password')->name('password.email');
        Route::post('/reset-password', [AuthController::class, 'reset'])
            ->middleware(['throttle:admin-reset-password', 'password.transport'])->name('password.update');

        // One-time nonce + RSA public key used by web login/reset forms (admin and restaurant partner).
        Route::get('/password-challenge', \Modules\Acl\Http\Controllers\PasswordChallengeController::class)
            ->middleware('throttle:30,1')->name('password.challenge');
    });
    
    Route::post('profile/dietary-certificates', [ProfileController::class, 'uploadCertificate'])
        ->middleware(['auth:admin', 'throttle:20,1'])->name('profile.certificates.store');

    Route::middleware(['web','check.permission'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth:admin')->name('dashboard.index');
        Route::resource('users', UserAdminController::class)->except(['destroy']);
        Route::post('/users/toggle-status', [UserAdminController::class, 'toggleStatus'])->name('users.toggle-status');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

        // Gallery Management Routes
        Route::get('gallery', [GalleryController::class, 'index'])->name('gallery.index');
        Route::post('gallery', [GalleryController::class, 'store'])->name('gallery.store');
        Route::delete('gallery/{id}', [GalleryController::class, 'destroy'])->name('gallery.destroy');

        // Analytics Detail Pages
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('restaurant/{restaurantId}', [RestaurantAnalyticsController::class, 'show'])->name('restaurant.show');
            Route::get('customer/{customerId}', [CustomerAnalyticsController::class, 'show'])->name('customer.show');
            Route::get('order/{orderId}', [OrderAnalyticsController::class, 'show'])->name('order.show');
            Route::get('transaction/{transactionId}', [TransactionAnalyticsController::class, 'show'])->name('transaction.show');
        });
    });
});
