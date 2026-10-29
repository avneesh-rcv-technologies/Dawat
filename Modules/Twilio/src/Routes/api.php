<?php

use Illuminate\Support\Facades\Route;
use Modules\Twilio\Http\Controllers\Api\HomeController;

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

Route::prefix('api/twilio')->name('api.twilio.')->group(function () {
    // Public webhook endpoint for Twilio callbacks (delivery status, inbound SMS)
    Route::get('/webhook', [\Modules\Twilio\Http\Controllers\WebhookController::class, 'handle'])->name('webhook');
});

// Api\HomeController will be generated automatically by the module generator 