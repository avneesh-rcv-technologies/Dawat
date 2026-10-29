<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Notifications\Services\NotificationService;
use Modules\Restaurant\Models\RestaurantEmailOtp;

class RestaurantOnboardingOtpService
{
    public const TYPE = 'onboarding_restaurant_email_verification';

    public function __construct(private NotificationService $notifications) {}

    public function send(string $email): array
    {
        $email = strtolower(trim($email));
        Validator::make(['email' => $email], [
            'email' => ['bail', 'required', 'string', 'max:255', 'email:rfc,dns', 'unique:admins,email'],
        ])->validate();

        return Cache::lock($this->lockKey($email), 15)->block(5, function () use ($email) {
            $record = $this->query($email)->first();
            if ($record?->last_sent_at && $record->last_sent_at->diffInSeconds(now()) < 60) {
                throw ValidationException::withMessages(['email' => ['Please wait 60 seconds before requesting another code.']]);
            }
            $withinWindow = $record?->last_sent_at && $record->last_sent_at->diffInHours(now()) < 24;
            if ($withinWindow && $record->resend_count >= 3) {
                throw ValidationException::withMessages(['email' => ['OTP request limit reached. Please try again later.']]);
            }

            $otp = (string) random_int(100000, 999999);
            RestaurantEmailOtp::updateOrCreate(['email' => $email, 'type' => self::TYPE], [
                'admin_id' => null,
                'otp' => $otp,
                'expires_at' => now()->addMinutes(2),
                'last_sent_at' => now(),
                'resend_count' => $withinWindow ? (int) $record->resend_count + 1 : 1,
                'attempts' => 0,
                'verified_at' => null,
            ]);

            try {
                $sent = $this->notifications->trigger('Restaurant', 'OnboardingRestaurantEmailVerification', $email, [
                    'email' => $email,
                    'otp' => $otp,
                    'restaurant_name' => 'Your Restaurant',
                    'expires_minutes' => 2,
                    'purpose' => self::TYPE,
                ]);
            } catch (\Throwable $e) {
                report($e);
                $sent = false;
            }

            if (!$sent) {
                throw ValidationException::withMessages(['email' => ['Unable to send the verification email. Please try again later.']]);
            }

            return ['success' => true, 'message' => 'Verification code sent to your email.', 'expires_in' => 120, 'resend_after' => 60];
        });
    }

    public function verify(string $email, string $otp): array
    {
        $email = strtolower(trim($email));

        return Cache::lock($this->lockKey($email), 15)->block(5, function () use ($email, $otp) {
            $record = $this->query($email)->first();
            if (!$record || $record->verified_at || !$record->expires_at || $record->expires_at->isPast()) {
                throw ValidationException::withMessages(['otp' => ['The code is expired or already used. Please request a new one.']]);
            }
            if ((int) $record->attempts >= 5) {
                throw ValidationException::withMessages(['otp' => ['Too many invalid attempts. Please request a new code.']]);
            }
            if (!hash_equals((string) $record->otp, trim($otp))) {
                $record->increment('attempts');
                throw ValidationException::withMessages(['otp' => ['Invalid verification code. Please try again.']]);
            }

            $record->markAsVerified();
            $token = Str::random(64);
            Cache::put($this->proofKey($token), $email, now()->addMinutes(30));

            return [
                'success' => true, 'verified' => true,
                'message' => 'Email verified successfully.',
                'verification_token' => $token,
            ];
        });
    }

    public function isVerified(string $email, string $token): bool
    {
        return strlen($token) === 64 && Cache::get($this->proofKey($token)) === strtolower(trim($email));
    }

    public function forgetVerification(string $token): void
    {
        Cache::forget($this->proofKey($token));
    }

    private function query(string $email)
    {
        return RestaurantEmailOtp::where('email', $email)->where('type', self::TYPE)->whereNull('admin_id')->latest('id');
    }

    private function lockKey(string $email): string
    {
        return 'restaurant-onboarding-otp:' . hash('sha256', $email);
    }

    private function proofKey(string $token): string
    {
        return 'restaurant-onboarding-email:' . hash('sha256', $token);
    }
}
