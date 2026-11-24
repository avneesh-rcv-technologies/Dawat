<?php

use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Controllers\HomeController;
use Modules\Cms\Http\Controllers\Page\PagesController;

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
    foreach (config('acl::guards.guards') as $guard => $guardConfig) {
        $prefix = $guardConfig['prefix'];
        // Keep existing admin route names and distinguish other guards.
        $namePrefix = $guard === 'admin' ? '' : $guard . '.';
        Route::group([
            'middleware' => ['check.permission'],
            'prefix' => $prefix,
            'as' => $namePrefix,
            'permission_name_prefix' => $namePrefix,
        ], function () {
            Route::prefix('cms')->name('cms.')->group(function () {
                
                /*
                |--------------------------------------------------------------------------
                | CMS Pages
                |--------------------------------------------------------------------------
                */
                Route::resource('pages', PagesController::class);
                Route::post('pages/bulk-delete', [PagesController::class, 'bulkDelete'])->name('pages.bulk-delete');
                Route::post('pages/toggle-status', [PagesController::class, 'toggleStatus'])->name('pages.toggle-status');
                
            });
        });
    }
}); 