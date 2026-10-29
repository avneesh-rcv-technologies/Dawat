<?php

use Illuminate\Support\Facades\Route;
use Modules\MobileConfiguration\Http\Controllers\HomeController;
use Modules\MobileConfiguration\Http\Controllers\Banner\BannersController;

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

Route::middleware('web')->group(function () {
    foreach (array_column(config('acl::guards.guards'), 'prefix') as $prefix) {
        Route::middleware(['check.permission'])->prefix($prefix)->group(function () {

        Route::prefix('mobile-configuration')->name('mobileconfiguration.')->group(function () {

            /*
                |--------------------------------------------------------------------------
                | Mobile Banners
                |--------------------------------------------------------------------------
                */
            Route::resource('banners', BannersController::class);
            Route::post('banners/bulk-delete', [BannersController::class, 'bulkDelete'])->name('banners.bulk-delete');
            Route::post('banners/toggle-status', [BannersController::class, 'toggleStatus'])->name('banners.toggle-status');

            /*
                |--------------------------------------------------------------------------
                | Mobile Features
                |--------------------------------------------------------------------------
                */
            Route::resource('features', \Modules\MobileConfiguration\Http\Controllers\Feature\FeaturesController::class);
            Route::post('features/bulk-delete', [\Modules\MobileConfiguration\Http\Controllers\Feature\FeaturesController::class, 'bulkDelete'])->name('features.bulk-delete');
            Route::post('features/toggle-status', [\Modules\MobileConfiguration\Http\Controllers\Feature\FeaturesController::class, 'toggleStatus'])->name('features.toggle-status');

            /*
                |--------------------------------------------------------------------------
                | Mobile Walkthroughs
                |--------------------------------------------------------------------------
                */
            Route::resource('walkthroughs', \Modules\MobileConfiguration\Http\Controllers\Walkthrough\WalkthroughsController::class);
            Route::post('walkthroughs/bulk-delete', [\Modules\MobileConfiguration\Http\Controllers\Walkthrough\WalkthroughsController::class, 'bulkDelete'])->name('walkthroughs.bulk-delete');
            Route::post('walkthroughs/toggle-status', [\Modules\MobileConfiguration\Http\Controllers\Walkthrough\WalkthroughsController::class, 'toggleStatus'])->name('walkthroughs.toggle-status');

            /*
                |--------------------------------------------------------------------------
                | Mobile CMS
                |--------------------------------------------------------------------------
                */
            Route::resource('cms', \Modules\MobileConfiguration\Http\Controllers\Cms\MobileCmsController::class);
            Route::post('cms/bulk-delete', [\Modules\MobileConfiguration\Http\Controllers\Cms\MobileCmsController::class, 'bulkDelete'])->name('cms.bulk-delete');
            Route::post('cms/toggle-status', [\Modules\MobileConfiguration\Http\Controllers\Cms\MobileCmsController::class, 'toggleStatus'])->name('cms.toggle-status');

            /*
                |--------------------------------------------------------------------------
                | Mobile Homepage Layouts
                |--------------------------------------------------------------------------
                */
            Route::resource('homepage-layouts', \Modules\MobileConfiguration\Http\Controllers\HomepageLayout\HomepageLayoutsController::class);
            Route::post('homepage-layouts/bulk-delete', [\Modules\MobileConfiguration\Http\Controllers\HomepageLayout\HomepageLayoutsController::class, 'bulkDelete'])->name('homepage-layouts.bulk-delete');
            Route::post('homepage-layouts/toggle-status', [\Modules\MobileConfiguration\Http\Controllers\HomepageLayout\HomepageLayoutsController::class, 'toggleStatus'])->name('homepage-layouts.toggle-status');
            Route::post('homepage-layouts/{homepageLayout}/set-default', [\Modules\MobileConfiguration\Http\Controllers\HomepageLayout\HomepageLayoutsController::class, 'setAsDefault'])->name('homepage-layouts.set-default');
        });
    });
    }
}); 