<?php

namespace Modules\Activitylog\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

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
        $router->aliasMiddleware('track-requests', \Modules\Activitylog\Http\Middleware\TrackRequestActivity::class);
        $router->aliasMiddleware('protect-requests', \Modules\Activitylog\Http\Middleware\ProtectRequestThreats::class);
        $router->aliasMiddleware('api-throttle', \Modules\Activitylog\Http\Middleware\ApiThrottle::class);
        $router->aliasMiddleware('ensure-admin-access-request-needed', \Modules\Activitylog\Http\Middleware\EnsureAdminAccessRequestNeeded::class);
        // Outer logging wrapper first, inner threat protection closer to the route.
        try {
            $router->pushMiddlewareToGroup('web', 'track-requests');
            $router->pushMiddlewareToGroup('web', 'protect-requests');
            $router->pushMiddlewareToGroup('api', 'api-throttle');
        } catch (\Throwable $e) {
            // Boot-time registration must not invoke the HTTP exception handler.
            report($e);
        }
    }
}
