<?php

use Illuminate\Support\Facades\Route;
use Modules\Activitylog\Http\Controllers\AdvanceLoggingController;

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
    foreach (array_column(config('acl::guards.guards'), 'prefix') as $prefix) {

        Route::middleware(['check.permission'])->prefix($prefix)->group(function () {
            Route::prefix('activity-log')->name('activity-log.')->group(function () {
                // New routes for advanced logging and blocked IPs
                Route::get('/advance-logging', [AdvanceLoggingController::class, 'index'])->name('advance-logging');
                Route::get('/advance-logging/candlestick/{ip}/chart', [AdvanceLoggingController::class, 'candlestickChart'])->name('advance-logging.candlestick-chart');
                Route::get('/advance-logging/candlestick/{ip}/data', [AdvanceLoggingController::class, 'candlestickChartData'])->name('advance-logging.candlestick-data');
                Route::get('/advance-logging/heatmap-data/{ip}', [AdvanceLoggingController::class, 'heatmapData'])->name('advance-logging.heatmap-data');
                Route::get('/blocked-ips', [AdvanceLoggingController::class, 'blockedIpsList'])->name('blocked-ips');
                
                Route::get('/access-requests', [AdvanceLoggingController::class, 'accessRequests'])->name('access-requests');
                Route::get('/access-requests/{id}/approve-form', [AdvanceLoggingController::class, 'approveAccessRequestForm'])->name('access-requests.approve-form');
                Route::post('/access-requests/{id}/approve', [AdvanceLoggingController::class, 'approveAccessRequest'])->name('access-requests.approve');
                Route::post('/access-requests/{id}/reject', [AdvanceLoggingController::class, 'rejectAccessRequest'])->name('access-requests.reject');
                Route::post('/access-requests/{id}/revoke', [AdvanceLoggingController::class, 'revokeAccessRequest'])->name('access-requests.revoke');

                Route::post('/access-requests/toggle-status', [AdvanceLoggingController::class, 'toggleAccessRequestStatus'])->name('access-requests.toggle-status');

                Route::post('/activity-log/is_blocked', [AdvanceLoggingController::class, 'toggleBlocking'])->name('is_blocked');

                // Advance logging CRUD & IP actions
                Route::prefix('advance-logging')->name('advance-logging.')->group(function () {
                    Route::get('/{id}', [AdvanceLoggingController::class, 'show'])->name('show');
                    Route::get('/{id}/block-ip', [AdvanceLoggingController::class, 'blockIpForm'])->name('block-ip');
                    Route::post('/{id}/block-ip', [AdvanceLoggingController::class, 'blockIp'])->name('block-ip');
                });

            });
        });
    }
});

Route::middleware(['web', 'ensure-admin-access-request-needed'])->prefix('admin-access-request')->name('activitylog.access-request.')->group(function () {
    Route::get('/denied', [AdvanceLoggingController::class, 'deniedAdminAccess'])->name('denied');
    Route::get('/form', [AdvanceLoggingController::class, 'adminAccessRequestForm'])->name('form');
    Route::post('/submit', [AdvanceLoggingController::class, 'storeAdminAccessRequest'])->name('store');
    Route::get('/otp', [AdvanceLoggingController::class, 'showAccessRequestOtpForm'])->name('otp.form');
    Route::post('/otp/verify', [AdvanceLoggingController::class, 'verifyAccessRequestOtp'])->name('otp.verify');
});
