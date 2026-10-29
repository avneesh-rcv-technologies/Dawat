<?php

namespace Modules\FoodManagements\DataView;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DataView\DataGrid;

class FeaturedItemsGrid extends DataGrid
{
    protected $primaryColumn = "id";
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        return DB::table('featured_items as fi')
            ->select(
                'fi.id',
                'fi.data',
                'fi.status',
                'fi.created_at',
                DB::raw('JSON_UNQUOTE(JSON_EXTRACT(fi.data, "$.title")) as title'),
                DB::raw('JSON_UNQUOTE(JSON_EXTRACT(fi.data, "$.description")) as description'),
                DB::raw('JSON_UNQUOTE(JSON_EXTRACT(fi.data, "$.featured_type")) as featured_type'),
                DB::raw('JSON_UNQUOTE(JSON_EXTRACT(fi.data, "$.selection_method")) as selection_method')
            );
    }

    /**
     * Define table columns.
     */
    public function prepareColumns()
    {
        // ID (hidden)
        $this->addColumn([
            'index' => 'id',
            'label' => 'ID',
            'type' => 'integer',
            'sortable' => true,
            'visibility' => false,
        ]);

        // Title
        $this->addColumn([
            'index' => 'title',
            'label' => 'Title',
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        // Featured Type
        $this->addColumn([
            'index' => 'featured_type',
            'label' => 'Type',
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Food Items', 'value' => 'food_item'],
                ['label' => 'Categories', 'value' => 'category'],
            ],
            'closure' => function ($row) {
                if ($row->featured_type === 'food_item') {
                    return '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-blue-100 text-blue-800">Food Items</span>';
                } elseif ($row->featured_type === 'category') {
                    return '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-purple-100 text-purple-800">Categories</span>';
                } else {
                    return '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-gray-100 text-gray-800">Unknown</span>';
                }
            },
        ]);

        // Selection Method
        $this->addColumn([
            'index' => 'selection_method',
            'label' => 'Method',
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Custom', 'value' => 'custom'],
                ['label' => 'Rating', 'value' => 'rating'],
                ['label' => 'Most Buying', 'value' => 'most_buying'],
                ['label' => 'Trending', 'value' => 'trending'],
                ['label' => 'New Arrivals', 'value' => 'new_arrivals'],
            ],
            'closure' => function ($row) {
                $methods = [
                    'custom' => ['Custom', 'bg-gray-100 text-gray-800'],
                    'rating' => ['Rating', 'bg-yellow-100 text-yellow-800'],
                    'most_buying' => ['Most Buying', 'bg-green-100 text-green-800'],
                    'trending' => ['Trending', 'bg-red-100 text-red-800'],
                    'new_arrivals' => ['New Arrivals', 'bg-indigo-100 text-indigo-800'],
                ];
                
                $method = $methods[$row->selection_method] ?? ['Unknown', 'bg-gray-100 text-gray-800'];
                return '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs ' . $method[1] . '">' . $method[0] . '</span>';
            },
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

        // Created At
        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Created At',
            'type' => 'date',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
        ]);
    }

    /**
     * Prepare row actions.
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('admin.foodmanagements.featured-items.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'url' => fn($row) => route('admin.foodmanagements.featured-items.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.featured-items.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => fn($row) => route('admin.foodmanagements.featured-items.destroy', $row->id),
            ]);
        }
    }

    /**
     * Prepare bulk actions.
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('admin.foodmanagements.featured-items.bulk-delete')) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => 'admin.foodmanagements.featured-items.bulk-delete',
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.featured-items.toggle-status')) {
            $this->addMassAction([
                'icon' => 'sync',
                'title' => 'Change Status',
                'method' => 'POST',
                'url' => 'admin.foodmanagements.featured-items.toggle-status',
                'action' => 'bg-gray-500 text-white',
                'options' => [
                    ['label' => 'Active', 'value' => 1],
                    ['label' => 'Inactive', 'value' => 0],
                ],
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.featured-items.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Featured Item',
                'is_popup' => false,
                'method' => 'GET',
                'action' => 'text-white bg-blue-500',
                'url' => 'admin.foodmanagements.featured-items.create',
            ]);
        }
    }
}