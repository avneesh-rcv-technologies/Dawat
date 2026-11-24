<?php

namespace Modules\Acl\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Acl\Models\Admin;
use Modules\Restaurant\Services\RestaurantApprovalAccess;
use Symfony\Component\HttpFoundation\Response;

class LoginRedirector
{
    public const COOKIE = 'dashboard_login_portal';

    public function portal(Request $request, ?Admin $actor = null): string
    {
        if ($actor) {
            return app(RestaurantApprovalAccess::class)->isOwner($actor) ? 'restaurant' : 'admin';
        }
        if ($request->routeIs('website.partner*', 'restaurant.onboarding.*', 'admin.restaurant.menu.*', 'restaurant.login*')
            || $request->is('partner/*', 'restaurant/login', 'restaurant/menu', 'restaurant/*/menu*', 'restaurant/onboarding/*')) {
            return 'restaurant';
        }
        // An explicit restaurant URL takes precedence over a stale login-portal hint.
        // This encrypted cookie is only a redirect hint, never an authorization decision.
        $remembered = $request->cookie(self::COOKIE);
        if (in_array($remembered, ['admin', 'restaurant'], true)) {
            return $remembered;
        }

        // Restaurant approval URLs belong to the admin panel, despite their path prefix.
        return 'admin';
    }

    public function url(Request $request, ?Admin $actor = null): string
    {
        $owner = $this->portal($request, $actor) === 'restaurant';
        $route = $owner ? 'website.partner-login' : 'admin.login.form';

        return Route::has($route) ? route($route) : url($owner ? '/partner/login' : '/admin/login');
    }

    public function remember(Response $response, Request $request, Admin $actor): Response
    {
        $response->headers->setCookie(cookie(
            self::COOKIE, $this->portal($request, $actor), 60 * 24 * 30,
            '/', config('session.domain'), config('session.secure') ?? $request->isSecure(), true, false, 'lax'
        ));

        return $response;
    }
}
