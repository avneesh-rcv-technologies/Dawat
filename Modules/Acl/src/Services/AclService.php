<?php

namespace Modules\Acl\Services;

use App\Rules\StrongPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AclService
{
    protected string $guard;
    protected string $broker;
    protected string $model;
    protected string $passwordResetTable;
    protected array $throttleConfig;
    protected array $passwordConfig;
    protected string $loginRoute;
    protected string $dashboardRoute;

    public function __construct(string $guard = 'web')
    {
        $this->guard = $guard;
        $this->setGuardConfig($guard);
    }

    protected function setGuardConfig(string $guard): void
    {
        // Get the guard configuration from our ACL config
        $aclConfig = config('acl.guards.' . $guard);
        
        if (empty($aclConfig)) {
            throw new \InvalidArgumentException("Guard [{$guard}] is not defined in acl.php configuration.");
        }

        // Get the provider from Laravel's auth config
        $authConfig = config("auth.guards.{$guard}");
        
        if (empty($authConfig) || !isset($authConfig['provider'])) {
            throw new \InvalidArgumentException("Provider for guard [{$guard}] is not properly configured in auth.php");
        }

        $this->model = config("auth.providers.{$authConfig['provider']}.model");
        $this->broker = $aclConfig['broker'];
        $this->passwordResetTable = $aclConfig['password_reset_table'];
        $this->throttleConfig = $aclConfig['throttle'];
        $this->passwordConfig = $aclConfig['password'];
        $this->loginRoute = $aclConfig['login_route'];
        $this->dashboardRoute = $aclConfig['dashboard_route'];
    }

    public function login(array $credentials, bool $remember = false): array
    {
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'min:' . ($this->passwordConfig['min_length'] ?? 8),
            ],
        ]);
        
        // Add password complexity rules if needed
        if ($this->passwordConfig['require_special_chars'] ?? false) {
            $validator->sometimes('password', 'regex:/[^a-zA-Z0-9]/', function() {
                return true;
            });
        }
        
        if ($this->passwordConfig['require_numbers'] ?? false) {
            $validator->sometimes('password', 'regex:/[0-9]/', function() {
                return true;
            });
        }
        
        if ($this->passwordConfig['require_mixed_case'] ?? false) {
            $validator->sometimes('password', 'regex:/[a-z]/', function() {
                return true;
            });
            $validator->sometimes('password', 'regex:/[A-Z]/', function() {
                return true;
            });
        }

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $ip = request()->ip();
        $throttleKey = $this->getThrottleKey($ip);

        if ($this->hasTooManyLoginAttempts($throttleKey)) {
            $this->incrementLoginAttempts($throttleKey);
            throw ValidationException::withMessages([
                'email' => [__('auth.throttle', ['seconds' => $this->availableIn($throttleKey)])],
            ]);
        }

        if (Auth::guard($this->guard)->attempt($credentials, $remember)) {
            $this->clearLoginAttempts($throttleKey);
            
            $user = Auth::guard($this->guard)->user();
            
            return [
                'success' => true,
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
            ];
        }

        $this->incrementLoginAttempts($throttleKey);
        
        throw ValidationException::withMessages([
            'email' => [__('auth.failed')],
        ]);
    }

    public function logout(): void
    {
        $user = Auth::guard($this->guard)->user();
        
        // Revoke all tokens
        $user->tokens()->delete();
        
        // Logout from the current guard
        Auth::guard($this->guard)->logout();
    }

    public function forgotPassword(array $credentials): array
    {
        $validator = Validator::make($credentials, [
            'email' => 'required|email|exists:' . $this->getTableFromModel() . ',email',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $status = Password::broker($this->broker)->sendResetLink(
            $credentials,
            function ($user, $token) {
                // Customize the notification if needed
                $user->sendPasswordResetNotification($token);
            }
        );

        return [
            'success' => $status === Password::RESET_LINK_SENT,
            'message' => __($status),
        ];
    }

    public function resetPassword(array $credentials): array
    {
        $validator = Validator::make($credentials, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'string', 'confirmed', new StrongPassword()],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $status = Password::broker($this->broker)->reset(
            $credentials,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordResetEvent($user));
            }
        );

        return [
            'success' => $status === Password::PASSWORD_RESET,
            'message' => __($status),
        ];
    }

    protected function getThrottleKey(string $ip): string
    {
        return Str::transliterate(Str::lower($this->guard . '|' . $ip));
    }

    protected function hasTooManyLoginAttempts(string $throttleKey): bool
    {
        return RateLimiter::tooManyAttempts(
            $throttleKey, 
            $this->throttleConfig['max_attempts'] ?? 5
        );
    }

    protected function incrementLoginAttempts(string $throttleKey): void
    {
        $decayMinutes = $this->throttleConfig['decay_minutes'] ?? 1;
        RateLimiter::hit($throttleKey, $decayMinutes * 60);
    }

    protected function clearLoginAttempts(string $throttleKey): void
    {
        RateLimiter::clear($throttleKey);
    }

    protected function availableIn(string $throttleKey): int
    {
        return RateLimiter::availableIn($throttleKey);
    }

    protected function getTableFromModel(): string
    {
        return (new $this->model)->getTable();
    }
}
