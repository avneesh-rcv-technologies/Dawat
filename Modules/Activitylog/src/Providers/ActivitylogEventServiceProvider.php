<?php

namespace Modules\Activitylog\Providers;

use Modules\Core\Events\RegisterSettingsMenu;
use Modules\Activitylog\Listeners\RegisterSettings;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class ActivitylogEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        RegisterSettingsMenu::class => [
            RegisterSettings::class,
            // add more listeners from other modules
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