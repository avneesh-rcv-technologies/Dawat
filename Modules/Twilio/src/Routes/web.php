<?php

use Google\Service\Resource;
use Illuminate\Support\Facades\Route;
use Modules\Twilio\Http\Controllers\ComposeController;
use Modules\Twilio\Http\Controllers\HomeController;
use Modules\Twilio\Http\Controllers\UploadController;
use Modules\Twilio\Http\Controllers\DashboardController;
use Modules\Twilio\Http\Controllers\SmsTemplateController;
use Modules\Twilio\Http\Controllers\MessagingAutomationController;

Route::prefix(config('core::prefix.admin'))->middleware('web')->group(function () {
    Route::middleware(['check.permission'])->group(function () {
        // Existing routes
        Route::get('/twilio/configuration', [DashboardController::class, 'configuration'])->name('twilio.configuration');
        Route::get('twilio/message/{id}', [DashboardController::class, 'messageOverview'])->name('twilio.message');
        Route::post('twilio/mass-send', [DashboardController::class, 'massSend'])->name('twilio.mass-send');
        Route::post('twilio/bulk-delete', [DashboardController::class, 'bulkDelete'])->name('twilio.bulk-delete');
        Route::post('twilio/mass-export', [DashboardController::class, 'massExport'])->name('twilio.mass-export');
        Route::resource('twilio', DashboardController::class);

        // SMS Templates Management
        Route::prefix('sms-templates')->name('sms-templates.')->group(function () {
            Route::get('/', [SmsTemplateController::class, 'index'])->name('index');
            Route::get('/create', [SmsTemplateController::class, 'create'])->name('create');
            Route::post('/', [SmsTemplateController::class, 'store'])->name('store');
            Route::get('/{template}/edit', [SmsTemplateController::class, 'edit'])->name('edit');
            Route::put('/{template}', [SmsTemplateController::class, 'update'])->name('update');
            Route::delete('/{template}', [SmsTemplateController::class, 'destroy'])->name('destroy');
            Route::patch('/{template}/toggle-status', [SmsTemplateController::class, 'toggleStatus'])->name('toggle-status');
        });

          
    });
});
