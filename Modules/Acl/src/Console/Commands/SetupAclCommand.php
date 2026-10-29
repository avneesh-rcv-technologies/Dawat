<?php

namespace Modules\Acl\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SetupAclCommand extends Command
{
    protected $signature = 'acl:setup {--guard= : The name of the guard (e.g., admin, user)}';
    protected $description = 'Setup complete ACL structure for a guard';

    public function handle(): int
    {
        $guard = strtolower($this->option('guard'));
        $modelName = ucfirst($guard);
        $model = 'Modules\\Acl\\Models\\' . $modelName;
        $table = Str::plural($guard);

        // Check if guard already exists
        if ($this->guardExists($guard)) {
            $this->warn("⚠️  Guard '{$guard}' already exists!");
            $this->line("The guard configuration is already set up. No changes were made.");
            return 0;
        }

        // 1. Create Config
        $this->createConfig($guard, $model, $table);

        // 2. Create Model
        $this->createModel($guard, $model, $table);

        // 3. Create Migration
        $this->createMigration($guard, $table);


        // 4. Create Controller
        $this->createController($guard, $model);

        // 5. Create Views
        $this->createViews($guard);


        // 5. Create Service
        $this->createService($guard, $model);

        // 6. Create Requests
        $this->createRequests($guard);

        // 7. Create Routes
        $this->createRoutes($guard);

        // 8. Create Notifications
        $this->createNotifications($guard);

        // 9. Create Middleware
        // $this->createMiddleware($guard);

        // 10. Update auth.php
        $this->updateAuthConfig($guard, $table);

        $this->info("✅ ACL setup for guard '{$guard}' completed successfully!");
        $this->line("");
        
        // Ask if user wants to create a module
        // if ($this->confirm("Do you want to create a module for '{$guard}'?", true)) {
        //     $this->createAndEnableModule($guard);
        // }
        
        $this->line("");
        $this->line("📝 Next steps:");
        $this->line("1. Run: php artisan migrate");
        $this->line("2. Routes are automatically loaded from: Modules/Acl/src/Routes/{$guard}.php");
        $this->line("3. Middleware registered: auth.{$guard}, guest.{$guard}, {$guard}.permission");
        $this->line("");
        $this->line("🎉 Your {$guard} guard is ready to use!");

        return 0;
    }

    /**
     * Create auth blade views for the guard using stub files.
     */
    protected function createViews($guard)
    {
        $stubDir = __DIR__ . '/stubs/views/auth';
        $viewNames = [
            'login.blade.php',
            'register.blade.php',
            'forgot-password.blade.php',
            'reset-password.blade.php',
        ];
        $guardViewPath = module_path('Acl') . "/src/Resources/views/{$guard}/auth";
        foreach ($viewNames as $viewFile) {
            $stubFile = "{$stubDir}/{$viewFile}";
            if (!File::exists($stubFile)) {
                $this->warn("⚠️  Stub view not found: {$stubFile}");
                continue;
            }
            $content = str_replace('{{guard}}', $guard, File::get($stubFile));
            File::ensureDirectoryExists($guardViewPath);
            File::put("{$guardViewPath}/{$viewFile}", $content);
            $this->info("✅ Created view: {$guard}/auth/{$viewFile}");
        }
    }

    protected function createAndEnableModule($guard)
    {
        $moduleName = ucfirst($guard);
        
        $this->info("Creating module '{$moduleName}'...");
        $this->call('module:make', ['name' => $moduleName]);
        
        $this->info("Enabling module '{$moduleName}'...");
        $this->call('module:enable', ['module' => $moduleName]);
        
        $this->info("✅ Module '{$moduleName}' created and enabled successfully!");
    }


    protected function createConfig($guard, $model, $table)
    {
        $configPath = module_path('Acl') . '/src/Config/guards.php';

        // Load existing config
        $config = require $configPath;

        // Check if guard already exists
        if (isset($config['guards'][$guard])) {
            $this->warn("Guard '{$guard}' already exists in config.");
            return;
        }

        // Add new guard
        $config['guards'][$guard] = [
            'driver' => 'session',
            'provider' => $guard . 's',
            'prefix' => $guard,
        ];

        // Add provider
        $config['providers'][$guard . 's'] = [
            'driver' => 'eloquent',
            'model' => $model,
        ];

        // Add password reset config
        $config['passwords'][$guard] = [
            'provider' => $guard . 's',
            'table' => "{$guard}_password_reset_tokens",
            'expire' => 60,
            'throttle' => 60,
        ];

        // Format the config with proper spacing
        $content = "<?php\n\nreturn [\n";

        // Guards
        $content .= "    'guards' => [\n";
        foreach ($config['guards'] as $guardName => $guardConfig) {
            $content .= "        '{$guardName}' => [\n";
            foreach ($guardConfig as $key => $value) {
                $content .= "            '{$key}' => '{$value}',\n";
            }
            $content .= "        ],\n";
        }
        $content .= "    ],\n\n";

        // Providers
        $content .= "    'providers' => [\n";
        foreach ($config['providers'] as $provider => $providerConfig) {
            $content .= "        '{$provider}' => [\n";
            foreach ($providerConfig as $key => $value) {
                if ($key === 'model') {
                    $value = str_replace('\\', '\\\\', $value);
                }
                $content .= "            '{$key}' => '{$value}',\n";
            }
            $content .= "        ],\n";
        }
        $content .= "    ],\n\n";

        // Passwords
        $content .= "    'passwords' => [\n";
        foreach ($config['passwords'] as $password => $passwordConfig) {
            $content .= "        '{$password}' => [\n";
            foreach ($passwordConfig as $key => $value) {
                $value = is_string($value) ? "'{$value}'" : $value;
                $content .= "            '{$key}' => {$value},\n";
            }
            $content .= "        ],\n";
        }
        $content .= "    ],\n";

        // Password timeout
        $content .= "\n    'password_timeout' => " . ($config['password_timeout'] ?? '10800') . ",\n";

        $content .= "];\n";

        // Write the file
        File::put($configPath, $content);

        $this->info("✅ Updated guards.php with '{$guard}' configuration");
    }

    protected function createModel($guard, $model, $table)
    {
        $modelClass = class_basename($model);
        $stub = File::get(__DIR__ . '/stubs/model.stub');
        $content = str_replace(
            ['{{modelName}}', '{{tableName}}', '{{guard}}'],
            [$modelClass, $table, $guard],
            $stub
        );

        $path = module_path('Acl') . "/src/Models/{$modelClass}.php";
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        $this->info("✅ Created model: {$model}");
    }

    protected function createMigration($guard, $table)
    {
        $stub = File::get(__DIR__ . '/stubs/migration.stub');
        $content = str_replace(
            ['{{table}}', '{{guard}}'],
            [$table, $guard],
            $stub
        );

        // Generate timestamp for migration filename
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_create_{$table}_table.php";

        $path = module_path('Acl') . "/src/Database/Migrations/{$filename}";
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        $this->info("✅ Created migration for table '{$table}'");
    }

    protected function createController($guard, $model)
    {
        $guardStudly = ucfirst($guard);
        $viewPrefix = $guard;

        $stub = File::get(__DIR__ . '/stubs/controller.stub');
        $content = str_replace(
            ['{{guard}}', '{{guardStudly}}', '{{viewPrefix}}'],
            [$guard, $guardStudly, $viewPrefix],
            $stub
        );

        $controller = $guardStudly . 'AuthController';

        // Create controller in subfolder except for admin
        if ($guard === 'admin') {
            $path = module_path('Acl') . "/src/Http/Controllers/{$controller}.php";
        } else {
            $path = module_path('Acl') . "/src/Http/Controllers/{$guardStudly}/{$controller}.php";
        }

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        $this->info("✅ Created controller: {$controller}");
    }

    protected function createService($guard, $model)
    {
        $modelName = class_basename($model);
        $stub = File::get(__DIR__ . '/stubs/service.stub');

        $content = str_replace(
            [
                '{{namespace}}',
                '{{modelName}}',
                '{{guard}}'
            ],
            [
                'Modules\\Acl\\Services\\' . $guard,
                $modelName,
                $guard
            ],
            $stub
        );

        $servicePath = module_path('Acl') . "/src/Services/" . ucfirst($guard);
        File::ensureDirectoryExists($servicePath, 0755, true);

        $serviceFile = "{$servicePath}/{$modelName}AuthService.php";

        // Only create the service if it doesn't exist
        if (!File::exists($serviceFile)) {
            File::put($serviceFile, $content);
            $this->info("✅ Created service: {$guard}/{$modelName}AuthService");
        } else {
            $this->info("ℹ️  Service already exists: {$guard}/{$modelName}AuthService");
        }
    }

    protected function createRequests($guard)
    {
        $requests = ['StoreRequest', 'UpdateRequest'];
        $stubDir = __DIR__ . '/stubs/requests';
        $guardStudly = ucfirst($guard);

        foreach ($requests as $request) {
            $stubFile = "{$stubDir}/{$request}.stub";

            // Check if stub file exists
            if (!File::exists($stubFile)) {
                $this->warn("⚠️  Stub file not found: {$stubFile}");
                continue;
            }

            $stub = File::get($stubFile);
            $content = str_replace(
                ['{{guard}}', '{{Guard}}'],
                [strtolower($guard), $guardStudly],
                $stub
            );

            $path = module_path('Acl') . "/src/Http/Requests/{$guard}/{$request}.php";
            File::ensureDirectoryExists(dirname($path));
            File::put($path, $content);
            $this->info("✅ Created request: {$guard}/{$request}");
        }
    }

    protected function updateAuthConfig($guard, $table)
    {
        $authPath = config_path('auth.php');
        $authConfig = require $authPath;

        // Add guard if not exists
        if (!isset($authConfig['guards'][$guard])) {
            $authConfig['guards'][$guard] = [
                'driver' => 'session',
                'provider' => $guard,
            ];
        }

        // Add provider if not exists
        if (!isset($authConfig['providers'][$guard])) {
            $authConfig['providers'][$guard] = [
                'driver' => 'eloquent',
                'model' => "Modules\\Acl\\Models\\" . ucfirst($guard),
            ];
        }

        File::put($authPath, "<?php\n\nreturn " . var_export($authConfig, true) . ";\n");
        $this->info("✅ Updated auth.php configuration");
    }

    protected function createRoutes($guard)
    {
        $guardStudly = ucfirst($guard);
        $stub = File::get(__DIR__ . '/stubs/routes.stub');

        $content = str_replace(
            ['{{guard}}', '{{guardStudly}}'],
            [$guard, $guardStudly],
            $stub
        );

        $path = module_path('Acl') . "/src/Routes/{$guard}.php";
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        $this->info("✅ Created routes: {$guard}.php");
    }

    protected function createNotifications($guard)
    {
        $stubDir = __DIR__ . '/stubs/notifications';
        $guardStudly = ucfirst($guard);

        $notificationFiles = [
            'UserAccountCreationNotification.stub' => 'AccountCreationNotification.php',
            'PasswordChangedNotification.stub' => 'PasswordChangedNotification.php',
            'UserPasswordChangedNotification.stub' => 'PasswordChangedNotification.php',
            'UserPasswordResetNotification.stub' => 'PasswordResetNotification.php',
            'UserUpdateNotification.stub' => 'UpdateNotification.php',
        ];

        
        // Determine notification path and namespace based on guard
        if ($guard !== 'admin') {
            $notifyPath = module_path('Acl') . "/src/Notifications/{$guardStudly}";
            $namespace = "\\{$guardStudly}";
        }

        foreach ($notificationFiles as $stubFile => $outputFile) {
            $stubFilePath = "{$stubDir}/{$stubFile}";
            
            if (!File::exists($stubFilePath)) {
                $this->warn("⚠️  Stub file not found: {$stubFilePath}");
                continue;
            }

            $stub = File::get($stubFilePath);

            $content = str_replace(
                ['{{guard}}', '{{guardStudly}}', '{{namespace}}'],
                [$guard, $guardStudly, $namespace],
                $stub
            );

            // Generate filename with guard prefix
            $notificationFile = $guardStudly . $outputFile;

            $filePath = "{$notifyPath}/{$notificationFile}";
            File::ensureDirectoryExists($notifyPath);
            File::put($filePath, $content);
            $this->info("✅ Created notification: " . ($guard !== 'admin' ? "{$guardStudly}/" : "") . $notificationFile);
        }
    }

    protected function createMiddleware($guard)
    {
        $guardStudly = ucfirst($guard);
        $middlewareStubs = [
            'Authenticate' => 'middleware/Authenticate.stub',
            'RedirectIfAuthenticated' => 'middleware/RedirectIfAuthenticated.stub',
            'CheckPermission' => 'middleware/CheckPermission.stub',
        ];

        foreach ($middlewareStubs as $middlewareName => $stubPath) {
            $stubFile = __DIR__ . '/stubs/' . $stubPath;

            if (!File::exists($stubFile)) {
                $this->warn("⚠️  Stub file not found: {$stubFile}");
                continue;
            }

            $stub = File::get($stubFile);
            $content = str_replace(
                ['{{guard}}', '{{guardStudly}}', '{{Guard}}'],
                [$guard, $guardStudly, $guardStudly],
                $stub
            );

            $className = $guardStudly . $middlewareName;
            $path = module_path('Acl') . "/src/Http/Middleware/{$className}.php";
            File::ensureDirectoryExists(dirname($path));
            File::put($path, $content);
            $this->info("✅ Created middleware: {$className}");
        }

        // Register middleware in MiddlewareServiceProvider
        $this->registerMiddlewareInProvider($guard, $guardStudly);
    }

    protected function registerMiddlewareInProvider($guard, $guardStudly)
    {
        $providerPath = module_path('Acl') . '/src/Providers/MiddlewareServiceProvider.php';

        if (!File::exists($providerPath)) {
            $this->warn("⚠️  MiddlewareServiceProvider not found");
            return;
        }

        $content = File::get($providerPath);

        // Check if middleware already registered
        if (str_contains($content, "auth.{$guard}")) {
            $this->info("ℹ️  Middleware already registered for {$guard}");
            return;
        }

        // Add use statements
        $authenticateUse = "use Modules\\Acl\\Http\\Middleware\\{$guardStudly}Authenticate;";
        $redirectUse = "use Modules\\Acl\\Http\\Middleware\\{$guardStudly}RedirectIfAuthenticated;";
        $permissionUse = "use Modules\\Acl\\Http\\Middleware\\{$guardStudly}CheckPermission;";

        if (!str_contains($content, $authenticateUse)) {
            $content = str_replace(
                "use Modules\\Acl\\Http\\Middleware\\CheckAdminPermission;",
                "use Modules\\Acl\\Http\\Middleware\\CheckAdminPermission;\n{$authenticateUse}\n{$redirectUse}\n{$permissionUse}",
                $content
            );
        }

        // Add middleware aliases
        $middlewareRegistration = "        \$router->aliasMiddleware('auth.{$guard}', {$guardStudly}Authenticate::class);\n";
        $middlewareRegistration .= "        \$router->aliasMiddleware('guest.{$guard}', {$guardStudly}RedirectIfAuthenticated::class);\n";
        $middlewareRegistration .= "        \$router->aliasMiddleware('{$guard}.permission', {$guardStudly}CheckPermission::class);\n";

        $content = str_replace(
            "        \$router->aliasMiddleware('admin.permission', CheckAdminPermission::class);",
            "        \$router->aliasMiddleware('admin.permission', CheckAdminPermission::class);\n{$middlewareRegistration}",
            $content
        );

        File::put($providerPath, $content);
        $this->info("✅ Registered middleware in MiddlewareServiceProvider");
    }

    protected function guardExists($guard)
    {
        $configPath = module_path('Acl') . '/src/Config/guards.php';

        if (!File::exists($configPath)) {
            return false;
        }

        $config = require $configPath;

        return isset($config['guards'][$guard]);
    }
}
