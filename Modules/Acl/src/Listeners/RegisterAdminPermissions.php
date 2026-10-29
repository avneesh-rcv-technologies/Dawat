<?php

namespace Modules\Acl\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event)
    {

        /**
         * Settings → Roles
         */
        $event->registerArray([
            'name' => 'settings',
            'code' => 'roles',
            'key' => [
                'settings.roles.index' => 'View Roles',
                'settings.roles.create' => 'Create Roles',
                'settings.roles.store' => 'Store Roles',
                'settings.roles.edit' => 'Edit Roles',
                'settings.roles.update' => 'Update Roles',
                'settings.roles.destroy' => 'Delete Roles',
                'settings.roles.bulk-delete' => 'Delete Bulk Roles',
            ]
        ]);

        /**
         * Admin → Customers
         */
        $event->registerArray([
            'name' => 'admin',
            'code' => 'customer',
            'key' => [
                'admin.customer.index' => 'View Customers',
                'admin.customer.create' => 'Create Customer',
                'admin.customer.store' => 'Store Customer',
                'admin.customer.edit' => 'Edit Customer',
                'admin.customer.update' => 'Update Customer',
                'admin.customer.view' => 'View Customer Details',
                'admin.customer.delete' => 'Delete Customer',
                'admin.customer.bulk-delete' => 'Bulk Delete Customers',
                'admin.customer.bulk-status' => 'Bulk Update Customer Status',
            ]
        ]);

        /**
         * Gallery Management
         */
        $event->registerArray([
            'name' => 'gallery',
            'code' => 'gallery',
            'key' => [
                'gallery.index' => 'View Gallery',
                'gallery.store' => 'Upload Gallery Images',
                'gallery.destroy' => 'Delete Gallery Images',
            ]
        ]);
    }
}
