<?php

namespace Modules\Rating\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterRatingPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Settings → Ratings
         */
        $event->registerArray([
            'name' => 'settings',
            'code' => 'ratings',
            'key' => [
                'ratings.index' => 'View Ratings',
                'ratings.create' => 'Create Ratings',
                'ratings.store' => 'Store Ratings',
                'ratings.edit' => 'Edit Ratings',
                'ratings.update' => 'Update Ratings',
                'ratings.destroy' => 'Delete Ratings',
                'ratings.toggle-status' => 'Change Rating Status',
                'ratings.approve' => 'Approve Ratings',
                'ratings.bulk-delete' => 'Delete Bulk Ratings',
            ]
        ]);
    }
}
