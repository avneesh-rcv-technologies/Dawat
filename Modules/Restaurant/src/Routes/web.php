<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\Auth\AuthController;
use Modules\Restaurant\Http\Controllers\OnboardingCertificateController;
use Modules\Restaurant\Http\Controllers\OnboardingCorrectionController;
use Modules\Restaurant\Http\Controllers\OnboardingMenuController;
use Modules\Restaurant\Http\Controllers\RestaurantApprovalController;
use Modules\Restaurant\Http\Controllers\RestaurantController;
use Modules\Restaurant\Http\Controllers\RestaurantDietaryCertificatesController;
use Modules\Restaurant\Http\Controllers\RestaurantGalleryController;
use Modules\Restaurant\Http\Controllers\RestaurantGallerySettingsController;
use Modules\Restaurant\Http\Controllers\RestaurantGeneralInformationController;
use Modules\Restaurant\Http\Controllers\RestaurantHolidayController;
use Modules\Restaurant\Http\Controllers\RestaurantMenuController;
use Modules\Restaurant\Http\Controllers\RestaurantOnboardingController;
use Modules\Restaurant\Http\Controllers\RestaurantOnboardingStatusController;
use Modules\Restaurant\Http\Controllers\RestaurantSettingsController;
use Modules\Restaurant\Http\Controllers\RestaurantWorkingHoursController;
use Modules\Restaurant\Http\Middleware\EnsureRestaurantApprovalAccess;
use Modules\Restaurant\Http\Middleware\EnsureRestaurantFeaturesApproved;
use Modules\Restaurant\Http\Middleware\ResolveMenuRestaurant;

Route::prefix('settings/restaurant')->name('settings.restaurant.')
    ->middleware(['web', 'auth:admin', 'check.permission:admin'])->group(function () {
        Route::get('/dietary-certificates', [RestaurantDietaryCertificatesController::class, 'index'])->name('dietary-certificates.index');
        Route::post('/dietary-certificates', [RestaurantDietaryCertificatesController::class, 'store'])->middleware('throttle:20,1')->name('dietary-certificates.store');
        Route::get('/general-information', [RestaurantGeneralInformationController::class, 'index'])->name('general-information.index');
        Route::post('/general-information', [RestaurantGeneralInformationController::class, 'update'])->name('general-information.update');
        Route::post('/general-information/address', [RestaurantGeneralInformationController::class, 'updateAddress'])->name('general-information.address');
        Route::get('/gallery', [RestaurantGallerySettingsController::class, 'index'])->name('gallery.index');
        Route::post('/gallery', [RestaurantGallerySettingsController::class, 'store'])->name('gallery.store');
        Route::delete('/gallery/{id}', [RestaurantGallerySettingsController::class, 'destroy'])->whereNumber('id')->name('gallery.destroy');
        Route::get('/preferences', [RestaurantSettingsController::class, 'index'])->name('preferences.index');
        Route::post('/preferences', [RestaurantSettingsController::class, 'update'])->name('preferences.update');
        Route::get('/delivery-policy', [RestaurantSettingsController::class, 'deliveryPolicy'])->name('delivery-policy.index');
        Route::post('/delivery-policy', [RestaurantSettingsController::class, 'updateDeliveryPolicy'])->name('delivery-policy.update');
    });

Route::prefix('settings/restaurant/holidays')->name('settings.restaurant.holidays.')
    ->middleware(['web', 'auth:admin', 'check.permission:admin'])->group(function () {
        Route::get('/', [RestaurantHolidayController::class, 'index'])->name('index');
        Route::post('/', [RestaurantHolidayController::class, 'update'])->name('update');
    });

Route::prefix('settings/restaurant/working-hours')->name('settings.restaurant.working-hours.')
    ->middleware(['web', 'auth:admin', 'check.permission:admin'])->group(function () {
        Route::get('/', [RestaurantWorkingHoursController::class, 'index'])->name('index');
        Route::post('/', [RestaurantWorkingHoursController::class, 'update'])->name('update');
    });

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
    Route::get('/restaurant/login', [AuthController::class, 'showLoginForm'])
        ->middleware('guest:admin')->name('restaurant.login.form');
    Route::post('/restaurant/login', [AuthController::class, 'login'])
        ->middleware(['throttle:5,1', 'password.transport'])->name('restaurant.login');

    Route::post('/restaurant/onboarding/corrections/registration', [OnboardingCorrectionController::class, 'registration'])
        ->middleware(['auth:admin', 'throttle:restaurant-onboarding-write'])->name('restaurant.onboarding.corrections.registration');
    Route::get('/restaurant/onboarding/kyc', RestaurantOnboardingStatusController::class)
        ->middleware('auth:admin')->name('restaurant.onboarding.kyc');
    Route::middleware('auth:admin')->prefix('restaurant/onboarding/certificates')->name('restaurant.onboarding.certificates.')->group(function () {
        Route::post('/', [OnboardingCertificateController::class, 'store'])->middleware('throttle:20,1')->name('store');
        Route::post('/skip', [OnboardingCertificateController::class, 'skip'])->name('skip');
        Route::get('/{id}/document', [OnboardingCertificateController::class, 'document'])->whereNumber('id')->name('document');
    });
    // Optional onboarding section: menu details (food items, categories, item features).
    Route::middleware('auth:admin')->prefix('restaurant/onboarding/menu')->name('restaurant.onboarding.menu.')
        ->controller(OnboardingMenuController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::middleware('throttle:60,1,onboarding-menu')->group(function () {
                Route::post('/items', 'storeItem')->name('items.store');
                Route::put('/items/{id}', 'updateItem')->whereNumber('id')->name('items.update');
                Route::delete('/items/{id}', 'destroyItem')->whereNumber('id')->name('items.destroy');
                Route::post('/categories', 'storeCategory')->name('categories.store');
                Route::put('/categories/{id}', 'updateCategory')->whereNumber('id')->name('categories.update');
                Route::delete('/categories/{id}', 'destroyCategory')->whereNumber('id')->name('categories.destroy');
                Route::post('/features', 'storeFeature')->name('features.store');
                Route::put('/features/{id}', 'updateFeature')->whereNumber('id')->name('features.update');
                Route::delete('/features/{id}', 'destroyFeature')->whereNumber('id')->name('features.destroy');
            });
            Route::get('/features/create', 'createFeature')->name('features.create');
            Route::get('/features/{id}/edit', 'editFeature')->whereNumber('id')->name('features.edit');
            Route::get('/features/lookup', 'lookupFeatures')->name('features.lookup');
            Route::get('/items/create', 'createItem')->name('items.create');
            Route::get('/items/{id}/edit', 'editItem')->whereNumber('id')->name('items.edit');
            Route::get('/items/{id}', 'showItem')->whereNumber('id')->name('items.show');
            Route::get('/categories/create', 'createCategory')->name('categories.create');
            Route::get('/categories/{id}/edit', 'editCategory')->whereNumber('id')->name('categories.edit');
        });

    Route::get('/partner-with-us', [RestaurantOnboardingController::class, 'showForm'])->name('restaurant.onboarding.form');
    Route::post('/partner-with-us/check-email', [RestaurantOnboardingController::class, 'checkEmail'])
        ->middleware('throttle:restaurant-onboarding-email-check')->name('restaurant.onboarding.check-email');
    Route::post('/partner-with-us', [RestaurantOnboardingController::class, 'submit'])
        ->middleware(['throttle:5,1,website-partner-signup', 'throttle:restaurant-onboarding-register'])
        ->name('restaurant.onboarding.submit');
});

Route::prefix(config('admin::prefix.admin'))->middleware('web')->name('admin.')->group(function () {

    Route::middleware(['check.permission'])->group(function () {

        // Restaurant Approval Management
        Route::prefix('restaurant/approvals')->name('restaurant.approvals.')
            ->middleware(['auth:admin', 'route.ids:id', EnsureRestaurantApprovalAccess::class])->group(function () {
                Route::get('/', [RestaurantApprovalController::class, 'index'])->name('index');
                Route::get('/{id}', [RestaurantApprovalController::class, 'show'])->name('show');
                Route::patch('/{id}/status', [RestaurantApprovalController::class, 'updateStatus'])
                    ->middleware('auth:admin')->name('status.update');
                Route::get('/{id}/review/pdf', [RestaurantApprovalController::class, 'downloadReviewPdf'])
                    ->middleware('auth:admin')->name('review.pdf');
                Route::post('/{id}/start-review', [RestaurantApprovalController::class, 'startReview'])
                    ->middleware('auth:admin')->name('start-review');
                Route::post('/{id}/request-changes', [RestaurantApprovalController::class, 'requestChanges'])->name('request-changes');
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
            Route::get('/{id}/edit', [RestaurantController::class, 'edit'])->middleware('route.ids:id')->name('edit');
            Route::get('/{id}/view', [RestaurantController::class, 'view'])->middleware('route.ids:id')->name('view');
            Route::delete('/{id}', [RestaurantController::class, 'destroy'])->middleware('route.ids:id')->name('delete');

            Route::post('/store-general', [RestaurantController::class, 'storeGeneral'])->name('store.general');
            Route::post('/{restaurant}/hours', [RestaurantController::class, 'updateHours'])->middleware('route.ids:restaurant')->name('update.hours');
            Route::post('/{restaurant}/address', [RestaurantController::class, 'updateAddress'])->middleware('route.ids:restaurant')->name('update.address');
            Route::post('/{restaurant}/holidays', [RestaurantController::class, 'updateHolidays'])->middleware('route.ids:restaurant')->name('update.holidays');
            Route::post('/{restaurant}/settings', [RestaurantController::class, 'updateSettings'])->middleware(['route.ids:restaurant', EnsureRestaurantFeaturesApproved::class.':settings'])->name('update.settings');

            // Gallery routes
            Route::post('/gallery', [RestaurantGalleryController::class, 'store'])->middleware(EnsureRestaurantFeaturesApproved::class.':gallery')->name('gallery.store');
            Route::delete('/gallery/{id}', [RestaurantGalleryController::class, 'destroy'])->middleware(EnsureRestaurantFeaturesApproved::class.':gallery')->name('gallery.destroy');

            // Bulk operations
            Route::post('/bulk-delete', [RestaurantController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/bulk-status', [RestaurantController::class, 'bulkStatus'])->name('bulk-status');
        });
    });

    // Menu Management in the restaurant panel (same screens as onboarding "Menu details").
    // Access is checked in RestaurantMenuController: owners -> own restaurant, others -> food items permission.
    Route::middleware('auth:admin')->group(function () {
        Route::get('restaurant/menu', [RestaurantMenuController::class, 'mine'])->name('restaurant.menu.mine');
        // {restaurant} is an encrypted token (route.ids), decrypted before ResolveMenuRestaurant reads it.
        Route::prefix('restaurant/{restaurant}/menu')->name('restaurant.menu.')
            ->middleware(['route.ids:restaurant', ResolveMenuRestaurant::class])
            ->controller(RestaurantMenuController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::middleware('throttle:60,1,panel-menu')->group(function () {
                    Route::post('/items', 'storeItem')->name('items.store');
                    Route::put('/items/{id}', 'updateItem')->whereNumber('id')->name('items.update');
                    Route::delete('/items/{id}', 'destroyItem')->whereNumber('id')->name('items.destroy');
                    Route::post('/categories', 'storeCategory')->name('categories.store');
                    Route::put('/categories/{id}', 'updateCategory')->whereNumber('id')->name('categories.update');
                    Route::delete('/categories/{id}', 'destroyCategory')->whereNumber('id')->name('categories.destroy');
                    Route::post('/features', 'storeFeature')->name('features.store');
                    Route::put('/features/{id}', 'updateFeature')->whereNumber('id')->name('features.update');
                    Route::delete('/features/{id}', 'destroyFeature')->whereNumber('id')->name('features.destroy');
                });
                Route::get('/items/create', 'createItem')->name('items.create');
                Route::get('/items/{id}/edit', 'editItem')->whereNumber('id')->name('items.edit');
                Route::get('/items/{id}', 'showItem')->whereNumber('id')->name('items.show');
                Route::get('/categories/create', 'createCategory')->name('categories.create');
                Route::get('/categories/{id}/edit', 'editCategory')->whereNumber('id')->name('categories.edit');
                Route::get('/features/create', 'createFeature')->name('features.create');
                Route::get('/features/{id}/edit', 'editFeature')->whereNumber('id')->name('features.edit');
                Route::get('/features/lookup', 'lookupFeatures')->name('features.lookup');
            });
    });
});


// Payout exports share the commission ledger permissions and restaurant ownership scope.
Route::prefix(config('core::prefix.admin'))->middleware(['web', 'auth:admin', 'route.ids:id'])
    ->get('restaurant/{id}/payouts/export', \Modules\Restaurant\Http\Controllers\RestaurantPayoutExportController::class)
    ->name('admin.restaurant.payouts.export');
