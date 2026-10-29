<?php

namespace Modules\WebPushNotification\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use PDOException;
use Illuminate\Console\View\Components\Alert;
use Modules\WebPushNotification\Channels\WebPushChannel;
use Modules\Notifications\Services\NotificationChannelManager;

class WebPushNotificationServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'WebPushNotification';
    protected string $moduleNameLower = 'webpushnotification';

    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge all module config files
        $this->registerModuleConfigs();

        // Register EventServiceProvider if exists
        $eventProvider = "Modules\\{$this->moduleName}\\Providers\\{$this->moduleName}EventServiceProvider";
        if (class_exists($eventProvider)) {
            $this->app->register($eventProvider);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishModuleConfigs();
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

            $this->app->singleton(WebPushChannel::class, function ($app) {
                return new WebPushChannel(config('services.webpush', []));
            });

            $this->app->when(WebPushChannel::class)
                ->needs('$config')
                ->giveConfig('services.webpush');

            $channelManager = $this->app->make(NotificationChannelManager::class);
            $channelManager->registerChannel('webpush', WebPushChannel::class);
        } catch (PDOException $e) {
            Log::warning('Skipping WebPushNotification channel registration: database unavailable.');
        }
    }

    protected function registerModuleConfigs(): void
    {
        $configPath = __DIR__ . '/../Config';
        if (File::isDirectory($configPath)) {
            foreach (File::files($configPath) as $file) {
                if ($file->getExtension() === 'php') {
                    $key = $file->getBasename('.php');
                    $this->mergeConfigFrom($file->getRealPath(), $key);
                }
            }
        }
    }

    /**
     * Publish all module config files.
     */
    protected function publishModuleConfigs(): void
    {
        $configPath = __DIR__ . '/../Config';
        if (File::isDirectory($configPath)) {
            $publish = [];
            foreach (File::files($configPath) as $file) {
                if ($file->getExtension() === 'php') {
                    $publish[$file->getRealPath()] = config_path($file->getFilename());
                }
            }
            if (!empty($publish)) {
                $this->publishes($publish, ['config', "{$this->moduleNameLower}-config"]);
            }
        }
    }

    /**
     * Register commands automatically from Console folder.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $commandPath = __DIR__ . '/../Console';
            if (File::isDirectory($commandPath)) {
                foreach (File::files($commandPath) as $file) {
                    $class = "Modules\\{$this->moduleName}\\Console\\" . $file->getBasename('.php');
                    if (class_exists($class)) {
                        $this->commands([$class]);
                    }
                }
            }
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
            Log::error("Error in {$this->moduleName}ServiceProvider registerRoutes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path("views/modules/{$this->moduleNameLower}");
        $sourcePath = __DIR__ . '/../Resources/views';

        if (File::isDirectory($sourcePath)) {
            $this->publishes([
                $sourcePath => $viewPath
            ], ['views', "{$this->moduleNameLower}-views"]);

            $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
        }
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $langPath = __DIR__ . '/../Resources/lang';
        if (File::isDirectory($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        }
    }

    /**
     * Register migrations.
     */
    protected function registerMigrations(): void
    {
        $migrationPath = __DIR__ . '/../Database/Migrations';
        if (File::isDirectory($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }

    /**
     * Register Blade/Livewire components.
     */
    protected function registerComponents(): void
    {
        // Example:
        // \Illuminate\Support\Facades\Blade::component('x-alert', \Modules\WebPushNotification\View\Components\Alert::class);
    }
}
