<?php

namespace Modules\Blogs\Providers;

use Modules\Core\Events\RegisterSettingsMenu;
use Modules\Core\Events\RegisterPermissions;
use Modules\Blogs\Listeners\RegisterBlogsSettings;
use Modules\Blogs\Listeners\RegisterAdminPermissions;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class BlogsEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        RegisterSettingsMenu::class => [
            RegisterBlogsSettings::class,
        ],
        RegisterPermissions::class => [
            RegisterAdminPermissions::class,
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
