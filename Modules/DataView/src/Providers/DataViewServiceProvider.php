<?php

namespace Modules\DataView\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Modules\DataView\View\Components\DataView\Layout;
use Modules\DataView\View\Components\DataView\Partials\Table;
use Modules\DataView\View\Components\DataView\Partials\Sidebar;
use Modules\DataView\View\Components\DataView\Partials\BackButton;
use Modules\DataView\View\Components\DataView\Partials\Pagination;
use Modules\DataView\View\Components\DataView\Partials\TopControls;
use Modules\DataView\View\Components\DynamicSelect;
use Modules\DataView\View\Components\Modal\Modal;

class DataViewServiceProvider extends ServiceProvider
{
    protected $moduleName = 'DataView';
    protected $moduleNameLower = 'dataview';

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'dataview'
        );

        $this->app->register(DataViewEventServiceProvider::class);
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
            __DIR__ . '/../Config/config.php' => config_path('dataview.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'dataview'
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
            \Illuminate\Support\Facades\Log::error("Error in DataViewServiceProvider registerRoutes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/dataview');
        $sourcePath = __DIR__ . '/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', 'dataview-module-views']);

        $this->loadViewsFrom($sourcePath, 'dataview');
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'dataview');
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
        Blade::component('data-view', Layout::class);
        Blade::component('table', Table::class);
        Blade::component('topbar', TopControls::class);
        Blade::component('pagination', Pagination::class);
        Blade::component('sidebar', Sidebar::class);
        Blade::component('back', BackButton::class);
        Blade::component('modal', Modal::class);
        Blade::component('dynamic-select', DynamicSelect::class);

        
    }
}
