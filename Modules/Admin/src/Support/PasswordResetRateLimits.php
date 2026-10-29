<?php

namespace Modules\Admin\Support;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class PasswordResetRateLimits
{
    public static function register(): void
    {
        $response = static function (Request $request, array $headers) {
            $seconds = (int) ($headers['Retry-After'] ?? 60);
            return response()->json([
                'success' => false,
                'errors' => ['email' => ["Too many attempts. Please wait {$seconds} seconds before trying again."]],
                'retry_after' => $seconds,
            ], 429, $headers);
        };

        RateLimiter::for('admin-forgot-password', static function (Request $request) use ($response) {
            $email = $request->input('email');
            $email = is_string($email) ? strtolower(trim($email)) : '';
            return [
                Limit::perMinute(5)->by('forgot-ip:'.$request->ip())->response($response),
                Limit::perMinute(1)->by('forgot-email:'.hash('sha256', $email))->response($response),
            ];
        });
        RateLimiter::for('admin-reset-password', static fn (Request $request) =>
            Limit::perMinute(10)->by('reset-ip:'.$request->ip())->response($response)
        );
    }
}
