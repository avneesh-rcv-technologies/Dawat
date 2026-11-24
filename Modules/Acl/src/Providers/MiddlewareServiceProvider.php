<?php

namespace Modules\Acl\Providers;

use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;
use Modules\Acl\Http\Middleware\AdminAuthenticate;
use Modules\Acl\Http\Middleware\AdminPermissionMiddleware;
use Modules\Acl\Http\Middleware\RedirectIfAdminAuthenticated;
use Modules\Acl\Routing\EncryptedIdUrlGenerator;
use Modules\Acl\Services\RouteIdCipher;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(RouteIdCipher::class, fn ($app) => new RouteIdCipher((string) $app['config']->get('app.key')));

        // route() encrypts the parameters a route lists in its route.ids middleware.
        $this->app->extend('url', fn (UrlGenerator $url, $app) => $url instanceof EncryptedIdUrlGenerator
            ? $url
            : EncryptedIdUrlGenerator::fromBase($url, $app->make(RouteIdCipher::class)));
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

        // Decrypts one-time password envelopes sent by web login / reset forms
        $router->aliasMiddleware('password.transport', \Modules\Acl\Http\Middleware\DecryptClientPasswords::class);

        // Decrypts encrypted record IDs in the URL (e.g. restaurant id) - IDOR hardening
        $router->aliasMiddleware('route.ids', \Modules\Acl\Http\Middleware\DecryptRouteIds::class);

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
