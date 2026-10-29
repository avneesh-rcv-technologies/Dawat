<?php

namespace Modules\Core\Services;

use Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
class AuthService 
{
    protected string $guard;
    protected string $rateLimitKey;

    public function __construct(string $guard = 'admin')
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

        if ($this->guard === 'agency') {
            // Store data here 
        }


        throw new \Exception('Registration not allowed for this guard');
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