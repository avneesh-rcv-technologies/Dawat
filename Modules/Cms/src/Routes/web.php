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
    foreach (array_column(config('acl::guards.guards'), 'prefix') as $prefix) {
        Route::middleware(['check.permission'])->prefix($prefix)->group(function () {
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