<?php

namespace Modules\Shipping\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class RegisterShippingSettings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        $event->register([
            'category_code' => "general",
            'title' => 'Shipping Methods',
            'description' => 'Configure shipping options like Takeaway and Delivery settings.',
            'route' => route('admin.shipping_methods.index'),
            'icon' => 'local_shipping',
            'permission' => 'admin.shipping_methods.index'
        ]);
    }
}