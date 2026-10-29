<?php

namespace Modules\Reports\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Core\Events\RegisterPermissions;
use Modules\Core\Events\RegisterSettingsMenu;
use Modules\Reports\Listeners\RegisterReportsPermissions;
use Modules\Reports\Listeners\Settings;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(RegisterPermissions::class, RegisterReportsPermissions::class);
        Event::listen(RegisterSettingsMenu::class, Settings::class);
    }
}
