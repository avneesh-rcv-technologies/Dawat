<?php

namespace Modules\Acl\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next)
    {
        foreach (config('auth.guards', []) as $name => $configuration) {
            if (($configuration['driver'] ?? null) !== 'session'
                || !config('auth.providers.'.($configuration['provider'] ?? ''))) {
                continue;
            }

            $guard = Auth::guard($name);
            $user = $guard->user();

            if (!$user || (!$this->isInactive($user->status) && !$this->isInactive($user->is_active))) {
                continue;
            }

            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $loginRoute = Route::has("{$name}.login.form") ? "{$name}.login.form" : 'admin.login.form';
            $url = route($loginRoute);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'redirect_url' => $url,
                ], 401)->header('Cache-Control', 'no-store, private');
            }

            return redirect()->to($url)->header('Cache-Control', 'no-store, private');
        }

        return $next($request);
    }

    private function isInactive(mixed $status): bool
    {
        if ($status instanceof \BackedEnum) {
            $status = $status->value;
        }

        return in_array($status, [false, 0, '0', 'inactive'], true);
    }
}
