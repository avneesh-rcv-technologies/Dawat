<?php

namespace Modules\Restaurant\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Restaurant\Http\Middleware\EnsureRestaurantFeaturesApproved;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantOwnerRoleResolver;
use Modules\Restaurant\Support\MenuContext;

/**
 * Menu Management in the restaurant panel (light admin layout).
 *
 * Same screens and logic as the onboarding "Menu details" step; only the restaurant comes
 * from the URL. Owners open their own restaurant (sidebar); admins with the food items
 * permission open any restaurant from the Restaurants list. Like the rest of food
 * management, it requires an approved restaurant.
 */
class RestaurantMenuController extends OnboardingMenuController
{
    public const PERMISSION = 'admin.foodmanagements.food-items.index';

    /** Sidebar item is shown to restaurant owners only. */
    public static function visibleInSidebar(): bool
    {
        return app(RestaurantOwnerRoleResolver::class)->isOwner(auth('admin')->user());
    }

    /** Sidebar shortcut: the owner's own restaurant menu. */
    public function mine(Request $request)
    {
        $admin = $request->user('admin');
        abort_unless($admin && app(RestaurantOwnerRoleResolver::class)->isOwner($admin), 403);
        $restaurantId = Restaurant::where('admin_id', $admin->id)->value('id');
        abort_unless($restaurantId, 404);

        return redirect()->route('admin.restaurant.menu.index', ['restaurant' => $restaurantId]);
    }

    protected function resolveRestaurant(Request $request): Restaurant
    {
        $admin = $request->user('admin');
        abort_unless($admin && $admin->status, 403);

        $restaurant = Restaurant::findOrFail((int) $request->attributes->get('menu_restaurant_id'));

        if (app(RestaurantOwnerRoleResolver::class)->isOwner($admin)) {
            abort_unless((int) $restaurant->admin_id === (int) $admin->id, 404);
        } else {
            // Admins reach it from the Restaurants list (Menu action).
            abort_unless($admin->hasPermission(self::PERMISSION), 403);
        }
        abort_unless($restaurant->canManageFeatures(), 403, EnsureRestaurantFeaturesApproved::MESSAGE);

        return $restaurant;
    }

    protected function makeContext(Restaurant $restaurant): MenuContext
    {
        return MenuContext::panel($restaurant);
    }
}
