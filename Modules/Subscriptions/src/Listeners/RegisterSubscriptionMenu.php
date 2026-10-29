<?php

namespace Modules\Subscriptions\Listeners;

use Modules\Core\Events\RegisterMenu;

class RegisterSubscriptionMenu
{
    public function handle(RegisterMenu $event)
    {
        // $event->addItem([
        //     'label' => 'Subscriptions',
        //     'route' => '#',
        //     'icon' => 'credit_card', // Settings
        //     'permission' => '#',
        //     'position' => 80,
        //     'children' => [
        //         [
        //             'label' => 'All Subscriptions',
        //             'route' => 'subscriptions.index',
        //             'icon' => 'credit_card', // Event
        //             'permission' => 'subscriptions.index',
        //         ],
        //         [
        //             'label' => 'Plans',
        //             'route' => 'subscriptions.plans.index',
        //             'icon' => 'credit_card', // Calendar Today
        //             'permission' => 'subscriptions.plans.index',
        //         ],
        //         [
        //             'label' => 'Features',
        //             'route' => 'subscriptions.features.index',
        //             'icon' => 'star', // Features
        //             'permission' => 'subscriptions.features.index',
        //         ],
        //     ],
        // ]);

    }
}
