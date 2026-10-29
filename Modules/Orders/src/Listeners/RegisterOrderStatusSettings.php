<?php

namespace Modules\Orders\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class RegisterOrderStatusSettings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        $event->register([
            'category_code' => 'general',
            'title' => 'Order Status',
            'description' => 'Manage order statuses and their configurations.',
            'route' => route('admin.orders-statuses.index'),
            'icon' => 'list_alt',
            'permission' => 'admin.orders-statuses.index'
        ]);
    }
}