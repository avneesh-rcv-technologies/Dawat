<?php

namespace Modules\Activitylog\Providers;

use Modules\Core\Events\RegisterSettingsMenu;
use Modules\Core\Events\RegisterPermissions;
use Modules\Activitylog\Listeners\RegisterSettings;
use Modules\Activitylog\Listeners\RegisterActivitylogPermissions;
use Modules\Activitylog\Listeners\LogAuthenticationToSpatieAudit;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class ActivitylogEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RegisterSettingsMenu::class => [
            RegisterSettings::class,
        ],
        RegisterPermissions::class => [
            RegisterActivitylogPermissions::class,
        ],
        Login::class => [
            LogAuthenticationToSpatieAudit::class,
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