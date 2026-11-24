<?php

use Illuminate\Support\Facades\Route;
use Modules\DietaryManagment\Http\Controllers\Api\CertificateController;
use Modules\Restaurant\Http\Middleware\OnboardingApiSession;

Route::prefix('api/dietarymanagment')->name('api.dietarymanagment.')
    ->middleware([OnboardingApiSession::class, 'auth:admin,sanctum'])
    ->group(function () {
        Route::get('labels', [CertificateController::class, 'labels'])->name('labels.index');
        Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('certificates/uploaded', [CertificateController::class, 'uploaded'])->name('certificates.uploaded');
        Route::post('certificates', [CertificateController::class, 'store'])->middleware('throttle:20,1')->name('certificates.store');
        Route::get('certificates/{typeId}', [CertificateController::class, 'show'])->whereNumber('typeId')->name('certificates.show');
        Route::patch('certificates/{typeId}', [CertificateController::class, 'update'])->whereNumber('typeId')->middleware('throttle:20,1')->name('certificates.update');
        Route::get('certificates/{typeId}/document', [CertificateController::class, 'document'])->whereNumber('typeId')->name('certificates.document');
    });
