<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Acl\Models\Admin;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantOnboardingDetailsService;

class OnboardingDetailsController
{
    public function __construct(private RestaurantOnboardingDetailsService $service) {}

    public function show(Request $request): JsonResponse
    {
        abort_unless($request->user() instanceof Admin, 403);
        $restaurant = Restaurant::query()->where('admin_id', $request->user()->id)->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Restaurant onboarding details retrieved successfully.',
            'data' => $this->service->build($restaurant),
        ]);
    }
}
