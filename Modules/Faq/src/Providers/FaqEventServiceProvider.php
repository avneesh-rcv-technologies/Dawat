<?php

namespace Modules\Faq\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use Modules\Core\Events\RegisterPermissions;
use Modules\Faq\Listeners\RegisterFaqMenu;
use Modules\Faq\Listeners\RegisterFaqPermissions;
use Modules\Core\Events\RegisterSettingsMenu;

class FaqEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        RegisterSettingsMenu::class => [
            RegisterFaqMenu::class,
        ],
        RegisterPermissions::class => [
            RegisterFaqPermissions::class,
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