<?php

namespace Modules\Rating\Providers;

use Modules\Core\Events\RegisterPermissions;
use Modules\Core\Events\RegisterSettingsMenu;
use Modules\Rating\Listeners\RegisterRatingPermissions;
use Modules\Rating\Listeners\Settings;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class RatingEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        RegisterPermissions::class => [
            RegisterRatingPermissions::class,
        ],
        \Modules\Core\Events\RegisterMenu::class => [
            \Modules\Rating\Listeners\RegisterMenuItems::class,
        ],
        // RegisterSettingsMenu::class => [
        //     Settings::class,
        // ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
