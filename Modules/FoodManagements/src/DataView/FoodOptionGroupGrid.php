<?php

namespace Modules\FoodManagements\DataView;

use Illuminate\Support\Facades\DB;
use Modules\DataView\DataGrid;

class FoodOptionGroupGrid extends DataGrid
{
    protected $primaryColumn = 'id';
    protected $itemsPerPage  = 10;
    protected $sortOrder     = 'desc';

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        $query = DB::table('food_option_groups')
            ->leftJoin('food_options', 'food_option_groups.id', '=', 'food_options.food_option_group_id')
            ->select(
                'food_option_groups.id',
                'food_option_groups.name',
                'food_option_groups.is_required',
                'food_option_groups.min',
                'food_option_groups.max',
                'food_option_groups.created_at',
                DB::raw('COUNT(food_options.id) as options_count')
            )
            ->groupBy(
                'food_option_groups.id',
                'food_option_groups.name',
                'food_option_groups.is_required',
                'food_option_groups.min',
                'food_option_groups.max',
                'food_option_groups.created_at'
            );

        $this->addFilter('name', 'food_option_groups.name');
        $this->addFilter('is_required', 'food_option_groups.is_required');
        $this->addFilter('created_at', 'food_option_groups.created_at');

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
            'label'      => 'Option Group',
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        // Required
        $this->addColumn([
            'index'      => 'is_required',
            'label'      => 'Required',
            'type'       => 'boolean',
            'sortable'   => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Yes', 'value' => 1],
                ['label' => 'No', 'value' => 0],
            ],
            'closure' => fn ($row) =>
                $row->is_required
                    ? '<span class="badge bg-green-100 text-green-800">Yes</span>'
                    : '<span class="badge bg-gray-100 text-gray-800">No</span>',
        ]);

        // Min
        $this->addColumn([
            'index'    => 'min',
            'label'    => 'Min',
            'type'     => 'integer',
            'sortable' => true,
        ]);

        // Max
        $this->addColumn([
            'index'    => 'max',
            'label'    => 'Max',
            'type'     => 'integer',
            'sortable' => true,
        ]);

        // Options Count
        $this->addColumn([
            'index'    => 'options_count',
            'label'    => 'Options',
            'type'     => 'integer',
            'sortable' => true,
            'closure' => fn ($row) => 
                '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-blue-100 text-blue-800">' . 
                $row->options_count . ' option' . ($row->options_count != 1 ? 's' : '') . 
                '</span>',
        ]);

        // Created At
        $this->addColumn([
            'index'      => 'created_at',
            'label'      => 'Created At',
            'type'       => 'date',
            'sortable'   => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
        ]);
    }

    /**
     * Row actions.
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('admin.foodmanagements.food-option-groups.view')) {
            $this->addAction([
                'icon'   => 'visibility',
                'title'  => 'View',
                'method' => 'GET',
                'url' => fn ($row) =>
                    route('admin.foodmanagements.food-option-groups.show', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.food-option-groups.edit')) {
            $this->addAction([
                'icon'   => 'edit',
                'title'  => 'Edit',
                'method' => 'GET',
                'is_popup'  => false,
                'url' => fn ($row) =>
                    route('admin.foodmanagements.food-option-groups.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.food-option-groups.destroy')) {
            $this->addAction([
                'icon'   => 'delete',
                'title'  => 'Delete',
                'method' => 'DELETE',
                'url' => fn ($row) =>
                    route('admin.foodmanagements.food-option-groups.destroy', $row->id),
            ]);
        }
    }

    /**
     * Bulk actions.
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('admin.foodmanagements.food-option-groups.bulk-delete')) {
            $this->addMassAction([
                'icon'   => 'delete',
                'title'  => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'bg-red-600 text-white',
                'url'    => 'admin.foodmanagements.food-option-groups.bulk-delete',
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.food-option-groups.create')) {
            $this->addMassAction([
                'icon'     => 'add',
                'title'    => 'Create Option Group',
                'method'   => 'GET',
                'is_popup' => false,
                'action'   => 'bg-blue-600 text-white',
                'url'      => 'admin.foodmanagements.food-option-groups.create',
            ]);
        }
    }
}
