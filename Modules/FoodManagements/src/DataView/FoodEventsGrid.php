<?php

namespace Modules\FoodManagements\DataView;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DataView\DataGrid;

class FoodEventsGrid extends DataGrid
{
    protected $primaryColumn = "id";
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        return DB::table('food_events as e')
            ->leftJoin('food_event_items as ei', 'e.id', '=', 'ei.food_event_id')
            ->leftJoin('food_event_categories as ec', 'e.id', '=', 'ec.food_event_id')
            ->select(
                'e.id',
                'e.name',
                'e.slug',
                'e.description',
                'e.status',
                'e.created_at',
                DB::raw('COUNT(DISTINCT ei.food_item_id) as items_count'),
                DB::raw('COUNT(DISTINCT ec.food_category_id) as categories_count')
            )
            ->groupBy('e.id', 'e.name', 'e.slug', 'e.description', 'e.status', 'e.created_at');
    }

    /**
     * Define table columns.
     */
    public function prepareColumns()
    {
        // ID
        $this->addColumn([
            'index' => 'id',
            'label' => 'ID',
            'type' => 'integer',
            'sortable' => true,
            'visibility' => false,
        ]);

        // Name
        $this->addColumn([
            'index' => 'name',
            'label' => 'Event Name',
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        // Slug
        $this->addColumn([
            'index' => 'slug',
            'label' => 'Slug',
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
        ]);

        // Description
        $this->addColumn([
            'index' => 'description',
            'label' => 'Description',
            'type' => 'string',
            'searchable' => true,
            'closure' => function ($row) {
                return $row->description 
                    ? '<span class="text-gray-600">' . Str::limit($row->description, 50) . '</span>'
                    : '<span class="text-gray-400 italic">No description</span>';
            },
        ]);

        // Items Count
        $this->addColumn([
            'index' => 'items_count',
            'label' => 'Food Items',
            'type' => 'integer',
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-blue-100 text-blue-800">' 
                    . $row->items_count . ' items</span>';
            },
        ]);

        // Categories Count
        $this->addColumn([
            'index' => 'categories_count',
            'label' => 'Categories',
            'type' => 'integer',
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-purple-100 text-purple-800">' 
                    . $row->categories_count . ' categories</span>';
            },
        ]);

        // Status
        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'boolean',
            'sortable' => true,
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
    }

    /**
     * Prepare row actions.
     */
    public function prepareActions()
    {
      
        if (bouncer()->hasPermission('admin.foodmanagements.events.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'url' => fn($row) => route('admin.foodmanagements.events.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.events.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => fn($row) => route('admin.foodmanagements.events.destroy', $row->id),
            ]);
        }
    }

    /**
     * Prepare bulk actions.
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('admin.foodmanagements.events.bulk-delete')) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => 'admin.foodmanagements.events.bulk-delete',
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.events.toggle-status')) {
            $this->addMassAction([
                'icon' => 'sync',
                'title' => 'Change Status',
                'method' => 'POST',
                'url' => 'admin.foodmanagements.events.toggle-status',
                'action' => 'bg-gray-500 text-white',
                'options' => [
                    ['label' => 'Active', 'value' => 1],
                    ['label' => 'Inactive', 'value' => 0],
                ],
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.events.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Event',
                'is_popup' => false,
                'method' => 'GET',
                'action' => 'text-white bg-blue-500',
                'url' => 'admin.foodmanagements.events.create',
            ]);
        }
    }
}