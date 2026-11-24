<?php

use Illuminate\Support\Facades\Route;
use Modules\DietaryManagment\Http\Controllers\DietaryLabelController;
use Modules\DietaryManagment\Http\Controllers\CertificationController;

Route::prefix(config('core::prefix.admin', '') . '/dietarymanagment')
    ->middleware(['web', 'check.permission'])
    ->name('admin.dietarymanagment.')
    ->group(function () {
        Route::get('certifications', [CertificationController::class, 'index'])->middleware('auth:admin')->name('certifications.index');
        Route::get('certifications/{id}', [CertificationController::class, 'show'])->middleware('auth:admin')->whereNumber('id')->name('certifications.show');
        Route::patch('certifications/{id}/review', [CertificationController::class, 'review'])->middleware('auth:admin')->whereNumber('id')->name('certifications.review');
        Route::resource('labels', DietaryLabelController::class)->except('show');
    });
