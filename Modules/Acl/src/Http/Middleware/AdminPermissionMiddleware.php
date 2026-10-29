<?php

namespace Modules\Acl\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminPermissionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $checker = new CheckGuardPermission();
        return $checker->handle($request, $next, 'admin');
    }
}
