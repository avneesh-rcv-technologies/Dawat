<?php

namespace Modules\Website\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class WebsiteEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \Modules\Core\Events\RegisterSettingsMenu::class => [
            \Modules\Website\Listeners\RegisterContactQuerySettings::class,
        ],
        \Modules\Core\Events\RegisterPermissions::class => [
            \Modules\Website\Listeners\RegisterContactQueryPermissions::class,
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