<?php

use Illuminate\Support\Facades\Route;
use Modules\FoodManagements\Http\Controllers\FoodsCategoryController;
use Modules\FoodManagements\Http\Controllers\FoodItemController;
use Modules\FoodManagements\Http\Controllers\FoodCategoryController;
use Modules\FoodManagements\Http\Controllers\FoodEventsController;
use Modules\FoodManagements\Http\Controllers\FoodTagController;
use Modules\FoodManagements\Http\Controllers\FoodOptionGroupController;
use Modules\FoodManagements\Http\Controllers\FoodOptionController;
use Modules\FoodManagements\Http\Controllers\SubCategoryController;
use Modules\FoodManagements\Http\Controllers\FeaturedItemsController;
use Modules\FoodManagements\Http\Controllers\FoodFeaturesController;
use Modules\FoodManagements\Http\Controllers\FoodFeatureGroupsController;

Route::prefix(config('core::prefix.admin'))->middleware('web')->name('admin.')->group(function () {
    Route::middleware(['check.permission'])->group(function () {
        Route::prefix('foodmanagements')->name('foodmanagements.')->middleware(\Modules\Restaurant\Http\Middleware\EnsureRestaurantFeaturesApproved::class)->group(function () {

            // Food Categories
            Route::resource('category', FoodsCategoryController::class);
            Route::post('category/bulk-delete', [FoodsCategoryController::class, 'bulkDelete'])->name('category.bulk-delete');
            Route::post('category/toggle-status', [FoodsCategoryController::class, 'toggleStatus'])->name('category.toggle-status');

            // Food Tags
            Route::resource('food-tags', FoodTagController::class);
            Route::post('food-tags/bulk-delete', [FoodTagController::class, 'bulkDelete'])
                ->name('food-tags.bulk-delete');
            

            // Food Option Groups
            Route::resource('food-option-groups', FoodOptionGroupController::class);
            Route::post('food-option-groups/bulk-delete', [FoodOptionGroupController::class, 'bulkDelete'])
                ->name('food-option-groups.bulk-delete');

                
            // Individual Food Options
            Route::post('food-options', [FoodOptionController::class, 'store'])
                ->name('food-options.store');
            Route::put('food-options/{id}', [FoodOptionController::class, 'update'])
                ->name('food-options.update');
            Route::delete('food-options/{id}', [FoodOptionController::class, 'destroy'])
                ->name('food-options.destroy');

            // Food Items
            Route::resource('food-items', FoodItemController::class);
            Route::post('food-items/store-general', [FoodItemController::class, 'storeGeneral'])
                ->name('food-items.store.general');
            Route::put('food-items/{id}/general',[FoodItemController::class, 'updateGeneral'])
                ->name('food-items.update.general');
            Route::post('food-items/{foodItem}/update-categories', [FoodItemController::class, 'updateCategories'])
                ->name('food-items.update.categories');
            Route::post('food-items/{foodItem}/update-sub-categories', [FoodItemController::class, 'updateSubCategories'])
                ->name('food-items.update.sub-categories');
            Route::post('food-items/{foodItem}/update-tags', [FoodItemController::class, 'updateTags'])
                ->name('food-items.update.tags');
            Route::post('food-items/{foodItem}/update-options', [FoodItemController::class, 'updateOptions'])
                ->name('food-items.update.options');
            Route::post('food-items/{foodItem}/update-option-prices', [FoodItemController::class, 'updateOptionPrices'])
                ->name('food-items.update.option-prices');
            Route::post('food-items/{foodItem}/update-images', [FoodItemController::class, 'updateImages'])
                ->name('food-items.update.images');
            Route::post('food-items/{foodItem}/update-features', [FoodItemController::class, 'updateFeatures'])
                ->name('food-items.update.features');
            Route::post('food-items/bulk-delete', [FoodItemController::class, 'bulkDelete'])
                ->name('food-items.bulk-delete');
            Route::post('food-items/toggle-status', [FoodItemController::class, 'toggleStatus'])
                ->name('food-items.toggle-status');

            // Food Events
            Route::resource('events', FoodEventsController::class);
            Route::post('events/{id}/update-items', [FoodEventsController::class, 'updateItems'])
                ->name('events.update-items');
            Route::post('events/{id}/update-categories', [FoodEventsController::class, 'updateCategories'])
                ->name('events.update-categories');
            Route::post('events/bulk-delete', [FoodEventsController::class, 'bulkDelete'])
                ->name('events.bulk-delete');
            Route::post('events/toggle-status', [FoodEventsController::class, 'toggleStatus'])
                ->name('events.toggle-status');

            // Featured Items
            Route::resource('featured-items', FeaturedItemsController::class);
            Route::post('featured-items/bulk-delete', [FeaturedItemsController::class, 'bulkDelete'])
                ->name('featured-items.bulk-delete');
            Route::post('featured-items/toggle-status', [FeaturedItemsController::class, 'toggleStatus'])
                ->name('featured-items.toggle-status');
            Route::get('featured-items-data/food-items', [FeaturedItemsController::class, 'getFoodItems'])
                ->name('featured-items.get-food-items');
            Route::get('featured-items-data/food-categories', [FeaturedItemsController::class, 'getFoodCategories'])
                ->name('featured-items.get-food-categories');

            // Food Features
            Route::prefix('features')->name('features.')->group(function () {
                Route::get('/', [FoodFeaturesController::class, 'index'])->name('index');
                Route::get('/create', [FoodFeaturesController::class, 'create'])->name('create');
                Route::post('/', [FoodFeaturesController::class, 'store'])->name('store');
                Route::get('/{feature}/edit', [FoodFeaturesController::class, 'edit'])->name('edit');
                Route::put('/{feature}', [FoodFeaturesController::class, 'update'])->name('update');
                Route::delete('/{feature}', [FoodFeaturesController::class, 'destroy'])->name('destroy');
                Route::post('/bulk-delete', [FoodFeaturesController::class, 'bulkDelete'])->name('bulk-delete');

                Route::get('/groups', [FoodFeatureGroupsController::class, 'index'])->name('group_index');
                Route::get('/groups/create', [FoodFeatureGroupsController::class, 'create'])->name('group_create');
                Route::post('/groups', [FoodFeatureGroupsController::class, 'store'])->name('group_store');
                Route::get('/groups/{group}/edit', [FoodFeatureGroupsController::class, 'edit'])->name('group_edit');
                Route::put('/groups/{group}', [FoodFeatureGroupsController::class, 'update'])->name('group_update');
                Route::delete('/groups/{group}', [FoodFeatureGroupsController::class, 'destroy'])->name('group_destroy');
                Route::post('/groups/bulk-delete', [FoodFeatureGroupsController::class, 'bulkDelete'])->name('group_bulk_delete');
            });
        });
    });
});
