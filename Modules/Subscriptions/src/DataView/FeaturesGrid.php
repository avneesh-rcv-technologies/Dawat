<?php

namespace Modules\Subscriptions\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class FeaturesGrid extends DataGrid
{
    protected $primaryColumn = "id";

    protected $itemsPerPage = 10;

    protected $sortOrder = 'asc';

    protected $sortColumn = 'sort_order';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('features')
            ->select(
                'id',
                'name',
                'display_name',
                'type',
                'unit',
                'default_value',
                'group',
                'sort_order',
                'is_active',
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
            'index' => 'name',
            'label' => 'Name',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return '<div class="flex flex-col">'
                    . '<span class="font-medium text-gray-900">' . e($row->name) . '</span>'
                    . '<span class="text-xs text-gray-500">' . e($row->display_name) . '</span>'
                    . '</div>';
            }
        ]);

        $this->addColumn([
            'index' => 'type',
            'label' => 'Type',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                $colors = [
                    'boolean' => 'bg-blue-100 text-blue-800',
                    'limit' => 'bg-green-100 text-green-800',
                    'package' => 'bg-purple-100 text-purple-800',
                ];

                $icons = [
                    'boolean' => 'toggle_on',
                    'limit' => 'speed',
                    'package' => 'inventory_2',
                ];

                $class = $colors[$row->type] ?? 'bg-gray-100 text-gray-800';
                $icon = $icons[$row->type] ?? 'label';

                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $class . '">'
                    . '<span class="material-icons-outlined text-xs mr-1">' . $icon . '</span>'
                    . ucfirst($row->type) . '</span>';
            }
        ]);

        $this->addColumn([
            'index' => 'default_value',
            'label' => 'Default Value',
            'type' => 'string',
            'searchable' => true,
            'sortable' => false,
            'closure' => function ($row) {
                if (!$row->default_value) {
                    return '<span class="text-gray-400">-</span>';
                }
                
                $value = $row->default_value;
                if ($row->unit) {
                    $value .= ' ' . $row->unit;
                }
                
                return '<span class="text-gray-700">' . e($value) . '</span>';
            }
        ]);

        $this->addColumn([
            'index' => 'group',
            'label' => 'Group',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                if (!$row->group) {
                    return '<span class="text-gray-400">Uncategorized</span>';
                }
                return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">'
                    . e($row->group) . '</span>';
            }
        ]);

        $this->addColumn([
            'index' => 'sort_order',
            'label' => 'Sort Order',
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="text-gray-600">' . $row->sort_order . '</span>';
            }
        ]);

        $this->addColumn([
            'index' => 'is_active',
            'label' => 'Status',
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->is_active) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">'
                        . '<span class="material-icons-outlined text-xs mr-1">check_circle</span>Active</span>';
                } else {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">'
                        . '<span class="material-icons-outlined text-xs mr-1">cancel</span>Inactive</span>';
                }
            }
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Created At',
            'type' => 'date',
            'searchable' => false,
            'sortable' => true,
        ]);
    }

    /**
     * Add actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.features.view')) {
            $this->addAction([
                'title' => 'View',
                'method' => 'GET',
                'icon' => 'open_in_new',
                'url' => function ($row) {
                    return route('subscriptions.features.show', $row->id);
                },
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.features.edit')) {
            $this->addAction([
                'title' => 'Edit',
                'method' => 'GET',
                'icon' => 'edit',
                'url' => function ($row) {
                    return route('subscriptions.features.edit', $row->id);
                },
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.features.destroy')) {
            $this->addAction([
                'title' => 'Delete',
                'method' => 'DELETE',
                'icon' => 'delete',
                'url' => function ($row) {
                    return route('subscriptions.features.destroy', $row->id);
                },
            ]);
        }
    }

    /**
     * Add mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.features.create')) {
            $this->addMassAction([
                'title' => 'Create Feature',
                'method' => 'GET',
                'action' => 'text-white bg-red-600',
                'url' => 'subscriptions.features.create'
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.features.destroy')) {
            $this->addMassAction([
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => 'subscriptions.features.massDestroy'
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.features.edit')) {
            $this->addMassAction([
                'title' => 'Bulk Activate',
                'method' => 'POST',
                'action' => 'text-white bg-green-600',
                'url' => 'subscriptions.features.massActivate'
            ]);

            $this->addMassAction([
                'title' => 'Bulk Deactivate',
                'method' => 'POST',
                'action' => 'text-white bg-gray-600',
                'url' => 'subscriptions.features.massDeactivate'
            ]);
        }
    }
}
