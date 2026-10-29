<?php

namespace Modules\MobileConfiguration\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class MobileCmsGrid extends DataGrid
{
    protected $primaryColumn = "id";
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';

    public function prepareQueryBuilder()
    {
        return DB::table('mobile_cms as mc')
            ->leftJoin('cms_page_settings as cp', 'mc.cms_page_id', '=', 'cp.id')
            ->select(
                'mc.id',
                'mc.name',
                'mc.cms_page_id',
                'mc.status',
                'mc.position',
                'mc.created_at',
                'cp.page_title',
                'cp.slug as page_slug'
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
            'index' => 'page_title',
            'label' => 'CMS Page',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return $row->page_title ?: 'Page not found';
            },
        ]);

        $this->addColumn([
            'index' => 'page_slug',
            'label' => 'Page Slug',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => false,
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
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('mobileconfiguration.cms.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',

                'url' => function ($row) {
                    return route('mobileconfiguration.cms.edit', $row->id);
                },
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('mobileconfiguration.cms.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('mobileconfiguration.cms.destroy', $row->id);
                },
            ]);
        }
    }

    public function prepareMassActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('mobileconfiguration.cms.bulk-delete')) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => 'mobileconfiguration.cms.bulk-delete',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('mobileconfiguration.cms.toggle-status')) {
            $this->addMassAction([
                'icon' => '',
                'title' => 'Status',
                'method' => 'POST',
                'url' => 'mobileconfiguration.cms.toggle-status',
                'action' => 'bg-gray-500',
                'options' => [
                    ['label' => 'Active', 'value' => 1],
                    ['label' => 'Inactive', 'value' => 0],
                ],
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('mobileconfiguration.cms.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Mobile CMS',

                'method' => 'GET',
                'action' => 'text-white bg-blue-500',
                'url' => 'mobileconfiguration.cms.create',
            ]);
        }
    }
}
