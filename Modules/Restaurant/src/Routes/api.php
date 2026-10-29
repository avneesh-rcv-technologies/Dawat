<?php

use Illuminate\Support\Facades\Route;
use Modules\Restaurant\Http\Controllers\Api\FoodCategoryApiController;
use Modules\Restaurant\Http\Controllers\Api\FoodEventApiController;
use Modules\Restaurant\Http\Controllers\Api\FoodFeatureApiController;
use Modules\Restaurant\Http\Controllers\Api\FoodFeatureGroupApiController;
use Modules\Restaurant\Http\Controllers\Api\FoodItemApiController;
use Modules\Restaurant\Http\Controllers\Api\FoodOptionApiController;
use Modules\Restaurant\Http\Controllers\Api\FoodTagsApiController;
use Modules\Restaurant\Http\Controllers\Api\HolidayApiController;
use Modules\Restaurant\Http\Controllers\Api\RestaurantAuthApiController;
use Modules\Restaurant\Http\Controllers\Api\RestaurantController;
use Modules\Restaurant\Http\Controllers\Api\OnboardingDocumentController;
use Modules\Restaurant\Http\Controllers\Api\WorkingHoursApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('api/restaurant')->name('api.restaurant.')->group(function () {
    Route::get('/me/onboarding-details', [\Modules\Restaurant\Http\Controllers\Api\OnboardingDetailsController::class, 'show'])
        ->middleware('auth:sanctum')->name('me.onboarding-details');
    Route::middleware([\Modules\Restaurant\Http\Middleware\OnboardingApiSession::class, 'auth:admin,sanctum'])
        ->prefix('me/tax-information')->name('me.tax-information.')->group(function () {
        Route::get('/get-tax-information', [\Modules\Restaurant\Http\Controllers\Api\TaxInformationController::class, 'show'])->name('show');
        Route::post('/store-tax-information', [\Modules\Restaurant\Http\Controllers\Api\TaxInformationController::class, 'store'])->middleware('throttle:restaurant-onboarding-write')->name('store');
        Route::patch('/update-tax-information', [\Modules\Restaurant\Http\Controllers\Api\TaxInformationController::class, 'update'])->middleware('throttle:restaurant-onboarding-write')->name('update');
    });
    // Onboarding owners can submit banking details before their application is approved.
    Route::middleware([\Modules\Restaurant\Http\Middleware\OnboardingApiSession::class, 'auth:admin,sanctum'])
        ->prefix('me/banking')->name('me.banking.')->group(function () {
        Route::get('/get-banking-details', [\Modules\Restaurant\Http\Controllers\Api\BankingDetailsController::class, 'show'])->name('show');
        Route::post('/store-banking-details', [\Modules\Restaurant\Http\Controllers\Api\BankingDetailsController::class, 'store'])->middleware('throttle:restaurant-onboarding-write')->name('store');
        Route::match(['put', 'patch'], '/update-banking-details', [\Modules\Restaurant\Http\Controllers\Api\BankingDetailsController::class, 'update'])->middleware('throttle:restaurant-onboarding-write')->name('update');
    });
    Route::post('/register', [RestaurantAuthApiController::class, 'register'])->name('register');

    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/login', [RestaurantAuthApiController::class, 'login'])->name('login');
    });

    Route::prefix('/auth')->name('auth.')->group(function () {
        Route::middleware('throttle:5,1')->group(function () {
            Route::post('/send-otp', [RestaurantAuthApiController::class, 'sendOtp'])->name('send-otp');
            Route::post('/resend-otp', [RestaurantAuthApiController::class, 'resendOtp'])->name('resend-otp');
            Route::post('/verify-otp', [RestaurantAuthApiController::class, 'verifyOtp'])->name('verify-otp');
        });
        Route::post('/reset-password', [RestaurantAuthApiController::class, 'resetPassword'])->name('reset-password');
    });


    // Restaurant authentication and profile management routes
    Route::middleware(['auth:sanctum', 'restaurant.active'])->group(function () {
        Route::get('/me', [RestaurantAuthApiController::class, 'me'])->name('me');
        Route::post('/me/update', [RestaurantAuthApiController::class, 'updateRestaurant'])->name('me.update');
        Route::post('/me/address', [RestaurantAuthApiController::class, 'updateAddress'])->name('me.update.address');
        Route::get('/me/settings', [RestaurantAuthApiController::class, 'getSettings'])->name('me.get.settings');
        Route::post('/me/hours', [WorkingHoursApiController::class, 'update'])->name('me.update.hours');
        // Get restaurant working hours
        Route::get('/me/hours', [WorkingHoursApiController::class, 'show'])->name('me.get.hours');

        Route::get('/working-hours', [WorkingHoursApiController::class, 'show'])->name('working-hours.show');
        Route::post('/working-hours', [WorkingHoursApiController::class, 'update'])->name('working-hours.store');
        Route::match(['put', 'patch'], '/working-hours', [WorkingHoursApiController::class, 'update'])->name('working-hours.update');

        // Get restaurant holidays
        Route::get('/me/holidays', [RestaurantAuthApiController::class, 'getHolidays'])->name('me.get.holidays');
        Route::post('/me/settings', [RestaurantAuthApiController::class, 'updateSettings'])->name('me.update.settings');
        Route::post('/me/holidays', [RestaurantAuthApiController::class, 'updateHolidays'])->name('me.update.holidays');

        Route::get('/holidays', [HolidayApiController::class, 'index'])->name('holidays.index');
        Route::post('/holidays', [HolidayApiController::class, 'store'])->name('holidays.store');
        Route::post('/holidays/bulk-delete', [HolidayApiController::class, 'bulkDelete'])->name('holidays.bulk-delete');
        Route::delete('/holidays/bulk-delete', [HolidayApiController::class, 'bulkDelete'])->name('holidays.bulk-delete.delete');
        Route::get('/holidays/{id}', [HolidayApiController::class, 'show'])->name('holidays.show');
        Route::match(['put', 'patch'], '/holidays/{id}', [HolidayApiController::class, 'update'])->name('holidays.update');
        Route::delete('/holidays/{id}', [HolidayApiController::class, 'destroy'])->name('holidays.destroy');

        // Update restaurant gallery (upload images)
        Route::post('/me/gallery', [RestaurantAuthApiController::class, 'updateGallery'])->name('me.update.gallery');

        //  get restaurant working hours api fo restaurant 

    });

    // Owners can configure their menu while restaurant/KYC approval is pending.
    // Controllers scope menu data to the authenticated owner's restaurant.
    Route::middleware(['auth:sanctum'])->group(function () {
        // Food Categories and Tags
        Route::get('/categories', [FoodCategoryApiController::class, 'index'])->name('categories.index');
        Route::post('/categories', [FoodCategoryApiController::class, 'store'])->name('categories.store');
        Route::post('/categories/bulk-delete', [FoodCategoryApiController::class, 'bulkDelete'])->name('categories.bulk-delete');
        Route::delete('/categories/bulk-delete', [FoodCategoryApiController::class, 'bulkDelete'])->name('categories.bulk-delete.delete');
        Route::get('/categories/parent-lists', [FoodCategoryApiController::class, 'activeParentCategoryLists'])->name('categories.parent-lists');
        Route::get('/categories/parent-category-lists', [FoodCategoryApiController::class, 'activeParentCategoryLists'])->name('categories.parent-category-lists');
        Route::get('/categories/active-parent-categories', [FoodCategoryApiController::class, 'activeParentCategoryLists'])->name('categories.active-parent-categories');
        Route::get('/categories/{id}', [FoodCategoryApiController::class, 'show'])->name('categories.show');
        Route::match(['put', 'patch'], '/categories/{id}', [FoodCategoryApiController::class, 'update'])->name('categories.update');
        Route::match(['put', 'patch'], '/categories/{id}/status', [FoodCategoryApiController::class, 'updateStatus'])->name('categories.status.update');
        Route::post('/categories/{id}/status', [FoodCategoryApiController::class, 'updateStatus'])->name('categories.status.update.post');
        Route::delete('/categories/{id}', [FoodCategoryApiController::class, 'destroy'])->name('categories.destroy');
        Route::post('/categories/{id}/image', [FoodCategoryApiController::class, 'uploadCategoryImage'])->name('categories.image.upload');
        Route::post('/categories/{id}/upload-image', [FoodCategoryApiController::class, 'uploadCategoryImage'])->name('categories.image.upload-legacy');
        Route::delete('/categories/{id}/image', [FoodCategoryApiController::class, 'removeCategoryImage'])->name('categories.image.remove');

        Route::get('/food-tags', [FoodTagsApiController::class, 'index'])->name('food-tags.index');
        Route::post('/food-tags', [FoodTagsApiController::class, 'store'])->name('food-tags.store');
        Route::post('/food-tags/bulk-delete', [FoodTagsApiController::class, 'bulkDelete'])->name('food-tags.bulk-delete');
        Route::delete('/food-tags/bulk-delete', [FoodTagsApiController::class, 'bulkDelete'])->name('food-tags.bulk-delete.delete');
        Route::get('/food-tags/{id}', [FoodTagsApiController::class, 'show'])->name('food-tags.show');
        Route::match(['put', 'patch'], '/food-tags/{id}', [FoodTagsApiController::class, 'update'])->name('food-tags.update');
        Route::delete('/food-tags/{id}', [FoodTagsApiController::class, 'destroy'])->name('food-tags.destroy');

        Route::get('/tags', [FoodTagsApiController::class, 'index'])->name('tags.index');
        Route::post('/tags', [FoodTagsApiController::class, 'store'])->name('tags.store');
        Route::post('/tags/bulk-delete', [FoodTagsApiController::class, 'bulkDelete'])->name('tags.bulk-delete');
        Route::delete('/tags/bulk-delete', [FoodTagsApiController::class, 'bulkDelete'])->name('tags.bulk-delete.delete');
        Route::get('/tags/{id}', [FoodTagsApiController::class, 'show'])->name('tags.show');
        Route::match(['put', 'patch'], '/tags/{id}', [FoodTagsApiController::class, 'update'])->name('tags.update');
        Route::delete('/tags/{id}', [FoodTagsApiController::class, 'destroy'])->name('tags.destroy');

        // Food Option Groups and Options

        Route::get('/feature-groups', [FoodFeatureGroupApiController::class, 'index'])->name('feature-groups.index');
        Route::post('/feature-groups', [FoodFeatureGroupApiController::class, 'store'])->name('feature-groups.store');
        Route::post('/feature-groups/bulk-delete', [FoodFeatureGroupApiController::class, 'bulkDelete'])->name('feature-groups.bulk-delete');
        Route::delete('/feature-groups/bulk-delete', [FoodFeatureGroupApiController::class, 'bulkDelete'])->name('feature-groups.bulk-delete.delete');
        Route::get('/feature-groups/{id}', [FoodFeatureGroupApiController::class, 'show'])->name('feature-groups.show');
        Route::match(['put', 'patch'], '/feature-groups/{id}', [FoodFeatureGroupApiController::class, 'update'])->name('feature-groups.update');
        Route::delete('/feature-groups/{id}', [FoodFeatureGroupApiController::class, 'destroy'])->name('feature-groups.destroy');

        Route::get('/food-feature-groups', [FoodFeatureGroupApiController::class, 'index'])->name('food-feature-groups.index');
        Route::post('/food-feature-groups', [FoodFeatureGroupApiController::class, 'store'])->name('food-feature-groups.store');
        Route::post('/food-feature-groups/bulk-delete', [FoodFeatureGroupApiController::class, 'bulkDelete'])->name('food-feature-groups.bulk-delete');
        Route::delete('/food-feature-groups/bulk-delete', [FoodFeatureGroupApiController::class, 'bulkDelete'])->name('food-feature-groups.bulk-delete.delete');
        Route::get('/food-feature-groups/{id}', [FoodFeatureGroupApiController::class, 'show'])->name('food-feature-groups.show');
        Route::match(['put', 'patch'], '/food-feature-groups/{id}', [FoodFeatureGroupApiController::class, 'update'])->name('food-feature-groups.update');
        Route::delete('/food-feature-groups/{id}', [FoodFeatureGroupApiController::class, 'destroy'])->name('food-feature-groups.destroy');

        Route::get('/food-features', [FoodFeatureApiController::class, 'index'])->name('food-features.index');
        Route::post('/food-features', [FoodFeatureApiController::class, 'store'])->name('food-features.store');
        Route::post('/food-features/bulk-delete', [FoodFeatureApiController::class, 'bulkDelete'])->name('food-features.bulk-delete');
        Route::delete('/food-features/bulk-delete', [FoodFeatureApiController::class, 'bulkDelete'])->name('food-features.bulk-delete.delete');
        Route::get('/food-features/{id}', [FoodFeatureApiController::class, 'show'])->name('food-features.show');
        Route::match(['put', 'patch'], '/food-features/{id}', [FoodFeatureApiController::class, 'update'])->name('food-features.update');
        Route::delete('/food-features/{id}', [FoodFeatureApiController::class, 'destroy'])->name('food-features.destroy');

        Route::get('/features', [FoodFeatureApiController::class, 'index'])->name('features.index');
        Route::post('/features', [FoodFeatureApiController::class, 'store'])->name('features.store');
        Route::post('/features/bulk-delete', [FoodFeatureApiController::class, 'bulkDelete'])->name('features.bulk-delete');
        Route::delete('/features/bulk-delete', [FoodFeatureApiController::class, 'bulkDelete'])->name('features.bulk-delete.delete');
        Route::get('/features/{id}', [FoodFeatureApiController::class, 'show'])->name('features.show');
        Route::match(['put', 'patch'], '/features/{id}', [FoodFeatureApiController::class, 'update'])->name('features.update');
        Route::delete('/features/{id}', [FoodFeatureApiController::class, 'destroy'])->name('features.destroy');

        Route::get('/food-option-groups', [FoodOptionApiController::class, 'index'])->name('food-option-groups.index');
        Route::post('/food-option-groups', [FoodOptionApiController::class, 'store'])->name('food-option-groups.store');
        Route::post('/food-option-groups/bulk-delete', [FoodOptionApiController::class, 'bulkDelete'])->name('food-option-groups.bulk-delete');
        Route::delete('/food-option-groups/bulk-delete', [FoodOptionApiController::class, 'bulkDelete'])->name('food-option-groups.bulk-delete.delete');
        Route::get('/food-option-groups/{id}', [FoodOptionApiController::class, 'show'])->name('food-option-groups.show');
        Route::match(['put', 'patch'], '/food-option-groups/{id}', [FoodOptionApiController::class, 'update'])->name('food-option-groups.update');
        Route::delete('/food-option-groups/{id}', [FoodOptionApiController::class, 'destroy'])->name('food-option-groups.destroy');

        Route::get('/option-groups', [FoodOptionApiController::class, 'index'])->name('option-groups.index');
        Route::post('/option-groups', [FoodOptionApiController::class, 'store'])->name('option-groups.store');
        Route::post('/option-groups/bulk-delete', [FoodOptionApiController::class, 'bulkDelete'])->name('option-groups.bulk-delete');
        Route::delete('/option-groups/bulk-delete', [FoodOptionApiController::class, 'bulkDelete'])->name('option-groups.bulk-delete.delete');
        Route::get('/option-groups/{id}', [FoodOptionApiController::class, 'show'])->name('option-groups.show');
        Route::match(['put', 'patch'], '/option-groups/{id}', [FoodOptionApiController::class, 'update'])->name('option-groups.update');
        Route::delete('/option-groups/{id}', [FoodOptionApiController::class, 'destroy'])->name('option-groups.destroy');

        Route::get('/food-option-groups/{groupId}/options', [FoodOptionApiController::class, 'listOptions'])->name('food-option-groups.options.index');
        Route::post('/food-option-groups/{groupId}/options/bulk-delete', [FoodOptionApiController::class, 'bulkDeleteOptions'])->name('food-option-groups.options.bulk-delete');
        Route::delete('/food-option-groups/{groupId}/options/bulk-delete', [FoodOptionApiController::class, 'bulkDeleteOptions'])->name('food-option-groups.options.bulk-delete.delete');
        Route::post('/food-option-groups/{groupId}/options', [FoodOptionApiController::class, 'storeOption'])->name('food-option-groups.options.store');
        Route::get('/food-option-groups/{groupId}/options/{optionId}', [FoodOptionApiController::class, 'showOption'])->name('food-option-groups.options.show');
        Route::match(['put', 'patch'], '/food-option-groups/{groupId}/options/{optionId}', [FoodOptionApiController::class, 'updateOption'])->name('food-option-groups.options.update');
        Route::delete('/food-option-groups/{groupId}/options/{optionId}', [FoodOptionApiController::class, 'destroyOption'])->name('food-option-groups.options.destroy');

        Route::get('/food-items', [FoodItemApiController::class, 'index'])->name('food-items.index');
        Route::post('/food-items', [FoodItemApiController::class, 'store'])->name('food-items.store');
        Route::get('/food-items/{id}', [FoodItemApiController::class, 'show'])->name('food-items.show');
        Route::post('/food-items/{id}', [FoodItemApiController::class, 'update'])->name('food-items.update');
        Route::delete('/food-items/{id}', [FoodItemApiController::class, 'destroy'])->name('food-items.destroy');

        Route::get('/items', [FoodItemApiController::class, 'index'])->name('items.index');
        Route::post('/items', [FoodItemApiController::class, 'store'])->name('items.store');
        Route::get('/items/{id}', [FoodItemApiController::class, 'show'])->name('items.show');
        Route::patch('/items/{id}', [FoodItemApiController::class, 'update'])->name('items.update');
        Route::delete('/items/{id}', [FoodItemApiController::class, 'destroy'])->name('items.destroy');

        Route::get('/food-events', [FoodEventApiController::class, 'index'])->name('food-events.index');
        Route::post('/food-events', [FoodEventApiController::class, 'store'])->name('food-events.store');
        Route::post('/food-events/bulk-delete', [FoodEventApiController::class, 'bulkDelete'])->name('food-events.bulk-delete');
        Route::post('/food-events/toggle-status', [FoodEventApiController::class, 'toggleStatus'])->name('food-events.toggle-status');
        Route::get('/food-events/{id}', [FoodEventApiController::class, 'show'])->name('food-events.show');
        Route::match(['put', 'patch'], '/food-events/{id}', [FoodEventApiController::class, 'update'])->name('food-events.update');
        Route::delete('/food-events/{id}', [FoodEventApiController::class, 'destroy'])->name('food-events.destroy');
        Route::get('/food-events/{id}/items', [FoodEventApiController::class, 'getEventItems'])->name('food-events.items');
        Route::get('/items-list-for-restaurant', [FoodEventApiController::class, 'getItemsList'])->name('food-events.items-list');
        Route::get('/food-events/{id}/categories', [FoodEventApiController::class, 'getEventCategories'])->name('food-events.categories');
        Route::post('/food-events/{id}/items', [FoodEventApiController::class, 'updateItems'])->name('food-events.update-items');
        Route::post('/food-events/{id}/categories', [FoodEventApiController::class, 'updateCategories'])->name('food-events.update-categories');

    });

    // Address/info/gallery for the authenticated restaurant (take id from auth)
    Route::middleware(['auth:sanctum', 'restaurant.active'])->group(function () {
        Route::get('/address', [RestaurantController::class, 'address'])->name('restaurants.address');
        Route::get('/info', [RestaurantController::class, 'info'])->name('restaurants.info');
        Route::get('/gallery', [RestaurantController::class, 'gallery'])->name('restaurants.gallery');
    });

    // Restaurant list with gallery images (also serves as home screen)
    Route::get('/', [RestaurantController::class, 'index'])->name('restaurants.index');

    // Onboarding document endpoints (KYC)
    Route::middleware([\Modules\Restaurant\Http\Middleware\OnboardingApiSession::class, 'auth:admin,sanctum'])->group(function () {
        Route::get('/onboarding/documents', [OnboardingDocumentController::class, 'getDocuments'])->name('onboarding.documents');
        Route::get('/onboarding/kyc-report', [OnboardingDocumentController::class, 'getKycReport'])->name('onboarding.kyc-report');
        Route::get('/onboarding/documents/rejected', [OnboardingDocumentController::class, 'getRejectedDocuments'])->name('onboarding.documents.rejected');
        Route::post('/onboarding/documents', [OnboardingDocumentController::class, 'uploadDocuments'])->middleware('throttle:restaurant-onboarding-write')->name('onboarding.documents.upload');
        Route::post('/onboarding/documents/reupload', [OnboardingDocumentController::class, 'uploadRejectedDocuments'])->middleware('throttle:restaurant-onboarding-write')->name('onboarding.documents.reupload');
        //  make api to upload rejected document same 
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [RestaurantAuthApiController::class, 'logout'])->name('logout');
    });

    // now make in 
});
