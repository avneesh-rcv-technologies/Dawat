<?php

namespace Modules\MobileConfiguration\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class MobileBannerGrid extends DataGrid
{
    protected $primaryColumn = "id";
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';

    public function prepareQueryBuilder()
    {
        return DB::table('mobile_banners')
            ->select(
                'id',
                'name',
                'type',
                'type_value',
                'status',
                'position',
                'created_at'
            );
    }

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
            'index' => 'name',
            'label' => 'Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'type',
            'label' => 'Type',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Product', 'value' => 'product'],
                ['label' => 'Category', 'value' => 'category'],
                ['label' => 'External Link', 'value' => 'external_link'],
                ['label' => 'No Link', 'value' => 'no_link'],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                $badges = [
                    'product' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Product</span>',
                    'category' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Category</span>',
                    'external_link' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">External Link</span>',
                    'no_link' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">No Link</span>',
                ];
                return $badges[$row->type] ?? $row->type;
            },
        ]);

        $this->addColumn([
            'index' => 'type_value',
            'label' => 'Link',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => false,
            'closure' => function ($row) {
                if ($row->type === 'no_link' || !$row->type_value) {
                    return '-';
                }
                return $row->type_value;
            },
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Active', 'value' => 1],
                ['label' => 'Inactive', 'value' => 0],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                return $row->status
                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>'
                    : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'position',
            'label' => 'Position',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Created At',
            'type' => 'date',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true,
        ]);
    }

    public function prepareActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('mobileconfiguration.banners.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
               
                'url' => function ($row) {
                    return route('mobileconfiguration.banners.edit', $row->id);
                },
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('mobileconfiguration.banners.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('mobileconfiguration.banners.destroy', $row->id);
                },
            ]);
        }
    }

    public function prepareMassActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('mobileconfiguration.banners.bulk-delete')) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => 'mobileconfiguration.banners.bulk-delete',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('mobileconfiguration.banners.toggle-status')) {
            $this->addMassAction([
                'icon' => '',
                'title' => 'Status',
                'method' => 'POST',
                'url' => 'mobileconfiguration.banners.toggle-status',
                'action' => 'bg-gray-500',
                'options' => [
                    ['label' => 'Active', 'value' => 1],
                    ['label' => 'Inactive', 'value' => 0],
                ],
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('mobileconfiguration.banners.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Banner',
               
                'method' => 'GET',
                'action' => 'text-white bg-blue-500',
                'url' => 'mobileconfiguration.banners.create',
            ]);
        }
    }
}