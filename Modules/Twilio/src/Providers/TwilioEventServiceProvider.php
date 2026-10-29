<?php

namespace Modules\Twilio\Providers;

use Modules\Core\Events\RegisterSettingsMenu;
use Modules\Twilio\Listeners\RegisterTwilioSettings;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class TwilioEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        RegisterSettingsMenu::class => [
            RegisterTwilioSettings::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
} 