<?php

namespace Modules\Reports\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Acl\Models\Admin;

class ReportsGrid extends DataGrid
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
        $guard = Auth::getDefaultDriver();

        $query = DB::table('reports')
            ->leftJoin('admins', function ($join) {
                $join->on('reports.created_by_id', '=', 'admins.id')
                    ->where('reports.created_by_type', '=', Admin::class);
            })
            ->select(
                'reports.id',
                'reports.name',
                'reports.slug',
                'reports.data_source',
                'reports.chart_type',
                'reports.is_active',
                'reports.is_public',
                'reports.execution_count',
                'reports.last_executed_at',
                'reports.created_at',
                'reports.deleted_at',
                'admins.name as creator_name'
            );

        return $query;
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
            'label' => 'Report Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                $badge = '';
                if (!$row->is_active) {
                    $badge = ' <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>';
                }
                return '<a href="' . route('reports.show', $row->id) . '" class="text-blue-600 hover:text-blue-800 font-medium">' . 
                       e($row->name) . '</a>' . $badge;
            },
        ]);

        $this->addColumn([
            'index' => 'data_source',
            'label' => 'Data Source',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">' 
                    . e($row->data_source) . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'chart_type',
            'label' => 'Chart Type',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'allow_multiple_values' => true,
            'filterable_options' => [
                ['label' => 'Table', 'value' => 'table'],
                ['label' => 'Stat', 'value' => 'stat'],
                ['label' => 'Comparison', 'value' => 'comparison'],
                ['label' => 'Bar Chart', 'value' => 'bar'],
                ['label' => 'Line Chart', 'value' => 'line'],
                ['label' => 'Pie Chart', 'value' => 'pie'],
                ['label' => 'Area Chart', 'value' => 'area'],
                ['label' => 'Donut Chart', 'value' => 'donut'],
                ['label' => 'Scatter Plot', 'value' => 'scatter'],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                $colors = [
                    'table' => 'gray',
                    'bar' => 'blue',
                    'stat' => 'teal',
                    'line' => 'green',
                    'pie' => 'yellow',
                    'area' => 'indigo',
                    'donut' => 'pink',
                    'scatter' => 'red',
                ];
                $color = $colors[$row->chart_type] ?? 'gray';
                
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-' . $color . '-100 text-' . $color . '-800">' . 
                       e(ucfirst($row->chart_type)) . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'creator_name',
            'label' => 'Created By',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return e($row->creator_name ?? 'Unknown');
            },
        ]);

        $this->addColumn([
            'index' => 'execution_count',
            'label' => 'Executions',
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="text-gray-700 font-medium">' . number_format($row->execution_count) . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'last_executed_at',
            'label' => 'Last Executed',
            'type' => 'date',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->last_executed_at) {
                    $date = \Carbon\Carbon::parse($row->last_executed_at);
                    return '<span class="text-sm text-gray-600" title="' . $date->format('Y-m-d H:i:s') . '">' . 
                           $date->diffForHumans() . '</span>';
                }
                return '<span class="text-sm text-gray-400 italic">Never</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'is_public',
            'label' => 'Access',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'allow_multiple_values' => false,
            'filterable_options' => [
                ['label' => 'Public', 'value' => 1],
                ['label' => 'Private', 'value' => 0],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                return $row->is_public
                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Public</span>'
                    : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Private</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'deleted_at',
            'label' => 'Status',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'allow_multiple_values' => false,
            'filterable_options' => [
                ['label' => 'Active', 'value' => 'active'],
                ['label' => 'Trashed', 'value' => 'trashed'],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->deleted_at) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Trashed</span>';
                }
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>';
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

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        $guard = \Illuminate\Support\Facades\Auth::getDefaultDriver();
        
        // Actions for active reports
        if (bouncer($guard)->hasPermission('reports.view')) {
            $this->addAction([
                'icon' => 'open_in_new',
                'title' => 'View',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('reports.show', $row->id);
                },
            ]);
        }

        if (bouncer($guard)->hasPermission('reports.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                // 'is_popup' => true,
                'url' => function ($row) {
                    return route('reports.edit', $row->id);
                },
            ]);
        }

        if (bouncer($guard)->hasPermission('reports.execute')) {
            $this->addAction([
                'icon' => 'refresh',
                'title' => 'Regenerate',
                'method' => 'POST',
                'url' => function ($row) {
                    return route('reports.execute', $row->id);
                },
            ]);

            $this->addAction([
                'icon' => 'send',
                'title' => 'Send Now',
                'method' => 'POST',
                'url' => function ($row) {
                    return route('reports.send-now', $row->id);
                },
                'visible' => function ($row) {
                    return !$row->deleted_at;
                },
            ]);
        }

        if (bouncer($guard)->hasPermission('reports.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('reports.destroy', $row->id);
                },
            ]);
        }

        if (bouncer($guard)->hasPermission('reports.duplicate')) {
            $this->addAction([
                'icon' => 'copy',
                'title' => 'Duplicate',
                'method' => 'POST',
                'url' =>  function ($row) {
                    return route('reports.duplicate', $row->id);
                },
            ]);
        }

        if (bouncer($guard)->hasPermission('reports.restore')) {
            $this->addAction([
                'icon' => 'restore',
                'title' => 'Restore',
                'method' => 'POST',
                'url' => function ($row) {
                    return route('reports.restore', $row->id);
                },
                'visible' => function ($row) {
                    return $row->deleted_at;
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
        $guard = \Illuminate\Support\Facades\Auth::getDefaultDriver();
        
        if (bouncer($guard)->hasPermission('reports.destroy')) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'POST',
                'action' => 'text-amber-100 bg-primary-100',
                'url' => 'reports.bulk-delete'
            ]);
        }

        if (bouncer($guard)->hasPermission('reports.destroy')) {
            $this->addMassAction([
                'icon' => 'restore',
                'title' => 'Restore',
                'method' => 'POST',
                'action' => 'text-blue-100 bg-blue-100',
                'url' => 'reports.bulk-restore'
            ]);
        }

        if (bouncer($guard)->hasPermission('reports.destroy')) {
            $this->addMassAction([
                'icon' => 'delete_forever',
                'title' => 'Force Delete',
                'method' => 'POST',
                'action' => 'text-red-100 bg-red-100',
                'url' => 'reports.bulk-force-delete'
            ]);
        }
    }
}
