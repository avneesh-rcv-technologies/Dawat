<?php

namespace Modules\Acl\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Modules\Acl\Http\Middleware\AdminAuthenticate;
use Modules\Acl\Http\Middleware\AdminPermissionMiddleware;
use Modules\Acl\Http\Middleware\RedirectIfAdminAuthenticated;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        // Register route middleware alias

        // Register the new guard/permission middleware
        $router->aliasMiddleware('check.permission', \Modules\Acl\Http\Middleware\CheckGuardPermission::class);

        // Register the request sanitization middleware
        $router->aliasMiddleware('sanitize.request', \Modules\Acl\Http\Middleware\SanitizeRequest::class);

        // Apply sanitization to all routes (now JSON-safe)
        try {
            // Add sanitization to web middleware group (for web routes)
            $router->pushMiddlewareToGroup('web', 'sanitize.request');

            // Add sanitization to api middleware group (for API routes)
            $router->pushMiddlewareToGroup('api', 'sanitize.request');

            // Optionally add activity tracking
            // $router->pushMiddlewareToGroup('web',  'admin.track');
        } catch (\Throwable $e) {
            // ignore if group does not exist
        }
    }
}
