<?php

use Illuminate\Support\Facades\Route;
use Modules\Acl\Http\Controllers\Api\CustomerAddressApiController;
use Modules\Acl\Http\Controllers\Api\CustomerAuthApiController;
use Modules\Acl\Http\Controllers\Api\CustomerMobileAuthApiController;
use Modules\Acl\Http\Controllers\Api\CustomerSocialAuthApiController;
use Modules\Acl\Http\Controllers\Api\GuestCheckoutApiController;
use Modules\Acl\Http\Controllers\Api\CustomerContactController;

/*
|--------------------------------------------------------------------------
| Customer API Authentication Routes
|--------------------------------------------------------------------------
|
| These routes handle customer authentication via API with token responses
|
*/

Route::prefix('api/customer')->name('api.customer.')->group(function () {
    // Public authentication routes (rate limited)
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/login', [CustomerAuthApiController::class, 'login'])->name('login');
        Route::post('/register', [CustomerAuthApiController::class, 'register'])->name('register');
        Route::post('/forgot-password', [CustomerAuthApiController::class, 'forgotPassword'])->name('forgot-password');
        Route::post('/reset-password', [CustomerAuthApiController::class, 'resetPassword'])->name('reset-password');
        
    });

    // Mobile OTP Authentication Routes (Unified, rate limited)
    Route::prefix('auth')->name('auth.')->middleware('throttle:5,1')->group(function () {
        // OTP Authentication (Unified for login/register)
        Route::post('/send-otp', [CustomerMobileAuthApiController::class, 'sendOtp'])->name('send-otp');
        Route::post('/verify-otp', [CustomerMobileAuthApiController::class, 'verifyOtp'])->name('verify-otp');
        Route::post('/resend-otp', [CustomerMobileAuthApiController::class, 'resendOtp'])->name('resend-otp');
        
        // Social Authentication (Google & Apple)
        Route::post('/google', [CustomerSocialAuthApiController::class, 'google'])->name('google');
        Route::post('/apple', [CustomerSocialAuthApiController::class, 'apple'])->name('apple');
        
        // Protected routes (require authentication)
        Route::middleware('auth:sanctum')->group(function () {
            // Link/Unlink social accounts
            Route::post('/link/{provider}', [CustomerSocialAuthApiController::class, 'link'])->name('link');
            Route::delete('/unlink/{provider}', [CustomerSocialAuthApiController::class, 'unlink'])->name('unlink');
            
            // Get linked social accounts
            Route::get('/social-accounts', [CustomerSocialAuthApiController::class, 'socialAccounts'])->name('social-accounts');
        });
    });

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [CustomerAuthApiController::class, 'logout'])->name('logout');
        Route::get('/profile', [CustomerAuthApiController::class, 'profile'])->name('profile');
        Route::put('/update-profile', [CustomerAuthApiController::class, 'updateProfile'])->name('update-profile');
        Route::post('/change-password', [CustomerAuthApiController::class, 'changePassword'])->name('change-password');

        Route::prefix('addresses')->name('addresses.')->group(function () {
            Route::get('/', [CustomerAddressApiController::class, 'index'])->name('index');
            Route::post('/', [CustomerAddressApiController::class, 'store'])->name('store');
            Route::get('/get-default', [CustomerAddressApiController::class, 'getDefault'])->name('default');
            Route::post('/set-default', [CustomerAddressApiController::class, 'setDefault'])->name('set-default');
            Route::get('/{id}', [CustomerAddressApiController::class, 'show'])->name('show');
            Route::put('/{id}', [CustomerAddressApiController::class, 'update'])->name('update');
            Route::delete('/{id}', [CustomerAddressApiController::class, 'destroy'])->name('destroy');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Guest Checkout Routes
|--------------------------------------------------------------------------
|
| Guest token generation for checkout flow
|
*/

Route::prefix('api/guest')->name('api.guest.')->group(function () {
    // Public guest token generation
    Route::post('/generate-token', [GuestCheckoutApiController::class, 'generateToken'])->name('generate-token');
});

/*
|--------------------------------------------------------------------------
| Support/Contact Routes
|--------------------------------------------------------------------------
|
| All support routes use Sanctum authentication (both guest and customer)
|
*/

Route::prefix('api/support')->middleware('auth:sanctum')->name('api.support.')->group(function () {
    Route::get('/categories', [CustomerContactController::class, 'categories'])->name('categories');
    Route::post('/contact', [CustomerContactController::class, 'store'])->name('contact');
    Route::get('/messages', [CustomerContactController::class, 'index'])->name('messages.index');
    Route::get('/messages/{referenceNumber}', [CustomerContactController::class, 'show'])->name('messages.show');
});
