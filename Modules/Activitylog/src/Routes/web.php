<?php

use Illuminate\Support\Facades\Route;
use Modules\Activitylog\Http\Controllers\ActivityLogController;

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
    Route::middleware(['check.permission'])->group(function () {
        Route::prefix('activity-log')->name('activity-log.')->group(function () {
            Route::get('/', [ActivityLogController::class, 'index'])->name('index');
            Route::get('/export-csv', [ActivityLogController::class, 'exportCsv'])->name('agency-export-csv');
            Route::get('/activitylog/data', [ActivityLogController::class, 'getActivitiesJson'])->name('agency-search');
        });
    });
});
