<?php

namespace Modules\Payments\DataView;

use Illuminate\Support\Facades\DB;
use Modules\DataView\DataGrid;
use Modules\Payments\Models\Refund;
use Modules\Payments\Services\RefundAccess;
use Modules\Restaurant\Models\Restaurant;

class RefundGrid extends DataGrid
{
    protected $primaryColumn = 'id';

    protected $itemsPerPage = 10;

    protected $sortOrder = 'desc';

    public function prepareQueryBuilder()
    {
        // Authorize here as well as in the controller, including direct grid/export use.
        $access = app(RefundAccess::class);
        $actor = $access->actor(request());
        $query = DB::table('refunds')
            ->join('orders', 'refunds.order_id', '=', 'orders.id')
            ->join('payments', 'refunds.payment_id', '=', 'payments.id')
            ->leftJoin('restaurants', function ($join) {
                $join->on('restaurants.id', '=', 'orders.vendor_id')
                    ->where('orders.vendor_type', (new Restaurant)->getMorphClass());
            })
            ->whereIn('refunds.id', $access->query($actor)->select('refunds.id'))
            ->select('refunds.id', 'refunds.refund_number', 'refunds.order_id', 'orders.order_number', 'restaurants.name as restaurant_name',
                'refunds.refund_amount', 'payments.currency', 'refunds.refund_source', 'refunds.refund_status', 'refunds.created_at');

        foreach (['id', 'refund_number', 'refund_amount', 'refund_source', 'refund_status', 'created_at'] as $column) {
            $this->addFilter($column, 'refunds.'.$column);
        }
        $this->addFilter('order_number', 'orders.order_number');
        $this->addFilter('restaurant_name', 'restaurants.name');
        $this->setSortColumn('refunds.id');

        return $query;
    }

    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'id', 'label' => 'ID', 'type' => 'integer',
            'searchable' => false, 'filterable' => false, 'sortable' => true, 'visibility' => false,
        ]);
        $this->addColumn([
            'index' => 'refund_number', 'label' => 'Refund number', 'type' => 'string',
            'searchable' => true, 'filterable' => true, 'sortable' => true,
            'closure' => fn ($row) => e($row->refund_number),
        ]);
        $this->addColumn([
            'index' => 'order_number', 'label' => 'Order number', 'type' => 'string',
            'searchable' => true, 'filterable' => true, 'sortable' => true,
            'closure' => fn ($row) => '<a href="'.e(route('admin.orders.show', $row->order_id)).'" class="text-blue-600 underline">'.e($row->order_number).'</a>',
        ]);
        $this->addColumn([
            'index' => 'restaurant_name', 'label' => 'Restaurant', 'type' => 'string',
            'searchable' => false, 'filterable' => true, 'sortable' => true,
            'closure' => fn ($row) => e($row->restaurant_name ?? '—'),
        ]);
        $this->addColumn([
            'index' => 'refund_amount', 'label' => 'Refund amount', 'type' => 'decimal',
            'searchable' => false, 'filterable' => false, 'sortable' => true,
            'closure' => fn ($row) => e(fn_format_currency_amount((float) $row->refund_amount, $row->currency)),
        ]);
        $this->addColumn([
            'index' => 'refund_source', 'label' => 'Source', 'type' => 'string',
            'filterable' => true, 'filterable_type' => 'dropdown', 'allow_multiple_values' => false,
            'filterable_options' => [
                ['label' => 'Customer cancellation', 'value' => 'customer_cancellation'],
                ['label' => 'Restaurant cancellation', 'value' => 'restaurant_cancellation'],
                ['label' => 'Admin', 'value' => 'admin'],
            ],
            'sortable' => true,
            'closure' => fn ($row) => e(ucfirst(str_replace('_', ' ', $row->refund_source))),
        ]);
        $this->addColumn([
            'index' => 'refund_status', 'label' => 'Refund status', 'type' => 'string',
            'filterable' => true, 'filterable_type' => 'dropdown', 'allow_multiple_values' => false,
            'filterable_options' => array_map(fn ($status) => ['label' => ucfirst($status), 'value' => $status], Refund::STATUSES),
            'sortable' => true,
            'closure' => function ($row) {
                [$background, $color] = match ($row->refund_status) {
                    'succeeded' => ['#ecfdf5', '#047857'],
                    'failed', 'rejected' => ['#fef2f2', '#b91c1c'],
                    default => ['#fff7ed', '#c2410c'],
                };

                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: '.$background.'; color: '.$color.'">'.e(ucfirst($row->refund_status)).'</span>';
            },
        ]);
        $this->addColumn([
            'index' => 'created_at', 'label' => 'Created at', 'type' => 'date',
            'filterable' => true, 'filterable_type' => 'date_range', 'sortable' => true,
        ]);
    }

    public function prepareActions()
    {
        // Read access is enforced by the grid query and the refund details controller.
        $this->addAction([
            'icon' => 'visibility', 'title' => 'View request', 'method' => 'GET',
            'url' => fn ($row) => route('admin.refunds.show', $row->id),
        ]);
    }

    /** Summarize all authorized, filtered requests, independent of pagination. */
    public function summary(): array
    {
        $query = $this->getQueryBuilder()->cloneWithout(['orders', 'limit', 'offset'])
            ->cloneWithoutBindings(['order'])
            ->select('payments.currency', 'refunds.refund_amount', 'refunds.refund_status');
        $totals = DB::query()->fromSub($query, 'refund_totals')
            ->selectRaw('currency, COUNT(*) as record_count, COALESCE(SUM(refund_amount), 0) as requested_amount,
                SUM(CASE WHEN refund_status = ? THEN COALESCE(refund_amount, 0) ELSE 0 END) as refunded_amount,
                SUM(CASE WHEN refund_status = ? THEN COALESCE(refund_amount, 0) ELSE 0 END) as pending_amount,
                SUM(CASE WHEN refund_status = ? THEN COALESCE(refund_amount, 0) ELSE 0 END) as processing_amount,
                SUM(CASE WHEN refund_status = ? THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN refund_status = ? THEN 1 ELSE 0 END) as processing_count',
                ['succeeded', 'pending', 'processing', 'pending', 'processing'])
            ->groupBy('currency')->orderBy('currency')->get();

        return [
            'total_records' => (int) $totals->sum('record_count'),
            'pending_records' => (int) $totals->sum('pending_count'),
            'processing_records' => (int) $totals->sum('processing_count'),
            'currencies' => $totals,
        ];
    }
}
