<?php

use Illuminate\Support\Facades\Route;
use Modules\Restaurant\Http\Controllers\RestaurantController;
use Modules\Restaurant\Http\Controllers\RestaurantGalleryController;
use Modules\Restaurant\Http\Controllers\RestaurantOnboardingController;
use Modules\Restaurant\Http\Controllers\RestaurantApprovalController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Frontend Restaurant Onboarding Routes
Route::middleware('web')->group(function () {
    Route::post('/restaurant/onboarding/corrections/registration', [\Modules\Restaurant\Http\Controllers\OnboardingCorrectionController::class, 'registration'])
        ->middleware(['auth:admin', 'throttle:restaurant-onboarding-write'])->name('restaurant.onboarding.corrections.registration');
    Route::post('/restaurant/onboarding/corrections/menu', [\Modules\Restaurant\Http\Controllers\OnboardingCorrectionController::class, 'menu'])
        ->middleware(['auth:admin', 'throttle:restaurant-onboarding-write'])->name('restaurant.onboarding.corrections.menu');
    Route::get('/restaurant/onboarding/kyc', \Modules\Restaurant\Http\Controllers\RestaurantOnboardingStatusController::class)
        ->name('restaurant.onboarding.kyc');

    Route::get('/partner-with-us', [RestaurantOnboardingController::class, 'showForm'])->name('restaurant.onboarding.form');
    Route::post('/partner-with-us/check-email', [RestaurantOnboardingController::class, 'checkEmail'])
        ->middleware('throttle:20,1')->name('restaurant.onboarding.check-email');
    Route::post('/partner-with-us', [RestaurantOnboardingController::class, 'submit'])->middleware('throttle:3,1')->name('restaurant.onboarding.submit');
});

Route::prefix(config('admin::prefix.admin'))->middleware('web')->name('admin.')->group(function () {

    Route::middleware(['check.permission'])->group(function () {
        
        // Restaurant Approval Management
        Route::prefix('restaurant/approvals')->name('restaurant.approvals.')
            ->middleware(['auth:admin', \Modules\Restaurant\Http\Middleware\EnsureRestaurantApprovalAccess::class])->group(function () {
            Route::get('/', [RestaurantApprovalController::class, 'index'])->name('index');
            Route::get('/{id}', [RestaurantApprovalController::class, 'show'])->name('show');
            Route::patch('/{id}/status', [RestaurantApprovalController::class, 'updateStatus'])
                ->middleware('auth:admin')->name('status.update');
            Route::patch('/{id}/onboarding-status', [RestaurantApprovalController::class, 'updateOnboardingStatus'])
                ->middleware('auth:admin')->name('onboarding-status.update');
            Route::get('/{id}/review/pdf', [RestaurantApprovalController::class, 'downloadReviewPdf'])
                ->middleware('auth:admin')->name('review.pdf');
            Route::post('/{id}/start-review', [RestaurantApprovalController::class, 'startReview'])
                ->middleware('auth:admin')->name('start-review');
            Route::post('/{id}/request-changes', [RestaurantApprovalController::class, 'requestChanges'])->name('request-changes');
            Route::post('/{id}/menu/review', [RestaurantApprovalController::class, 'reviewMenu'])->name('menu.review');
            Route::post('/{id}/approve', [RestaurantApprovalController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [RestaurantApprovalController::class, 'reject'])->name('reject');
            Route::patch('/{id}/banking', [RestaurantApprovalController::class, 'updateBanking'])
                ->middleware('auth:admin')->name('banking.update');
            Route::post('/{id}/banking/approve', [RestaurantApprovalController::class, 'approveBanking'])
                ->middleware('auth:admin')->name('banking.approve');
            Route::post('/{id}/banking/reject', [RestaurantApprovalController::class, 'rejectBanking'])
                ->middleware('auth:admin')->name('banking.reject');
            Route::post('/{id}/tax/approve', [RestaurantApprovalController::class, 'approveTax'])
                ->middleware('auth:admin')->name('tax.approve');
            Route::patch('/{id}/tax', [RestaurantApprovalController::class, 'updateTax'])
                ->middleware('auth:admin')->name('tax.update');
            Route::patch('/{id}/tax/type', [RestaurantApprovalController::class, 'updateTaxType'])
                ->middleware('auth:admin')->name('tax.type');
            Route::post('/{id}/tax/reject', [RestaurantApprovalController::class, 'rejectTax'])
                ->middleware('auth:admin')->name('tax.reject');
            Route::put('/{id}/registration', [RestaurantApprovalController::class, 'updateRegistration'])->name('registration.update');
            Route::patch('/{id}/registration/email-verification', [RestaurantApprovalController::class, 'updateEmailVerification'])
                ->middleware('auth:admin')->name('registration.email-verification');
        });

        Route::prefix('restaurant')->name('restaurant.')->group(function () {
            Route::get('/', [RestaurantController::class, 'index'])->name('index');
            Route::get('/create', [RestaurantController::class, 'create'])->name('create');
            Route::get('/{id}/edit', [RestaurantController::class, 'edit'])->name('edit');
            Route::get('/{id}/view', [RestaurantController::class, 'view'])->name('view');
            Route::delete('/{id}', [RestaurantController::class, 'destroy'])->name('delete');

            Route::post('/store-general', [RestaurantController::class, 'storeGeneral'])->name('store.general');
            Route::post('/{restaurant}/hours', [RestaurantController::class, 'updateHours'])->name('update.hours');
            Route::post('/{restaurant}/address', [RestaurantController::class, 'updateAddress'])->name('update.address');
            Route::post('/{restaurant}/holidays', [RestaurantController::class, 'updateHolidays'])->name('update.holidays');
            Route::post('/{restaurant}/settings', [RestaurantController::class, 'updateSettings'])->middleware(\Modules\Restaurant\Http\Middleware\EnsureRestaurantFeaturesApproved::class . ':settings')->name('update.settings');
            
            // Gallery routes
            Route::post('/gallery', [RestaurantGalleryController::class, 'store'])->middleware(\Modules\Restaurant\Http\Middleware\EnsureRestaurantFeaturesApproved::class . ':gallery')->name('gallery.store');
            Route::delete('/gallery/{id}', [RestaurantGalleryController::class, 'destroy'])->middleware(\Modules\Restaurant\Http\Middleware\EnsureRestaurantFeaturesApproved::class . ':gallery')->name('gallery.destroy');
            
            // Bulk operations
            Route::post('/bulk-delete', [RestaurantController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/bulk-status', [RestaurantController::class, 'bulkStatus'])->name('bulk-status');
        });
    });
});
