<?php

namespace Modules\Restaurant\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;

class OnboardingApiSession
{
    public function handle(Request $request, Closure $next)
    {
        // Mobile/API clients continue to authenticate with their bearer token.
        if ($request->bearerToken()) {
            return $next($request);
        }

        // The onboarding page uses its existing admin session and CSRF token.
        return app(Pipeline::class)->send($request)->through([
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ])->then($next);
    }
}
