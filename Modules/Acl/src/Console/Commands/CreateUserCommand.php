<?php

namespace Modules\Acl\Console\Commands;

use App\Rules\StrongPassword;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Acl\Models\Role;

class CreateUserCommand extends Command
{
    protected $signature = 'user:create 
                            {--guard= : The guard to create user for (e.g., admin, user, api)} 
                            {--name= : User\'s full name} 
                            {--email= : User\'s email} 
                            {--password= : User\'s password (8-16 chars, uppercase, lowercase, number and symbol)}
                            {--role= : Assign role to the user} 
                            {--verified : Mark email as verified} 
                            {--force : Skip confirmation}';

    protected $description = 'Create a new user for the specified guard with optional role assignment';

    public function handle()
    {
        try {
            // Get or ask for guard
            $guard = $this->option('guard') ?: $this->choice(
                'Select guard',
                array_keys(config('acl::guards.guards', [])),
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

            // Get or create role
            $defaultRole = $this->option('role') ?: $guard;
            $role = Role::firstOrCreate(
                ['name' => $guard, 'guard' => $guard],
                [
                    'name' => ('admin' === Str::title($guard)) ? 'Admin' : Str::title($guard),
                    'description' => ucfirst($guard) . ' role',
                    'guard' => $guard,
                    'permission_type' => $guard === 'admin' ? 'all' : 'custom',
                    'permissions' => $guard === 'admin' ? null : ["dashboard.index", "profile.edit", "profile.update"]
                ]
            );

            // Validate inputs
            $validator = Validator::make(
                [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'role_id' => $role->id,
                ],
                [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:' . $table . ',email',
                    'password' => ['required', 'string', new StrongPassword()],
                    'role_id' => 'required|exists:roles,id',
                ]
            );

            if ($validator->fails()) {
                $this->error('Validation failed: ' . implode(' ', $validator->errors()->all()));
                return 1;
            }

            // Confirm before creating
            if (!$this->option('force') && !$this->confirm("Create {$guard} user '{$name}' with email '{$email}' and role '{$role->name}'?")) {
                $this->info('User creation cancelled.');
                return 0;
            }

            // Create user
            $userData = [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role_id' => $role->id,
                'email_verified_at' => $this->option('verified') ? now() : null,
                'status' => true,
            ];

            $user = $model::create($userData);

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
        $config = config("acl::guards.guards.{$guard}");

        if (!$config) {
            $this->error("Guard '{$guard}' is not configured in acl.php");
            $this->line("Available guards: " . implode(', ', array_keys(config('acl::guards.guards', []))));
            return null;
        }

        $config = array_merge($config, config("acl::guards.providers.{$config['provider']}"));
        $config = array_merge($config, [
            'table' => (new $config['model'])->getTable(),
        ]);

        if (!class_exists($config['model'])) {
            $this->error("Model class '{$config['model']}' not found for guard '{$guard}'");
            return null;
        }

        return $config;
    }
}