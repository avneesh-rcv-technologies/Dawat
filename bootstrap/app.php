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
        $middleware->web(append: [
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

            $minutes = ceil($retryAfter / 60);
            $message = $request->routeIs('admin.login')
                ? "Too many attempts. Please try again in about {$minutes} minute(s)."
                : "Too many attempts. Please try again in about {$minutes} minute(s).";

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 429, $e->getHeaders());
        });

    })
    ->create();
