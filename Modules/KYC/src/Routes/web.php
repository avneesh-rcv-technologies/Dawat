<?php

use Illuminate\Support\Facades\Route;
use Modules\KYC\Http\Controllers\KycTypeController;
use Modules\KYC\Http\Controllers\KycSubmissionController;
use Modules\KYC\Http\Controllers\KycRequestController;
use Modules\KYC\Http\Controllers\ComplianceController;

/*
|--------------------------------------------------------------------------
| KYC Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth:admin', 'admin.permission'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // KYC Types Management
        Route::prefix('kyc/types')->name('kyc.types.')->group(function () {
        Route::get('/', [KycTypeController::class, 'index'])->name('index');
        Route::get('/create', [KycTypeController::class, 'create'])->name('create');
        Route::post('/', [KycTypeController::class, 'store'])->name('store');
        Route::post('/bulk-delete', [KycTypeController::class, 'bulkDelete'])->name('bulk-delete');
        Route::get('/{type}/edit', [KycTypeController::class, 'edit'])->name('edit');
        Route::put('/{type}', [KycTypeController::class, 'update'])->name('update');
        Route::delete('/{type}', [KycTypeController::class, 'destroy'])->name('destroy');
        
        // KYC Fields Management
        Route::get('/{type}/fields', [KycTypeController::class, 'fields'])->name('fields');
        Route::post('/{type}/fields', [KycTypeController::class, 'addField'])->name('add-field');
        Route::put('/{type}/fields/{field}', [KycTypeController::class, 'updateField'])->name('update-field');
        Route::delete('/{type}/fields/{field}', [KycTypeController::class, 'deleteField'])->name('delete-field');
        Route::post('/{type}/fields/reorder', [KycTypeController::class, 'reorderFields'])->name('reorder-fields');
        });
        
        // KYC Submissions Management
        Route::prefix('kyc/submissions')->name('kyc.submissions.')
            ->middleware(\Modules\KYC\Http\Middleware\EnsureKycSubmissionAccess::class)->group(function () {
            Route::get('/', [KycSubmissionController::class, 'index'])->name('index');
            Route::get('/{submission}', [KycSubmissionController::class, 'show'])->name('show');
            Route::post('/bulk-review', [KycSubmissionController::class, 'bulkReview'])->name('bulk-review');
            Route::post('/{submission}/review', [KycSubmissionController::class, 'review'])->name('review');
            Route::get('/{submission}/file/{file}/preview', [KycSubmissionController::class, 'previewFile'])->name('preview-file');
            Route::get('/{submission}/file/{file}/download', [KycSubmissionController::class, 'downloadFile'])->name('download-file');
            Route::post('/{submission}/message', [KycSubmissionController::class, 'addMessage'])->name('add-message');
        });
        
        // Compliance Records Management
        Route::prefix('kyc/compliance')->name('kyc.compliance.')->group(function () {
            Route::get('/', [ComplianceController::class, 'index'])->name('index');
            Route::post('/remind-bulk', [ComplianceController::class, 'remindBulk'])->name('remind-bulk');
            Route::post('/{record}/remind', [ComplianceController::class, 'remind'])->name('remind');

            // Owners who have NOT uploaded compliance
            Route::get('/missing', [ComplianceController::class, 'missing'])->name('missing');
            Route::post('/missing/remind-bulk', [ComplianceController::class, 'remindMissingBulk'])->name('remind-missing-bulk');
            Route::post('/missing/{owner}/{type}/remind', [ComplianceController::class, 'remindMissing'])->name('remind-missing');
        });

        // KYC Requests Management
        Route::prefix('kyc/requests')->name('kyc.requests.')->group(function () {
            Route::get('/', [KycRequestController::class, 'index'])->name('index');
            Route::get('/create', [KycRequestController::class, 'create'])->name('create');
            Route::post('/', [KycRequestController::class, 'store'])->name('store');
            Route::post('/bulk-destroy', [KycRequestController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::delete('/{request}', [KycRequestController::class, 'destroy'])->name('destroy');
            Route::get('/users', [KycRequestController::class, 'getUsers'])->name('get-users');
        });
        
    });


/*
|--------------------------------------------------------------------------
| Owner KYC Routes
|--------------------------------------------------------------------------
*/

// Route::prefix('owner')->middleware('web')->name('owner.')->group(function () {
    
//     // Restaurant owner authentication middleware (adjust based on your restaurant owner auth)
//     Route::middleware(['auth:restaurant_owner'])->group(function () {
        
//         // KYC Submission Routes
//         Route::prefix('kyc')->name('kyc.')->group(function () {
//             Route::get('/requests', [KycRequestController::class, 'ownerRequests'])->name('requests');
//             Route::get('/submit/{request}', [KycRequestController::class, 'showSubmissionForm'])->name('submit');
//             Route::post('/submit/{request}', [KycRequestController::class, 'submitKyc'])->name('submit.store');
//             Route::get('/submissions', [KycSubmissionController::class, 'ownerSubmissions'])->name('submissions');
//             Route::get('/submissions/{submission}', [KycSubmissionController::class, 'ownerShow'])->name('submissions.show');
//         });
        
//     });
// }); 
