<?php

use Illuminate\Support\Facades\Route;
use Modules\FoodManagements\Http\Controllers\Api\MenuApiController;
use Modules\FoodManagements\Http\Controllers\Api\FoodEventsApiController;
use Modules\FoodManagements\Http\Controllers\Api\FeaturedItemsApiController;
use Modules\FoodManagements\Http\Controllers\Api\FoodFeaturesApiController;

// Public API routes for menu
Route::prefix('api/menu')->name('api.menu.')->group(function () {
    Route::get('categories', [MenuApiController::class, 'getCategories'])->name('categories');
    Route::get('categories/{categoryId}/items', [MenuApiController::class, 'getFoodItemsByCategory'])->name('categories.items');
    Route::get('item/{itemId}', [MenuApiController::class, 'getFoodItemDetails'])->name('items.details');
    Route::get('tags/{tagId}/items', [MenuApiController::class, 'getFoodItemsByTag'])->name('tags.items');
    Route::get('option-groups', [MenuApiController::class, 'getOptionGroups'])->name('option-groups');
    Route::get('complete', [MenuApiController::class, 'getCompleteMenu'])->name('complete');
});

// Public API routes for food events
Route::prefix('api/food-events')->name('api.food-events.')->group(function () {
    Route::get('/', [FoodEventsApiController::class, 'index'])->name('index');
    Route::get('/{id}', [FoodEventsApiController::class, 'show'])->name('show');
    Route::get('/slug/{slug}', [FoodEventsApiController::class, 'showBySlug'])->name('show-by-slug');
    Route::get('/{id}/items', [FoodEventsApiController::class, 'getEventItems'])->name('items');
    Route::get('/{id}/categories', [FoodEventsApiController::class, 'getEventCategories'])->name('categories');
});

// Public API routes for food features
Route::prefix('api/food-features')->name('api.food-features.')->group(function () {
    Route::get('/', [FoodFeaturesApiController::class, 'index'])->name('index');
});


Route::prefix('api/featured-items')->name('api.featured-items.')->group(function () {
    Route::get('/', [FeaturedItemsApiController::class, 'index'])->name('index');
    Route::get('/food-items', [FeaturedItemsApiController::class, 'getFoodItems'])->name('food-items');
    Route::get('/categories', [FeaturedItemsApiController::class, 'getCategories'])->name('categories');
    Route::get('/{id}', [FeaturedItemsApiController::class, 'show'])->name('show');
});









