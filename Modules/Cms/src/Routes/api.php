<?php

use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Controllers\Api\PageApiController;

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

Route::prefix('cms')->name('cms.')->group(function () {
    Route::get('pages/{slug}', [PageApiController::class, 'show'])->name('pages.show');
});