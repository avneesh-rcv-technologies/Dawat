<?php

namespace Modules\Restaurant\Listeners;

use Modules\Core\Events\RegisterMenu;

class RegisterMenuItems
{
    /**
     * Handle the event.
     *
     * @param  \Modules\Core\Events\RegisterMenu  $event
     * @return void
     */
    public function handle(RegisterMenu $event)
    {
        // $event->addItem([
        //     'label' => 'Restaurant',
        //     'route' => 'admin.restaurant.index',
        //     'icon' => 'store',
        //     'permission' => 'admin.restaurant.index',
        //     'position' => 77,
        //     'children' => [
        //         [
        //             'label' => 'Onboarding & Approvals',
        //             'route' => 'admin.restaurant.approvals.index',
        //             'icon' => 'pending_actions',
        //             'permission' => 'admin.restaurant.approvals.index',
        //         ],
        //         [
        //             'label' => 'All Restaurants',
        //             'route' => 'admin.restaurant.index',
        //             'icon' => 'list',
        //             'permission' => 'admin.restaurant.index',
        //         ],

        //     ]
        // ]);
        $event->addItem([
            'label' => 'Onboarding Details',
            'route' => 'admin.restaurant.approvals.index',
            'icon' => 'pending_actions',
            'permission' => 'admin.restaurant.approvals.index',
            'position' => 77,
        ]);

        // Restaurant owners only: manage their own restaurant's menu.
        $event->addItem([
            'label' => 'Menu Management',
            'route' => 'admin.restaurant.menu.mine',
            'icon' => 'restaurant_menu',
            'permission' => \Modules\Restaurant\Http\Controllers\RestaurantMenuController::PERMISSION,
            'visibility' => [\Modules\Restaurant\Http\Controllers\RestaurantMenuController::class, 'visibleInSidebar'],
            'position' => 79,
        ]);

        $event->addItem([
            'label' => 'Restaurants',
            'route' => 'admin.restaurant.index',
            'icon' => 'store',
            'permission' => 'admin.restaurant.index',
            'position' => 78,
        ]);
    }
}
