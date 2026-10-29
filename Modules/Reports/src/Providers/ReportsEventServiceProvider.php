<?php

namespace Modules\Reports\Providers;

use Modules\Reports\Listeners\Settings;
use Modules\Core\Events\RegisterPermissions;
use Modules\Core\Events\RegisterSettingsMenu;
use Modules\Reports\Listeners\RegisterReportsPermissions;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class ReportsEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        RegisterPermissions::class => [
            RegisterReportsPermissions::class,
        ],
        RegisterSettingsMenu::class => [
            Settings::class,
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
