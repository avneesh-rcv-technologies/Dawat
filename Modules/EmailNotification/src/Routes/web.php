<?php

use Illuminate\Support\Facades\Route;
use Modules\EmailNotification\Http\Controllers\EmailTemplateController;

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
        Route::resource('notification/email/templates', EmailTemplateController::class)
            ->names([
                'index' => 'notification.email.templates.index',
                'create' => 'notification.email.templates.create',
                'store' => 'notification.email.templates.store',
                'show' => 'notification.email.templates.show',
                'edit' => 'notification.email.templates.edit',
                'update' => 'notification.email.templates.update',
                'destroy' => 'notification.email.templates.destroy',
            ]);

        Route::get('/notification/email-design/{key}/edit', [EmailTemplateController::class, 'editEmailDesign'])
            ->name('notification.email-design.edit');

        Route::post('/notification/email-design/update', [EmailTemplateController::class, 'updateEmailDesign'])
            ->name('notification.email-design.update');
    });
});
