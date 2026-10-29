<?php

namespace Modules\Restaurant\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Modules\Restaurant\Models\RestaurantOnboardingStep;
use Modules\Restaurant\Observers\OnboardingStepObserver;

class RestaurantServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Restaurant';
    protected string $moduleNameLower = 'restaurant';

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

        // Register repositories and services
        $this->registerRepositories();
        $this->registerServices();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        \Modules\Restaurant\Support\OnboardingRateLimits::register();
        RestaurantOnboardingStep::observe(OnboardingStepObserver::class);

        $this->publishModuleConfigs();
        $this->publishModuleConfigs();
        $this->registerCommands();
        $this->registerRoutes();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerMigrations();
        $this->registerComponents();
        $this->registerRouteMiddleware();
    }

    protected function registerRouteMiddleware(): void
    {
        \Illuminate\Support\Facades\Route::aliasMiddleware(
            'restaurant.active',
            \Modules\Restaurant\Http\Middleware\RestaurantActiveMiddleware::class
        );
    }

    /**
     * Dynamically merge all config files from the module's Config directory.
     */
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
        // \Illuminate\Support\Facades\Blade::component('x-alert', \Modules\Restaurant\View\Components\Alert::class);
    }

    /**
     * Register repositories.
     */
    protected function registerRepositories(): void
    {
        $this->app->bind(
            \Modules\Restaurant\Repositories\RestaurantRepository::class,
            \Modules\Restaurant\Repositories\RestaurantRepository::class
        );

        $this->app->bind(
            \Modules\Restaurant\Repositories\RestaurantHourRepository::class,
            \Modules\Restaurant\Repositories\RestaurantHourRepository::class
        );

        $this->app->bind(
            \Modules\Restaurant\Repositories\RestaurantHolidayRepository::class,
            \Modules\Restaurant\Repositories\RestaurantHolidayRepository::class
        );

        $this->app->bind(
            \Modules\Restaurant\Repositories\AdminRepository::class,
            \Modules\Restaurant\Repositories\AdminRepository::class
        );
    }

    /**
     * Register services.
     */
    protected function registerServices(): void
    {
        $this->app->bind(
            \Modules\Restaurant\Services\RestaurantService::class,
            \Modules\Restaurant\Services\RestaurantService::class
        );

        $this->app->bind(
            \Modules\Restaurant\Services\RestaurantAuthService::class,
            \Modules\Restaurant\Services\RestaurantAuthService::class
        );

        $this->app->bind(
            \Modules\Restaurant\Services\RestaurantFoodCategoryService::class,
            \Modules\Restaurant\Services\RestaurantFoodCategoryService::class
        );

        $this->app->bind(
            \Modules\Restaurant\Services\RestaurantFoodEventService::class,
            \Modules\Restaurant\Services\RestaurantFoodEventService::class
        );

        $this->app->bind(
            \Modules\Restaurant\Services\RestaurantFoodTagService::class,
            \Modules\Restaurant\Services\RestaurantFoodTagService::class
        );

        $this->app->bind(
            \Modules\Restaurant\Services\RestaurantFoodOptionService::class,
            \Modules\Restaurant\Services\RestaurantFoodOptionService::class
        );

        $this->app->bind(
            \Modules\Restaurant\Services\RestaurantFoodFeatureGroupService::class,
            \Modules\Restaurant\Services\RestaurantFoodFeatureGroupService::class
        );

        $this->app->bind(
            \Modules\Restaurant\Services\RestaurantFoodFeatureService::class,
            \Modules\Restaurant\Services\RestaurantFoodFeatureService::class
        );

        $this->app->bind(
            \Modules\Restaurant\Services\RestaurantFoodItemService::class,
            \Modules\Restaurant\Services\RestaurantFoodItemService::class
        );

        $this->app->bind(
            \Modules\Restaurant\Services\RestaurantHolidayService::class,
            \Modules\Restaurant\Services\RestaurantHolidayService::class
        );

        $this->app->bind(
            \Modules\Restaurant\Services\RestaurantWorkingHourService::class,
            \Modules\Restaurant\Services\RestaurantWorkingHourService::class
        );
    }
}
