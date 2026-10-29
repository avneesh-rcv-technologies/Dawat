<?php

namespace Modules\Orders\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;
use Modules\Orders\Enums\TransactionStatus;

class TransactionGrid extends DataGrid
{
    protected $primaryColumn = 'id';

    protected $itemsPerPage = 10;

    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $query = DB::table('transactions')
            ->leftJoin('orders', 'transactions.order_id', '=', 'orders.id')
            ->select(
                'transactions.id',
                'transactions.order_id',
                'transactions.transaction_number',
                'transactions.type',
                'transactions.status',
                'transactions.amount',
                'transactions.currency',
                'transactions.gateway_reference',
                'transactions.processed_at',
                'transactions.created_at',
                'orders.order_number'
            );

        // Map datagrid columns to fully-qualified columns to avoid ambiguity in global search
        $this->addFilter('id', 'transactions.id');
        $this->addFilter('transaction_number', 'transactions.transaction_number');
        $this->addFilter('order_number', 'orders.order_number');
        $this->addFilter('type', 'transactions.type');
        $this->addFilter('status', 'transactions.status');
        $this->addFilter('amount', 'transactions.amount');
        $this->addFilter('currency', 'transactions.currency');
        $this->addFilter('processed_at', 'transactions.processed_at');
        $this->addFilter('created_at', 'transactions.created_at');

        // Ensure default sorting uses a fully-qualified column
        $this->setSortColumn('transactions.id');

        return $query;
    }

    /**
     * Prepare columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'id',
            'label' => 'ID',
            'type' => 'integer',
            'searchable' => false,
            'visibility' => false,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'transaction_number',
            'label' => 'Transaction #',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'order_number',
            'label' => 'Order #',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => false,
            'closure' => function ($row) {
                return "<a href='/admin/orders/{$row->order_id}' style='text-decoration: underline; color: bg-primary-500;'>{$row->order_number}</a>";
            }
        ]);

        $this->addColumn([
            'index' => 'type',
            'label' => 'Type',
            'type' => 'string',
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Payment', 'value' => 'payment'],
                ['label' => 'Refund', 'value' => 'refund'],
                ['label' => 'Adjustment', 'value' => 'adjustment'],
            ],
            'closure' => function ($row) {
                $labels = [
                    'payment' => 'bg-blue-500',
                    'refund' => 'bg-yellow-500',
                    'adjustment' => 'bg-gray-500',
                ];
                $class = $labels[$row->type] ?? 'bg-gray-400';

                return '<span class="px-2 py-1 text-xs rounded-full text-white ' . $class . '">' .
                    ucfirst($row->type) . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'amount',
            'label' => 'Amount',
            'type' => 'decimal',
            'filterable' => false,
            'searchable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return fn_convert_currency($row->amount, $row->currency);
            }
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'string',
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Pending', 'value' => 'pending'],
                ['label' => 'Completed', 'value' => 'completed'],
                ['label' => 'Failed', 'value' => 'failed'],
                ['label' => 'Reversed', 'value' => 'reversed'],
            ],
            'closure' => function ($row) {
                $statusColors = [
                    'pending' => 'bg-yellow-400',
                    'completed' => 'bg-green-500',
                    'failed' => 'bg-red-500',
                    'reversed' => 'bg-gray-400',
                ];

                $class = $statusColors[$row->status] ?? 'bg-gray-300';

                return '<span class="px-2 py-1 text-xs rounded-full text-white ' . $class . '">' .
                    ucfirst($row->status) . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Created At',
            'type' => 'date',
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        // if (bouncer()->hasPermission('admin.transactions.edit')) {
        //     $this->addAction([
        //         'icon' => 'edit',
        //         'title' => 'Update',
        //         'method' => 'GET',
        //         'url' => function ($row) {
        //             return route('admin.transactions.edit', $row->id);
        //         },
        //     ]);
        // }

        if (bouncer()->hasPermission('admin.transactions.view')) {
            $this->addAction([
                'icon' => 'visibility',
                'title' => 'View',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.transactions.show', $row->id);
                },
            ]);
        }

        // if (bouncer()->hasPermission('admin.transactions.reload')) {
        //     $this->addAction([
        //         'icon' => 'refresh',
        //         'title' => 'Refresh',
        //         'method' => 'GET',
        //         'url' => function ($row) {
        //             if($row->gateway_reference) {
        //                 return route('admin.transactions.reload', $row->id);
        //             }
        //             return '#';
        //         },
        //     ]);
        // }

        if (bouncer()->hasPermission('admin.transactions.delete')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.transactions.destroy', $row->id);
                },
            ]);
        }
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('admin.transactions.bulk-delete')) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-red-600 bg-red-100',
                'url' => 'admin.transactions.bulk-delete',
            ]);
        }

        $transactionStatuses = TransactionStatus::cases();
        if (bouncer()->hasPermission('admin.transactions.toggle-status')) {
            $this->addMassAction([
                'icon' => '',
                'title' => 'Update Status',
                'method' => 'POST',
                'action' => 'bg-gray-500',
                'url' => 'admin.transactions.toggle-status',
                'options' => collect($transactionStatuses)->map(function ($name, $id) {
                    return [
                        'label' => $name->name,
                        'value' => $name->value
                    ];
                })->values()->toArray(),
            ]);
        }
    }
}
