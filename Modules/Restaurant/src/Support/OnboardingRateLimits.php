<?php

namespace Modules\Restaurant\Support;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class OnboardingRateLimits
{
    public static function register(): void
    {
        RateLimiter::for('restaurant-onboarding-write', static function (Request $request) {
            $name = $request->route()?->getName() ?? '';
            $step = str_contains($name, 'banking') ? 'banking' : (str_contains($name, 'tax-information') ? 'tax' : 'kyc');
            $key = ($request->user()?->getAuthIdentifier() ?? $request->ip()).':'.$step;
            return Limit::perMinute(3)->by($key)->response(static function (Request $request, array $headers) {
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
