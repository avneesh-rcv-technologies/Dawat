<?php

namespace Modules\FoodManagements\DataView;

use Illuminate\Support\Facades\DB;
use Modules\DataView\DataGrid;

class FoodItemsGrid extends DataGrid
{
    protected $primaryColumn = 'id';
    protected $itemsPerPage  = 10;
    protected $sortOrder     = 'desc';

 


    public function prepareQueryBuilder()
    {
        $query = DB::table('food_items')
            ->select(
                'food_items.id',
                'food_items.name',
                'food_items.sale_price',
                'food_items.list_price',
                'food_items.in_stock',
                'food_items.quantity_type',
                'food_items.quantity',
                'food_items.is_available',
                'food_items.is_active',
                'food_items.created_at'
            );

        // Filter by restaurant ownership (no hardcoded role IDs)
        $currentAdmin = auth('admin')->user();

      if ($currentAdmin) {
    $restaurant = \Modules\Restaurant\Models\Restaurant::where(
        'admin_id',
        $currentAdmin->id
    )->first();

    $resId = request()->route('id');

    $restaurantId = $restaurant?->id ?? $resId;

    $query
        ->leftJoin(
            'food_category_items',
            'food_items.id',
            '=',
            'food_category_items.food_item_id'
        )
        ->leftJoin(
            'food_categories',
            'food_category_items.food_category_id',
            '=',
            'food_categories.id'
        )
        ->where(function ($q) use ($restaurantId) {
            $q->where('food_categories.restaurant_id', $restaurantId)
              ->orWhere(function ($q) use ($restaurantId) {
                  $q->whereNull('food_category_items.food_item_id')
                    ->where('food_items.restaurant_id', $restaurantId);
              });
        })
        ->select('food_items.*')
        ->distinct();
}

        return $query;
    }




    /**
     * Define table columns.
     */
    public function prepareColumns()
    {
        // ID (hidden)
        $this->addColumn([
            'index'      => 'id',
            'label'      => 'ID',
            'type'       => 'integer',
            'sortable'   => true,
            'visibility' => false,
        ]);

        // Name
        $this->addColumn([
            'index'      => 'name',
            'label'      => 'Food Item',
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
        ]);



        // Sale Price
        $this->addColumn([
            'index'    => 'sale_price',
            'label'    => 'Price',
            'type'     => 'decimal',
            'sortable' => true,
            'closure'  => fn($row) => '<span class="inline-block whitespace-nowrap">' . number_format((float) $row->sale_price, 2) . '</span>',
        ]);

        // List Price
        $this->addColumn([
            'index'    => 'list_price',
            'label'    => 'MRP',
            'type'     => 'decimal',
            'sortable' => true,
            'closure'  => fn($row) => $row->list_price !== null
                ? '<span class="inline-block whitespace-nowrap">' . number_format((float) $row->list_price, 2) . '</span>'
                : '<span class="inline-block whitespace-nowrap">—</span>',
        ]);

        // In Stock
        // In Stock
        $this->addColumn([
            'index'      => 'in_stock',
            'label'      => 'Stock Status',
            'type'       => 'boolean',
            'sortable'   => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'In Stock', 'value' => 1],
                ['label' => 'Out of Stock', 'value' => 0],
            ],
            'closure' => fn($row) =>
            (int) $row->in_stock === 1
                ? '<span class="inline-flex whitespace-nowrap px-2.5 py-0.5 rounded-full text-xs bg-green-100 text-green-600">In Stock</span>'
                : '<span class="inline-flex whitespace-nowrap px-2.5 py-0.5 rounded-full text-xs bg-red-100 text-red-600">Out of Stock</span>',
        ]);

        // Quantity Type
        $this->addColumn([
            'index'      => 'quantity_type',
            'label'      => 'Type',
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Manual', 'value' => 'manual'],
                ['label' => 'Grouping', 'value' => 'grouping'],
                ['label' => 'Piece', 'value' => 'piece'],
            ],
            'closure' => fn($row) =>
            $row->quantity_type === 'manual'
                ? '<span class="inline-flex whitespace-nowrap px-2.5 py-0.5 rounded-full text-xs bg-blue-100 text-blue-600">Manual</span>'
                : ($row->quantity_type === 'piece'
                    ? '<span class="inline-flex whitespace-nowrap px-2.5 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-600">Piece</span>'
                    : '<span class="inline-flex whitespace-nowrap px-2.5 py-0.5 rounded-full text-xs bg-purple-100 text-purple-600">Grouping</span>'),
        ]);

        // Quantity
        $this->addColumn([
            'index'    => 'quantity',
            'label'    => 'Quantity',
            'type'     => 'integer',
            'sortable' => true,
            'closure' => fn($row) => '<span class="inline-block whitespace-nowrap">' . ($row->quantity ?
                ($row->quantity_type === 'grouping' ? $row->quantity . ' guests min' : $row->quantity . ' stock') :
                'N/A') . '</span>',
        ]);

        // Availability
        $this->addColumn([
            'index'      => 'is_available',
            'label'      => 'Available',
            'type'       => 'boolean',
            'sortable'   => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Yes', 'value' => 1],
                ['label' => 'No', 'value' => 0],
            ],
            'closure' => fn($row) =>
            $row->is_available
                ? '<span class="inline-flex whitespace-nowrap px-2.5 py-0.5 rounded-full text-xs bg-green-100 text-green-600">Yes</span>'
                : '<span class="inline-flex whitespace-nowrap px-2.5 py-0.5 rounded-full text-xs bg-red-100 text-red-600">No</span>',
        ]);

        // Status
        $this->addColumn([
            'index'      => 'is_active',
            'label'      => 'Status',
            'type'       => 'boolean',
            'sortable'   => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Active', 'value' => 1],
                ['label' => 'Inactive', 'value' => 0],
            ],
            'closure' => fn($row) =>
            $row->is_active
                ? '<span class="inline-flex whitespace-nowrap px-2.5 py-0.5 rounded-full text-xs bg-green-100 text-green-600">Active</span>'
                : '<span class="inline-flex whitespace-nowrap px-2.5 py-0.5 rounded-full text-xs bg-red-100 text-red-600">Inactive</span>',
        ]);

        // Created
        $this->addColumn([
            'index'      => 'created_at',
            'label'      => 'Created At',
            'type'       => 'date',
            'sortable'   => true,
            'filterable_type' => 'date_range',
        ]);

        $this->addFilter('name', 'food_items.name');
    }

    /**
     * Row actions.
     */
    public function prepareActions()
    {
        // Get restaurant ID from route if available
        $restaurantId = request()->route('id');

        if (bouncer()->hasPermission('admin.foodmanagements.food-items.show')) {
            $this->addAction([
                'icon'   => 'visibility',
                'title'  => 'View Details',
                'method' => 'GET',
                'url' => fn($row) =>
                route('admin.foodmanagements.food-items.show', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.food-items.edit')) {
            $this->addAction([
                'icon'   => 'edit',
                'title'  => 'Edit',
                'method' => 'GET',
                'url' => fn($row) => $restaurantId
                    ? route('admin.foodmanagements.food-items.edit', [
                        'food_item' => $row->id,
                        'restaurant_id' => $restaurantId,
                        'from_restaurant' => 1
                    ])
                    : route('admin.foodmanagements.food-items.edit', ['food_item' => $row->id]),
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.food-items.destroy')) {
            $this->addAction([
                'icon'   => 'delete',
                'title'  => 'Delete',
                'method' => 'DELETE',
                'url' => fn($row) =>
                route('admin.foodmanagements.food-items.destroy', $row->id),
            ]);
        }
    }

    /**
     * Bulk actions.
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('admin.foodmanagements.food-items.bulk-delete')) {
            $this->addMassAction([
                'icon'   => 'delete',
                'title'  => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'bg-red-600 text-white',
                'url'    => 'admin.foodmanagements.food-items.bulk-delete',
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.food-items.toggle-status')) {
            $this->addMassAction([
                'icon'   => 'sync',
                'title'  => 'Change Status',
                'method' => 'POST',
                'url'    => 'admin.foodmanagements.food-items.toggle-status',
                'action' => 'bg-gray-600 text-white',
                'options' => [
                    ['label' => 'Active', 'value' => 1],
                    ['label' => 'Inactive', 'value' => 0],
                ],
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.food-items.create')) {
            $this->addMassAction([
                'icon'   => 'add',
                'title'  => 'Create Food Item',
                'method' => 'GET',
                'is_popup' => false,
                'action' => 'bg-blue-600 text-white',
                'url'    => 'admin.foodmanagements.food-items.create',
            ]);
        }
    }
}
