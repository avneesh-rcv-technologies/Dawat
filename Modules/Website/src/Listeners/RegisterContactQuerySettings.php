<?php

namespace Modules\Website\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class RegisterContactQuerySettings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        $event->register([
            'category_code' => 'general',
            'title' => 'Contact Queries',
            'description' => 'View messages and enquiries submitted through the website contact form.',
            'route' => route('website.contact-queries.index'),
            'icon' => 'mail',
            'guard' => 'admin',
            'permission' => 'website.contact-queries.index',
        ]);
    }
}
