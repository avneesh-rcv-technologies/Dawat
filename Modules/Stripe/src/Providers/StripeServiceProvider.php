<?php

namespace Modules\Stripe\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Stripe\Stripe;
use Modules\Stripe\Processors\StripeProcessor;

class StripeServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Stripe';
    protected $moduleNameLower = 'stripe';

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'stripe'
        );

        $this->app->register(StripeEventServiceProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Initialize Stripe SDK with configured API key
        $apiKey = config('services.stripe.secret') ?? env('STRIPE_SECRET');
        if ($apiKey) {
            Stripe::setApiKey($apiKey);
        } else {
            // Log::warning('Stripe API key not configured (services.stripe.secret or STRIPE_SECRET)');
        }
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
            __DIR__.'/../Config/config.php' => config_path('stripe.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'stripe'
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
            \Illuminate\Support\Facades\Log::error("Error in StripeServiceProvider registerRoutes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/stripe');
        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', 'stripe-module-views']);

        $this->loadViewsFrom($sourcePath, 'stripe');
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'stripe');
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

     protected function registerProcessors(): void
    {
        $paymentProcessorContract = 'Modules\\Payments\\Contracts\\PaymentProcessor';
        if (!interface_exists($paymentProcessorContract)) {
            Log::warning('Skipping Stripe processor registration: PaymentProcessor contract not found.', [
                'contract' => $paymentProcessorContract,
            ]);
            return;
        }

        $stripeProcessor = new StripeProcessor();
        $registryClass = 'Modules\\Payments\\Services\\PaymentsProcessorRegistry';

        if (class_exists($registryClass)) {
            try {
                $registry = app($registryClass);
                $registry->register('stripe', $stripeProcessor);
                return;
            } catch (\Throwable $e) {
                Log::warning('Stripe processor registry fallback triggered', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
        else {
            Log::warning('PaymentsProcessorRegistry class not found. Ensure Payments module is enabled and autoload is dumped.');
        }

        // Fallback: register directly in payment_methods if Payments module classes are not autoloaded.
        if (Schema::hasTable('payment_methods')) {
            DB::table('payment_methods')->updateOrInsert(
                ['code' => 'stripe'],
                [
                    'class_name' => get_class($stripeProcessor),
                    'name' => $stripeProcessor->getName(),
                    'template' => $stripeProcessor->getTemplate(),
                    'processor' => $stripeProcessor->getProcessorTemplate(),
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
