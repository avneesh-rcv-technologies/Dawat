<?php

namespace Modules\Acl\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventAuthenticatedPageCaching
{
    public function handle(Request $request, Closure $next): Response
    {
        // Capture this before logout clears the authenticated user.
        $actor = Auth::guard('admin')->user();
        $authenticated = $actor !== null;
        $response = $next($request);

        if ($authenticated || $request->attributes->has('active_guard')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, private, max-age=0, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        if ($actor instanceof \Modules\Acl\Models\Admin) {
            app(\Modules\Acl\Services\LoginRedirector::class)->remember($response, $request, $actor);
        }
        return $response;
    }
}
