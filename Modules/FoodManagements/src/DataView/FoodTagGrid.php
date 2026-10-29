<?php

namespace Modules\FoodManagements\DataView;

use Illuminate\Support\Facades\DB;
use Modules\DataView\DataGrid;

class FoodTagGrid extends DataGrid
{
    protected $primaryColumn = 'id';
    protected $itemsPerPage  = 10;
    protected $sortOrder     = 'desc';

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        return DB::table('food_tags')->select(
            'id',
            'name',
            'slug',
            'created_at'
        );
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
            'label'      => 'Tag Name',
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        // Slug
        $this->addColumn([
            'index'      => 'slug',
            'label'      => 'Slug',
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
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
        if (bouncer()->hasPermission('admin.foodmanagements.food-tags.edit')) {
            $this->addAction([
                'icon'   => 'edit',
                'title'  => 'Edit',
                'method' => 'GET',
                'is_popup'  => true,
                'url' => fn ($row) =>
                    route('admin.foodmanagements.food-tags.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.food-tags.destroy')) {
            $this->addAction([
                'icon'   => 'delete',
                'title'  => 'Delete',
                'method' => 'DELETE',
                'url' => fn ($row) =>
                    route('admin.foodmanagements.food-tags.destroy', $row->id),
            ]);
        }
    }

    /**
     * Bulk actions.
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('admin.foodmanagements.food-tags.bulk-delete')) {
            $this->addMassAction([
                'icon'   => 'delete',
                'title'  => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'bg-red-600 text-white',
                'url'    => 'admin.foodmanagements.food-tags.bulk-delete',
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.food-tags.create')) {
            $this->addMassAction([
                'icon'     => 'add',
                'title'    => 'Create Food Tag',
                'method'   => 'GET',
                'is_popup' => true,
                'action'   => 'bg-blue-600 text-white',
                'url'      => 'admin.foodmanagements.food-tags.create',
            ]);
        }
    }
}
