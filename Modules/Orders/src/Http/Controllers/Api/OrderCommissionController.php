<?php

namespace Modules\Orders\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Orders\Models\OrderCommission;
use Modules\Orders\Services\OrderCommissionAccess;

class OrderCommissionController extends Controller
{
    public function __construct(private OrderCommissionAccess $access) {}

    public function index(Request $request): JsonResponse
    {
        $actor = $this->access->actor($request);
        $filters = $request->validate([
            'restaurant_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(OrderCommission::STATUSES)],
            'order_number' => ['nullable', 'string', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'regex:/^[a-zA-Z]{3}$/'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => array_merge(['nullable', 'date_format:Y-m-d'], $request->filled('date_from') ? ['after_or_equal:date_from'] : []),
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $orderNumber = trim($filters['order_number'] ?? '');
        $search = trim($filters['search'] ?? '');
        $commissions = $this->access->query($actor)->with(['order', 'restaurant'])
            ->when($filters['restaurant_id'] ?? null, fn ($q, $id) => $q->where('restaurant_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['currency'] ?? null, fn ($q, $currency) => $q->where('currency', strtoupper($currency)))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('calculated_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('calculated_at', '<=', $date))
            ->when($orderNumber !== '', fn ($q) => $q->whereHas('order', fn ($orders) => $orders
                ->where('order_number', 'like', '%'.addcslashes($orderNumber, '%_\\').'%')))
            ->when($search !== '', fn ($q) => $q->where(function ($matches) use ($search) {
                $term = '%'.addcslashes($search, '%_\\').'%';
                $matches->whereHas('order', fn ($orders) => $orders->where('order_number', 'like', $term))
                    ->orWhereHas('restaurant', fn ($restaurants) => $restaurants->where('name', 'like', $term));
            }))
            ->latest('id')->paginate($filters['per_page'] ?? 20)->withQueryString();
        $commissions->through(fn (OrderCommission $commission) => $this->record($commission));

        return response()->json(['success' => true, 'data' => $commissions])
            ->header('Cache-Control', 'no-store, private');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $actor = $this->access->actor($request);
        $commission = $this->access->query($actor)
            ->with(['order', 'restaurant', 'processor', 'histories.actor'])->findOrFail($id);
        $data = $this->record($commission) + [
            'policy_reference' => $commission->policy_reference,
            'policy_snapshot' => $commission->policy_snapshot,
            'admin_notes' => $commission->admin_notes,
            'processed_by' => $commission->processor?->name,
            'histories' => $commission->histories->map(fn ($history) => [
                'old_status' => $history->old_status,
                'new_status' => $history->new_status,
                'actor' => $history->actor?->name,
                'notes' => $history->notes,
                'created_at' => $history->created_at?->toIso8601String(),
            ]),
        ];

        return response()->json(['success' => true, 'data' => $data])
            ->header('Cache-Control', 'no-store, private');
    }

    private function record(OrderCommission $commission): array
    {
        return $commission->settlementData() + [
            'id' => $commission->id,
            'order_number' => $commission->order?->order_number,
            'restaurant' => ['id' => $commission->restaurant_id, 'name' => $commission->restaurant?->name],
            'order_amount' => $commission->order_amount,
            'commission_percentage' => $commission->commission_percentage,
            'platform_commission_amount' => $commission->platform_commission_amount,
            'restaurant_amount' => $commission->restaurant_amount,
            'currency' => $commission->currency,
            'status' => $commission->status,
            'calculated_at' => $commission->calculated_at?->toIso8601String(),
            'processed_at' => $commission->processed_at?->toIso8601String(),
            'created_at' => $commission->created_at?->toIso8601String(),
            'detail_url' => route('api.order-commissions.show', $commission->id),
        ];
    }
}
