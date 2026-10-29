<?php

namespace Modules\Twilio\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use PDOException;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Twilio\Channels\TwilioChannel;

class TwilioServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Twilio';
    protected $moduleNameLower = 'twilio';

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'twilio'
        );

        $this->app->register(TwilioEventServiceProvider::class);
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
        // Composer scripts must be able to boot without a database.
        if ($this->app->runningConsoleCommand('package:discover', 'vendor:publish')) {
            return;
        }

        try {
            // Only register if the table exists
            if (!Schema::hasTable('notification_channels')) {
                return;
            }

            // Create or update the email channel in database
            NotificationChannel::updateOrCreate(
                ['name' => 'sms'],
                [
                    'channel_class' => TwilioChannel::class,
                    'status' => true,
                    'config' => [
                       'twilio_account_sid' => fn_get_setting('twilio.twilio_account_sid'),
                       'twilio_auth_token' => fn_get_setting('twilio.twilio_auth_token'),
                       'twilio_phone_number' => fn_get_setting('twilio.twilio_phone_number'),
                    ]
                ]
            );
        } catch (PDOException $e) {
            Log::warning('Skipping Twilio channel registration: database unavailable.');
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('twilio.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'twilio'
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
            \Illuminate\Support\Facades\Log::error("Error in TwilioServiceProvider registerRoutes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/twilio');
        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', 'twilio-module-views']);

        $this->loadViewsFrom($sourcePath, 'twilio');
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'twilio');
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