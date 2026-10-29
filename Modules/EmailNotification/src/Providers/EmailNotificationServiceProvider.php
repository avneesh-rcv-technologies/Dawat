<?php

namespace Modules\EmailNotification\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\EmailNotification\Channels\EmailChannel;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Services\NotificationChannelManager;

class EmailNotificationServiceProvider extends ServiceProvider
{
    protected $moduleName = 'EmailNotification';
    protected $moduleNameLower = 'emailnotification';

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'emailnotification'
        );

        $this->app->register(EmailNotificationEventServiceProvider::class);
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
        $this->resgisterChannel();
    }

    /**
     * Method resgisterChannel
     *
     * @return void
     */
    protected function resgisterChannel()
    {
        // Only register if the table exists
        if (!Schema::hasTable('notification_channels')) {
            return;
        }

        // Create or update the email channel in database
        NotificationChannel::updateOrCreate(
            ['name' => 'email'],
            [
                'channel_class' => EmailChannel::class,
                'status' => true,
                'config' => []
            ]
        );
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('emailnotification.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'emailnotification'
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
            $webRoutePath = __DIR__ . '/../Routes/web.php';
            $apiRoutePath = __DIR__ . '/../Routes/api.php';

            if (file_exists($webRoutePath)) {
                $this->loadRoutesFrom($webRoutePath);
            }

            if (file_exists($apiRoutePath)) {
                $this->loadRoutesFrom($apiRoutePath);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in EmailNotificationServiceProvider registerRoutes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/emailnotification');
        $sourcePath = __DIR__ . '/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', 'emailnotification-module-views']);

        $this->loadViewsFrom($sourcePath, 'emailnotification');
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'emailnotification');
    }

    /**
     * Register migrations.
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Register compoenents
     */

    protected function registerComponents(): void {}
}
