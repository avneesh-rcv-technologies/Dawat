<?php

namespace Modules\Notifications\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use PDOException;
use Modules\Notifications\Channels\SystemChannel;
use Modules\Notifications\Services\NotificationChannelManager;

class NotificationsServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Notifications';
    protected $moduleNameLower = 'notifications';

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'notifications'
        );

        $this->app->register(NotificationsEventServiceProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerConfig();
        $this->registerCommands();
        $this->registerRoutes();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerMigrations();
        $this->registerComponents();
        $this->registerNotificationChannel();
    }

    /**
     * Register the WebPush notification channel.
     */
    protected function registerNotificationChannel(): void
    {
        // Composer scripts must be able to boot without a database.
        if ($this->app->runningConsoleCommand('package:discover', 'vendor:publish')) {
            return;
        }

        try {
            // Only register if the table exists
            if (!Schema::hasTable('notification_channels')) {
                return;
            }

            $this->app->singleton(SystemChannel::class, function ($app) {
                return new SystemChannel(config('services.system', []));
            });

            $this->app->when(SystemChannel::class)
                ->needs('$config')
                ->giveConfig('services.system');

            $channelManager = $this->app->make(NotificationChannelManager::class);
            $channelManager->registerChannel('system', SystemChannel::class);
        } catch (PDOException $e) {
            Log::warning('Skipping Notifications channel registration: database unavailable.');
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('notifications.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'notifications'
        );
    }

    /**
     * Register commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Add your commands here
            ]);
        }
    }

    /**
     * Register routes.
     */
    protected function registerRoutes(): void
    {
        try {
            $webRoutePath = __DIR__.'/../Routes/web.php';
            $apiRoutePath = __DIR__.'/../Routes/api.php';

            if (file_exists($webRoutePath)) {
                $this->loadRoutesFrom($webRoutePath);
            }

            if (file_exists($apiRoutePath)) {
                $this->loadRoutesFrom($apiRoutePath);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in NotificationsServiceProvider registerRoutes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/notifications');
        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', 'notifications-module-views']);

        $this->loadViewsFrom($sourcePath, 'notifications');
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'notifications');
    }

    /**
     * Register migrations.
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    /**
    * Register compoenents
    */

    protected function registerComponents(): void
    {
       
    }
} 