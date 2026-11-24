<?php

namespace Modules\Restaurant\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Restaurant\Services\RestaurantApprovalAccess;
use Modules\Restaurant\Services\RestaurantOnboardingAccessService;

class RequireRestaurantOnboarding
{
    public function __construct(private RestaurantOnboardingAccessService $access) {}

    public function handle(Request $request, Closure $next)
    {
        $admin = $request->user('admin');

        if (! $admin || $request->routeIs('profile.edit', 'profile.update', 'profile.certificates.store', 'settings.restaurant.dietary-certificates.*', 'restaurant.onboarding.kyc', 'restaurant.onboarding.corrections.*', 'restaurant.onboarding.certificates.*', 'restaurant.onboarding.menu.*', 'admin.logout')) {
            return $next($request);
        }

        if (! app(RestaurantApprovalAccess::class)->isOwner($admin)
            || ! $this->access->forAdmin($admin)) {
            return $next($request);
        }

        $url = route('restaurant.onboarding.kyc');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Your restaurant onboarding is not complete.',
                'redirect_url' => $url,
            ], 403);
        }

        return redirect()->to($url);
    }
}
