<?php

namespace Modules\Restaurant\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Orders\Models\Transaction;
use Modules\Restaurant\Models\Restaurant;

class RestaurantTransactionService
{
    public function query(Restaurant $restaurant, array $filters = []): Builder
    {
        $query = Transaction::whereHas('order', function ($order) use ($restaurant, $filters) {
            $order->where('vendor_type', $restaurant->getMorphClass())->where('vendor_id', $restaurant->id);
            foreach (['customer_id' => 'customer_id', 'payment_status' => 'payment_status', 'order_status' => 'status'] as $key => $column) {
                if (isset($filters[$key])) {
                    $order->where($column, $filters[$key]);
                }
            }
            if (isset($filters['order_type'])) {
                $order->where('order_type', $filters['order_type'] === 'pickup' ? 'takeaway' : $filters['order_type']);
            }
        });
        foreach (['type', 'status', 'order_id', 'payment_id', 'currency'] as $field) {
            if (isset($filters[$field])) {
                $query->where('transactions.'.$field, $filters[$field]);
            }
        }
        if (isset($filters['payment_method'])) {
            $query->where(function ($query) use ($filters) {
                $query->whereHas('payment', fn ($payment) => $payment
                    ->whereColumn('payments.order_id', 'transactions.order_id')
                    ->where('payment_method_id', $filters['payment_method']))
                    ->orWhere(fn ($query) => $query->whereNull('payment_id')->whereHas('order', fn ($order) =>
                        $order->where('payment_method', $filters['payment_method'])));
            });
        }
        if (isset($filters['search']) && trim($filters['search']) !== '') {
            $search = '%'.trim($filters['search']).'%';
            $query->where(fn ($query) => $query->where('transaction_number', 'like', $search)
                ->orWhere('gateway_reference', 'like', $search)
                ->orWhereHas('order', fn ($order) => $order->where('order_number', 'like', $search)));
        }
        foreach (['date_from' => ['created_at', '>='], 'date_to' => ['created_at', '<='],
            'processed_from' => ['processed_at', '>='], 'processed_to' => ['processed_at', '<=']] as $key => [$column, $operator]) {
            if (isset($filters[$key])) {
                $query->whereDate('transactions.'.$column, $operator, $filters[$key]);
            }
        }
        foreach (['min_amount' => '>=', 'max_amount' => '<='] as $key => $operator) {
            if (isset($filters[$key])) {
                $query->where('transactions.amount', $operator, $filters[$key]);
            }
        }

        return $query;
    }

    public function listing(Restaurant $restaurant, array $filters)
    {
        return $this->query($restaurant, $filters)->with([
            'order.customer', 'order.vendor', 'order.paymentConfiguration.method', 'payment.method.method', 'collector',
        ])->orderBy('transactions.'.($filters['sort_by'] ?? 'created_at'), $filters['sort_dir'] ?? 'desc')
            ->orderBy('transactions.id', $filters['sort_dir'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 15)->withQueryString();
    }

    public function kpis(Restaurant $restaurant, array $filters): array
    {
        $rows = $this->query($restaurant, $filters)
            ->selectRaw('currency, type, status, COUNT(*) AS transaction_count, SUM(amount) AS total_amount')
            ->groupBy('currency', 'type', 'status')->get();
        $currencies = $rows->groupBy('currency')->map(function ($rows, $currency) {
            $bucket = function ($matching) use ($currency) {
                $amount = number_format($matching->sum('total_amount'), 2, '.', '');

                return ['count' => (int) $matching->sum('transaction_count'), 'amount' => $amount,
                    'formatted' => $currency.' '.number_format((float) $amount, 2)];
            };
            $completed = $rows->where('status', 'completed');
            $payments = $bucket($completed->where('type', 'payment'));
            $refunds = $bucket($completed->where('type', 'refund'));
            $chargebacks = $bucket($completed->where('type', 'chargeback'));
            $net = round((float) $payments['amount'] - (float) $refunds['amount'] - (float) $chargebacks['amount'], 2);

            return [
                'currency' => $currency, 'total_transactions' => (int) $rows->sum('transaction_count'),
                'completed_payments' => $payments, 'completed_refunds' => $refunds,
                'completed_chargebacks' => $chargebacks,
                'pending_payments' => $bucket($rows->where('type', 'payment')->where('status', 'pending')),
                'failed_payments' => $bucket($rows->where('type', 'payment')->whereIn('status', ['failed', 'declined'])),
                'net_payments' => number_format($net, 2, '.', ''),
                'net_payments_formatted' => $currency.' '.number_format($net, 2),
                'by_status' => $rows->groupBy('status')->map(fn ($matching, $status) => [
                    'status' => $status, ...$bucket($matching),
                ])->values()->all(),
                'by_type' => $rows->groupBy('type')->map(fn ($matching, $type) => [
                    'type' => $type, ...$bucket($matching),
                ])->values()->all(),
            ];
        })->values()->all();

        return ['total_transactions' => (int) $rows->sum('transaction_count'), 'currencies' => $currencies];
    }
}
