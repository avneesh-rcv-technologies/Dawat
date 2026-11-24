<?php

namespace Modules\Restaurant\Support;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class OnboardingRateLimits
{
    public static function register(): void
    {
        // Separate registration, email checks, OTP sends and OTP verification.
        // Numeric throttles otherwise share a single guest IP counter.
        foreach ([
            'restaurant-onboarding-email-check' => [20, 60],
            'restaurant-onboarding-register' => [3, 15],
            'restaurant-auth-otp-send' => [5, 30],
            'restaurant-auth-otp-verify' => [5, 30],
            'restaurant-partner-login' => [5, 30],
            'restaurant-partner-forgot-password' => [1, 5],
            'restaurant-partner-reset-password' => [10, 30],
        ] as $name => [$emailLimit, $ipLimit]) {
            RateLimiter::for($name, static function (Request $request) use ($emailLimit, $ipLimit) {
                $email = $request->input('email');
                $email = is_string($email) ? strtolower(trim($email)) : '';
                $key = hash('sha256', $request->ip().'|'.$email);

                return [
                    Limit::perMinute($emailLimit)->by('email:'.$key),
                    Limit::perMinute($ipLimit)->by('ip:'.$request->ip()),
                ];
            });
        }

        RateLimiter::for('restaurant-onboarding-write', static function (Request $request) {
            $name = $request->route()?->getName() ?? '';
            $step = str_contains($name, 'banking') ? 'banking' : (str_contains($name, 'tax-information') ? 'tax' : 'kyc');
            $key = ($request->user()?->getAuthIdentifier() ?? $request->ip()).':'.$step;
            // Allow multi-document uploads and corrections without hitting the limit after three requests.
            return Limit::perMinute(20)->by($key)->response(static function (Request $request, array $headers) {
                $seconds = (int) ($headers['Retry-After'] ?? 60);
                return response()->json([
                    'success' => false,
                    'message' => "You have submitted this step several times. Please wait {$seconds} seconds before trying again.",
                    'retry_after' => $seconds,
                ], 429, $headers);
            });
        });
    }
}
