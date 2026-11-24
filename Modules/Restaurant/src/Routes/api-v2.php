<?php

use Illuminate\Support\Facades\Route;
use Modules\Restaurant\Http\Controllers\Api\V2\FoodItemController;
use Modules\Restaurant\Http\Controllers\Api\V2\FoodItemImageController;
use Modules\Restaurant\Http\Middleware\FoodItemV2ApiResponse;
use Modules\Restaurant\Http\Middleware\OnboardingApiSession;

Route::prefix('api/v2/restaurant/food-items')->name('api.v2.restaurant.food-items.')
    ->middleware([FoodItemV2ApiResponse::class, OnboardingApiSession::class, 'auth:admin,sanctum'])
    ->group(function () {
        Route::get('form-options', [FoodItemController::class, 'formOptions'])->name('form-options');
        Route::get('serving-options', [FoodItemController::class, 'servingOptions'])->name('serving-options');
        Route::get('/', [FoodItemController::class, 'index'])->name('index');
        Route::post('/', [FoodItemController::class, 'store'])->name('store');
        Route::get('{id}', [FoodItemController::class, 'show'])->whereNumber('id')->name('show');
        Route::patch('{id}', [FoodItemController::class, 'update'])->whereNumber('id')->name('update');
        Route::post('{id}', [FoodItemController::class, 'update'])->whereNumber('id')->name('update.post');
        Route::patch('{id}/status', [FoodItemController::class, 'status'])->whereNumber('id')->name('status');
        Route::delete('{id}', [FoodItemController::class, 'destroy'])->whereNumber('id')->name('destroy');
        Route::get('{id}/images', [FoodItemImageController::class, 'index'])->whereNumber('id')->name('images.index');
        Route::post('{id}/images', [FoodItemImageController::class, 'store'])->whereNumber('id')->name('images.store');
        Route::post('{id}/single-image', [FoodItemImageController::class, 'storeSingle'])->whereNumber('id')->name('images.store-single');
        Route::patch('{id}/images', [FoodItemImageController::class, 'replace'])->whereNumber('id')->name('images.replace');
        Route::delete('{id}/images', [FoodItemImageController::class, 'destroyMany'])->whereNumber('id')->name('images.destroy-many');
        Route::patch('{id}/images/{imageId}', [FoodItemImageController::class, 'update'])
            ->whereNumber('id')->whereNumber('imageId')->name('images.update');
        Route::delete('{id}/images/{imageId}', [FoodItemImageController::class, 'destroy'])
            ->whereNumber('id')->whereNumber('imageId')->name('images.destroy');
        Route::post('{id}/serving-options', [FoodItemController::class, 'addServingOption'])->whereNumber('id')->name('serving-options.store');
        Route::patch('{id}/serving-options/{optionId}', [FoodItemController::class, 'updateServingOption'])
            ->whereNumber('id')->whereNumber('optionId')->name('serving-options.update');
        Route::delete('{id}/serving-options/{optionId}', [FoodItemController::class, 'deleteServingOption'])
            ->whereNumber('id')->whereNumber('optionId')->name('serving-options.destroy');
    });
