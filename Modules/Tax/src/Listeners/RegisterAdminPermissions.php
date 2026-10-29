<?php

namespace Modules\Tax\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Discount
         */
        $event->registerArray([
            'name' => 'Tax',
            'code' => 'tax',
            'key' => [
                'admin.tax.index' => 'View Tax',
                'admin.tax.create' => 'Create Tax',
                'admin.tax.edit' => 'Edit Tax',
                'admin.tax.destroy' => 'Destroy Tax',
                'admin.tax-rules.index' => 'View Tax Rules',
                'admin.tax-rules.edit' => 'Edit Tax Rules',
                'admin.tax-rules.destroy' => 'Delete Tax Rules'
            ]
        ]);
    }
}
