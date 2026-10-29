<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ReportController;

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

// Route::prefix('api/reports')->middleware(['web', 'check.permission'])->name('api.reports.')->group(function () {
//     Route::get('/{id}/preview-data', [ReportController::class, 'previewData'])->name('preview-data');
// });
Route::prefix('api/reports')->middleware(['web', 'auth:admin'])->name('api.reports.')->group(function () {
    Route::get('/{id}/preview-data', [ReportController::class, 'previewData'])->name('preview-data');
});
