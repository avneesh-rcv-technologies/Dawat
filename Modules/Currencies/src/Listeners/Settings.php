<?php

namespace Modules\Currencies\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class Settings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        $event->register(
            [
                'category_code' => "general",
                'title' => 'Currencies',
                'description' => 'Configure accepted currencies and exchange rates.',
                'route' => route('settings.currencies.index'),
                'icon' => 'currency_exchange',
                'permission' => 'settings.currencies.index'
            ]
        );
        
    }
}
