<?php

namespace Modules\Cms\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Cms\Listeners\RegisterMenuItems;
use Modules\Cms\Listeners\RegisterAdminPermissions;
use Modules\Core\Events\RegisterMenu;
use Modules\Core\Events\RegisterPermissions;
use Modules\Cms\Listeners\RegisterCmsSettings;
use Modules\Core\Events\RegisterSettingsMenu;
class CmsEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        RegisterMenu::class => [
            RegisterMenuItems::class,
        ],
        RegisterPermissions::class => [
            RegisterAdminPermissions::class,
        ],
        RegisterSettingsMenu::class => [
            RegisterCmsSettings::class,
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