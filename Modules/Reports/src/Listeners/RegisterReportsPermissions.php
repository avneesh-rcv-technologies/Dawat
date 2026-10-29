<?php

namespace Modules\Reports\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterReportsPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Settings → Reports
         */
        // $event->registerArray([
        //     'name' => 'settings',
        //     'code' => 'reports',
        //     'key' => [
        //         'reports.index' => 'View Reports',
        //         'reports.create' => 'Create Reports',
        //         'reports.store' => 'Store Reports',
        //         'reports.edit' => 'Edit Reports',
        //         'reports.update' => 'Update Reports',
        //         'reports.destroy' => 'Delete Reports',
        //         'reports.show' => 'View Report Details',
        //         'reports.execute' => 'Execute Reports',
        //         'reports.export' => 'Export Reports',
        //         'reports.duplicate' => 'Duplicate Reports',
        //         'reports.toggle-active' => 'Toggle Report Status',
        //         'reports.schedule' => 'Schedule Reports',
        //         'reports.bulk-delete' => 'Delete Bulk Reports',
        //     ]
        // ]);
    }
}
