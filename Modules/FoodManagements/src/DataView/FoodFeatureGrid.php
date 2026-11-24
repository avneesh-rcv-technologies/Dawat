<?php

namespace Modules\FoodManagements\DataView;

use Illuminate\Support\Facades\DB;
use Modules\DataView\DataGrid;

class FoodFeatureGrid extends DataGrid
{
    protected $primaryColumn = 'feature_id';
    protected $itemsPerPage = 10;
    protected $sortOrder = 'asc';

    public function prepareQueryBuilder()
    {
        $ownerRestaurantId = $this->ownerRestaurantId();

        return DB::table('food_features as pf')
            ->leftJoin('restaurants as fr', 'fr.id', '=', 'pf.restaurant_id')
            ->leftJoin('food_feature_descriptions as pfd', function ($join) {
                $join->on('pf.feature_id', '=', 'pfd.feature_id')
                    ->where('pfd.lang_code', '=', 'en');
            })
            ->leftJoin('food_features as pg', 'pf.parent_id', '=', 'pg.feature_id')
            ->leftJoin('food_feature_descriptions as pgd', function ($join) {
                $join->on('pg.feature_id', '=', 'pgd.feature_id')
                    ->where('pgd.lang_code', '=', 'en');
            })
            ->select(
                'pf.feature_id',
                'pf.status',
                'pfd.name as feature_name',
                'pgd.name as group_name',
                'fr.name as restaurant_name',
                DB::raw('pf.feature_id as id')
            )
            ->selectSub(function ($query) {
                $query->from('food_feature_categories as pfc')
                    ->join('food_categories as c', 'c.id', '=', 'pfc.category_id')
                    ->whereColumn('pfc.feature_id', 'pf.feature_id')
                    ->selectRaw("GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ')");
            }, 'categories')
            ->where('pf.feature_type', '!=', 'G')
            ->when($ownerRestaurantId, fn ($query) => $query->where('pf.restaurant_id', $ownerRestaurantId));

    }

    public function prepareColumns()
    {
        if (!$this->ownerRestaurantId()) {
            $this->addColumn([
                'index' => 'restaurant_name', 'label' => 'Restaurant', 'type' => 'string',
                'searchable' => false, 'filterable' => false, 'sortable' => true,
            ]);
        }

        $this->addColumn([
            'index' => 'feature_name',
            'label' => 'Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'group_name',
            'label' => 'Group',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'categories',
            'label' => 'Categories',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
            'closure' => function ($row) {
                $categories = trim((string) ($row->categories ?? ''));
                return '<div style="white-space: normal; word-break: break-word; max-width: 26rem; line-height: 1.35;">' . ($categories !== '' ? e($categories) : 'All Categories') . '</div>';
            },
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
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

        $this->addFilter('feature_name', 'pfd.name');
        $this->addFilter('group_name', 'pgd.name');
        $this->addFilter('status', 'pf.status');
    }

    public function prepareActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.foodmanagements.features.edit')) {
            $this->addAction([
                'icon' => 'edit', 'title' => 'Edit', 'method' => 'GET', 'is_popup' => false,
                'url' => fn ($row) => route('admin.foodmanagements.features.edit', $row->feature_id),
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.foodmanagements.features.destroy')) {
            $this->addAction([
                'icon' => 'delete', 'title' => 'Delete', 'method' => 'DELETE',
                'url' => fn ($row) => route('admin.foodmanagements.features.destroy', $row->feature_id),
            ]);
        }
    }

    public function prepareMassActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.foodmanagements.features.bulk-delete')) {
            $this->addMassAction(['icon' => 'delete', 'title' => 'Bulk Delete', 'method' => 'POST', 'action' => 'text-red-600 bg-red-100', 'url' => 'admin.foodmanagements.features.bulk-delete']);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.foodmanagements.features.create')) {
            $this->addMassAction(['icon' => 'add', 'title' => 'Create Feature', 'method' => 'GET', 'is_popup' => false, 'action' => 'text-amber-100 bg-primary-100', 'url' => 'admin.foodmanagements.features.create']);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.foodmanagements.features.group_index')) {
            $this->addMassAction(['icon' => 'group_work', 'title' => 'Feature Groups', 'method' => 'GET', 'action' => 'text-red-600 bg-red-100', 'url' => 'admin.foodmanagements.features.group_index']);
        }
    }

    /** Restaurant owners only see their own restaurant's features. */
    private function ownerRestaurantId(): ?int
    {
        $admin = auth('admin')->user();
        if (!$admin || !app(\Modules\Restaurant\Services\RestaurantOwnerRoleResolver::class)->isOwner($admin)) {
            return null;
        }

        return (int) \Modules\Restaurant\Models\Restaurant::where('admin_id', $admin->id)->value('id') ?: -1;
    }
}
