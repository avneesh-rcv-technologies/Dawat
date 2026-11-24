<?php

namespace Modules\Admin\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Support\DashboardFilters;
use Modules\Admin\Support\DashboardTimeline;
use Modules\Currencies\Models\Currency;
use Modules\Orders\Models\OrderCommission;
use Modules\Orders\Support\CommissionRefundAmounts;

class AdminDashboardFinanceService
{
    private const GROSS = 'COALESCE(order_commissions.platform_commission_amount, 0)';
    private const REVERSED = 'COALESCE(order_commissions.commission_reversed_amount, 0)';
    private const PAYABLE = 'COALESCE(order_commissions.final_payable_amount, 0)';
    private const PAID = 'COALESCE(order_commissions.paid_amount, 0)';

    private function query(DashboardFilters $filters, ?int $restaurantId = null): Builder
    {
        if ($restaurantId !== null) abort_unless($restaurantId > 0, 403);
        $orders = app(AdminDashboardService::class)->orders()->whereBetween('created_at', $filters->bounds())
            ->when($restaurantId !== null, fn ($q) => $q->where('vendor_id', $restaurantId))
            ->when($filters->fulfillment !== 'all', fn ($q) => $q->where('order_type', $filters->fulfillment));

        return OrderCommission::query()->join('orders', 'orders.id', '=', 'order_commissions.order_id')
            ->whereIn('orders.id', $orders->reorder()->select('orders.id'))
            ->whereColumn('orders.vendor_id', 'order_commissions.restaurant_id')
            ->when($restaurantId !== null, fn ($q) => $q->where('order_commissions.restaurant_id', $restaurantId))
            ->where('order_commissions.currency', $filters->currency);
    }

    public function summarize(DashboardFilters $filters, ?int $restaurantId = null): array
    {
        $query = $this->query($filters, $restaurantId);
        $gross = self::GROSS; $reversed = self::REVERSED; $payable = self::PAYABLE; $paid = self::PAID;
        $outstanding = "GREATEST($payable - $paid, 0)";
        $recovery = "GREATEST($paid - $payable, 0)";
        $totals = (clone $query)->selectRaw("COUNT(*) AS records, SUM($gross) AS gross, SUM($reversed) AS reversed,
            SUM($gross - $reversed) AS revenue, SUM($payable) AS payable, SUM($paid) AS paid,
            SUM($outstanding) AS outstanding, SUM($recovery) AS recovery,
            SUM(LEAST($payable, $paid)) AS covered")->first();
        $amounts = [];
        foreach (['gross', 'reversed', 'revenue', 'payable', 'paid', 'outstanding', 'recovery', 'covered'] as $field) {
            $amounts[$field] = CommissionRefundAmounts::money(CommissionRefundAmounts::cents($totals->{$field} ?? '0'));
        }
        $timeline = DashboardTimeline::seed($filters, ['revenue' => 0, 'gross' => 0, 'paid' => 0, 'payable' => 0, 'outstanding' => 0]);
        $format = $filters->timezone === config('app.timezone', 'UTC') ? '%Y-%m-%d' : '%Y-%m-%d %H:%i:00';
        $groups = (clone $query)->selectRaw("DATE_FORMAT(orders.created_at, '$format') AS bucket_time,
            SUM($gross - $reversed) AS revenue, SUM($gross) AS gross, SUM($paid) AS paid, SUM($payable) AS payable, SUM($outstanding) AS outstanding")
            ->groupBy('bucket_time')->orderBy('bucket_time')->get();
        foreach ($groups as $group) {
            $date = CarbonImmutable::parse($group->bucket_time, config('app.timezone', 'UTC'))->setTimezone($filters->timezone);
            $key = DashboardTimeline::bucket($date, $timeline['unit'])->format('Y-m-d');
            if (!isset($timeline['points'][$key])) continue;
            foreach (['revenue', 'gross', 'paid', 'payable', 'outstanding'] as $field) {
                // Accumulate whole cents; conversion to chart coordinates happens only at presentation.
                $timeline['points'][$key][$field] += CommissionRefundAmounts::cents($group->{$field} ?? '0');
            }
        }
        $timeline['points'] = array_values(array_map(function ($point) {
            foreach (['revenue', 'gross', 'paid', 'payable', 'outstanding'] as $field) $point[$field] /= 100;
            return $point;
        }, $timeline['points']));
        $states = (clone $query)->select('order_commissions.status')->selectRaw("COUNT(*) AS records, SUM($outstanding) AS outstanding")
            ->groupBy('order_commissions.status')->get()->keyBy('status');
        $currencies = Currency::query()->pluck('code')->merge(OrderCommission::query()->when($restaurantId !== null, fn ($q) => $q->where('restaurant_id', $restaurantId))->distinct()->pluck('currency'))
            ->push($filters->currency)->filter(fn ($code) => is_string($code) && preg_match('/^[A-Za-z]{3}$/', $code))
            ->map(fn ($code) => strtoupper($code))->unique()->sort()->values();
        $metadata = fn_get_currency_by_code($filters->currency);

        return $amounts + [
            'currency' => $filters->currency, 'symbol' => fn_get_currency_symbol($filters->currency) ?: $filters->currency,
            'decimals' => (int) ($metadata?->decimal ?? 2), 'currencies' => $currencies,
            'records' => (int) $totals->records, 'timeline' => $timeline, 'states' => $states,
            'paidPercent' => (float) $amounts['payable'] > 0 ? min(100, round((float) $amounts['covered'] / (float) $amounts['payable'] * 100, 1)) : 0,
            'recent' => (clone $query)->select('order_commissions.*')->with(['order:id,order_number,created_at', 'restaurant:id,name'])
                ->orderByDesc('order_commissions.calculated_at')->orderByDesc('order_commissions.id')->limit(6)->get(),
        ];
    }
}
