<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantApprovalAccess;
use Modules\Restaurant\Services\RestaurantDeliveryPolicy;
use Modules\Restaurant\Services\RestaurantService;

class RestaurantSettingsController extends Controller
{
    public function __construct(
        private RestaurantService $service,
        private Restaurant $restaurants,
        private RestaurantApprovalAccess $access,
    ) {}

    private function ownedRestaurant(Request $request): Restaurant
    {
        $owner = $request->user('admin');
        abort_unless($this->access->isOwner($owner), 403);
        $restaurant = $this->restaurants->newQuery()->where('admin_id', $owner->id)
            ->orderBy('id')->firstOrFail();
        abort_unless($restaurant->canManageFeatures(), 403, 'Both onboarding and application approval are required.');

        return $restaurant;
    }

    private function settingsPage(Request $request, string $section, string $title)
    {
        $restaurant = $this->ownedRestaurant($request);
        $canUpdate = $request->user('admin')->hasPermission("settings.restaurant.{$section}.update");

        return view('restaurant::admin.owner-settings', compact('restaurant', 'section', 'title', 'canUpdate'));
    }

    private function authorizeUpdate(Request $request, string $section): Restaurant
    {
        $restaurant = $this->ownedRestaurant($request);
        abort_unless($request->user('admin')->hasPermission("settings.restaurant.{$section}.index")
            && $request->user('admin')->hasPermission("settings.restaurant.{$section}.update"), 403);

        return $restaurant;
    }

    public function index(Request $request)
    {
        return $this->settingsPage($request, 'preferences', 'Restaurant Settings');
    }

    public function deliveryPolicy(Request $request)
    {
        return $this->settingsPage($request, 'delivery-policy', 'Order Delivery Policy');
    }

    public function update(Request $request)
    {
        $restaurant = $this->authorizeUpdate($request, 'preferences');
        $data = $request->validate([
            'food_type' => ['nullable', 'in:all,veg,non_veg,vegan'],
            'status' => ['nullable', 'in:draft,active,inactive'],
            'order_time_limit' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'cancellation_time_limit' => ['nullable', 'integer', 'min:0', 'max:120'],
            'cancellable_statuses' => ['nullable', 'array'],
            'cancellable_statuses.*' => ['string'],
            'is_takeaway' => ['sometimes', 'boolean'],
            'is_delivery' => ['sometimes', 'boolean'],
            'is_accepting_orders' => ['sometimes', 'boolean'],
            'allow_order_cancellation' => ['sometimes', 'boolean'],
        ]);
        foreach (['is_takeaway', 'is_delivery', 'is_accepting_orders', 'allow_order_cancellation'] as $field) {
            $data[$field] = $request->boolean($field);
        }
        $data['cancellable_statuses'] = $data['cancellable_statuses'] ?? [];
        $this->service->updateSettings($restaurant->id, $data);

        return back()->with('success', 'Restaurant settings updated successfully.');
    }

    public function updateDeliveryPolicy(Request $request)
    {
        $restaurant = $this->authorizeUpdate($request, 'delivery-policy');
        $data = $request->validate(RestaurantDeliveryPolicy::rules(), RestaurantDeliveryPolicy::messages());
        $data['free_delivery_enabled'] = $request->boolean('free_delivery_enabled');
        RestaurantDeliveryPolicy::validateThreshold($restaurant, $data);
        $this->service->updateSettings($restaurant->id, $data);

        return back()->with('success', 'Order delivery policy updated successfully.');
    }
}
