<?php

namespace Modules\Filemanager\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Core\Events\RegisterPermissions;
use Modules\Filemanager\Listeners\RegisterFilemanagerPermissions;

class FilemanagerEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RegisterPermissions::class => [
            RegisterFilemanagerPermissions::class,
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