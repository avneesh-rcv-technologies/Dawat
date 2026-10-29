<?php

namespace Modules\Activitylog\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

class ActivitylogServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Activitylog';
    protected $moduleNameLower = 'activitylog';

    /**
     * Register services.
     */
    public function register(): void
    {
        /* $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'activitylog' 
        );*/

        $configPath = __DIR__ . '/../Config';

        foreach (glob($configPath . '/*.php') as $file) {
            $filename = basename($file, '.php');

            if ($filename === 'config') {
                // Merge main config file as 'activitylog'
                $this->mergeConfigFrom($file, 'activitylog');
            } else {
                // Merge others as 'activitylog.filename'
                $this->mergeConfigFrom($file, "activitylog::$filename");
            }
        }

        $this->app->register(ActivitylogEventServiceProvider::class);
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
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('activitylog.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'activitylog'
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
                Route::group(['middleware' => ['web']], function () use ($webRoutePath) {
                    $this->loadRoutesFrom($webRoutePath);
                });
            }

            if (file_exists($apiRoutePath)) {
                Route::group(['middleware' => ['api']], function () use ($apiRoutePath) {
                    $this->loadRoutesFrom($apiRoutePath);
                });
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in ActivitylogServiceProvider registerRoutes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/activitylog');
        $sourcePath = __DIR__ . '/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', 'activitylog-module-views']);

        $this->loadViewsFrom($sourcePath, 'activitylog');
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'activitylog');
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
