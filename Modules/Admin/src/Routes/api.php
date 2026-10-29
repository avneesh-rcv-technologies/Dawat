<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\Api\GalleryController;
use Modules\Admin\Http\Controllers\Api\AnalyticsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "api" middleware group. Now create something great!
|
*/

Route::prefix('api/admin')->name('api.admin.')->group(function () {
    // Admin Gallery Routes
    // Route::get('gallery', [GalleryController::class, 'index'])->name('gallery.index');
    // Route::post('gallery', [GalleryController::class, 'store'])->name('gallery.store');
    // Route::delete('gallery/{id}', [GalleryController::class, 'destroy'])->name('gallery.destroy');

    // Analytics Routes
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('restaurant/{restaurantId}', [AnalyticsController::class, 'restaurantAnalytics'])->name('restaurant');
        Route::get('customer/{customerId}', [AnalyticsController::class, 'customerAnalytics'])->name('customer');
        Route::get('order/{orderId}', [AnalyticsController::class, 'orderAnalytics'])->name('order');
        Route::get('transaction/{transactionId}', [AnalyticsController::class, 'transactionAnalytics'])->name('transaction');
    });
});

// Api\HomeController will be generated automatically by the module generator 