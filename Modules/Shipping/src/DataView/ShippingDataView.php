<?php

namespace Modules\Shipping\DataView;

use Modules\DataView\DataGrid;
use Modules\Shipping\Models\Shipping;
use Illuminate\Database\Eloquent\Builder;

class ShippingDataView implements DataGrid
{
    public function getQuery(): Builder
    {
        return Shipping::query()
            ->with(['shippingMethod'])
            ->select([
                'id',
                'order_id',
                'shipping_method_code',
                'tracking_number',
                'status',
                'cost',
                'currency',
                'estimated_delivery_at',
                'actual_delivery_at',
                'created_at',
                'updated_at'
            ]);
    }

    public function getColumns(): array
    {
        return [
            [
                'key' => 'id',
                'label' => 'ID',
                'sortable' => true,
                'searchable' => false,
            ],
            [
                'key' => 'order_id',
                'label' => 'Order ID',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'tracking_number',
                'label' => 'Tracking Number',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'shipping_method_code',
                'label' => 'Method',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'status',
                'label' => 'Status',
                'sortable' => true,
                'searchable' => true,
                'render' => function ($value) {
                    $statusColors = [
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'out_for_delivery' => 'info',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        'returned' => 'secondary',
                        'ready_for_pickup' => 'info',
                        'picked_up' => 'success',
                    ];
                    
                    $color = $statusColors[$value] ?? 'secondary';
                    return "<span class='badge badge-{$color}'>" . ucfirst(str_replace('_', ' ', $value)) . "</span>";
                }
            ],
            [
                'key' => 'cost',
                'label' => 'Cost',
                'sortable' => true,
                'searchable' => false,
                'render' => function ($value, $row) {
                    return $row->currency . ' ' . number_format($value, 2);
                }
            ],
            [
                'key' => 'estimated_delivery_at',
                'label' => 'Estimated Delivery',
                'sortable' => true,
                'searchable' => false,
                'render' => function ($value) {
                    return $value ? $value->format('Y-m-d H:i') : '-';
                }
            ],
            [
                'key' => 'actual_delivery_at',
                'label' => 'Actual Delivery',
                'sortable' => true,
                'searchable' => false,
                'render' => function ($value) {
                    return $value ? $value->format('Y-m-d H:i') : '-';
                }
            ],
            [
                'key' => 'created_at',
                'label' => 'Created',
                'sortable' => true,
                'searchable' => false,
                'render' => function ($value) {
                    return $value->format('Y-m-d H:i');
                }
            ],
        ];
    }

    public function getFilters(): array
    {
        return [
            [
                'key' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'options' => [
                    '' => 'All Statuses',
                    'pending' => 'Pending',
                    'processing' => 'Processing',
                    'shipped' => 'Shipped',
                    'out_for_delivery' => 'Out for Delivery',
                    'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled',
                    'returned' => 'Returned',
                    'ready_for_pickup' => 'Ready for Pickup',
                    'picked_up' => 'Picked Up',
                ],
            ],
            [
                'key' => 'shipping_method_code',
                'label' => 'Shipping Method',
                'type' => 'select',
                'options' => [
                    '' => 'All Methods',
                    'takeaway' => 'Takeaway',
                    'delivery' => 'Delivery',
                ],
            ],
            [
                'key' => 'date_range',
                'label' => 'Date Range',
                'type' => 'date_range',
                'apply' => function ($query, $value) {
                    if (!empty($value['start'])) {
                        $query->whereDate('created_at', '>=', $value['start']);
                    }
                    if (!empty($value['end'])) {
                        $query->whereDate('created_at', '<=', $value['end']);
                    }
                },
            ],
        ];
    }

    public function getActions(): array
    {
        return [
            [
                'key' => 'view',
                'label' => 'View',
                'icon' => 'eye',
                'url' => function ($row) {
                    return route('admin.shipping.show', $row->id);
                },
            ],
            [
                'key' => 'track',
                'label' => 'Track',
                'icon' => 'search',
                'url' => function ($row) {
                    return route('api.shipping.track', $row->tracking_number);
                },
            ],
        ];
    }

    public function getBulkActions(): array
    {
        return [
            [
                'key' => 'update_status',
                'label' => 'Update Status',
                'icon' => 'edit',
                'form' => [
                    [
                        'key' => 'status',
                        'label' => 'New Status',
                        'type' => 'select',
                        'required' => true,
                        'options' => [
                            'pending' => 'Pending',
                            'processing' => 'Processing',
                            'shipped' => 'Shipped',
                            'out_for_delivery' => 'Out for Delivery',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled',
                            'returned' => 'Returned',
                            'ready_for_pickup' => 'Ready for Pickup',
                            'picked_up' => 'Picked Up',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getDefaultSort(): array
    {
        return ['created_at', 'desc'];
    }

    public function getPerPageOptions(): array
    {
        return [10, 25, 50, 100];
    }
}