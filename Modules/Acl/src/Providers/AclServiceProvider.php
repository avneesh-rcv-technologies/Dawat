<?php

namespace Modules\Acl\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Modules\Acl\Console\Commands\CreateUserCommand;

class AclServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Acl';
    protected $moduleNameLower = 'acl';

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'acl'
        );

        $configPath = __DIR__ . '/../Config';

        foreach (glob($configPath . '/*.php') as $file) {
            $filename = basename($file, '.php');

            if ($filename === 'config') {
                // Merge main config file as 'subscription'
                $this->mergeConfigFrom($file, 'acl');
            } else {
                // Merge others as 'subscription.filename'
                $this->mergeConfigFrom($file, "acl::$filename");
            }
        }

        $this->app->register(AclEventServiceProvider::class);
        $this->app->register(MiddlewareServiceProvider::class);

        // Bind repositories and services for ACL
        $this->app->bind(\Modules\Acl\Repositories\RoleRepository::class, function ($app) {
            return new \Modules\Acl\Repositories\RoleRepository();
        });

        $this->app->when(\Modules\Acl\Services\RoleService::class)
            ->needs(\Modules\Acl\Repositories\RoleRepository::class)
            ->give(function ($app) {
                return $app->make(\Modules\Acl\Repositories\RoleRepository::class);
            });

        // Bind Customer Repository and Service
        $this->app->bind(\Modules\Acl\Repositories\CustomerRepository::class, function ($app) {
            return new \Modules\Acl\Repositories\CustomerRepository();
        });

        $this->app->bind(\Modules\Acl\Services\CustomerService::class, function ($app) {
            return new \Modules\Acl\Services\CustomerService(
                $app->make(\Modules\Acl\Repositories\CustomerRepository::class)
            );
        });

        config(['acl.currentUser' => \Illuminate\Support\Facades\Auth::getDefaultDriver()]);

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerConfig();
        $this->registerCommands();
        $this->app->register(\Modules\Acl\Providers\ConsoleServiceProvider::class);
        $this->registerRoutes();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerMigrations();
        $this->registerComponents();
        $this->registerGuards();
    }

    public function registerGuards(): void
    {
        $guardsConfig = config('acl::guards.guards', []);  
        
        // get guard name from url
        Route::bind('guard', function ($value) use ($guardsConfig) {
            return array_key_exists($value, $guardsConfig) ? $value : null;
        });

        // pass current guard key in blade view
        view()->composer('*', function ($view) use ($guardsConfig) {
            // Get current guard from route parameter or authenticated user
            $currentGuard = \Illuminate\Support\Facades\Auth::getDefaultDriver();
            
            if (!$currentGuard) {
                // Check which guard the user is authenticated with
                foreach (array_keys($guardsConfig) as $guardName) {
                    if (auth()->guard($guardName)->check()) {
                        $currentGuard = $guardName;
                        break;
                    }
                }
            }
            
            // Default to first guard if none found
            if (!$currentGuard && !empty($guardsConfig)) {
                $currentGuard = array_key_first($guardsConfig);
            }
            
            $view->with('guard', $currentGuard);
        });
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('acl.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'acl'
        );
    }

    /**
     * Register commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateUserCommand::class,
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
            $adminRoutePath = __DIR__ . '/../Routes/admin.php';

            if (file_exists($webRoutePath)) {
                $this->loadRoutesFrom($webRoutePath);
            }

            if (file_exists($apiRoutePath)) {
                $this->loadRoutesFrom($apiRoutePath);
            }

            if (file_exists($adminRoutePath)) {
                $this->loadRoutesFrom($adminRoutePath);
            }

            // Load dynamic guard routes
            $this->loadDynamicGuardRoutes();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in AclServiceProvider registerRoutes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Load dynamic guard routes based on guards config
     */
    protected function loadDynamicGuardRoutes(): void
    {
        $guardsConfig = config('acl::guards.guards', []);
        
        foreach ($guardsConfig as $guard => $config) {
            $routePath = __DIR__ . "/../Routes/{$guard}.php";
            
            if (file_exists($routePath)) {
                $this->loadRoutesFrom($routePath);
            }
        }
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/acl');
        $sourcePath = __DIR__ . '/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', 'acl-module-views']);

        $this->loadViewsFrom($sourcePath, 'acl');
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'acl');
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

    }
}