<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\Http\Controllers\HomeController;
use Modules\Notifications\Http\Controllers\LogsController;
use Modules\Notifications\Http\Controllers\NotificationController;
use Modules\Notifications\Http\Controllers\NotificationBellController;

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
        // Notification CRUD
        Route::resource('notification', NotificationController::class);
        
        // System Notifications (Bell Icon) - Using NotificationBellController
        Route::prefix('system/notifications')->name('system.')->group(function () {
            Route::get('/', [NotificationBellController::class, 'index'])->name('notifications');
            Route::get('/{id}', [NotificationBellController::class, 'show'])->name('notification-show');
            Route::post('/mark-as-read/{id}', [NotificationBellController::class, 'markAsRead'])->name('markAsRead');
        });
        
        Route::resource('/notification-logs', LogsController::class)->names([
            'index' => 'notification-logs.index',
            'show' => 'notification-logs.show',
            'destroy' => 'notification-logs.destroy',
        ]);

        // Bell Icon API Routes
        Route::prefix('notifications/bell')->name('notifications.bell.')->group(function () {
            Route::get('/unread-count', [NotificationBellController::class, 'unreadCount'])->name('unread-count');
            Route::get('/recent', [NotificationBellController::class, 'recent'])->name('recent');
            Route::post('/{id}/mark-as-read', [NotificationBellController::class, 'markAsRead'])->name('mark-as-read');
            Route::post('/{id}/toggle-read-status', [NotificationBellController::class, 'toggleReadStatus'])->name('toggle-read-status');
            Route::post('/mark-all-as-read', [NotificationBellController::class, 'markAllAsRead'])->name('mark-all-as-read');
        });
    });
});

// HomeController will be generated automatically by the module generator
