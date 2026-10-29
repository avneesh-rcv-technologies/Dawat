<?php

use Illuminate\Support\Facades\Route;
use Modules\MobileConfiguration\Http\Controllers\Api\BannerApiController;
use Modules\MobileConfiguration\Http\Controllers\Api\FeatureApiController;
use Modules\MobileConfiguration\Http\Controllers\Api\HomepageLayoutApiController;
use Modules\MobileConfiguration\Http\Controllers\Api\MobileCmsApiController;
use Modules\MobileConfiguration\Http\Controllers\Api\WalkthroughApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('api/mobile')->name('mobile.')->group(function () {
    // Route::get('banners', [BannerApiController::class, 'active'])->name('banners.index');
    // Route::get('features', [FeatureApiController::class, 'index'])->name('features.index');
    // Route::get('features/{slug}', [FeatureApiController::class, 'show'])->name('features.show');
    // Route::get('walkthroughs', [WalkthroughApiController::class, 'index'])->name('walkthroughs.index');
    Route::get('cms', [MobileCmsApiController::class, 'index'])->name('cms.index');
    Route::get('cms/{id}', [MobileCmsApiController::class, 'show'])->name('cms.show');
    
    // Homepage Layout API
    Route::get('homepage-layout', [HomepageLayoutApiController::class, 'getLayout'])->name('homepage-layout.default');
    Route::get('homepage-layout/{slug}', [HomepageLayoutApiController::class, 'getLayoutBySlug'])->name('homepage-layout.show');
});