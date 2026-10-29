<?php

namespace Modules\Core\Providers;

use DateTimeZone;
use Modules\Core\Helpers\Hook;
use Modules\Core\Hook\HookManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Views\Components\BreadCrumbs;
use Modules\Core\Views\Components\AdvancedSelector;
use Modules\Core\Providers\CoreEventServiceProvider;
use Modules\Core\Views\Components\Address\CountryState;
use Modules\Core\Views\Components\Autocomplete\Autocomplete;

class CoreServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Core';
    protected $moduleNameLower = 'core';

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'core'
        );

        $configPath = __DIR__ . '/../Config';

        foreach (glob($configPath . '/*.php') as $file) {
            $filename = basename($file, '.php');

            if ($filename === 'config') {
                // Merge main config file as 'subscription'
                $this->mergeConfigFrom($file, 'core');
            } else {
                // Merge others as 'subscription.filename'
                $this->mergeConfigFrom($file, "core::$filename");
            }
        }

        // Load Core helpers (if present)
        foreach (glob(__DIR__ . '/../Helpers/*.php') as $file) {
            require_once $file;
        }

        // Load helpers from all modules: Modules/<Module>/src/Helpers/*.php
        $modulesPath = base_path('Modules');
        foreach (glob($modulesPath . '/*/src/Helpers/*.php') as $file) {
            require_once $file;
        }

        $this->app->register(CoreEventServiceProvider::class);
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
        $this->initializeHooks();
    }

    protected function initializeHooks()
    {
        try {
            // Set application timezone, name, and base_url from settings (validate before applying)
            if (Schema::hasTable('settings')) {
                try {
                    $timezone = null;
                    $appName = null;
                    $appUrl = null;

                    // Prefer using the module SettingService if available
                    if ($this->app->bound(\Modules\GeneralSetting\Services\SettingService::class)) {
                        $service = $this->app->make(\Modules\GeneralSetting\Services\SettingService::class);
                        $timezone = $service->getValidatedTimezone('general.timezone');
                        $appName = $service->get('general.company.name');
                    } elseif (function_exists('fn_get_setting')) {
                        // Timezone
                        $raw = fn_get_setting('general.timezone');
                        if (is_string($raw)) {
                            $decoded = @json_decode($raw, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_string($decoded)) {
                                $raw = $decoded;
                            }
                            $raw = trim($raw, "\"' ");
                        }
                        $identifiers = DateTimeZone::listIdentifiers();
                        if (is_string($raw) && in_array($raw, $identifiers, true)) {
                            $timezone = $raw;
                        }
                        // App Name
                        $appName = fn_get_setting('general.company.name');
                        if (is_string($appName)) {
                            $decoded = @json_decode($appName, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_string($decoded)) {
                                $appName = $decoded;
                            }
                            $appName = trim($appName, "\"' ");
                        }
                    }

                    if (!empty($timezone)) {
                        config(['app.timezone' => $timezone]);
                        try {
                            date_default_timezone_set($timezone);
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning('Failed to set timezone: ' . $e->getMessage());
                        }
                    }
                    if (!empty($appName)) {
                        config(['app.name' => $appName]);
                    }

                    // Set locale and fallback locale as per configuration or settings
                    $locale = fn_get_setting('general.locale') ?? config('app.locale', 'en');
                    config(['app.locale' => $locale]);
                    app()->setLocale($locale);

                    $debugMode = fn_get_setting('general.debug_mode') ?? config('app.debug', false);
                    config(['app.debug' => $debugMode]);

                    $fallbackLocale = config('app.fallback_locale', 'en');
                    config(['app.fallback_locale' => $fallbackLocale]);
                } catch (\Throwable $e) {
                    // protect boot from settings errors
                    \Illuminate\Support\Facades\Log::warning('Error while applying app config from settings: ' . $e->getMessage());
                }
            }

            Blade::directive('hook', function ($expression) {
                return "<?php echo \\Modules\\Core\\Helpers\\Hook::render($expression); ?>";
            });



            $modulesPath = base_path('Modules');

            // check module under module.json file module status is true or false
            $enabledModules = [];
            foreach (glob($modulesPath . '/*/module.json') as $moduleJsonFile) {
                $moduleDir = dirname($moduleJsonFile);
                $moduleName = basename($moduleDir);

                try {
                    $moduleData = json_decode(file_get_contents($moduleJsonFile), true);
                    if (isset($moduleData['enabled']) && $moduleData['enabled'] === true) {
                        $enabledModules[] = strtolower($moduleName);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Error reading module.json for {$moduleName}: " . $e->getMessage());
                }
            }

            foreach (glob($modulesPath . '//src/Resources/views/hooks/**/.blade.php') as $file) {

                $path = str_replace('\\', '/', $file);

                // detect module name safely
                $parts = explode('/Modules/', $path);

                if (count($parts) < 2)
                    continue;
                $module = explode('/', $parts[1])[0];
                $module = strtolower($module);

                // skip if module is not enabled
                if (!in_array($module, $enabledModules)) {
                    continue;
                }

                // hook path after "hooks/"
                $relative = explode('hooks/', $path)[1];

                // hook name: top.pre → top.pre
                $hook = str_replace(['/', '.blade.php'], [':', ''], $relative);
                // view name
                $viewName = $module . '::hooks.' . str_replace(['/', '.blade.php'], ['.', ''], $relative);

                // override
                if (str_ends_with($hook, '.override')) {
                    $pure = str_replace('.override', '', $hook);
                    HookManager::override($pure, $viewName);
                    continue;
                }

                // priorities
                $priority = 10;
                if (str_ends_with($hook, '.pre'))
                    $priority = 1;
                if (str_ends_with($hook, '.post'))
                    $priority = 20;

                HookManager::add($hook, $viewName, $priority);
            }
        } catch (\Exception $e) {
            Log::error("Error initializing hooks in CoreServiceProvider: " . $e->getMessage());
        }
    }

    protected function convertHookName($file)
    {
        // example: product/top.blade.php → product:top
        $relative = explode('hooks/', $file)[1];
        return str_replace(['/', '.blade.php'], [':', ''], $relative);
    }

    protected function convertViewName($file)
    {
        // module name from path: modules/AbandonedCart/...
        preg_match('#modules/([^/]+)/#', $file, $m);
        $module = strtolower($m[1]);

        // view path within hooks folder
        $relative = explode('hooks/', $file)[1];
        $relative = str_replace(['/', '.blade.php'], ['.', ''], $relative);

        return $module . '::hooks.' . $relative;
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('core.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'core'
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
            \Illuminate\Support\Facades\Log::error("Error in CoreServiceProvider registerRoutes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/core');
        $sourcePath = __DIR__ . '/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', 'core-module-views']);

        $this->loadViewsFrom($sourcePath, 'core');
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'core');
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

    protected function registerComponents(): void
    {
        Blade::component('advanced-selector', AdvancedSelector::class);
        Blade::component('breadcrumbs', BreadCrumbs::class);
    }
}
