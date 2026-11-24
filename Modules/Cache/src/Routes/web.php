<?php

use Illuminate\Support\Facades\Route;
use Modules\Cache\Http\Controllers\Admin\CacheController;

Route::middleware(['web', 'check.permission'])
    ->prefix('admin')
    ->group(function () {
        Route::prefix('cache')->name('cache.')->group(function () {
            Route::get('/', [CacheController::class, 'index'])->name('index');
            Route::post('/flush-all', [CacheController::class, 'flushAll'])->name('flush-all');
            Route::post('/bulk-flush', [CacheController::class, 'bulkFlush'])->name('bulk-flush');

            Route::get('/{profile}', [CacheController::class, 'show'])->name('show');
            Route::post('/{profile}/flush', [CacheController::class, 'flush'])->name('flush');
            Route::delete('/{profile}/key', [CacheController::class, 'forget'])->name('forget');
        });
    });
