<?php

namespace Modules\Restaurant\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Panel menu routes are /restaurant/{restaurant}/menu/... . The restaurant is moved to a
 * request attribute so the shared menu controller actions keep their (Request, int $id)
 * signatures.
 */
class ResolveMenuRestaurant
{
    public function handle(Request $request, Closure $next)
    {
        $request->attributes->set('menu_restaurant_id', (int) $request->route('restaurant'));
        $request->route()->forgetParameter('restaurant');

        return $next($request);
    }
}
