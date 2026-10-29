<?php

use Illuminate\Support\Facades\Route;
use Modules\WebPushNotification\Http\Controllers\HomeController;
use Modules\WebPushNotification\Http\Controllers\TemplateController;

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
                Route::resource('push-notification-template', TemplateController::class);
                Route::resource('firebase', HomeController::class);

                Route::post('firebase/send-test-notification', [HomeController::class, 'sendTestNotification'])->name('firebase.send-test-notification');
                Route::resource('firebase-templates', TemplateController::class);
        });
});
