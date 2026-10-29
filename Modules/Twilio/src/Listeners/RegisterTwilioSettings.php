<?php

namespace Modules\Twilio\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class RegisterTwilioSettings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        $event->register([
            'category_code' => "notifications",
            'title' => 'Twilio Configuration',
            'description' => 'Set up twilio credentials.',
            'route' => route('twilio.configuration'),
            'icon' => 'message',
            'permission' => 'twilio.configuration'
        ]);
        $event->register([
            'category_code' => "notifications",
            'title' => 'SMS Templates',
            'description' => 'Manage SMS templates for twilio integration.',
            'route' => route('sms-templates.index'),
            'icon' => 'message',
            'permission' => 'sms-templates.index'
        ]);
    }
}
