<?php

namespace Modules\Restaurant\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Modules\Filemanager\Models\FileLink;
use Modules\FoodManagements\Http\Controllers\FoodItemController;
use Modules\FoodManagements\Http\Controllers\FoodsCategoryController;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodItem;
use Modules\Restaurant\Models\Restaurant;

class EnsureRestaurantFeaturesApproved
{
    public const MESSAGE = 'Settings, Gallery, and Food Management require approved onboarding and application statuses.';

    public function handle(Request $request, Closure $next, string $feature = 'food')
    {
        $restaurantIds = [];
        if ($feature === 'settings') {
            $restaurantIds[] = $request->route('restaurant');
        } elseif ($feature === 'file') {
            $link = FileLink::findOrFail($request->route('id'));
            if ($link->object_type === 'restaurant_gallery') {
                $restaurantIds[] = $link->object_id;
            } elseif ($link->object_type === 'food_image') {
                $restaurantIds[] = FoodItem::findOrFail($link->object_id)->restaurant_id;
            } elseif ($link->object_type === 'category_image') {
                $restaurantIds[] = FoodCategory::findOrFail($link->object_id)->restaurant_id;
            }
        } elseif ($feature === 'gallery') {
            if ($request->route('id')) {
                $link = FileLink::where('object_type', 'restaurant_gallery')
                    ->findOrFail($request->route('id'));
                $restaurantIds[] = $link->object_id;
            } else {
                $restaurantIds[] = $request->input('restaurant_id');
            }
        } else {
            $restaurantIds[] = $request->input('restaurant_id');
            $controller = $request->route()->getControllerClass();
            $model = match ($controller) {
                FoodItemController::class => FoodItem::class,
                FoodsCategoryController::class => FoodCategory::class,
                default => null,
            };
            if ($model) {
                $ids = array_merge((array) $request->input('ids', []), [
                    $request->route('foodItem'),
                    $request->route('food_item'),
                    $request->route('category'),
                    $request->route('id'),
                    $request->input('id'),
                ]);
                $ids = array_filter(array_map(static fn ($id) => $id instanceof Model ? $id->getKey() : $id, $ids));
                if ($ids) {
                    $restaurantIds = array_merge($restaurantIds, $model::whereKey($ids)->pluck('restaurant_id')->all());
                } elseif ($controller === FoodItemController::class) {
                    $restaurantIds[] = $request->session()->get('food_item_restaurant_id');
                } elseif ($request->route()->getActionMethod() === 'create') {
                    $restaurantIds[] = $request->session()->get('category_restaurant_id');
                }
            }

            // Restaurant owners must meet approval requirements even without a restaurant_id parameter.
            $admin = auth('admin')->user();
            if ($admin && (int) $admin->role_id === 2) {
                $restaurantIds = array_merge($restaurantIds, Restaurant::where('admin_id', $admin->id)->pluck('id')->all());
            }
        }

        foreach (array_unique(array_filter($restaurantIds)) as $id) {
            $restaurant = Restaurant::findOrFail($id);
            abort_unless($restaurant->canManageFeatures(), 403, self::MESSAGE);
        }

        return $next($request);
    }
}
