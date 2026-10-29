<?php

namespace Modules\Acl\Services;

use App\Rules\StrongPassword;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Customer;
use Modules\Acl\Models\CustomerOtp;
use Modules\Notifications\Services\NotificationService;

class CustomerAuthService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Authenticate customer and generate token
     *
     * @param array $credentials
     * @return array
     * @throws Exception
     */
    public function login(array $credentials): array
    {
        return DB::transaction(function () use ($credentials) {
            $customer = Customer::whereRaw('LOWER(email) = ?', [strtolower(trim($credentials['email']))])->first();

            if (!$customer || !Hash::check($credentials['password'], $customer->password)) {
                throw new Exception('Invalid credentials', 401);
            }

            if (isset($customer->status) && $customer->status !== true) {
                throw new Exception('Account is not active', 401);
            }

            if (empty($customer->email_verified_at) && !empty($customer->email)) {
                app(CustomerOtpService::class)->sendOtp($customer->email, 'login_email_verification', 'email');

                return [
                    'verification_required' => true,
                    'message' => 'Email verification is required before login. A verification code has been sent to your email.',
                    'email' => $customer->email,
                    'otp_sent' => true,
                    'type' => 'login_email_verification',
                ];
            }

            $token = $customer->createToken('customer-token')->plainTextToken;

            return [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone ?? null,
                    'created_at' => $customer->created_at,
                    'model' => 'customer'
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ];
        });
    }

    /**
     * Register new customer
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            if (Customer::whereRaw('LOWER(email) = ?', [strtolower(trim($data['email']))])->exists()) {
                throw new Exception('Email already exists', 422);
            }

            $customer = Customer::create([
                'name' => $data['name'],
                'email' => strtolower(trim($data['email'])),
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'status' => 1,
                'email_verified' => false,
            ]);

            $this->notificationService->trigger(
                'Acl',
                'CustomerCreated',
                $customer->email,
                $customer
            );

            $this->notificationService->trigger(
                'Acl',
                'CustomerRegistered',
                $customer->email,
                $customer
            );

            app(CustomerOtpService::class)->sendOtp($customer->email, 'register_email_verification', 'email');

            return [
                'verification_required' => true,
                'message' => 'Registration successful. Please verify your email to continue.',
                'email' => $customer->email,
                'otp_sent' => true,
                'type' => 'register_email_verification',
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone ?? null,
                    'created_at' => $customer->created_at,
                    'model' => 'customer'
                ],
            ];
        });
    }

    /**
     * Update customer profile
     *
     * @param Customer $customer
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function updateProfile(Customer $customer, array $data): array
    {
        return DB::transaction(function () use ($customer, $data) {
            // Check if email is being changed and already exists
            if (isset($data['email']) && $data['email'] !== $customer->email) {
                if (Customer::where('email', $data['email'])->where('id', '!=', $customer->id)->exists()) {
                    throw new Exception('Email already exists', 422);
                }
            }

            // Update customer
            $customer->update([
                'name' => $data['name'] ?? $customer->name,
                'email' => $data['email'] ?? $customer->email,
                'phone' => $data['phone'] ?? $customer->phone,
            ]);

            // Trigger customer profile updated notification
            $this->notificationService->trigger(
                'Acl',
                'CustomerProfileUpdated',
                $customer->email,
                $customer
            );

            return [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone ?? null,
                    'updated_at' => $customer->updated_at,
                    'model' => 'customer'
                ]
            ];
        });
    }

    /**
     * Change customer password
     *
     * @param Customer $customer
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function changePassword(Customer $customer, array $data): array
    {
        return DB::transaction(function () use ($customer, $data) {
            // Verify current password
            if (!Hash::check($data['current_password'], $customer->password)) {
                throw new Exception('Current password is incorrect', 422);
            }

            // Update password
            $customer->update([
                'password' => Hash::make($data['new_password'])
            ]);

            return [
                'message' => 'Password changed successfully'
            ];
        });
    }

    /**
     * Logout customer (revoke token)
     *
     * @param Customer $customer
     * @return array
     */
    public function logout(Customer $customer): array
    {
        // Revoke current token
        if ($customer->currentAccessToken()) {
            $customer->currentAccessToken()->delete();
        }

        return [
            'message' => 'Logged out successfully'
        ];
    }

    public function resetPasswordByEmail(string $email, string $newPassword, string $otp): array
    {
        $email = strtolower(trim($email));

        $customer = Customer::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'email' => ['Customer account not found for this email.'],
            ]);
        }

        Validator::make(['password' => $newPassword], [
            'password' => ['required', 'string', new StrongPassword()],
        ])->validate();

        $otpError = DB::transaction(function () use ($email, $newPassword, $otp, $customer) {
            $customerOtp = CustomerOtp::query()
                ->where('email', $email)
                ->where('type', 'forget_password')
                ->where('channel_type', 'email')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (!$customerOtp) {
                return 'No password reset code found. Please request a new code.';
            }

            if ($customerOtp->is_verified) {
                return 'Password reset code has already been used or invalidated. Please request a new code.';
            }

            if (!$customerOtp->expires_at || $customerOtp->expires_at->isPast()) {
                return 'Password reset code has expired. Please request a new code.';
            }

            if ($customerOtp->attempts >= 3) {
                return 'Maximum verification attempts exceeded. Please request a new code.';
            }

            if (!hash_equals((string) $customerOtp->otp, trim($otp))) {
                $customerOtp->incrementAttempts();
                $remainingAttempts = 3 - $customerOtp->attempts;

                return $remainingAttempts > 0
                    ? "Invalid password reset code. {$remainingAttempts} attempts remaining."
                    : 'Maximum verification attempts exceeded. Please request a new code.';
            }

            $customer->password = Hash::make($newPassword);
            $customer->save();
            $customerOtp->markAsVerified();

            return null;
        });

        // Raise validation errors after committing so failed attempts remain recorded.
        if ($otpError !== null) {
            throw ValidationException::withMessages(['otp' => [$otpError]]);
        }

        $this->notificationService->trigger(
            'Acl',
            'CustomerPasswordReset',
            $customer->email,
            $customer
        );

        return [
            'success' => true,
            'message' => 'Password updated successfully.',
        ];
    }
}
