<?php

namespace Modules\Orders\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Orders\Models\OrderCommission;
use Modules\Orders\Support\CommissionRefundAmounts;
use Modules\Restaurant\Models\Restaurant;

class CommissionRevenueAnalytics
{
    /** Use the same scoped order cohort and order dates as the surrounding analytics. */
    public function forOrders(Builder $orders, bool $owner, ?string $currency = null): array
    {
        $currency ??= fn_get_setting('general.currency', 'USD');
        $commissions = OrderCommission::whereIn('order_id', (clone $orders)->reorder()->select('orders.id'))
            ->whereHas('order', fn ($q) => $q->where('vendor_type', (new Restaurant)->getMorphClass())
                ->whereColumn('orders.vendor_id', 'order_commissions.restaurant_id'))
            ->with('order:id,created_at,payment_method,order_type')->get();
        $rates = [];
        $records = $commissions->map(function ($commission) use ($owner, $currency, &$rates) {
            $amount = $owner ? $commission->final_payable_amount : $commission->effective_commission;
            $source = $commission->currency ?: $currency;
            if (! isset($rates[$source])) {
                $rates[$source] = $this->rate($source, $currency);
            }

            return [
                'cents' => (int) round(CommissionRefundAmounts::cents($amount) * $rates[$source]),
                'date' => $commission->order->created_at->format('Y-m-d'),
                'method' => $commission->order->payment_method ?: 'unknown',
                'type' => $commission->order->order_type,
            ];
        });
        $values = $records->pluck('cents')->filter(fn ($amount) => $amount > 0)->values();
        $total = $records->sum('cents');
        $stats = [
            'total' => $total / 100,
            'average_order_value' => $values->isEmpty() ? 0 : round($values->avg() / 100, 2),
            'highest_order' => ($values->max() ?? 0) / 100,
            'lowest_order' => ($values->min() ?? 0) / 100,
        ];
        foreach ($stats as $key => $amount) {
            $stats[$key.'_formatted'] = fn_format_currency_amount($amount, $currency);
        }
        $stats += [
            'currency' => $currency,
            'basis' => $owner ? 'restaurant_payable' : 'effective_commission',
            'label' => $owner ? 'Restaurant earnings' : 'Dawat commission revenue',
            'description' => $owner ? 'Restaurant payable after successful refunds; includes amounts awaiting payout.'
                : 'Original platform commission less successful refund reversals.',
            'commissioned_orders' => $commissions->count(),
            'earning_orders' => $values->count(),
        ];
        $group = static fn ($field) => $records->groupBy($field)->map(fn ($rows) => [
            'count' => $rows->count(), 'revenue' => $rows->sum('cents') / 100,
            'revenue_formatted' => fn_format_currency_amount($rows->sum('cents') / 100, $currency),
        ]);

        return ['revenue' => $stats, 'by_date' => $group('date'), 'by_method' => $group('method'), 'by_type' => $group('type')];
    }

    private function rate(string $from, string $to): float
    {
        if ($from === $to) {
            return 1;
        }
        $source = fn_get_currency_by_code($from);
        $target = fn_get_currency_by_code($to);
        $direct = $source && $target ? DB::table('currency_exchange_rates')
            ->where('base_currency', $source->id)->where('target_currency', $target->id)->value('rate') : null;
        if ((float) $direct > 0) {
            return (float) $direct;
        }
        $reverse = $source && $target ? DB::table('currency_exchange_rates')
            ->where('base_currency', $target->id)->where('target_currency', $source->id)->value('rate') : null;
        if ((float) $reverse > 0) {
            return 1 / (float) $reverse;
        }

        throw new \RuntimeException("Configure the {$from} to {$to} exchange rate before viewing revenue analytics.");
    }
}
