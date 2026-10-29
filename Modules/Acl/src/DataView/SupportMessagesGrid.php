<?php

namespace Modules\Acl\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class SupportMessagesGrid extends DataGrid
{
    protected $primaryColumn = "id";

    protected $itemsPerPage = 10;

    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('support_messages')
            ->select(
                'id',
                'reference_number',
                'name',
                'email',
                'category',
                'subject',
                'customer_id',
                'order_id',
                'status',
                'created_at'
            );
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'id',
            'label' => 'ID',
            'type' => 'integer',
            'visibility' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'reference_number',
            'label' => 'Reference',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="font-mono text-sm font-semibold text-blue-600">' . $row->reference_number . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => 'Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'email',
            'label' => 'Email',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'category',
            'label' => 'Category',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'allow_multiple_values' => false,
            'filterable_options' => [
                ['label' => 'General Inquiry', 'value' => 'general_inquiry'],
                ['label' => 'Order Issue', 'value' => 'order_issue'],
                ['label' => 'Delivery Issue', 'value' => 'delivery_issue'],
                ['label' => 'Payment Issue', 'value' => 'payment_issue'],
                ['label' => 'Account Issue', 'value' => 'account_issue'],
                ['label' => 'Technical Issue', 'value' => 'technical_issue'],
                ['label' => 'Feedback', 'value' => 'feedback'],
                ['label' => 'Other', 'value' => 'other'],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                $categories = [
                    'general_inquiry' => 'General Inquiry',
                    'order_issue' => 'Order Issue',
                    'delivery_issue' => 'Delivery Issue',
                    'payment_issue' => 'Payment Issue',
                    'account_issue' => 'Account Issue',
                    'technical_issue' => 'Technical Issue',
                    'feedback' => 'Feedback',
                    'other' => 'Other',
                ];
                $label = $categories[$row->category] ?? ucfirst(str_replace('_', ' ', $row->category));
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">' . $label . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'subject',
            'label' => 'Subject',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="text-sm">' . \Illuminate\Support\Str::limit($row->subject, 50) . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'customer_id',
            'label' => 'Customer',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->customer_id) {
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Customer #' . $row->customer_id . '</span>';
                }
                return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Guest</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'order_id',
            'label' => 'Order',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->order_id) {
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">Order #' . $row->order_id . '</span>';
                }
                return '<span class="text-gray-400">-</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'allow_multiple_values' => false,
            'filterable_options' => [
                ['label' => 'New', 'value' => 'new'],
                ['label' => 'Read', 'value' => 'read'],
                ['label' => 'Replied', 'value' => 'replied'],
                ['label' => 'Closed', 'value' => 'closed'],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                $statusColors = [
                    'new' => 'bg-yellow-100 text-yellow-800',
                    'read' => 'bg-blue-100 text-blue-800',
                    'replied' => 'bg-green-100 text-green-800',
                    'closed' => 'bg-gray-100 text-gray-800',
                ];
                $color = $statusColors[$row->status] ?? 'bg-gray-100 text-gray-800';
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $color . '">' . ucfirst($row->status) . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Created At',
            'type' => 'date',
            'searchable' => true,
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
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('support.show')) {
            $this->addAction([
                'icon' => 'eye',
                'title' => 'View',
                'method' => 'GET',
                'is_popup' => true,
                'url' => function ($row) {
                    return route('support.show', $row->id);
                },
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('support.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('support.destroy', $row->id);
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
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('support.bulk-delete')) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => 'support.bulk-delete',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('support.update-status')) {
            $this->addMassAction([
                'icon' => '',
                'title' => 'Update Status',
                'method' => 'POST',
                'url' => 'support.update-status',
                'action' => 'bg-blue-500',
                'options' => [
                    ['label' => 'New', 'value' => 'new'],
                    ['label' => 'Read', 'value' => 'read'],
                    ['label' => 'Replied', 'value' => 'replied'],
                    ['label' => 'Closed', 'value' => 'closed'],
                ],
            ]);
        }
    }
}
