<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Orders\Enums\OrderStatus;
use Modules\Restaurant\Http\Requests\RestaurantOrderFilters;
use Modules\Restaurant\Http\Resources\RestaurantOrderResource;
use Modules\Restaurant\Services\RestaurantOrderService;

class RestaurantOrderController
{
    public function __construct(private RestaurantOrderService $service) {}

    public function index(RestaurantOrderFilters $request)
    {
        $restaurant = $this->service->restaurantForUser($request->user());
        $filters = $request->validated();
        $page = $this->service->listing($restaurant, $filters);

        return $this->response('Orders retrieved successfully.',
            $page->getCollection()->map(fn ($order) => (new RestaurantOrderResource($order))->resolve($request)), [
                'meta' => [
                    'current_page' => $page->currentPage(), 'per_page' => $page->perPage(),
                    'total' => $page->total(), 'last_page' => $page->lastPage(), 'has_more_pages' => $page->hasMorePages(),
                ],
                'filters' => $filters,
            ]);
    }

    public function show(Request $request, int $id)
    {
        $restaurant = $this->service->restaurantForUser($request->user());

        return $this->response('Order retrieved successfully.',
            (new RestaurantOrderResource($this->service->find($restaurant, $id)))->resolve($request));
    }

    public function statuses(Request $request)
    {
        $restaurant = $this->service->restaurantForUser($request->user());
        $data = $request->validate([
            'order_type' => ['sometimes', Rule::in(['delivery', 'pickup', 'takeaway', 'dine_in'])],
            'order_id' => ['sometimes', 'integer', 'min:1'],
        ]);
        $order = isset($data['order_id']) ? $this->service->find($restaurant, $data['order_id']) : null;
        $type = $order?->order_type ?? ($data['order_type'] ?? 'delivery');

        return $this->response('Order statuses retrieved successfully.', [
            'order_id' => $order?->id, 'order_type' => $type === 'pickup' ? 'takeaway' : $type,
            'statuses' => $this->service->statuses($type, $order),
        ]);
    }

    public function nextStatuses(Request $request, int $id)
    {
        $restaurant = $this->service->restaurantForUser($request->user());
        $order = $this->service->find($restaurant, $id);
        $statuses = array_values(array_filter(
            $this->service->statuses($order->order_type, $order),
            fn ($status) => $status['can_transition'] === true
        ));

        return $this->response('Allowed next order statuses retrieved successfully.', [
            'order_id' => $order->id,
            'order_type' => $order->order_type,
            'current_status' => $order->status,
            'current_status_name' => OrderStatus::fromCode($order->status)?->label(),
            'payment_status' => $order->payment_status,
            'can_update_status' => $statuses !== [],
            'statuses' => $statuses,
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $restaurant = $this->service->restaurantForUser($request->user());
        $data = $request->validate([
            'status' => ['required', Rule::in(array_map(fn ($status) => $status->code(), OrderStatus::cases())),
                ...($request->input('status') === 'R' ? [] : [Rule::exists('statuses', 'status_code')->where('type_code', 'O')])],
            'payment_status' => ['prohibited'], 'restaurant_id' => ['prohibited'],
            ...\Modules\Orders\Services\OrderStatusActionService::rules((string) $request->input('status')),
        ]);
        $order = $this->service->updateStatus($restaurant, $id, $data['status'], $data, $request->user());

        return $this->response('Order status updated successfully.', (new RestaurantOrderResource($order))->resolve($request));
    }

    public function collectCash(Request $request, int $id)
    {
        $restaurant = $this->service->restaurantForUser($request->user());
        $data = $request->validate(['notes' => ['nullable', 'string', 'max:2000']]);
        $order = $this->service->collectCash($restaurant, $id, $request->user(), $data['notes'] ?? null);

        return $this->response('Cash payment collected successfully.', (new RestaurantOrderResource($order))->resolve($request));
    }

    public function destroy(Request $request, int $id)
    {
        $restaurant = $this->service->restaurantForUser($request->user());
        $this->service->delete($restaurant, $id);

        return $this->response('Order deleted successfully.', ['id' => $id]);
    }

    public function kpis(RestaurantOrderFilters $request)
    {
        $restaurant = $this->service->restaurantForUser($request->user());
        $filters = $request->validated();

        return $this->response('Order KPIs retrieved successfully.', $this->service->kpis($restaurant, $filters), ['filters' => $filters]);
    }

    private function response(string $message, $data, array $extra = [])
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data, ...$extra])
            ->header('Cache-Control', 'private, no-store');
    }
}
