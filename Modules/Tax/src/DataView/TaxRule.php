<?php

namespace Modules\Tax\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class TaxRule extends DataGrid
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
        return DB::table('tax_rules')
            ->leftJoin('tax_categories', 'tax_rules.tax_category_id', '=', 'tax_categories.id')
            ->leftJoin('tax_rates', 'tax_rules.tax_rate_id', '=', 'tax_rates.id')
            ->leftJoin('countries', 'tax_rates.country_id', '=', 'countries.id')
            ->select(
                'tax_rules.id',
                'tax_rules.priority',

                'tax_categories.name as category_name',

                'tax_rates.name as rate_name',
                'tax_rates.rate_value',
                'tax_rates.type',
                'tax_rates.state',
                'tax_rates.postcode',
                'tax_rates.city',
                'tax_rates.is_active',

                'countries.name as country_name',
                'countries.code as country_code',

                'tax_rules.created_at'
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

       $this->addColumn([
            'index' => 'category_name',
            'label' => 'Tax Category',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
        ]);

         $this->addColumn([
            'index' => 'rate_name',
            'label' => 'Tax Rate',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'priority',
            'label' => 'Priority',
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
        ]);

        // $this->addColumn([
        //     'index' => 'created_at',
        //     'label' => 'Created At',
        //     'type' => 'date',
        //     'searchable' => false,
        //     'sortable' => true,
        // ]);
    }
    public function prepareActions()
    {
        if (bouncer()->hasPermission('admin.tax-rules.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'is_popup'  => true,
                'url' => function ($row) {
                    return route('admin.tax-rules.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('admin.tax-rules.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.tax-rules.destroy', $row->id);
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

        if (bouncer()->hasPermission('admin.tax-rules.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Tax Rule',
                'method' => 'GET',
                'action' => 'text-amber-100 bg-primary-100',
                'is_popup'  => true,
                'url' => 'admin.tax-rules.create'
            ]);
        }
        
        $options = [];
        if (bouncer()->hasPermission('admin.tax-rules.index')) {
            $options[] = [
                'label' => 'Tax Rules',
                'value' => 'admin.tax-rules.index'  
            ];
        }

        // if (bouncer()->hasPermission('admin.tax-rules.index')) {
        //     $options[] = [
        //         'label' => 'Tax Rules',
        //         'value' => 'admin.tax-rules.index'
        //     ];
        // }

        if(!empty($options)) {
            $this->addMassAction([
                'icon' => 'settings',
                'title' => 'More',
                'method' => 'GET',
                'action' => 'text-amber-100 bg-primary-100',
                'url' => 'admin.tax-rules.index',
                'options' => $options
            ]);
        }
    }

    /**
     * Set up row actions
     */
    // public function prepareActions()
    // {
        // //Edit Action
        // $this->addAction([
        //     'icon' => 'edit',
        //     'title' => 'Edit',
        //     'method' => 'GET',
        //     'url' => function ($row) {
        //         return route('admin.tax.rates.edit', $row->id);
        //     },
        //     'condition' => function () {
        //         return auth()->user()->can('tax.rates.edit');
        //     },
        //     'class' => 'text-blue-600 hover:text-blue-900'
        // ]);

        // // Delete Action
        // $this->addAction([
        //     'icon' => 'delete',
        //     'title' => 'Delete',
        //     'method' => 'DELETE',
        //     'url' => function ($row) {
        //         return route('admin.tax.rates.destroy', $row->id);
        //     },
        //     'condition' => function () {
        //         return auth()->user()->can('tax.rates.delete');
        //     },
        //     'confirm' => 'Are you sure you want to delete this tax rate?',
        //     'class' => 'text-red-600 hover:text-red-900'
        // ]);

        // // Toggle Status Action
        // $this->addAction([
        //     'icon' => function ($row) {
        //         return $row->is_active ? 'toggle_off' : 'toggle_on';
        //     },
        //     'title' => function ($row) {
        //         return $row->is_active ? 'Deactivate' : 'Activate';
        //     },
        //     'method' => 'POST',
        //     'url' => function ($row) {
        //         return route('admin.tax.rates.toggle-status', $row->id);
        //     },
        //     'condition' => function () {
        //         return auth()->user()->can('tax.rates.edit');
        //     },
        //     'class' => function ($row) {
        //         return $row->is_active 
        //             ? 'text-orange-600 hover:text-orange-900' 
        //             : 'text-green-600 hover:text-green-900';
        //     }
        // ]);
    // }

    /**
     * Set up mass actions
     */
    // public function prepareMassActions()
    // {
        // Create New Tax Rate
        // $this->addMassAction([
        //     'icon' => 'add',
        //     'title' => 'Create Tax Rate',
        //     'method' => 'GET',
        //     'url' => route('admin.tax.rates.create'),
        //     'action' => 'bg-blue-600 text-white',
        //     'condition' => function () {
        //         return auth()->user()->can('tax.rates.create');
        //     }
        // ]);

        // // Bulk Activate
        // $this->addMassAction([
        //     'icon' => 'toggle_on',
        //     'title' => 'Activate Selected',
        //     'method' => 'POST',
        //     'url' => route('admin.tax.rates.bulk-activate'),
        //     'action' => 'bg-green-600 text-white',
        //     'confirm' => 'Are you sure you want to activate selected tax rates?',
        //     'condition' => function () {
        //         return auth()->user()->can('tax.rates.edit');
        //     }
        // ]);

        // // Bulk Deactivate
        // $this->addMassAction([
        //     'icon' => 'toggle_off',
        //     'title' => 'Deactivate Selected',
        //     'method' => 'POST',
        //     'url' => route('admin.tax.rates.bulk-deactivate'),
        //     'action' => 'bg-gray-600 text-white',
        //     'confirm' => 'Are you sure you want to deactivate selected tax rates?',
        //     'condition' => function () {
        //         return auth()->user()->can('tax.rates.edit');
        //     }
        // ]);

        // // Bulk Delete
        // $this->addMassAction([
        //     'icon' => 'delete',
        //     'title' => 'Delete Selected',
        //     'method' => 'POST',
        //     'url' => route('admin.tax.rates.bulk-delete'),
        //     'action' => 'bg-red-600 text-white',
        //     'confirm' => 'Are you sure you want to delete selected tax rates? This action cannot be undone.',
        //     'condition' => function () {
        //         return auth()->user()->can('tax.rates.delete');
        //     }
        // ]);
    // }
}
