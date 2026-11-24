<?php

namespace Modules\Acl\Console\Commands;

use App\Rules\StrongPassword;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Acl\Models\Role;

class CreateUserCommand extends Command
{
    protected $signature = 'user:create 
                            {--guard= : The guard to create user for (admin or customer)} 
                            {--name= : User\'s full name} 
                            {--email= : User\'s email} 
                            {--password= : User\'s password (8-16 chars, uppercase, lowercase, number and symbol)}
                            {--role= : Role name to assign; defaults to the selected guard} 
                            {--verified : Mark email as verified} 
                            {--force : Skip confirmation}';

    protected $description = 'Create a new user for the specified guard with optional role assignment';

    public function handle()
    {
        try {
            // Get or ask for guard
            $guard = $this->option('guard') ?: $this->choice(
                'Select guard',
                array_keys($this->guardSettings()['guards']),
                0
            );

            // Validate guard
            $guardConfig = $this->getGuardConfig($guard);
            if (!$guardConfig) {
                return 1;
            }

            // Get model and table
            $model = $guardConfig['model'];
            $table = $guardConfig['table'];

            // Get or ask for user details
            $name = $this->option('name') ?: $this->ask('Enter user name');
            $email = $this->option('email') ?: $this->ask('Enter user email');
            $password = $this->option('password') ?: $this->secret('Enter user password (8-16 characters, uppercase, lowercase, number and symbol)');

            $roleName = $this->option('role') ?: Str::title($guard);

            // Validate inputs
            $validator = Validator::make(
                [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'role_name' => $roleName,
                ],
                [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:' . $table . ',email',
                    'password' => ['required', 'string', new StrongPassword()],
                    'role_name' => 'required|string|max:255',
                ]
            );

            if ($validator->fails()) {
                $this->error('Validation failed: ' . implode(' ', $validator->errors()->all()));
                return 1;
            }

            // Confirm before creating
            if (!$this->option('force') && !$this->confirm("Create {$guard} user '{$name}' with email '{$email}' and role '{$roleName}'?")) {
                $this->info('User creation cancelled.');
                return 0;
            }

            [$user, $role] = DB::transaction(function () use ($model, $guard, $roleName, $name, $email, $password) {
                $isAdminRole = $guard === 'admin' && strcasecmp($roleName, 'Admin') === 0;
                $role = Role::firstOrCreate(['name' => $roleName, 'guard' => $guard], [
                    'description' => $roleName.' role',
                    'permission_type' => $isAdminRole ? 'all' : 'custom',
                    'permissions' => $isAdminRole ? null : ['dashboard.index', 'profile.edit', 'profile.update'],
                ]);
                $user = $model::create([
                    'name' => $name, 'email' => $email, 'password' => Hash::make($password),
                    'role_id' => $role->id,
                    'email_verified_at' => $this->option('verified') ? now() : null,
                    'status' => true,
                ]);

                return [$user, $role];
            });

            $this->info("✅ {$guard} user created successfully!");
            $this->line("ID: {$user->id}");
            $this->line("Name: {$user->name}");
            $this->line("Email: {$user->email}");
            $this->line("Role: {$role->name}");
            $this->line("Status: " . ($user->status ?? true ? 'Active' : 'Inactive'));

            return 0;

        } catch (Exception $e) {
            $this->error("Error creating user: " . $e->getMessage());
            if ($this->getOutput()->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            return 1;
        }
    }

    /**
     * Get guard configuration
     */
    protected function getGuardConfig(string $guard): ?array
    {
        $settings = $this->guardSettings();
        $config = $settings['guards'][$guard] ?? null;
        if (!$config) {
            $this->error("Guard '{$guard}' is not configured.");
            $this->line('Available guards: '.implode(', ', array_keys($settings['guards'])));
            return null;
        }

        $provider = $settings['providers'][$config['provider'] ?? ''] ?? null;
        $model = $provider['model'] ?? null;
        if (!$model || !class_exists($model)) {
            $this->error("No valid model is configured for guard '{$guard}'.");
            return null;
        }

        return array_merge($config, $provider, ['table' => (new $model())->getTable()]);
    }
    protected function guardSettings(): array
    {
        return array_replace_recursive(
            require __DIR__.'/../../Config/guards.php',
            config('acl::guards', [])
        );
    }
}
