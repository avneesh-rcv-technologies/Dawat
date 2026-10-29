<?php

namespace Modules\FoodManagements\DataView;

use Illuminate\Support\Facades\DB;
use Modules\DataView\DataGrid;

class FoodsCategoryGrid extends DataGrid
{
    protected $primaryColumn = "id";
    protected $itemsPerPage  = 10;
    protected $sortOrder     = 'desc';

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        $query = DB::table('food_categories as c')
            ->leftJoin('food_categories as p', 'c.parent_id', '=', 'p.id')
            ->leftJoin('restaurants as r', 'c.restaurant_id', '=', 'r.id')
            ->select(
                'c.id',
                'c.name as category_name',
                'c.slug',
                'p.name as parent_name',
                'r.name as restaurant_name',
                'c.food_type',
                'c.status',
                'c.created_at'
            );

        $currentAdmin = auth('admin')->user();

        if ($currentAdmin) {
            $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $currentAdmin->id)->first();
            
            // Check session for restaurant context (when viewing from restaurant page)
            $sessionRestaurantId = session('category_restaurant_id');
            
            if ($restaurant) {
                // Restaurant admin: show only their categories
                $query->where('c.restaurant_id', $restaurant->id);
            } elseif ($sessionRestaurantId) {
                // Super admin viewing from restaurant context
                $query->where('c.restaurant_id', $sessionRestaurantId);
            }
            // Super admin (no restaurant context): show all categories
        }
        $this->addFilter('category_name', 'c.name');
        $this->addFilter('food_type', 'c.food_type');
        $this->addFilter('status', 'c.status');

        return $query;
    }

    /**
     * Define table columns.
     */
    public function prepareColumns()
    {
        // ID
        $this->addColumn([
            'index'      => 'id',
            'label'      => 'ID',
            'type'       => 'integer',
            'sortable'   => true,
            'visibility' => false,
        ]);

        // Name
        $this->addColumn([
            'index'      => 'category_name',
            'label'      => 'Category Name',
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => false,
            'filterable' => true,
        ]);

        // Slug
        $this->addColumn([
            'index'      => 'slug',
            'label'      => 'Slug',
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => false,
        ]);

        // Restaurant
        $this->addColumn([
            'index'      => 'restaurant_name',
            'label'      => 'Restaurant',
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => false,
            'closure'    => fn($row) => $row->restaurant_name
                ? '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">' . e($row->restaurant_name) . '</span>'
                : '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">—</span>',
        ]);

        $this->addColumn([
            'index'      => 'category_name_search',
            'label'      => 'Category Search',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable'   => false,
            'visibility' => false,
        ]);

        $this->addColumn([
            'index'      => 'slug_search',
            'label'      => 'Slug Search',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable'   => false,
            'visibility' => false,
        ]);

        $this->addColumn([
            'index'      => 'restaurant_name_search',
            'label'      => 'Restaurant Search',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable'   => false,
            'visibility' => false,
        ]);

        // Parent Category
        // $this->addColumn([
        //     'index' => 'parent_name',
        //     'label' => 'Parent Category',
        //     'type'  => 'string',
        //     'closure' => fn($row) =>
        //     $row->parent_name
        //         ? '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-green-100 text-green-600">' . $row->parent_name . '</span>'
        //         : '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-red-200 text-red-600">Parent Category</span>',
        // ]);

        // Food Type
        $this->addColumn([
            'index'    => 'food_type',
            'label'    => 'Food Type',
            'type'     => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'All', 'value' => 'all'],
                ['label' => 'Veg', 'value' => 'veg'],
                ['label' => 'Non-Veg', 'value' => 'non_veg'],
                ['label' => 'Vegan', 'value' => 'vegan'],
            ],
            'closure' => function ($row) {
                return match ($row->food_type) {
                    'veg'     => '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-green-200 text-green-800">Veg</span>',
                    'non_veg' => '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-red-200 text-red-800">Non-Veg</span>',
                    'vegan'   => '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-emerald-200 text-emerald-800">Vegan</span>',
                    'all'   => '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-orange-200 text-orange-800">All</span>',
                };
            },
        ]);

        // Status
        $this->addColumn([
            'index'      => 'status',
            'label'      => 'Status',
            'type'       => 'boolean',
            'sortable'   => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Active', 'value' => 1],
                ['label' => 'Inactive', 'value' => 0],
            ],
            'closure' => function ($row) {
                return $row->status
                    ? '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-green-100 text-green-800">Active</span>'
                    : '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-red-100 text-red-800">Inactive</span>';
            },
        ]);

        $this->addFilter('category_name_search', 'c.name');
        $this->addFilter('slug_search', 'c.slug');
        $this->addFilter('restaurant_name_search', 'r.name');
    }

    /**
     * Prepare row actions.
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('admin.foodmanagements.category.edit')) {
            $this->addAction([
                'icon'      => 'edit',
                'title'     => 'Edit',
                'method'    => 'GET',
                // 'is_popup'  => true,
                'url' => fn($row) =>
                route('admin.foodmanagements.category.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.category.destroy')) {
            $this->addAction([
                'icon'   => 'delete',
                'title'  => 'Delete',
                'method' => 'DELETE',
                'url' => fn($row) =>
                route('admin.foodmanagements.category.destroy', $row->id),
            ]);
        }
    }

    /**
     * Prepare bulk actions.
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('admin.foodmanagements.category.bulk-delete')) {
            $this->addMassAction([
                'icon'   => 'delete',
                'title'  => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url'    => 'admin.foodmanagements.category.bulk-delete',
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.category.toggle-status')) {
            $this->addMassAction([
                'icon'   => 'sync',
                'title'  => 'Change Status',
                'method' => 'POST',
                'url'    => 'admin.foodmanagements.category.toggle-status',
                'action' => 'bg-gray-500 text-white',
                'options' => [
                    ['label' => 'Active', 'value' => 1],
                    ['label' => 'Inactive', 'value' => 0],
                ],
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.category.create')) {
            $this->addMassAction([
                'icon'     => 'add',
                'title'    => 'Create Category',
                'is_popup' => false,
                'method'   => 'GET',
                'action'   => 'text-white bg-blue-500',
                'url'      => 'admin.foodmanagements.category.create',
            ]);
        }
    }
}
