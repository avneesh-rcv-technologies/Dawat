<?php

namespace Modules\Payments\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Payments\Processors\CodProcessor;
use Modules\Payments\Services\PaymentsProcessorRegistry;
use Modules\Payments\Providers\PaymentsEventServiceProvider;

class PaymentsServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Payments';
    protected $moduleNameLower = 'payments';

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'payments'
        );

        $this->app->register(PaymentsEventServiceProvider::class);
    }

    /**
     * Register the available payment processors.
     */
    protected function registerProcessors(): void
    {
        $registry = new PaymentsProcessorRegistry();
        $CodProcessor = new CodProcessor();
        $registry->register('cod', $CodProcessor);
        
        // Bind the registry to the container
        $this->app->singleton(PaymentsProcessorRegistry::class, function () use ($registry) {
            return $registry;
        });
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
        $this->registerProcessors();
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('payment.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'payment'
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
            \Illuminate\Support\Facades\Log::error("Error in PaymentServiceProvider registerRoutes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/payments');
        $sourcePath = __DIR__ . '/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', 'payment-module-views']);

        $this->loadViewsFrom($sourcePath, 'payments');
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'payments');
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
