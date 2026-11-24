<?php

namespace Modules\Acl\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforceSessionIdleTimeout
{
    public function handle(Request $request, Closure $next)
    {
        $guard = Auth::guard('admin');
        if (! $guard->check()) {
            return $next($request);
        }

        $session = $request->session();
        $now = now()->timestamp;
        $lastActivity = $session->get('auth.last_activity_at');
        $lifetime = max(1, (int) config('session.lifetime')) * 60;

        // An expired session loses its activity timestamp. A remember cookie
        // must not silently authenticate it again and start a fresh idle window.
        if ($guard->viaRemember() || ($lastActivity !== null && $now - (int) $lastActivity >= $lifetime)) {
            $guard->logout();
            $session->invalidate();
            $session->regenerateToken();

            $loginUrl = route('admin.login.form');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your session has expired. Please sign in again.',
                    'redirect_url' => $loginUrl,
                ], 401)->header('Cache-Control', 'no-store, private');
            }

            return redirect()->to($loginUrl)->header('Cache-Control', 'no-store, private');
        }

        // Polling must not keep an otherwise idle authenticated session alive.
        if ($lastActivity === null || ! $request->routeIs(
            'notifications.bell.recent',
            'notifications.bell.unread-count',
        )) {
            $session->put('auth.last_activity_at', $now);
        }

        return $next($request);
    }
}
