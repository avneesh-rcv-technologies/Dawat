<?php

namespace Modules\Orders\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;
use Modules\Payments\Enums\PaymentStatus;

class OrderGrid extends DataGrid
{
    protected $primaryColumn = 'id';

    protected $itemsPerPage = 10;

    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    // public function prepareQueryBuilder()
    // {
    //     return DB::table('orders')
    //         ->leftJoin('customers', function($join) {
    //             $join->on('orders.customer_id', '=', 'customers.id')
    //                  ->where('orders.customer_type', '=', 'Modules\\Acl\\Models\\Customer');
    //         })
    //         ->leftJoin('users', function($join) {
    //             $join->on('orders.customer_id', '=', 'users.id')
    //                  ->where('orders.customer_type', '=', 'App\\Models\\User');
    //         })
    //         ->leftJoin('payment_methods', 'payment_methods.code', '=', 'orders.payment_method')
    //         ->select(
    //             'orders.id',
    //             'orders.order_number',
    //             'orders.customer_id',
    //             'orders.customer_type',
    //             'orders.status',
    //             'orders.subtotal',
    //             'orders.tax',
    //             'orders.delivery_fee',
    //             'orders.total',
    //             'orders.payment_method',
    //             'payment_methods.name as payment_method_name',
    //             'orders.payment_status',
    //             'orders.currency',
    //             'orders.created_at',
    //             'customers.name as customer_name',
    //             'users.name as user_name'
    //         );
    // }


    public function prepareQueryBuilder()
    {
        $query = DB::table('orders')
            ->leftJoin('customers', function ($join) {
                $join->on('orders.customer_id', '=', 'customers.id')
                    ->where('orders.customer_type', '=', 'Modules\\Acl\\Models\\Customer');
            })
            ->leftJoin('users', function ($join) {
                $join->on('orders.customer_id', '=', 'users.id')
                    ->where('orders.customer_type', '=', 'App\\Models\\User');
            })
            ->leftJoin('payment_methods', 'payment_methods.code', '=', 'orders.payment_method')
            // Also join with payment_configuration by ID since orders.payment_method stores ID
            ->leftJoin('payment_configurations', 'payment_configurations.id', '=', 'orders.payment_method')
            ->select(
                'orders.id',
                'orders.order_number',
                'orders.customer_id',
                'orders.customer_type',
                'orders.status as order_status',
                'orders.subtotal',
                'orders.tax',
                'orders.delivery_fee',
                'orders.total',
                'orders.payment_method',
                DB::raw('COALESCE(payment_configurations.merchant_name, payment_methods.name) as payment_method_name'),
                'orders.payment_status',
                'orders.currency',
                'orders.created_at as created_at',
                'customers.name as customer_name',
                'users.name as user_name',
                DB::raw('COALESCE(customers.name, users.name) as customer_display_name')
            );

        $currentAdmin = auth('admin')->user();

        if ($currentAdmin) {
            // Check if this admin owns a restaurant
            $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $currentAdmin->id)->first();

            if ($restaurant) {
                // Restaurant admin: show only their restaurant's orders
                $query->where('orders.vendor_id', $restaurant->id)
                    ->where('orders.vendor_type', \Modules\Restaurant\Models\Restaurant::class);
            }
        }
    $this->addFilter('order_status' , 'orders.status');
    $this->addFilter('created_at' , 'orders.created_at');
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
            'label' => 'Order ID',
            'type' => 'integer',
            'visibility' => false,
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'order_number',
            'label' => 'Order Number',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'customer_display_name',
            'label' => 'Customer',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
            'closure' => function ($row) {
                // Get the customer name based on customer type
                $customerName = '';
                if ($row->customer_type === 'Modules\\Acl\\Models\\Customer') {
                    $customerName = $row->customer_name ?? 'N/A';
                    return "<p>{$customerName}</p>";
                } elseif ($row->customer_type === 'App\\Models\\User') {
                    $customerName = $row->user_name ?? 'N/A';
                    return "<p>{$customerName}</p>";
                } else {
                    return 'N/A';
                }
            }
        ]);

        $this->addColumn([
            'index' => 'customer_name_search',
            'label' => 'Customer Search',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => false,
            'visibility' => false,
        ]);

        $this->addColumn([
            'index' => 'user_name_search',
            'label' => 'User Search',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => false,
            'visibility' => false,
        ]);

        $this->addColumn([
            'index' => 'total',
            'label' => 'Total',
            'type' => 'decimal',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return fn_convert_currency($row->total ?? 0, $row->currency);
            }
        ]);

        $orderStatuses = fn_get_order_statuses('O');

        $this->addColumn([
            'index' => 'order_status',
            'label' => 'Order Status',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $orderStatuses->map(function ($status) {
                return [
                    'label' => $status->name,
                    'value' => $status->status_code
                ];
            })->values()->toArray(),
            'closure' => function ($row) {
                $statusCode = $row->order_status;
                $status = fn_get_order_status($statusCode, 'O');
                
                if ($status) {
                    $color = $status->color ?? '#6B7280';
                    // Convert hex to RGB for background with opacity
                    $rgb = sscanf($color, "#%02x%02x%02x");
                    $bgColor = "rgba({$rgb[0]}, {$rgb[1]}, {$rgb[2]}, 0.1)";
                    
                    return "<span style='background-color: {$bgColor}; color: {$color}; border: 1px solid {$color};' class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'>
                        {$status->name}
                    </span>";
                } else {
                    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-300'>" . ucfirst($statusCode) . "</span>";
                }
            },
        ]);

        $this->addColumn([
            'index' => 'payment_status',
            'label' => 'Payment',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => array_map(function (PaymentStatus $status) {
                return [
                    'label' => $status->label(),
                    'value' => $status->value,
                ];
            }, PaymentStatus::cases()),
            'sortable' => true,
            'closure' => function ($row) {
                $status = PaymentStatus::tryFrom($row->payment_status ?? '');
                $label = e($status?->label() ?? \Illuminate\Support\Str::headline($row->payment_status ?? 'Unknown'));
                $style = $status?->class() ?? 'background-color: #6B7280; color: #ffffff;';

                return "<span style='{$style}' class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'>{$label}</span>";
            },
        ]);

        $this->addColumn([
            'index' => 'payment_method',
            'label' => 'Payment Method',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'sortable' => false,
            'filterable_options' => DB::table('payment_methods')
                ->select('code', 'name')
                ->get()
                ->map(function ($method) {
                    return [
                        'label' => $method->name,
                        'value' => $method->code
                    ];
                })->values()->toArray(),
            'closure' => function ($row) {
                return  '<span style="background-color: #f3f4f6; color: #1f2937; border: 1px solid #d1d5db;" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">'
                    . fn_get_payment_method_code($row->payment_method) ?? 'N/A'
                    . '</span>';
            }
        ]);
        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Order Date',
            'type' => 'date',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true,
        ]);

        $this->addFilter('order_number', 'orders.order_number');
        $this->addFilter('customer_name_search', 'customers.name');
        $this->addFilter('user_name_search', 'users.name');
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('admin.orders.show')) {
            $this->addAction([
                'icon' => 'visibility',
                'title' => 'View Detail',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.orders.show', $row->id);
                },
            ]);
        }

        // if (bouncer()->hasPermission('admin.transactions.create')) {
        //     $this->addAction([
        //         'icon' => 'add',
        //         'title' => 'Create Transaction',
        //         'method' => 'GET',
        //         'url' => function ($row) {
        //             if (in_array($row->payment_status, [PaymentStatus::COMPLETED->value, PaymentStatus::REFUNDED->value, PaymentStatus::CANCELLED->value, PaymentStatus::FAILED->value])) {
        //                 return '#';
        //             }
        //             return route('admin.transactions.create', ['id' => $row->id]);
        //         },
        //     ]);
        // }

        if (bouncer()->hasPermission('admin.orders.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.orders.destroy', $row->id);
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
        if (bouncer()->hasPermission('admin.orders.create')) {
            // $this->addMassAction([
            //     'icon' => 'add',
            //     'title' => 'New Order',
            //     'method' => 'GET',
            //     'action' => 'text-amber-100 bg-primary-100',
            //     'url' => 'admin.orders.create',
            // ]);
        }

        // if (bouncer()->hasPermission('admin.orders.bulk-delete')) {
        //     $this->addMassAction([
        //         'icon' => 'delete',
        //         'title' => 'Bulk Delete',
        //         'method' => 'POST',
        //         'action' => 'text-red-600 bg-red-100',
        //         'url' => 'admin.orders.bulk-delete',
        //     ]);
        // }

        $orderStatuses = fn_get_order_statuses('O');

        // if (bouncer()->hasPermission('admin.orders.toggle-status')) {
        //     $this->addMassAction([
        //         'icon' => '',
        //         'title' => 'Update Status',
        //         'method' => 'POST',
        //         'action' => 'bg-gray-500',
        //         'url' => 'admin.orders.toggle-status',
        //         'options' => $orderStatuses->map(function ($status) {
        //             return [
        //                 'label' => $status->name,
        //                 'value' => $status->status_code
        //             ];
        //         })->values()->toArray(),
        //     ]);
        // }
    }
}
