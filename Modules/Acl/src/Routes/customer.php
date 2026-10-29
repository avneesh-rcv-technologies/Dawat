<?php

use Illuminate\Support\Facades\Route;
use Modules\Acl\Http\Controllers\Customer\CustomerAuthController;

/*
|--------------------------------------------------------------------------
| Customer Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('web')->group(function () {
    Route::prefix(config('acl::guards.guards.customer.prefix'))
        ->name(config('acl::guards.guards.customer.prefix') . '.')
        ->group(function () {

        Route::middleware('guest:customer')->group(function () {
            Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login.form');
            Route::get('/register', [CustomerAuthController::class, 'showRegisterForm'])->name('register.form');
            Route::get('/forgot-password', [CustomerAuthController::class, 'showForgotPasswordForm'])->name('password.request');
            Route::get('/reset-password/{token}', [CustomerAuthController::class, 'showResetPasswordForm'])->name('password.reset');
        });
        // API routes delegated to ACL Module (handles authentication logic)
        Route::post('/login', [CustomerAuthController::class, 'login'])->name('login');
        Route::post('/register', [CustomerAuthController::class, 'register'])->name('register');
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
        Route::post('/forgot-password', [CustomerAuthController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::post('/reset-password', [CustomerAuthController::class, 'resetPassword'])->name('password.update');
    });
    
    Route::middleware(['web','check.permission'])->group(function () {
        // Example protected routes:
        // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        // Route::resource('users', UserCustomerController::class);
        // Route::post('/users/toggle-status', [UserCustomerController::class, 'toggleStatus'])->name('users.toggle-status');
        // Route::post('/users/bulk-delete', [UserCustomerController::class, 'bulkDelete'])->name('users.bulk-delete');
        // Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        // Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    });
});
