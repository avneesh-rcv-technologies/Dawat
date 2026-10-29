<?php

namespace Modules\Subscriptions\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterSubscriptionPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Plans Management
         */
        $event->registerArray([
            'name' => 'plans',
            'key' => [
                'subscriptions.plans.index' => 'View Plans',
                'subscriptions.plans.create' => 'Create Plan',
                'subscriptions.plans.store' => 'Store Plan',
                'subscriptions.plans.edit' => 'Edit Plan',
                'subscriptions.plans.update' => 'Update Plan',
                'subscriptions.plans.destroy' => 'Delete Plan',
            ]
        ]);

        /**
         * Subscriptions Management
         */
        $event->registerArray([
            'name' => 'subscriptions',
            'key' => [
                'subscriptions.index' => 'View Subscriptions',
                'subscriptions.create' => 'Create Subscription',
                'subscriptions.store' => 'Store Subscription',
                'subscriptions.view' => 'View Subscription Details',
                'subscriptions.show' => 'Show Subscription Details',
                'subscriptions.edit' => 'Edit Subscription',
                'subscriptions.update' => 'Update Subscription',
                'subscriptions.cancel' => 'Cancel Subscription',
                'subscriptions.resume' => 'Resume Subscription',
                'subscriptions.destroy' => 'Delete Subscription',
            ]
        ]);

        /**
         * Subscriptions Management
         */
        $event->registerArray([
            'name' => 'Features',
            'key' => [
                'subscriptions.features.index' => 'View Features',
                'subscriptions.features.create' => 'Create Feature',
                'subscriptions.features.store' => 'Store Feature',
                'subscriptions.features.view' => 'View Feature Details',
                'subscriptions.features.show' => 'Show Feature Details',
                'subscriptions.features.edit' => 'Edit Feature',
                'subscriptions.features.update' => 'Update Feature',
                'subscriptions.features.cancel' => 'Cancel Feature',
                'subscriptions.features.resume' => 'Resume Feature',
                'subscriptions.features.destroy' => 'Delete Feature',
            ]
        ]);
    }
}
