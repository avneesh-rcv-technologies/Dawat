<?php

namespace Modules\Tax\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class TaxCategory extends DataGrid
{
    protected $primaryColumn = "id";
    protected $itemsPerPage = 25;
    protected $sortOrder = 'desc';
    protected $defaultSortColumn = 'priority';

    /**
     * Prepare query builder with joins
     */
    public function prepareQueryBuilder()
    {
         return DB::table('tax_categories')
           
            ->select(
                'id',
                'name',
                'description',
                'priority',
                'status',
                'created_at',
               
            );
         
    }

    /**
     * Set up columns with enhanced display
     */
    public function prepareColumns()
    {
        // ID Column
        $this->addColumn([
            'index' => 'id',
            'label' => 'ID',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);

        // Name Column
        $this->addColumn([
            'index' => 'name',
            'label' => 'Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        // Description Column
        $this->addColumn([
            'index' => 'description',
            'label' => 'Description',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => false,
            'closure' => function ($row) {
                return $row->description ? substr($row->description, 0, 50) . (strlen($row->description) > 50 ? '...' : '') : 'N/A';
            }
        ]);

        // Status Column
        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'boolean',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Active', 'value' => 1],
                ['label' => 'Inactive', 'value' => 0]
            ],
            'closure' => function ($row) {
                return $row->status
                    ? '<span class="px-2 py-1 rounded bg-green-100 text-green-800">Active</span>'
                    : '<span class="px-2 py-1 rounded bg-gray-100 text-gray-800">Inactive</span>';
            }
        ]);

        // Priority Column
        $this->addColumn([
            'index' => 'priority',
            'label' => 'Priority',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);

        // Created At Column
        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Created',
            'type' => 'date',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'filterable_type' => 'date_range',
        ]);
    }

    /**
     * Set up row actions
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('admin.tax-category.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'is_popup'  => true,
                'url' => function ($row) {
                    return route('admin.tax-category.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('admin.tax-category.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.tax-category.destroy', $row->id);
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

        if (bouncer()->hasPermission('admin.tax-category.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Tax Category',
                'method' => 'GET',
                'action' => 'text-amber-100 bg-primary-100',
                'is_popup'  => true,
                'url' => 'admin.tax-category.create'
            ]);
        }
        
        $options = [];
        if (bouncer()->hasPermission('admin.tax-category.index')) {
            $options[] = [
                'label' => 'Tax Rates',
                'value' => 'admin.tax.index'
            ];
        }

        if (bouncer()->hasPermission('admin.tax-rules.index')) {
            $options[] = [
                'label' => 'Tax Rules',
                'value' => 'admin.tax.index'
            ];
        }

        if(!empty($options)) {
            $this->addMassAction([
                'icon' => 'settings',
                'title' => 'More',
                'method' => 'GET',
                'action' => 'text-amber-100 bg-primary-100',
                'url' => 'admin.tax.index',
                'options' => $options
            ]);
        }
    }
}