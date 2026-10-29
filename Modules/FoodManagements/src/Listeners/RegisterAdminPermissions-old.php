<?php

namespace Modules\FoodManagements\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Food Management → Dashboard
         */
        $event->registerArray([
            'name' => 'Foods Management',
            'code' => 'foods_management',
            'key' => [
                'admin.foodmanagements.index' => 'View Food Management',
            ]
        ]);

        /**
         * Food Management → Categories
         */
        $event->registerArray([
            'name' => 'Foods Management',
            'code' => 'categories',
            'key' => [
                'admin.foodmanagements.category.index' => 'View Food Categories',
                'admin.foodmanagements.category.create' => 'Create Food Category',
                'admin.foodmanagements.category.store' => 'Store Food Category',
                'admin.foodmanagements.category.view' => 'View Food Category Details',
                'admin.foodmanagements.category.edit' => 'Edit Food Category',
                'admin.foodmanagements.category.update' => 'Update Food Category',
                'admin.foodmanagements.category.destroy' => 'Delete Food Category',
                'admin.foodmanagements.category.bulk-delete' => 'Bulk Delete Food Categories',
                'admin.foodmanagements.category.toggle-status' => 'Update Food Category Status',
            ]
        ]);

        /**
         * Food Management → Food Tags
         */
        $event->registerArray([
            'name' => 'Foods Management',
            'code' => 'food_tags',
            'key' => [
                'admin.foodmanagements.food-tags.index' => 'View Food Tags',
                'admin.foodmanagements.food-tags.create' => 'Create Food Tag',
                'admin.foodmanagements.food-tags.store' => 'Store Food Tag',
                'admin.foodmanagements.food-tags.view' => 'View Food Tag Details',
                'admin.foodmanagements.food-tags.edit' => 'Edit Food Tag',
                'admin.foodmanagements.food-tags.update' => 'Update Food Tag',
                'admin.foodmanagements.food-tags.destroy' => 'Delete Food Tag',
                'admin.foodmanagements.food-tags.bulk-delete' => 'Bulk Delete Food Tags',
            ]
        ]);

        /**
         * Food Management → Option Groups
         */
        $event->registerArray([
            'name' => 'Foods Management',
            'code' => 'food_option_groups',
            'key' => [
                'admin.foodmanagements.food-option-groups.index' => 'View Food Option Groups',
                'admin.foodmanagements.food-option-groups.show' => 'View Food Option Group',
                'admin.foodmanagements.food-option-groups.create' => 'Create Food Option Group',
                'admin.foodmanagements.food-option-groups.store' => 'Store Food Option Group',
                'admin.foodmanagements.food-option-groups.view' => 'View Food Option Group Details',
                'admin.foodmanagements.food-option-groups.edit' => 'Edit Food Option Group',
                'admin.foodmanagements.food-option-groups.update' => 'Update Food Option Group',
                'admin.foodmanagements.food-option-groups.destroy' => 'Delete Food Option Group',
                'admin.foodmanagements.food-option-groups.bulk-delete' => 'Bulk Delete Food Option Groups',
            ]
        ]);

        /**
         * Food Management → Options
         */
        $event->registerArray([
            'name' => 'Foods Management',
            'code' => 'food_options',
            'key' => [
                'admin.foodmanagements.food-options.store' => 'Store Food Option',
                'admin.foodmanagements.food-options.update' => 'Update Food Option',
                'admin.foodmanagements.food-options.destroy' => 'Delete Food Option',
            ]
        ]);

        /**
         * Food Management → Food Items
         */
        $event->registerArray([
            'name' => 'Foods Management',
            'code' => 'food_items',
            'key' => [
                'admin.foodmanagements.food-items.index' => 'View Food Items',
                'admin.foodmanagements.food-items.create' => 'Create Food Items',
                'admin.foodmanagements.food-items.store' => 'Store Food Items',
                'admin.foodmanagements.food-items.show' => 'View Food Items Details',
                'admin.foodmanagements.food-items.edit' => 'Edit Food Items',
                'admin.foodmanagements.food-items.update' => 'Update Food Items',
                'admin.foodmanagements.food-items.destroy' => 'Delete Food Items',

                // Step-wise permissions
                'admin.foodmanagements.food-items.store.general' => 'Store Food Item General Info',
                'admin.foodmanagements.food-items.update.general' => 'Update Food Item General Info',
                'admin.foodmanagements.food-items.update.categories' => 'Update Food Item Categories',
                'admin.foodmanagements.food-items.update.sub-categories' => 'Update Food Item Sub-Categories',
                'admin.foodmanagements.food-items.update.tags' => 'Update Food Item Tags',
                'admin.foodmanagements.food-items.update.options' => 'Update Food Item Options',
                'admin.foodmanagements.food-items.update.images' => 'Update Food Item Images',

                'admin.foodmanagements.food-items.bulk-delete' => 'Bulk Delete Food Items',
                'admin.foodmanagements.food-items.toggle-status' => 'Toggle Food Items Status',
            ]
        ]);


        /**
         * Food Management → Food Events
         */
        $event->registerArray([
            'name' => 'Foods Management',
            'code' => 'food_events',
            'key' => [
                'admin.foodmanagements.events.index' => 'View Food Events',
                'admin.foodmanagements.events.create' => 'Create Food Events',
                'admin.foodmanagements.events.edit' => 'Edit Food Events',
                'admin.foodmanagements.events.destroy' => 'Delete Food Events',
                'admin.foodmanagements.events.bulk-delete' => 'Bulk Delete Food Events',
                'admin.foodmanagements.events.toggle-status' => 'Toggle Food Events Status',           
            ]
        ]);
    }
}
