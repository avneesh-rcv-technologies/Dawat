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

        $event->addItem([
            'label' => 'Restaurants',
            'route' => 'admin.restaurant.index',
            'icon' => 'store',
            'permission' => 'admin.restaurant.index',
            'position' => 78,
        ]);
    }
}
