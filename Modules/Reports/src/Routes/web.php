<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ReportController;
use Modules\Reports\Http\Controllers\ReportBuilderController;

Route::middleware('web')->group(function () {
    foreach (array_column(config('acl::guards.guards'), 'prefix') as $prefix) {

        Route::middleware(['check.permission'])->prefix($prefix)->group(function () {
            Route::prefix('reports')->name('reports.')->group(function () {
                // Main CRUD routes (specific routes MUST come before wildcard {id} routes)
                Route::get('/', [ReportController::class, 'index'])->name('index');
                Route::get('/create', [ReportController::class, 'create'])->name('create');
                Route::post('/', [ReportController::class, 'store'])->name('store');
                
                // Report Builder (must come before {id} routes to avoid conflicts)
                Route::prefix('builder')->name('builder.')->group(function () {
                    Route::get('/', [ReportBuilderController::class, 'index'])->name('index');
                    Route::post('/get-columns', [ReportBuilderController::class, 'getColumns'])->name('get-columns');
                    Route::post('/preview', [ReportBuilderController::class, 'preview'])->name('preview');
                    Route::get('/aggregations', [ReportBuilderController::class, 'getAggregations'])->name('aggregations');
                    Route::get('/operators', [ReportBuilderController::class, 'getOperators'])->name('operators');
                    Route::get('/chart-types', [ReportBuilderController::class, 'getChartTypes'])->name('chart-types');
                });
                
                // Additional non-wildcard routes
                Route::post('/bulk-delete', [ReportController::class, 'bulkDelete'])->name('bulk-delete');
                Route::post('/bulk-restore', [ReportController::class, 'bulkRestore'])->name('bulk-restore');
                Route::post('/bulk-force-delete', [ReportController::class, 'bulkForceDelete'])->name('bulk-force-delete');
                Route::get('/statistics/all', [ReportController::class, 'statistics'])->name('statistics');
                Route::get('/guard-config', [ReportController::class, 'getGuardConfig'])->name('guard-config');
                
                // Wildcard {id} routes with specific actions (must come before generic {id} routes)
                // Route::get('/{id}/preview-data', [ReportController::class, 'previewData'])->name('preview-data');
                Route::get('/{id}/edit', [ReportController::class, 'edit'])->name('edit');
                Route::post('/{id}/restore', [ReportController::class, 'restore'])->name('restore');
                Route::post('/{id}/duplicate', [ReportController::class, 'duplicate'])->name('duplicate');
                Route::post('/{id}/toggle-active', [ReportController::class, 'toggleActive'])->name('toggle-active');
                Route::post('/{id}/execute', [ReportController::class, 'execute'])->name('execute');
                Route::post('/{id}/send-now', [ReportController::class, 'sendNow'])->name('send-now');
                Route::post('/{id}/export', [ReportController::class, 'export'])->name('export');
                
                // Generic wildcard {id} routes MUST come last
                Route::get('/{id}', [ReportController::class, 'show'])->name('show');
                Route::put('/{id}', [ReportController::class, 'update'])->name('update');
                Route::delete('/{id}', [ReportController::class, 'destroy'])->name('destroy');

                Route::get('/download/{exportId}', [ReportController::class, 'downloadLink'])->name('download_link');
            });
        });
    }
});

// HomeController will be generated automatically by the module generator 