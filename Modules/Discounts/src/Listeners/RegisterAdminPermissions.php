<?php

namespace Modules\Discounts\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Discount
         */
        $event->registerArray([
            'name' => 'Discount',
            'code' => 'discount',
            'key' => [
                'discount.index' => 'View Discount',
                'discount.create' => 'Create Discount',
                'discount.edit' => 'Edit Discount',
                'discount.destory' => 'Destroy Discount'
            ]
        ]);
    }
}
