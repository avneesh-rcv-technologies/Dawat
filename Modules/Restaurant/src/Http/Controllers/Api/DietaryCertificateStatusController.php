<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\DietaryManagment\Services\LabelCertificateEligibility;
use Modules\Restaurant\Services\RestaurantFoodItemV2Service;

class DietaryCertificateStatusController
{
    public function __construct(private RestaurantFoodItemV2Service $items, private LabelCertificateEligibility $eligibility) {}

    public function show(Request $request)
    {
        $restaurant = $this->items->restaurantForUser($request->user());
        $labels = collect($this->eligibility->forRestaurant($restaurant->id))->filter(fn ($label) => $label['required'])
            ->map(fn ($label, $id) => ['id' => (string) $id, ...$label])->values();

        return response()->json([
            'success' => true, 'message' => 'Restaurant dietary certificate status retrieved successfully.',
            'data' => [
                'restaurant_id' => $restaurant->id,
                'required_count' => $labels->count(),
                'uploaded_count' => $labels->where('is_uploaded', true)->count(),
                'approved_count' => $labels->where('allowed', true)->count(),
                'missing_upload_count' => $labels->where('is_uploaded', false)->count(),
                'awaiting_approval_count' => $labels->filter(fn ($label) => $label['is_uploaded']
                    && in_array($label['status'], ['submitted', 'under_review'], true))->count(),
                'all_required_uploaded' => $labels->every(fn ($label) => $label['is_uploaded']),
                'all_required_approved' => $labels->every(fn ($label) => $label['allowed']),
                'dietary_labels' => $labels,
            ],
        ])->header('Cache-Control', 'private, no-store');
    }
}
