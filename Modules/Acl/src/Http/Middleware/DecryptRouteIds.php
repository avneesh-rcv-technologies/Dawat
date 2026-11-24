<?php

namespace Modules\Acl\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Acl\Services\RouteIdCipher;

/**
 * Decrypts encrypted ID route parameters before the controller (and later middleware) see them.
 *
 * Usage: ->middleware('route.ids:id') or ->middleware('route.ids:restaurant,id')
 *
 * Plain numeric IDs are rejected (404), so /restaurant/30/edit no longer works; only
 * /restaurant/{token}/edit does. URLs built with route() for these routes are encrypted
 * automatically by EncryptedIdUrlGenerator, so views and controllers need no changes.
 */
class DecryptRouteIds
{
    public function __construct(private RouteIdCipher $cipher) {}

    public function handle(Request $request, Closure $next, string ...$parameters)
    {
        $route = $request->route();

        foreach ($parameters as $name) {
            $inPath = $route->hasParameter($name);
            if (!$inPath && !$request->query->has($name)) {
                continue;
            }

            $token = $inPath ? $route->parameter($name) : $request->query($name);
            abort_unless(is_string($token), 404);
            $id = $this->cipher->decrypt($token);
            abort_if($id === null, 404);

            if ($inPath) {
                $route->setParameter($name, $id);
            } else {
                $request->query->set($name, $id);
            }
        }

        return $next($request);
    }
}
