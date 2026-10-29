<?php

namespace Modules\Tax\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class Tax extends DataGrid
{
    protected $primaryColumn = "id";
    protected $itemsPerPage = 25;
    protected $sortOrder = 'asc';
    protected $defaultSortColumn = 'priority';

    /**
     * Prepare query builder with joins
     */
    public function prepareQueryBuilder()
    {

         return DB::table('tax_rates')
            ->leftJoin('countries', 'tax_rates.country_id', '=', 'countries.id')
            ->select(
                'tax_rates.id',
                'tax_rates.name',
                'tax_rates.rate_value',
                'tax_rates.type',
                'countries.name as country_name',
                'countries.code as country_code',
                'tax_rates.state',
                'tax_rates.postcode',
                'tax_rates.city',
                'tax_rates.is_active',
                'tax_rates.priority',
                'tax_rates.created_at',
            );

    }

    /**
     * Set up columns with enhanced display
     */
    public function prepareColumns()
    {
        

        // Name Column
        $this->addColumn([
            'index' => 'name',
            'label' => 'Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        // Rate Value Columnhe DataGrid base class or applySearch() methods.


        $this->addColumn([
            'index' => 'rate_value',
            'label' => 'Rate',
            'type' => 'decimal',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return $row->type === 'P' 
                    ? $row->rate_value.'%' 
                    : fn_get_currency($row->rate_value);
            }
        ]);

        // Type Column
        $this->addColumn([
            'index' => 'type',
            'label' => 'Type',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Percentage', 'value' => 'P'],
                ['label' => 'Fixed', 'value' => 'F']
            ],
            'closure' => function ($row) {
                return ($row->type == 'F') ? 'Fixed' : 'Percentage';
            }
        ]);

        // Location Column
        $this->addColumn([
            'index' => 'location',
            'label' => 'Location',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
            'closure' => function ($row) {
                $location = $row->country_name;
                
                if ($row->state) {
                    $location .= ', '.$row->state;
                }
                
                if ($row->city) {
                    $location .= ', '.$row->city;
                }
                
                if ($row->postcode) {
                    $location .= ' ('.$row->postcode.')';
                }
                
                return $location;
            }
        ]);

      

        // Status Column
        $this->addColumn([
            'index' => 'is_active',
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
                return $row->is_active
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
        if (bouncer()->hasPermission('admin.tax.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'is_popup'  => true,
                'url' => function ($row) {
                    return route('admin.tax.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('admin.tax.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.tax.destroy', $row->id);
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

        if (bouncer()->hasPermission('admin.tax.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Tax',
                'method' => 'GET',
                'is_popup'  => true,
                'action' => 'text-amber-100 bg-primary-100',
                'url' => 'admin.tax.create'
            ]);
        }
        
        $options = [];
        // if (bouncer()->hasPermission('admin.tax-category.index')) {
        //     $options[] = [
        //         'label' => 'Tax Category',
        //         'value' => 'admin.tax-category.index'
        //     ];
        // }

        if (bouncer()->hasPermission('admin.tax-rules.index')) {
            $options[] = [
                'label' => 'Rules',
                'value' => 'admin.tax-rules.index'
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