<?php

namespace Modules\Seo\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Modules\Seo\Http\Middleware\HandleSeoRedirects;
use Modules\Seo\Repositories\RedirectRepository;
use Modules\Seo\Services\RedirectCache;
use Modules\Seo\Services\RedirectMatcher;
use Modules\Seo\Services\RobotsBuilder;
use Modules\Seo\Services\SchemaBuilder;
use Modules\Seo\Services\SeoResolver;
use Modules\Seo\Services\SitemapBuilder;
use Modules\Seo\View\Components\SeoGlobalBodyConfig;
use Modules\Seo\View\Components\SeoGlobalHeadConfig;
use Modules\Seo\View\Components\SeoMeta;

class SeoServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Seo';
    protected string $moduleNameLower = 'seo';

    public function register(): void
    {
        $this->registerHelpers();
        $this->registerModuleConfigs();

        $eventProvider = "Modules\\{$this->moduleName}\\Providers\\{$this->moduleName}EventServiceProvider";
        if (class_exists($eventProvider)) {
            $this->app->register($eventProvider);
        }

        $this->app->singleton(SchemaBuilder::class);
        $this->app->singleton(SeoResolver::class);
        $this->app->singleton(SitemapBuilder::class);
        $this->app->singleton(RobotsBuilder::class);
        $this->app->singleton(RedirectMatcher::class);
        $this->app->singleton(RedirectRepository::class);
        $this->app->singleton(RedirectCache::class);
        $this->app->singleton(\Modules\Seo\Repositories\SeoSettingRepository::class);
        $this->app->singleton(\Modules\Seo\Services\SeoSettingService::class);
        $this->app->singleton(\Modules\Seo\Repositories\SeoPageRepository::class);
        $this->app->singleton(\Modules\Seo\Services\SeoPageService::class);
    }

    protected function registerHelpers(): void
    {
        $helpers = __DIR__ . '/../Helpers/seohelpers.php';
        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }

    public function boot(): void
    {
        $this->publishModuleConfigs();
        $this->registerCommands();
        $this->registerRoutes();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerMigrations();
        $this->registerMiddleware();
        $this->registerComponents();
    }

    protected function registerModuleConfigs(): void
    {
        $configPath = __DIR__ . '/../Config';
        foreach (glob($configPath . '/*.php') as $file) {
            $filename = basename($file, '.php');

            if ($filename === 'config') {
                $this->mergeConfigFrom($file, $this->moduleNameLower);
            } else {
                $this->mergeConfigFrom($file, "$this->moduleNameLower::$filename");
            }
        }
    }

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
            if (! empty($publish)) {
                $this->publishes($publish, ['config', "{$this->moduleNameLower}-config"]);
            }
        }
    }

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

    protected function registerViews(): void
    {
        $viewPath = resource_path("views/modules/{$this->moduleNameLower}");
        $sourcePath = __DIR__ . '/../Resources/views';

        if (File::isDirectory($sourcePath)) {
            $this->publishes([
                $sourcePath => $viewPath,
            ], ['views', "{$this->moduleNameLower}-views"]);

            $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
        }
    }

    protected function registerTranslations(): void
    {
        $langPath = __DIR__ . '/../Resources/lang';
        if (File::isDirectory($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        }
    }

    protected function registerMigrations(): void
    {
        $migrationPath = __DIR__ . '/../Database/Migrations';
        if (File::isDirectory($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }

    protected function registerMiddleware(): void
    {
        $this->app->booted(function () {
            $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
            $kernel->pushMiddleware(HandleSeoRedirects::class);            // prependMiddlewareToGroup('web', HandleSeoRedirects::class);
        });
    }

    protected function registerComponents(): void
    {
        Blade::component('seo-meta', SeoMeta::class);
        Blade::component('seo-global-head-config', SeoGlobalHeadConfig::class);
        Blade::component('seo-global-body-config', SeoGlobalBodyConfig::class);
    }
}
