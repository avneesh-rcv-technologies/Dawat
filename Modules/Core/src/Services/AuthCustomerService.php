<?php

namespace Modules\Core\Services;

use Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Modules\Customers\Models\User;
class AuthCustomerService
{
    protected string $guard;
    protected string $rateLimitKey;

    public function __construct(string $guard = 'customer')
    {
        $this->guard = $guard;
    }

    public function login(array $credentials): bool
    {
        return Auth::guard($this->guard)->attempt($credentials);
    }

    public function logout(): void
    {
        Auth::guard($this->guard)->logout();
    }

    public function register(array $data): mixed
    {
        $hashedPassword = Hash::make($data['password']);

        $user = User::create(array_merge($data, ['password' => $hashedPassword, 'user_group_id' =>fn_get_setting('general.user_group')]));
        return Auth::guard($this->guard)->attempt($data);

    }

    protected function getRateLimitKey(string $ip): string
    {
        return Str::lower("login|{$this->guard}|{$ip}");
    }

    public function hasTooManyLoginAttempts(string $ip, int $maxAttempts = 5): bool
    {
        $key = $this->getRateLimitKey($ip);
        return RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    public function incrementLoginAttempts(string $ip, int $decaySeconds = 60): void
    {
        $key = $this->getRateLimitKey($ip);
        RateLimiter::hit($key, $decaySeconds);
    }

    public function clearLoginAttempts(string $ip): void
    {
        $key = $this->getRateLimitKey($ip);
        RateLimiter::clear($key);
    }
}