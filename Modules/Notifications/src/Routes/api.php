<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\Http\Controllers\Api\HomeController;
use Modules\Notifications\Http\Controllers\Api\NotificationController;

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

Route::middleware(['auth:sanctum'])->prefix('api/customer')->group(function () {
    // Get all notifications
    Route::get('/notifications', [NotificationController::class, 'index']);

    // Get unread count
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);

    // Mark notification as read
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);

    // Mark all as read
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);

    // Clear all notifications
    Route::delete('/notifications/clear-all', [NotificationController::class, 'clearAll']);
    
    // Delete notification
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});

Route::post(
    '/notifications/trigger/{eventType}',
    [\Modules\Notifications\Http\Controllers\NotificationController::class, 'triggerNotification']
)
    ->middleware('auth:api');
// Api\HomeController will be generated automatically by the module generator 