<?php

namespace Modules\FoodManagements\DataView;

use Illuminate\Support\Facades\DB;
use Modules\DataView\DataGrid;

class FoodFeatureGroupGrid extends DataGrid
{
    protected $primaryColumn = 'feature_id';
    protected $itemsPerPage = 10;
    protected $sortOrder = 'asc';

    public function prepareQueryBuilder()
    {
        return DB::table('food_features as pf')
            ->leftJoin('food_feature_descriptions as pfd', function ($join) {
                $join->on('pf.feature_id', '=', 'pfd.feature_id')
                    ->where('pfd.lang_code', '=', 'en');
            })
            ->select(
                'pf.feature_id',
                'pf.status',
                'pfd.name as group_name',
                DB::raw('pf.feature_id as id')
            )
            ->selectSub(function ($query) {
                $query->from('food_features as child_pf')
                    ->leftJoin('food_feature_descriptions as child_pfd', function ($join) {
                        $join->on('child_pf.feature_id', '=', 'child_pfd.feature_id')
                            ->where('child_pfd.lang_code', '=', 'en');
                    })
                    ->whereColumn('child_pf.parent_id', 'pf.feature_id')
                    ->where('child_pf.feature_type', '!=', 'G')
                    ->selectRaw("GROUP_CONCAT(DISTINCT NULLIF(child_pfd.name, '') ORDER BY child_pfd.name SEPARATOR ', ')");
            }, 'children_features')
            ->selectSub(function ($query) {
                $query->from('food_feature_categories as pfc')
                    ->join('food_categories as c', 'c.id', '=', 'pfc.category_id')
                    ->whereColumn('pfc.feature_id', 'pf.feature_id')
                    ->selectRaw("GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ')");
            }, 'categories')
            ->where('pf.feature_type', '=', 'G');
    }

    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'group_name', 'label' => 'Name', 'type' => 'string',
            'searchable' => true, 'filterable' => true, 'sortable' => true,
            'closure' => function ($row) {
                $name = trim((string) ($row->group_name ?? ''));
                return '<div style="white-space: normal; word-break: break-word; max-width: 22rem; line-height: 1.35;">' . ($name !== '' ? e($name) : '—') . '</div>';
            },
        ]);

        $this->addColumn([
            'index' => 'children_features', 'label' => 'Features', 'type' => 'string',
            'searchable' => false, 'filterable' => false, 'sortable' => false,
            'closure' => function ($row) {
                $children = trim((string) ($row->children_features ?? ''));
                return '<div style="white-space: normal; word-break: break-word; max-width: 26rem; line-height: 1.35;">' . ($children !== '' ? e($children) : '—') . '</div>';
            },
        ]);

        $this->addColumn([
            'index' => 'categories', 'label' => 'Categories', 'type' => 'string',
            'searchable' => false, 'filterable' => false, 'sortable' => false,
            'closure' => function ($row) {
                $categories = trim((string) ($row->categories ?? ''));
                return '<div style="white-space: normal; word-break: break-word; max-width: 26rem; line-height: 1.35;">' . ($categories !== '' ? e($categories) : 'All Categories') . '</div>';
            },
        ]);

        $this->addColumn([
            'index' => 'status', 'label' => 'Status', 'type' => 'string',
            'searchable' => false, 'filterable' => true, 'sortable' => true,
            'closure' => function ($row) {
                $statusCode = strtoupper((string) ($row->status ?? ''));
                [$label, $class] = match ($statusCode) {
                    'A' => ['Active', 'bg-green-100 text-green-800'],
                    'H' => ['Hidden', 'bg-yellow-100 text-yellow-800'],
                    'D' => ['Disabled', 'bg-red-100 text-red-800'],
                    default => [$statusCode !== '' ? $statusCode : 'Unknown', 'bg-gray-100 text-gray-800'],
                };
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $class . '">' . e($label) . '</span>';
            },
        ]);

        $this->addFilter('group_name', 'pfd.name');
        $this->addFilter('status', 'pf.status');
    }

    public function prepareActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.foodmanagements.features.edit')) {
            $this->addAction(['icon' => 'edit', 'title' => 'Edit', 'method' => 'GET', 'is_popup' => false, 'url' => fn ($row) => route('admin.foodmanagements.features.group_edit', $row->feature_id)]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.foodmanagements.features.destroy')) {
            $this->addAction(['icon' => 'delete', 'title' => 'Delete', 'method' => 'DELETE', 'url' => fn ($row) => route('admin.foodmanagements.features.group_destroy', $row->feature_id)]);
        }
    }

    public function prepareMassActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.foodmanagements.features.bulk-delete')) {
            $this->addMassAction(['icon' => 'delete', 'title' => 'Bulk Delete', 'method' => 'POST', 'action' => 'text-red-600 bg-red-100', 'url' => 'admin.foodmanagements.features.group_bulk_delete']);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.foodmanagements.features.create')) {
            $this->addMassAction(['icon' => 'add', 'title' => 'Create Group', 'method' => 'GET', 'is_popup' => false, 'action' => 'text-amber-100 bg-primary-100', 'url' => 'admin.foodmanagements.features.group_create']);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.foodmanagements.features.index')) {
            $this->addMassAction(['icon' => 'list', 'title' => 'All Features', 'method' => 'GET', 'action' => 'text-blue-600 bg-blue-100', 'url' => 'admin.foodmanagements.features.index']);
        }
    }
}
