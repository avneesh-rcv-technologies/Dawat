<?php

use Illuminate\Support\Facades\Route;
use Modules\Faq\Http\Controllers\FaqController;

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

Route::prefix(config('core::prefix.admin'))->middleware('web')->name('admin.')->group(function () {
    Route::middleware(['check.permission'])->group(function () {

        Route::get('faqs/group/{group}/edit', [FaqController::class, 'editGroup'])->name('faqs.group.edit');
        Route::put('faqs/group/{group}', [FaqController::class, 'updateGroup'])->name('faqs.group.update');
        Route::delete('faqs/group/{group}', [FaqController::class, 'destroyGroup'])->name('faqs.group.destroy');
        Route::resource('faqs', FaqController::class);
    });
});
