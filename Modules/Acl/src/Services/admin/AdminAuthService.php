<?php

namespace Modules\Acl\Services\admin;

use Modules\Acl\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthService
{
    public function register(array $data): Admin
    {
        $data['password'] = Hash::make($data['password']);
        return Admin::create($data);
    }

    public function login(array $credentials, bool $remember = false): bool
    {
        return Auth::guard('admin')->attempt($credentials, $remember);
    }

    public function logout(): void
    {
        Auth::guard('admin')->logout();
    }

    public function user(): ?Admin
    {
        return Auth::guard('admin')->user();
    }

    public function sendPasswordResetLink(string $email): string
    {
        $user = Admin::where('email', $email)->first();
        if (!$user) {
            return 'We can\'t find a user with that email address.';
        }

        // Generate and save reset token
        $token = app('auth.password.broker')->createToken($user);
        return $token;
    }

    public function resetPassword(array $credentials): string
    {
        $response = app('auth.password.broker')->reset(
            $credentials,
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        return $response === 'passwords.reset' ? 'success' : $response;
    }
}
