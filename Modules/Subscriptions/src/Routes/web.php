<?php

use Illuminate\Support\Facades\Route;
use Modules\Subscriptions\Http\Controllers\PlanController;
use Modules\Subscriptions\Http\Controllers\FeatureController;
use Modules\Subscriptions\Http\Controllers\PlanFeatureController;
use Modules\Subscriptions\Http\Controllers\SubscriptionController;
use Modules\Subscriptions\Http\Controllers\SubscriptionFeatureController;

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

        // Features Management
        Route::prefix('subscriptions/features')->name('subscriptions.features.')->group(function () {
            Route::get('/', [FeatureController::class, 'index'])->name('index');
            Route::get('/create', [FeatureController::class, 'create'])->name('create');
            Route::post('/', [FeatureController::class, 'store'])->name('store');
            
            // Mass actions - must be before {id} routes
            Route::delete('/mass-destroy', [FeatureController::class, 'massDestroy'])->name('massDestroy');
            Route::post('/mass-activate', [FeatureController::class, 'massActivate'])->name('massActivate');
            Route::post('/mass-deactivate', [FeatureController::class, 'massDeactivate'])->name('massDeactivate');
            
            Route::get('/{id}', [FeatureController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [FeatureController::class, 'edit'])->name('edit');
            Route::put('/{id}', [FeatureController::class, 'update'])->name('update');
            Route::delete('/{id}', [FeatureController::class, 'destroy'])->name('destroy');
        });

        // Plans Management
        Route::prefix('subscriptions/plans')->name('subscriptions.plans.')->group(function () {
            Route::get('/', [PlanController::class, 'index'])->name('index');
            Route::get('/create', [PlanController::class, 'create'])->name('create');
            Route::post('/', [PlanController::class, 'store'])->name('store');
            
            // Mass actions - must be before {id} routes
            Route::delete('/mass-destroy', [PlanController::class, 'massDestroy'])->name('massDestroy');
            Route::post('/mass-activate', [PlanController::class, 'massActivate'])->name('massActivate');
            Route::post('/mass-deactivate', [PlanController::class, 'massDeactivate'])->name('massDeactivate');
            
            Route::get('/{id}', [PlanController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [PlanController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PlanController::class, 'update'])->name('update');
            Route::delete('/{id}', [PlanController::class, 'destroy'])->name('destroy');
            
            // Plan Features Management
            Route::get('/{planId}/features/edit', [PlanFeatureController::class, 'edit'])->name('features.edit');
            Route::put('/{planId}/features', [PlanFeatureController::class, 'update'])->name('features.update');
            Route::delete('/{planId}/features/{featureId}', [PlanFeatureController::class, 'destroy'])->name('features.destroy');
        });

        // Subscriptions Management
        Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
            Route::get('/', [SubscriptionController::class, 'index'])->name('index');
            Route::get('/create', [SubscriptionController::class, 'create'])->name('create');
            Route::post('/', [SubscriptionController::class, 'store'])->name('store');
            
            // Mass actions - must be before {id} routes
            Route::delete('/mass-destroy', [SubscriptionController::class, 'massDestroy'])->name('massDestroy');
            Route::post('/mass-cancel', [SubscriptionController::class, 'massCancel'])->name('massCancel');
            Route::post('/mass-resume', [SubscriptionController::class, 'massResume'])->name('massResume');
            
            Route::get('/{id}', [SubscriptionController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [SubscriptionController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SubscriptionController::class, 'update'])->name('update');
            Route::post('/{id}/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
            Route::post('/{id}/resume', [SubscriptionController::class, 'resume'])->name('resume');
            Route::post('/{id}/pause', [SubscriptionController::class, 'pause'])->name('pause');
            Route::get('/{id}/send-notification', [SubscriptionController::class, 'sendNotificationView'])->name('sendNotification');
            Route::post('/{id}/send-notification', [SubscriptionController::class, 'sendNotification'])->name('sendNotification');
            Route::delete('/{id}', [SubscriptionController::class, 'destroy'])->name('destroy');
            
            // Subscription Features Management
            Route::get('/{subscriptionId}/features', [SubscriptionFeatureController::class, 'index'])->name('features.index');
            Route::post('/{subscriptionId}/features', [SubscriptionFeatureController::class, 'store'])->name('features.store');
            Route::get('/{subscriptionId}/features/{featureId}', [SubscriptionFeatureController::class, 'show'])->name('features.show');
            Route::put('/{subscriptionId}/features/{featureId}', [SubscriptionFeatureController::class, 'update'])->name('features.update');
            Route::post('/{subscriptionId}/features/{featureId}/reset-usage', [SubscriptionFeatureController::class, 'resetUsage'])->name('features.resetUsage');
            Route::post('/{subscriptionId}/features/{featureId}/toggle', [SubscriptionFeatureController::class, 'toggle'])->name('features.toggle');
        });
    });
});
