<?php

namespace Modules\Restaurant\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Acl\Models\Admin;
use Modules\Orders\Models\OrderCommission;
use Modules\Orders\Services\OrderCommissionAccess;
use Modules\Restaurant\Models\Restaurant;

class RestaurantPayoutReport
{
    public const SORTS = [
        'order' => 'orders.order_number', 'date' => 'order_commissions.calculated_at',
        'amount' => 'order_commissions.order_amount', 'refunded' => 'order_commissions.refunded_amount',
        'rate' => 'order_commissions.commission_percentage', 'reversed' => 'order_commissions.commission_reversed_amount',
        'commission' => 'net_commission', 'payable' => 'order_commissions.final_payable_amount',
        'paid' => 'order_commissions.paid_amount', 'currency' => 'order_commissions.currency', 'status' => 'order_commissions.status',
    ];

    public function canView(int $restaurantId, ?Admin $actor): bool
    {
        return $actor && $actor->status && OrderCommissionAccess::visibleInSidebar()
            && (! app(RestaurantApprovalAccess::class)->isOwner($actor)
                || Restaurant::whereKey($restaurantId)->where('admin_id', $actor->id)->exists());
    }

    public function filters(Request $request): array
    {
        // Initial visits and Reset use this month; explicitly cleared dates stay unbounded.
        if (! $request->hasAny(['payout_from', 'payout_to'])) {
            $today = now();
            $request->merge([
                'payout_from' => $today->copy()->startOfMonth()->toDateString(),
                'payout_to' => $today->copy()->endOfMonth()->toDateString(),
            ]);
        }

        return $request->validate([
            'payout_search' => ['nullable', 'string', 'max:100'],
            'payout_status' => ['nullable', Rule::in(OrderCommission::STATUSES)],
            'payout_currency' => ['nullable', 'string', 'regex:/^[a-zA-Z]{3}$/'],
            'payout_from' => ['nullable', 'date_format:Y-m-d'],
            'payout_to' => array_merge(['nullable', 'date_format:Y-m-d'], $request->filled('payout_from') ? ['after_or_equal:payout_from'] : []),
            'payout_sort' => ['sometimes', Rule::in(array_keys(self::SORTS))],
            'payout_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'payout_page' => ['sometimes', 'integer', 'min:1'],
            'payout_per_page' => ['sometimes', 'integer', Rule::in([5, 10, 25, 50])],
        ]);
    }

    public function query(int $restaurantId, Admin $actor, array $filters): Builder
    {
        abort_unless($this->canView($restaurantId, $actor), 403);
        $search = trim($filters['payout_search'] ?? '');

        return app(OrderCommissionAccess::class)->query($actor)
            ->join('orders', 'orders.id', '=', 'order_commissions.order_id')
            ->where('order_commissions.restaurant_id', $restaurantId)
            ->select('order_commissions.*', 'orders.order_number')
            ->selectRaw('(order_commissions.platform_commission_amount - order_commissions.commission_reversed_amount) AS net_commission')
            ->when($search !== '', fn ($q) => $q->where('orders.order_number', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->when($filters['payout_status'] ?? null, fn ($q, $status) => $q->where('order_commissions.status', $status))
            ->when($filters['payout_currency'] ?? null, fn ($q, $currency) => $q->where('order_commissions.currency', strtoupper($currency)))
            ->when($filters['payout_from'] ?? null, fn ($q, $date) => $q->whereDate('order_commissions.calculated_at', '>=', $date))
            ->when($filters['payout_to'] ?? null, fn ($q, $date) => $q->whereDate('order_commissions.calculated_at', '<=', $date))
            ->orderBy(self::SORTS[$filters['payout_sort'] ?? 'date'], $filters['payout_direction'] ?? 'desc')
            ->orderByDesc('order_commissions.id');
    }

    public function page(int $restaurantId, Admin $actor, array $filters): array
    {
        $query = $this->query($restaurantId, $actor, $filters);
        $totals = (clone $query)->reorder()->toBase()->select('order_commissions.currency')
            ->selectRaw('COUNT(*) AS record_count, SUM(order_commissions.order_amount) AS order_amount,
                SUM(order_commissions.refunded_amount) AS refunded_amount,
                SUM(order_commissions.commission_reversed_amount) AS reversed_amount,
                SUM(order_commissions.platform_commission_amount - order_commissions.commission_reversed_amount) AS net_commission,
                SUM(order_commissions.final_payable_amount) AS payable_amount, SUM(order_commissions.paid_amount) AS paid_amount')
            ->groupBy('order_commissions.currency')->orderBy('order_commissions.currency')->get();
        $perPage = (int) ($filters['payout_per_page'] ?? 5);
        $rows = $query->paginate($perPage, ['*'], 'payout_page', $filters['payout_page'] ?? 1)
            ->withQueryString()->appends(array_merge($filters, ['tab' => 'payouts', 'payout_per_page' => $perPage]))
            ->fragment('restaurant-payouts');

        return compact('rows', 'totals', 'filters');
    }
}
