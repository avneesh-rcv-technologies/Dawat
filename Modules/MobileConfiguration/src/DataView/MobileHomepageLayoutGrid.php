<?php

namespace Modules\MobileConfiguration\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class MobileHomepageLayoutGrid extends DataGrid
{
    protected $primaryColumn = "id";
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';

    public function prepareQueryBuilder()
    {
        return DB::table('mobile_homepage_layouts')
            ->select(
                'id',
                'name',
                'slug',
                'description',
                'is_default',
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
            'index' => 'slug',
            'label' => 'Slug',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => true,
        ]);



        $this->addColumn([
            'index' => 'is_default',
            'label' => 'Default',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Yes', 'value' => 1],
                ['label' => 'No', 'value' => 0],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                return $row->is_default
                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Default</span>'
                    : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">No</span>';
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
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('mobileconfiguration.homepage-layouts.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('mobileconfiguration.homepage-layouts.edit', $row->id);
                },
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('mobileconfiguration.homepage-layouts.set-default')) {
            $this->addAction([
                'icon' => 'star',
                'title' => 'Set as Default',
                'method' => 'POST',
                'url' => function ($row) {
                    return route('mobileconfiguration.homepage-layouts.set-default', $row->id);
                },
                'condition' => function ($row) {
                    return !$row->is_default;
                },
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('mobileconfiguration.homepage-layouts.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('mobileconfiguration.homepage-layouts.destroy', $row->id);
                },
                'condition' => function ($row) {
                    return !$row->is_default; // Prevent deleting default layout
                },
            ]);
        }
    }

    public function prepareMassActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('mobileconfiguration.homepage-layouts.bulk-delete')) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => 'mobileconfiguration.homepage-layouts.bulk-delete',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('mobileconfiguration.homepage-layouts.toggle-status')) {
            $this->addMassAction([
                'icon' => '',
                'title' => 'Status',
                'method' => 'POST',
                'url' => 'mobileconfiguration.homepage-layouts.toggle-status',
                'action' => 'bg-gray-500',
                'options' => [
                    ['label' => 'Active', 'value' => 1],
                    ['label' => 'Inactive', 'value' => 0],
                ],
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('mobileconfiguration.homepage-layouts.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Layout',
                'method' => 'GET',
                'action' => 'text-white bg-blue-500',
                'url' => 'mobileconfiguration.homepage-layouts.create',
            ]);
        }
    }
}