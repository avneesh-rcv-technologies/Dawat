<?php

namespace Modules\Currencies\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class CurrencyView extends DataGrid
{
    protected $primaryColumn = "id";
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('currencies')
            ->select(
                'id',
                'code',
                'name',
                'symbol',
                'decimal',
            );
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'id',
            'label' => 'ID',
            'type' => 'integer',
            'visibility' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'code',
            'label' => 'Code',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => 'Currency_Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'symbol',
            'label' => 'Symbol',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,

        ]);

        $this->addColumn([
            'index' => 'decimal',
            'label' => 'Decimal',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        // if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('settings.currencies.edit')) {
        //     $this->addAction([
        //         'icon' => 'edit',
        //         'title' => 'Edit',
        //         'is_popup' => true,
        //         'method' => 'GET',
        //         'url' => function ($row) {
        //             return route('settings.currencies.edit', $row->id);
        //         },
        //     ]);
        // }

        // if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('settings.currencies.destroy')) {
        //     $this->addAction([
        //         'icon' => 'delete',
        //         'title' => 'Delete',
        //         'method' => 'DELETE',
        //         'url' => function ($row) {
        //             return route('settings.currencies.destroy', $row->id);
        //         },
        //     ]);
        // }
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {

        // if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('settings.currencies.create')) {
        //     $this->addMassAction([
        //         'icon' => 'add',
        //         'title' => 'Create Currency',
        //         'method' => 'GET',
        //         'is_popup' => true,
        //         'action' => 'text-white bg-blue-500',
        //         'url' => 'settings.currencies.create',
        //     ]);
        // }
    }
}
