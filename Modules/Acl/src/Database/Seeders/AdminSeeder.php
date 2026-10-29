<?php

namespace Modules\Acl\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Acl\Models\Admin;
use Modules\Acl\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Permissions granted to the seeded Restaurant Owner role.
     * Approval access is deliberately read-only; owner scoping is enforced by
     * the approval and KYC access middleware.
     */
    public static function restaurantOwnerPermissions(): array
    {
        return [
            'profile.edit',
            'profile.update',
            'dashboard.index',
            'settings.countries.index',
            'settings.countries.create',
            'settings.countries.store',
            'settings.countries.edit',
            'settings.countries.update',
            'settings.countries.destroy',
            'settings.states.index',
            'settings.states.create',
            'settings.states.store',
            'settings.states.edit',
            'settings.states.update',
            'settings.states.destroy',
            'settings.cities.index',
            'settings.cities.create',
            'settings.cities.store',
            'settings.cities.edit',
            'settings.cities.update',
            'settings.cities.destroy',
            'admin.restaurant.index',
            'admin.restaurant.edit',
            'admin.restaurant.view',
            'admin.restaurant.store.general',
            'admin.restaurant.update.hours',
            'admin.restaurant.update.address',
            'admin.restaurant.update.holidays',
            'admin.restaurant.update.settings',
            'admin.restaurant.gallery.store',
            'admin.restaurant.gallery.destroy',

            'admin.foodmanagements.index',
            'admin.foodmanagements.category.index',
            'admin.foodmanagements.category.create',
            'admin.foodmanagements.category.store',
            'admin.foodmanagements.category.show',
            'admin.foodmanagements.category.edit',
            'admin.foodmanagements.category.update',
            'admin.foodmanagements.category.destroy',
            'admin.foodmanagements.category.bulk-delete',
            'admin.foodmanagements.category.toggle-status',
            'admin.foodmanagements.food-tags.index',
            'admin.foodmanagements.food-tags.create',
            'admin.foodmanagements.food-tags.store',
            'admin.foodmanagements.food-tags.show',
            'admin.foodmanagements.food-tags.edit',
            'admin.foodmanagements.food-tags.update',
            'admin.foodmanagements.food-tags.destroy',
            'admin.foodmanagements.food-tags.bulk-delete',
            'admin.foodmanagements.food-option-groups.index',
            'admin.foodmanagements.food-option-groups.show',
            'admin.foodmanagements.food-option-groups.create',
            'admin.foodmanagements.food-option-groups.store',
            'admin.foodmanagements.food-option-groups.edit',
            'admin.foodmanagements.food-option-groups.update',
            'admin.foodmanagements.food-option-groups.destroy',
            'admin.foodmanagements.food-option-groups.bulk-delete',
            'admin.foodmanagements.food-options.store',
            'admin.foodmanagements.food-options.update',
            'admin.foodmanagements.food-options.destroy',
            'admin.foodmanagements.food-items.index',
            'admin.foodmanagements.food-items.create',
            'admin.foodmanagements.food-items.store',
            'admin.foodmanagements.food-items.show',
            'admin.foodmanagements.food-items.edit',
            'admin.foodmanagements.food-items.update',
            'admin.foodmanagements.food-items.destroy',
            'admin.foodmanagements.food-items.store.general',
            'admin.foodmanagements.food-items.update.general',
            'admin.foodmanagements.food-items.update.categories',
            'admin.foodmanagements.food-items.update.sub-categories',
            'admin.foodmanagements.food-items.update.tags',
            'admin.foodmanagements.food-items.update.options',
            'admin.foodmanagements.food-items.update.option-prices',
            'admin.foodmanagements.food-items.update.features',
            'admin.foodmanagements.food-items.update.images',
            'admin.foodmanagements.food-items.bulk-delete',
            'admin.foodmanagements.food-items.toggle-status',
            'admin.foodmanagements.events.index',
            'admin.foodmanagements.events.create',
            'admin.foodmanagements.events.store',
            'admin.foodmanagements.events.show',
            'admin.foodmanagements.events.edit',
            'admin.foodmanagements.events.update',
            'admin.foodmanagements.events.destroy',
            'admin.foodmanagements.events.update-items',
            'admin.foodmanagements.events.update-categories',
            'admin.foodmanagements.events.bulk-delete',
            'admin.foodmanagements.events.toggle-status',

            'admin.orders.index',
            'admin.orders.show',
            'admin.transactions.index',
            'admin.transactions.show',

            'notifications.bell.unread-count',
            'notifications.bell.recent',
            'notifications.bell.mark-as-read',
            'notifications.bell.toggle-read-status',
            'notifications.bell.mark-all-as-read',

            'admin.restaurant.approvals.index',
            'admin.restaurant.approvals.show',
            'admin.restaurant.approvals.review.pdf',
            'admin.restaurant.approvals.banking.show',
            'admin.restaurant.approvals.tax.show',
            'admin.kyc.submissions.index',
            'admin.kyc.submissions.show',
            'admin.kyc.submissions.preview-file',
        ];
    }

    public function run(): void
    {
        DB::transaction(function (): void {
            Role::firstOrCreate(
                ['id' => 1],
                [
                    'name' => 'Admin Owner',
                    'description' => 'Admin owner role',
                    'guard' => 'admin',
                    'permission_type' => 'all',
                    'permissions' => [],
                ],
            );

            Role::updateOrCreate(
                ['id' => 2],
                [
                    'name' => 'Restaurant Owner',
                    'description' => 'Restaurant owner role',
                    'guard' => 'admin',
                    'permission_type' => 'custom',
                    'permissions' => self::restaurantOwnerPermissions(),
                ],
            );

            // Preserve credentials for an existing account when this seeder is rerun.
            Admin::firstOrCreate(
                ['email' => 'admin@admin.com'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('password'),
                    'status' => true,
                    'role_id' => 1,
                ],
            );
        });
    }
}
