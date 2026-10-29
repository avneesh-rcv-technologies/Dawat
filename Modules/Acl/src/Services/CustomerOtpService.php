<?php

namespace Modules\Acl\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Acl\Models\Customer;
use Modules\Acl\Models\CustomerOtp;
use Modules\Acl\Notifications\OtpVerificationNotification;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\EmailNotification\Channels\EmailChannel;
use Modules\Notifications\Services\NotificationService;
use Modules\Twilio\Channels\TwilioChannel;
use Modules\Twilio\Services\TwilioService;

class CustomerOtpService
{
    protected TwilioService $twilioService;
    protected NotificationService $notificationService;

    protected int $otpLength = 4;
    protected int $otpExpiryMinutes = 5;
    protected int $maxAttemptsPerOtp = 3;
    protected int $maxOtpRequestsPerHour = 5;

    public function __construct(TwilioService $twilioService, NotificationService $notificationService)
    {
        $this->twilioService = $twilioService;
        $this->notificationService = $notificationService;
    }

    public function sendOtp(string $identifier, ?string $type = null, ?string $channel = null, bool $isResend = false): array
    {
        return DB::transaction(function () use ($identifier, $type, $channel, $isResend) {
            $target = $this->resolveTarget($identifier, $type, $channel);
            $channel = $target['channel'];
            $value = $target['value'];
            $type = $target['type'];

            $this->checkRateLimit($channel, $value, $type);

            $customer = $this->findCustomerByIdentifier($channel, $value);
            $purpose = $this->resolvePurpose($type, $customer);

            $this->invalidatePreviousOtps($channel, $value, $type);

            $otp = $type === 'forget_password' ? (string) random_int(1000, 9999) : $this->generateOtp();

            CustomerOtp::create([
                'phone' => $channel === 'sms' ? $value : null,
                'email' => $channel === 'email' ? strtolower(trim($value)) : null,
                'otp' => $otp,
                'purpose' => $purpose,
                'type' => $type,
                'channel_type' => $channel,
                'expires_at' => Carbon::now()->addMinutes($this->otpExpiryMinutes),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'attempts' => 0,
                'is_verified' => false,
            ]);

            if ($channel === 'email') {
                $this->sendEmailOtp($value, $otp, $type);
            } else {
                $this->sendOtpSms($value, $otp, $purpose);
            }

            return [
                'success' => true,
                'message' => 'Verification code sent successfully',
                'channel' => $channel,
                'type' => $type,
                'expires_in' => $this->otpExpiryMinutes * 60,
                'identifier' => $value,
                'is_existing_user' => !is_null($customer),
            ];
        });
    }

    public function verifyOtp(string $identifier, string $otp, ?string $type = null, ?string $channel = null): array
    {
        return DB::transaction(function () use ($identifier, $otp, $type, $channel) {
            $target = $this->resolveTarget($identifier, $type, $channel);
            $channel = $target['channel'];
            $value = $target['value'];
            $type = $target['type'];

            $customerOtp = CustomerOtp::query()
                ->where(function ($query) use ($channel, $value) {
                    if ($channel === 'email') {
                        $query->where('email', strtolower(trim($value)));
                    } else {
                        $query->where('phone', $this->normalizePhone($value));
                    }
                })
                ->where('channel_type', $channel)
                ->where(function ($query) use ($type) {
                    if ($type) {
                        $query->where('type', $type);
                    } else {
                        $query->whereIn('type', ['login', 'register', 'verify_phone', 'login_email_verification', 'register_email_verification', 'email_verification']);
                    }
                })
                ->where('is_verified', false)
                ->orderByDesc('created_at')
                ->first();

            if (!$customerOtp) {
                throw new Exception('Invalid or expired verification code.', 422);
            }

            if ($customerOtp->expires_at && $customerOtp->expires_at->isPast()) {
                throw new Exception('Verification code has expired. Please request a new one.', 422);
            }

            if ($customerOtp->otp !== trim((string) $otp)) {
                $customerOtp->incrementAttempts();
                $remainingAttempts = $this->maxAttemptsPerOtp - $customerOtp->attempts;

                if ($remainingAttempts <= 0) {
                    throw new Exception('Maximum verification attempts exceeded. Please request a new code.', 422);
                }

                throw new Exception("Invalid verification code. {$remainingAttempts} attempts remaining.", 422);
            }

            $customerOtp->markAsVerified();

            if ($channel === 'email') {
                $customer = Customer::whereRaw('LOWER(email) = ?', [strtolower(trim($value))])->first();
                if ($customer) {
                    $customer->update([
                        'email_verified' => true,
                        'email_verified_at' => Carbon::now(),
                    ]);
                }

                return [
                    'success' => true,
                    'message' => 'Email verified successfully.',
                    'verified' => true,
                    'channel' => 'email',
                    'type' => $type,
                ];
            }

            $customer = Customer::where('phone', $this->normalizePhone($value))->first();
            if ($customer) {
                $customer->update([
                    'phone_verified' => true,
                    'phone_verified_at' => Carbon::now(),
                ]);
            }

            return [
                'success' => true,
                'message' => 'Phone verified successfully.',
                'verified' => true,
                'channel' => 'phone',
                'type' => $type,
            ];
        });
    }

    public function resendOtp(string $identifier, ?string $type = null, ?string $channel = null): array
    {
        return $this->sendOtp($identifier, $type, $channel, true);
    }

    protected function resolveTarget(string $identifier, ?string $type = null, ?string $channel = null): array
    {
        $identifier = trim((string) $identifier);

        if ($identifier === '') {
            throw new Exception('Phone number or email is required.', 422);
        }

        $resolvedChannel = $this->normalizeChannel($channel, $identifier);
        $normalizedType = $this->resolveType($resolvedChannel, $type);

        if ($resolvedChannel === 'email') {
            $identifier = strtolower(trim($identifier));
            if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Please provide a valid email address.', 422);
            }
        } else {
            $identifier = $this->normalizePhone($identifier);
        }

        return [
            'channel' => $resolvedChannel,
            'value' => $identifier,
            'type' => $normalizedType,
        ];
    }

    protected function resolveType(string $channel, ?string $type = null): string
    {
        $normalized = strtolower(trim((string) ($type ?? '')));

        if ($normalized === 'forget_password') {
            if ($channel !== 'email') {
                throw new Exception('Password reset requires an email address.', 422);
            }

            return $normalized;
        }

        if ($normalized === 'login_email_verification' || $normalized === 'register_email_verification' || $normalized === 'email_verification') {
            if ($channel === 'email') {
                return $normalized === 'login_email_verification' || $normalized === 'email_verification' ? 'login_email_verification' : 'register_email_verification';
            }

            return 'verify_phone';
        }

        if ($normalized === 'login' || $normalized === 'register' || $normalized === 'verify_phone') {
            return $normalized;
        }

        if ($channel === 'email') {
            return 'login_email_verification';
        }

        return 'verify_phone';
    }

    protected function normalizeChannel(?string $channel, string $identifier): string
    {
        $normalized = strtolower(trim((string) ($channel ?? '')));

        if (in_array($normalized, ['sms', 'phone'], true)) {
            return 'sms';
        }

        if ($normalized === 'email') {
            return 'email';
        }

        return str_contains($identifier, '@') ? 'email' : 'sms';
    }

    protected function resolvePurpose(string $type, ?Customer $customer): string
    {
        if (str_contains($type, 'email_verification')) {
            return $customer ? 'login' : 'register';
        }

        if ($type === 'register') {
            return 'register';
        }

        return 'login';
    }

    protected function findCustomerByIdentifier(string $channel, string $value): ?Customer
    {
        if ($channel === 'email') {
            return Customer::query()->whereRaw('LOWER(email) = ?', [strtolower(trim($value))])->first();
        }

        return Customer::query()->where('phone', $this->normalizePhone($value))->first();
    }

    protected function generateOtp(): string
    {
        return '1234';
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 10) {
            $phone = '1' . $phone;
        }

        return '+' . ltrim($phone, '+');
    }

    protected function sendOtpSms(string $phone, string $otp, string $purpose): void
    {
        try {
            $notification = new OtpVerificationNotification([
                'phone' => $phone,
                'otp' => $otp,
                'purpose' => $purpose,
                'expiryMinutes' => $this->otpExpiryMinutes,
            ]);
            $channel = app(TwilioChannel::class, ['config' => []]);
            $channel->send([$phone], $notification);
        } catch (Exception $e) {
            ApiExceptionHandler::handle($e);
        }
    }

    protected function sendEmailOtp(string $email, string $otp, string $type): void
    {
        try {
            $notification = new OtpVerificationNotification([
                'email' => $email,
                'otp' => $otp,
                'purpose' => $type,
                'expiryMinutes' => $this->otpExpiryMinutes,
            ]);

            $channel = app(EmailChannel::class, ['config' => []]);
            $channel->send([$email], $notification);
        } catch (Exception $e) {
            ApiExceptionHandler::handle($e);
        }
    }

    protected function maskPhone(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 4) {
            return $phone;
        }

        return substr($phone, 0, -4) . str_repeat('*', 4);
    }

    protected function checkRateLimit(string $channel, string $value, string $type): void
    {
        $query = CustomerOtp::query()->where('type', $type)->where('channel_type', $channel);

        if ($channel === 'email') {
            $query->where('email', strtolower(trim($value)));
        } else {
            $query->where('phone', $this->normalizePhone($value));
        }

        $count = $query->where('created_at', '>', Carbon::now()->subHour())->count();

        if ($count >= $this->maxOtpRequestsPerHour) {
            throw new Exception('Too many verification requests. Please try again later.', 429);
        }
    }

    protected function invalidatePreviousOtps(string $channel, string $value, string $type): void
    {
        $query = CustomerOtp::query()->where('type', $type)->where('channel_type', $channel);

        if ($channel === 'email') {
            $query->where('email', strtolower(trim($value)));
        } else {
            $query->where('phone', $this->normalizePhone($value));
        }

        $query->where('is_verified', false)->update(['is_verified' => true]);
    }
}
