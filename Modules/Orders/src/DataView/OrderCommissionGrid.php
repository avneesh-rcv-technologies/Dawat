<?php

namespace Modules\Orders\DataView;

use Illuminate\Support\Facades\DB;
use Modules\DataView\DataGrid;
use Modules\Orders\Exports\OrderCommissionExport;
use Modules\Orders\Models\OrderCommission;
use Modules\Orders\Services\OrderCommissionAccess;

class OrderCommissionGrid extends DataGrid
{
    protected $primaryColumn = 'id';

    protected $sortOrder = 'desc';

    protected $itemsPerPage = 10;

    public function prepareQueryBuilder()
    {
        $access = app(OrderCommissionAccess::class);
        $actor = $access->actor(request());
        $query = DB::table('order_commissions')
            ->join('orders', 'order_commissions.order_id', '=', 'orders.id')
            ->join('restaurants', 'order_commissions.restaurant_id', '=', 'restaurants.id')
            ->whereIn('order_commissions.id', $access->query($actor)->select('order_commissions.id'))
            ->select('order_commissions.*', 'orders.order_number', 'restaurants.name as restaurant_name')
            ->selectRaw('(order_commissions.platform_commission_amount - order_commissions.commission_reversed_amount) as effective_commission');
        foreach (['id', 'order_amount', 'commission_percentage', 'platform_commission_amount', 'restaurant_amount', 'refunded_amount', 'commission_reversed_amount', 'final_payable_amount', 'currency', 'status', 'calculated_at'] as $column) {
            $this->addFilter($column, 'order_commissions.' . $column);
        }
        $this->addFilter('order_number', 'orders.order_number');
        $this->addFilter('restaurant_name', 'restaurants.name');
        $this->setSortColumn('order_commissions.id');

        return $query;
    }

    public function prepareColumns()
    {
        $this->addColumn(['index' => 'id', 'label' => 'ID', 'type' => 'integer', 'visibility' => false, 'exportable' => false]);
        $this->addColumn([
            'index' => 'order_number',
            'label' => 'Order',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => fn($row) => '<a class="text-blue-600 underline" href="' . e(route('admin.orders.show', $row->order_id)) . '">' . e($row->order_number) . '</a>'
        ]);
        $this->addColumn([
            'index' => 'restaurant_name',
            'label' => 'Restaurant',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => fn($row) => e($row->restaurant_name)
        ]);
        $detailFields = ['commission_percentage', 'platform_commission_amount', 'restaurant_amount', 'commission_reversed_amount'];
        foreach (['order_amount' => 'Order amount', 'commission_percentage' => 'Commission %', 'platform_commission_amount' => 'Original commission', 'restaurant_amount' => 'Original restaurant share', 'refunded_amount' => 'Refunded', 'commission_reversed_amount' => 'Commission reversed', 'effective_commission' => 'Net commission', 'final_payable_amount' => 'Restaurant payable'] as $field => $label) {
            $this->addColumn([
                'index' => $field,
                'label' => $label,
                'type' => 'decimal',
                'sortable' => true,
                'visibility' => !in_array($field, $detailFields, true),
                'closure' => fn($row) => $field === 'commission_percentage'
                    ? number_format((float) $row->{$field}, 2) . '%'
                    : e(fn_format_currency_amount((float) $row->{$field}, $row->currency))
            ]);
        }
        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'string',
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'allow_multiple_values' => false,
            'sortable' => true,
            'filterable_options' => array_map(fn($status) => ['value' => $status, 'label' => ucfirst(str_replace('_', ' ', $status))], OrderCommission::STATUSES),
            'closure' => function ($row) {
                $style = match ($row->status) {
                    'processed' => 'background:#ecfdf5;color:#047857;', 'failed' => 'background:#fef2f2;color:#b91c1c;', default => 'background:#fff7ed;color:#c2410c;'
                };

                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="' . $style . '">' . e(ucfirst(str_replace('_', ' ', $row->status))) . '</span>';
            }
        ]);
        $this->addColumn([
            'index' => 'calculated_at',
            'label' => 'Date',
            'type' => 'date',
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true
        ]);
    }

    public function prepareActions()
    {
        $this->addAction([
            'icon' => 'visibility',
            'title' => 'View commission',
            'method' => 'GET',
            'url' => fn($row) => route('admin.order-commissions.show', $row->id)
        ]);
        $access = app(OrderCommissionAccess::class);
        if ($access->canUpdate($access->actor(request()))) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Update status',
                'method' => 'GET',
                'url' => fn($row) => route('admin.order-commissions.show', $row->id) . '#commission-status'
            ]);
        }
    }

    public function getExporter()
    {
        return new OrderCommissionExport($this);
    }

    /** Totals for all filtered records, independent of the current grid page. */
    public function summary(): array
    {
        $query = $this->getQueryBuilder()->cloneWithout(['orders', 'limit', 'offset'])
            ->cloneWithoutBindings(['order'])
            ->select(
                'order_commissions.currency',
                'order_commissions.order_amount',
                'order_commissions.platform_commission_amount',
                'order_commissions.restaurant_amount',
                'order_commissions.commission_reversed_amount',
                'order_commissions.final_payable_amount',
                'order_commissions.status'
            );
        $totals = DB::query()->fromSub($query, 'commission_totals')
            ->selectRaw('currency, COUNT(*) as record_count, SUM(order_amount) as order_amount,
                SUM(platform_commission_amount - commission_reversed_amount) as platform_commission_amount, SUM(final_payable_amount) as restaurant_amount,
                SUM(CASE WHEN status = ? THEN platform_commission_amount - commission_reversed_amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count', ['pending', 'pending'])
            ->groupBy('currency')->orderBy('currency')->get();

        return [
            'total_records' => (int) $totals->sum('record_count'),
            'pending_records' => (int) $totals->sum('pending_count'),
            'currencies' => $totals,
        ];
    }
}
