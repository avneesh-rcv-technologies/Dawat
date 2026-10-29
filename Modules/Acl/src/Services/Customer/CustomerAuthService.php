<?php

namespace Modules\Acl\Services\customer;

use Modules\Acl\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class CustomerAuthService
{
    /**
     * Register a new user.
     */
    public function register(array $data): Customer
    {
        $data['password'] = Hash::make($data['password']);
        return Customer::create($data);
    }

    /**
     * Attempt to log the user in.
     */
    public function login(array $credentials, bool $remember = false): bool
    {
        return Auth::guard('customer')->attempt(
            $credentials,
            $remember
        );
    }

    /**
     * Log the user out.
     */
    public function logout(): void
    {
        Auth::guard('customer')->logout();
    }

    /**
     * Get the authenticated user.
     */
    public function user(): ?Customer
    {
        return Auth::guard('customer')->user();
    }

    /**
     * Send a password reset link to the user.
     */
    public function sendPasswordResetLink(string $email): string
    {
        $status = Password::broker('customers')->sendResetLink(['email' => $email]);
        
        if ($status === Password::RESET_LINK_SENT) {
            return 'success';
        }
        
        return $status;
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(array $credentials): string
    {
        $status = Password::broker('customers')->reset(
            $credentials,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET ? 'success' : $status;
    }
}
