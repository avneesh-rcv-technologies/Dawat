<?php

namespace Modules\Orders\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;
use Modules\Orders\Enums\TransactionStatus;
use Modules\Orders\Enums\TransactionType;
use Illuminate\Support\Str;

class TransactionGrid extends DataGrid
{
    protected $primaryColumn = 'id';
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
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

        // Filter by restaurant ownership (same pattern as FoodManagements)
        $currentAdmin = auth('admin')->user();
        
        if ($currentAdmin) {
            $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $currentAdmin->id)->first();
            
            if ($restaurant) {
                // Restaurant admin: show only transactions for their restaurant orders
                $query->where('orders.vendor_type', \Modules\Restaurant\Models\Restaurant::class)
                      ->where('orders.vendor_id', $restaurant->id);
            }
            // Super admin (no restaurant): show all transactions
        }

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
                if (empty($row->order_id) || empty($row->order_number)) {
                    return 'Not available';
                }

                $url = e(route('admin.orders.show', $row->order_id));
                $orderNumber = e($row->order_number);

                return "<a href='{$url}' class='text-blue-600 underline'>{$orderNumber}</a>";
            }
        ]);

        $this->addColumn([
            'index' => 'type',
            'label' => 'Type',
            'type' => 'string',
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => array_map(fn (TransactionType $type) => [
                'label' => $type->label(),
                'value' => $type->value,
            ], TransactionType::cases()),
            'closure' => function ($row) {
                $type = TransactionType::tryFrom($row->type ?? '');
                $label = e($type?->label() ?? Str::headline($row->type ?? 'Unknown'));
                $style = $type?->class() ?? 'background-color: #6c757d; color: #ffffff;';

                return "<span style='{$style}' class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'>{$label}</span>";
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
            'filterable_options' => array_map(fn (TransactionStatus $status) => [
                'label' => $status->label(),
                'value' => $status->value,
            ], TransactionStatus::cases()),
            'closure' => function ($row) {
                $status = TransactionStatus::tryFrom($row->status ?? '');
                $label = e($status?->label() ?? Str::headline($row->status ?? 'Unknown'));
                $style = $status?->class() ?? 'background-color: #6c757d; color: #ffffff;';

                return "<span style='{$style}' class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'>{$label}</span>";
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
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('admin.transactions.show')) {
            $this->addAction([
                'icon' => 'visibility',
                'title' => 'View',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.transactions.show', $row->id);
                },
            ]);
        }

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
     */
    public function prepareMassActions()
    {
        // if (bouncer()->hasPermission('admin.transactions.bulk-delete')) {
        //     $this->addMassAction([
        //         'icon' => 'delete',
        //         'title' => 'Bulk Delete',
        //         'method' => 'POST',
        //         'action' => 'text-red-600 bg-red-100',
        //         'url' => 'admin.transactions.bulk-delete',
        //     ]);
        // }

        // $transactionStatuses = TransactionStatus::cases();
        // if (bouncer()->hasPermission('admin.transactions.toggle-status')) {
        //     $this->addMassAction([
        //         'icon' => '',
        //         'title' => 'Update Status',
        //         'method' => 'POST',
        //         'action' => 'bg-gray-500',
        //         'url' => 'admin.transactions.toggle-status',
        //         'options' => collect($transactionStatuses)->map(function (TransactionStatus $status) {
        //             return [
        //                 'label' => $status->label(),
        //                 'value' => $status->value,
        //             ];
        //         })->values()->toArray(),
        //     ]);
        // }
    }
}
