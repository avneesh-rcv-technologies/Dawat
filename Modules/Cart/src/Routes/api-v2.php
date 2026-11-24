<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Http\Controllers\Api\V2\CartController;
use Modules\Cart\Http\Middleware\CartV2ApiResponse;
use Modules\Cart\Http\Middleware\CartV2Identity;

Route::prefix('api/v2/cart')->name('api.v2.cart.')
    ->middleware([CartV2ApiResponse::class, CartV2Identity::class])
    ->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('add-items', [CartController::class, 'store'])->name('store');
        Route::delete('items', [CartController::class, 'clear'])->name('clear');
        Route::get('items/{cartItemId}', [CartController::class, 'show'])
            ->whereNumber('cartItemId')->name('show');
        Route::match(['put', 'patch'], 'items/{cartItemId}', [CartController::class, 'update'])
            ->whereNumber('cartItemId')->name('update');
        Route::delete('items/{cartItemId}', [CartController::class, 'destroy'])
            ->whereNumber('cartItemId')->name('destroy');
    });
