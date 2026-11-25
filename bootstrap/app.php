<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToPriorityList(
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Modules\Acl\Http\Middleware\DecryptRouteIds::class,
        );
        $middleware->redirectGuestsTo(fn (Request $request) => app(\Modules\Acl\Services\LoginRedirector::class)->url($request));

        $middleware->web(append: [
            \Modules\Acl\Http\Middleware\EnforceSessionIdleTimeout::class,
            \Modules\Acl\Http\Middleware\PreventAuthenticatedPageCaching::class,
            \Modules\Acl\Http\Middleware\EnsureActiveAccount::class,
            \Modules\Restaurant\Http\Middleware\RequireRestaurantOnboarding::class,
        ]);

        $middleware->alias([
            'admin.auth' => \Modules\Core\Http\Middleware\AuthChecking::class,
            'auth.technician' => \Modules\Core\Http\Middleware\TechnicianAuth::class,
            'admin.permission' => \Modules\Acl\Http\Middleware\AdminPermissionMiddleware::class,

        ]);
    })
     ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() === 419 && $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your session has expired. Please refresh the page and try again.',
                ], 419);
            }

            if ($e->getStatusCode() === 404 && !$request->expectsJson()) {
                return response()->view('errors.404', [], 404, $e->getHeaders());
            }

            if ($e->getStatusCode() !== 403) {
                return null;
            }

            $message = 'you are not permitted to do this work';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 403, $e->getHeaders());
            }

            return response()->view('errors.403', ['message' => $message], 403, $e->getHeaders());
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;

            if ($request->routeIs('website.partner.verify-captcha', 'website.partner-signup.verify-captcha', 'restaurant.onboarding.submit') && $request->expectsJson()) {
                $seconds = max(1, (int) $retryAfter);
                return response()->json([
                    'success' => false,
                    'message' => "Too many submission attempts. Please wait {$seconds} seconds before trying again.",
                    'retry_after' => $seconds,
                ], 429, $e->getHeaders());
            }

            if ($request->routeIs('website.contact.store')) {
                $seconds = max(1, (int) $retryAfter);
                $message = "Too many contact attempts. Please wait {$seconds} seconds before trying again.";

                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 429, $e->getHeaders());
                }

                return redirect()->to(route('website.contact').'#contact-form')
                    ->withInput($request->only(['first_name', 'last_name', 'email', 'message', 'terms']))
                    ->withErrors(['throttle' => $message])
                    ->withHeaders($e->getHeaders());
            }

            $minutes = ceil($retryAfter / 60);
            $message = $request->routeIs('admin.login')
                ? "Too many attempts. Please try again in about {$minutes} minute(s)."
                : "Too many attempts. Please try again in about {$minutes} minute(s).";

            if ($request->routeIs('restaurant.onboarding.submit') && !$request->expectsJson()) {
                return redirect()->route('restaurant.onboarding.form')
                    ->withInput($request->only([
                        'owner_name', 'email', 'restaurant_name', 'phone',
                        'country_id', 'state_id', 'city_id', 'address_line1',
                        'email_verification_token',
                    ]))
                    ->withErrors(['throttle' => $message]);
            }

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 429, $e->getHeaders());
        });

    })
    ->create();
