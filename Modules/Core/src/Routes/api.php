<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Api\SearchController;
use Modules\Core\Http\Controllers\Api\LocationController;
use Modules\Core\Http\Controllers\Api\AutocompleteController;
use Modules\Core\Http\Controllers\Api\AdvancedSelectorController;
use Modules\Core\Http\Controllers\Api\AutocompleteSingleController;

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

Route::prefix('api')->middleware(['web', 'auth:admin'])->name('api')->group(function () {
    Route::get('/search', [SearchController::class, 'search'])->name('search');
    Route::get('/advanced-selector-data', [AdvancedSelectorController::class, 'getData'])->name('advanced-selector.data');
    Route::post('/advanced-selector-selected', [AdvancedSelectorController::class, 'getSelectedItems'])->name('advanced-selector.selected');

});

// Api\HomeController will be generated automatically by the module generator 
