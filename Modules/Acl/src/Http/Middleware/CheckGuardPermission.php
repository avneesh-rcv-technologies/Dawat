<?php

namespace Modules\Acl\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


class CheckGuardPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guards  Comma-separated list of guards to check (e.g., "admin,vendor")
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guards = null)
    {
        // Allow guest access to login, register, and password routes to prevent redirect loops
        $guestAllowed = ['login', 'login.form', 'register', 'register.form', 'password.request', 'password.reset'];
        $currentRoute = Route::currentRouteName();
        foreach ($guestAllowed as $allowed) {
            if (str_ends_with($currentRoute, $allowed)) {
                return $next($request);
            }
        }


        // If no guards specified, use the current default guard
        if ($guards) {
            $guards = explode(',', $guards);
        } else {
            $guards = config('acl::guards.guards') ? array_keys(config('acl::guards.guards')) : ['admin'];
        }

        $routeName = Route::currentRouteName();
        foreach ($guards as $guard) {
            $guard = $guard ?: null;
            $user = Auth::guard($guard)->user();
            if (!$user) {
                // Not authenticated for this guard, try next
                continue;
            }            
            // Account status is enforced by EnsureActiveAccount in the web group.
            if($user->role && $user->role->guard !== $guard) {
                Auth::guard($guard)->logout();
                abort(403, 'Unauthorized route access');
            }
           

            // Check role and permissions if exists
            if (!empty($user->role)) {   
                $role = $user->role;
                if (!$role || ($role->permission_type !== 'all' && !in_array($routeName, $role->permissions ?? []))) {
                    abort(403, 'Unauthorized route access');
                }
            }
            // Set the active guard on the request and session for global access
            $request->attributes->set('active_guard', $guard);
            \Illuminate\Support\Facades\Auth::shouldUse($guard);
            return $next($request);
        }

        // If no guard passed, redirect to first guard's login if route exists, else abort
        $firstGuard = $guards[0] ?? 'admin';
        $loginRoute = "{$firstGuard}.login.form";
        if (\Route::has($loginRoute)) {
            return redirect()->route($loginRoute);
        }
        // Fallback: abort if no login route exists
        abort(403, 'Unauthorized and no login route defined.');
    }
}
