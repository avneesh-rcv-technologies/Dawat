<?php

namespace Modules\FoodManagements\Listeners;

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
        //     'label' => 'Food Management',
        //     'icon' => 'food_bank',
        //     'permission' => 'admin.foodmanagements.index',
        //     'position' => 78,
        //     'children' => [
        //         [
        //             'label' => 'Manage Food Categories',
        //             'route' => 'admin.foodmanagements.category.index',
        //             'guard' => 'admin',
        //             'permission' => 'admin.foodmanagements.index',
        //         ],
        //         [
        //             'label' => 'Manage Food Items',
        //             'route' => 'admin.foodmanagements.food-items.index',
        //             'guard' => 'admin',
        //             'permission' => 'admin.foodmanagements.food-items.index',
        //         ],
        //         [
        //             'label' => 'Manage Food Tags',
        //             'route' => 'admin.foodmanagements.food-tags.index',
        //             'guard' => 'admin',
        //             'permission' => 'admin.foodmanagements.food-tags.index',
        //         ],
        //         [
        //             'label' => 'Manage Food Options',
        //             'route' => 'admin.foodmanagements.food-option-groups.index',
        //             'guard' => 'admin',
        //             'permission' => 'admin.foodmanagements.food-option-groups.index',
        //         ],
        //         [
        //             'label' => 'Manage Events',
        //             'route' => 'admin.foodmanagements.events.index',
        //             'guard' => 'admin',
        //             'permission' => 'admin.foodmanagements.events.index',
        //         ],
        //         [
        //             'label' => 'Manage Featured Items',
        //             'route' => 'admin.foodmanagements.featured-items.index',
        //             'guard' => 'admin',
        //             'permission' => 'admin.foodmanagements.featured-items.index',
        //         ],
        //         [
        //             'label' => 'Food Features',
        //             'route' => 'admin.foodmanagements.features.index',
        //             'guard' => 'admin',
        //             'permission' => 'admin.foodmanagements.features.index',
        //         ],
               
        //     ],
        // ]);
    }
}
