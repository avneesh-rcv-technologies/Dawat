<?php

namespace Modules\Restaurant\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Admin;
use Modules\FoodManagements\Models\FoodItem;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\CodPaymentService;
use Modules\Orders\Services\OrderCacheService;
use Modules\Orders\Services\OrderService;
use Modules\Restaurant\Models\Restaurant;

class RestaurantOrderService
{
    public const RELATIONS = ['customer', 'vendor', 'items', 'transactions', 'payments', 'refunds', 'paymentConfiguration.method'];

    private function relations(): array
    {
        return [
            ...self::RELATIONS,
            'items.item' => fn (MorphTo $morph) => $morph->morphWith([FoodItem::class => ['categories.parent']]),
            'vendor' => fn (MorphTo $morph) => $morph->morphWith([
                Restaurant::class => ['addresses.city', 'addresses.state', 'addresses.country'],
            ]),
        ];
    }

    public function __construct(
        private OrderService $orders,
        private CodPaymentService $cash,
        private RestaurantOrderPolicy $policy,
    ) {}

    public function restaurantForUser($user): Restaurant
    {
        abort_unless($user instanceof Admin && $user->status && $user->email_verified_at, 403,
            'An active, verified restaurant owner account is required.');

        return Restaurant::where('admin_id', $user->id)->whereIn('status', ['draft', 'active'])->orderBy('id')->firstOrFail();
    }

    public function query(Restaurant $restaurant, array $filters = []): Builder
    {
        $query = Order::where('vendor_type', $restaurant->getMorphClass())->where('vendor_id', $restaurant->id);
        foreach (['status', 'payment_status', 'payment_method', 'customer_id', 'currency'] as $field) {
            if (isset($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
        if (isset($filters['order_type'])) {
            $query->where('order_type', $filters['order_type'] === 'pickup' ? 'takeaway' : $filters['order_type']);
        }
        if (isset($filters['transaction_status'])) {
            $query->whereHas('transactions', fn ($query) => $query->where('status', $filters['transaction_status']));
        }
        if (isset($filters['search']) && trim($filters['search']) !== '') {
            $search = '%'.trim($filters['search']).'%';
            $query->where(fn ($query) => $query->where('order_number', 'like', $search)
                ->orWhereHas('customer', fn ($query) => $query->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search)->orWhere('phone', 'like', $search)));
        }
        foreach (['date_from' => '>=', 'date_to' => '<='] as $key => $operator) {
            if (isset($filters[$key])) {
                $query->whereDate('created_at', $operator, $filters[$key]);
            }
        }
        foreach (['min_total' => '>=', 'max_total' => '<='] as $key => $operator) {
            if (isset($filters[$key])) {
                $query->where('total', $operator, $filters[$key]);
            }
        }

        return $query;
    }

    public function listing(Restaurant $restaurant, array $filters)
    {
        return $this->query($restaurant, $filters)->with($this->relations())
            ->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_dir'] ?? 'desc')
            ->orderBy('id', $filters['sort_dir'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 15)->withQueryString();
    }

    public function find(Restaurant $restaurant, int $id, bool $lock = false): Order
    {
        $order = $this->query($restaurant)->when($lock, fn ($query) => $query->lockForUpdate())->findOrFail($id);
        if ($lock) {
            // Use the same lock order as cash collection and payment processing.
            $order->setRelation('payments', $order->payments()->lockForUpdate()->get());
            $order->setRelation('transactions', $order->transactions()->lockForUpdate()->get());
        }

        return $order->loadMissing($this->relations());
    }

    public function isCod(Order $order): bool
    {
        return in_array(strtolower((string) $order->payment_method), ['cod', 'cash', 'cash_on_delivery'], true)
            || $this->cash->isCodConfiguration($order->paymentConfiguration);
    }

    public function statuses(string $type, ?Order $order = null): array
    {
        $type = $type === 'pickup' ? 'takeaway' : $type;
        $statuses = [...OrderStatus::workflow($type), OrderStatus::CANCELLED, OrderStatus::FAILED, OrderStatus::REFUNDED];
        $isCod = $order ? $this->isCod($order) : false;
        $actor = request()->user('admin') ?? request()->user();
        $canRecordRefund = $actor instanceof Admin && app(\Modules\Payments\Services\RefundAccess::class)->canUpdate($actor);

        return array_map(function ($status) use ($order, $isCod, $canRecordRefund) {
            $error = $order ? $this->policy->transitionError($order, $status === OrderStatus::REFUNDED ? 'C' : $status->code(), $isCod) : null;
            if ($order && $status === OrderStatus::REFUNDED && ! in_array($order->payment_status, ['paid', 'completed', 'captured', 'partially_refunded'], true)) {
                $error = 'There is no captured payment remaining to refund.';
            }

            if ($status === OrderStatus::REFUNDED && ! $canRecordRefund) {
                $error = 'Only authorized platform admins can record completed refunds.';
            }
            return [
                'code' => $status->code(), 'key' => $status->value, 'name' => $status->label(),
                'color' => $status->color(), 'is_current' => $order?->status === $status->code(),
                'can_transition' => $order ? $error === null && $order->status !== $status->code() : null,
                'restriction_reason' => $error,
                'requires_confirmation' => in_array($status, [OrderStatus::CANCELLED, OrderStatus::REFUNDED], true),
                'requires_refund_reference' => $status === OrderStatus::REFUNDED,
            ];
        }, $statuses);
    }

    public function updateStatus(Restaurant $restaurant, int $id, string $status, array $data = [], ?Admin $actor = null): Order
    {
        return DB::transaction(function () use ($restaurant, $id, $status, $data, $actor) {
            $order = $this->find($restaurant, $id, lock: true);
            $policyStatus = $status === 'R' ? 'C' : $status;
            if ($error = $this->policy->transitionError($order, $policyStatus, $this->isCod($order))) {
                throw ValidationException::withMessages(['status' => $error]);
            }
            abort_unless($actor instanceof Admin, 403);
            app(\Modules\Orders\Services\OrderStatusActionService::class)
                ->update($order, $status, $data, $actor, 'restaurant_cancellation');
            $this->forgetAfterCommit($order);

            return $order->refresh()->load($this->relations());
        });
    }

    public function collectCash(Restaurant $restaurant, int $id, Admin $actor, ?string $notes): Order
    {
        return DB::transaction(function () use ($restaurant, $id, $actor, $notes) {
            $order = $this->find($restaurant, $id, lock: true);

            return $this->cash->collect($order, $actor, $notes)->load($this->relations());
        });
    }

    public function delete(Restaurant $restaurant, int $id): void
    {
        DB::transaction(function () use ($restaurant, $id) {
            $order = $this->find($restaurant, $id, lock: true);
            if ($error = $this->policy->deletionError($order)) {
                throw ValidationException::withMessages(['order' => $error]);
            }
            if (! $order->delete()) {
                throw new \RuntimeException('Unable to delete the order.');
            }
            $this->forgetAfterCommit($order);
        });
    }

    public function deletionError(Order $order): ?string
    {
        return $this->policy->deletionError($order);
    }

    public function kpis(Restaurant $restaurant, array $filters): array
    {
        $query = $this->query($restaurant, $filters);
        $rows = (clone $query)->selectRaw('currency, payment_status, status, COUNT(*) AS order_count, SUM(total) AS amount')
            ->groupBy('currency', 'payment_status', 'status')->get();
        $currencies = $rows->groupBy('currency')->map(function ($rows, $currency) use ($query) {
            $bucket = function (array $statuses, bool $activeOnly = false) use ($rows, $currency) {
                $matching = $rows->whereIn('payment_status', $statuses);
                if ($activeOnly) {
                    $matching = $matching->whereNotIn('status', [Order::STATUS_CANCELLED, Order::STATUS_FAILED]);
                }

                return [
                    'count' => (int) $matching->sum('order_count'),
                    'amount' => number_format($matching->sum('amount'), 2, '.', ''),
                    'formatted' => $currency.' '.number_format($matching->sum('amount'), 2),
                ];
            };
            $paid = $bucket(['paid', 'completed', 'captured']);
            $revenue = app(\Modules\Orders\Services\CommissionRevenueAnalytics::class)
                ->forOrders((clone $query)->where('currency', $currency), true, $currency)['revenue'];

            return [
                'currency' => $currency, 'total_orders' => (int) $rows->sum('order_count'),
                'gross_order_value' => number_format($rows->sum('amount'), 2, '.', ''),
                'total_revenue' => number_format($revenue['total'], 2, '.', ''), 'total_revenue_formatted' => $revenue['total_formatted'],
                'revenue_basis' => $revenue['basis'], 'revenue_label' => $revenue['label'],
                'average_earnings_per_order' => number_format($revenue['average_order_value'], 2, '.', ''),
                'paid_payments' => $paid, 'failed_payments' => $bucket(['failed']),
                'pending_payments' => $bucket(['pending', 'unpaid', 'processing', 'authorized'], true),
                'refunded_payments' => $bucket(['refunded']),
                'partially_refunded_payments' => $bucket(['partially_refunded']),
                'cancelled_payments' => $bucket(['cancelled']),
                'average_paid_order_value' => number_format($paid['count'] ? (float) $paid['amount'] / $paid['count'] : 0, 2, '.', ''),
            ];
        })->values()->all();

        return [
            'total_orders' => (int) $rows->sum('order_count'), 'currencies' => $currencies,
            'by_status' => (clone $query)->selectRaw('status, COUNT(*) AS count')->groupBy('status')->get()
                ->map(fn ($row) => ['code' => $row->status, 'name' => OrderStatus::fromCode($row->status)?->label(), 'count' => (int) $row->count])->all(),
            'by_order_type' => (clone $query)->selectRaw('order_type, COUNT(*) AS count')->groupBy('order_type')->get()
                ->map(fn ($row) => ['order_type' => $row->order_type, 'count' => (int) $row->count])->all(),
        ];
    }

    private function forgetAfterCommit(Order $order): void
    {
        DB::afterCommit(function () use ($order) {
            try {
                $cache = app(OrderCacheService::class);
                if ($order->customer_id) {
                    $cache->forgetOrder($order->id, (int) $order->customer_id);
                    $cache->forgetOrdersList((int) $order->customer_id);
                }
                $cache->forgetCodOrders();
            } catch (\Throwable $exception) {
                Log::warning('Restaurant order cache refresh failed.', ['order_id' => $order->id]);
            }
        });
    }
}
