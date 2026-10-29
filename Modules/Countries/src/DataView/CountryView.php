<?php

namespace Modules\Countries\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class CountryView extends DataGrid
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
        return DB::table('countries')
            ->select(
                'id',
                'code',
                'name',
                'phonecode',
                'capital',
                'currency',
                'wikiDataId',
                'flag'
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
            'visibility' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'flag',
            'label' => 'flag',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
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
            'label' => 'Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);




        $this->addColumn([
            'index' => 'phonecode',
            'label' => 'Phone Code',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'capital',
            'label' => 'Capital',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'currency',
            'label' => 'Currency',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        // Add filter mappings for filterable columns
        $this->addFilter('name', 'name');
        $this->addFilter('code', 'code');
        $this->addFilter('phonecode', 'phonecode');
        $this->addFilter('capital', 'capital');
        $this->addFilter('currency', 'currency');
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {

        $options = [];

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('settings.states.index')) {
            $options[] = [
                'label' => 'States',
                'value' => 'settings.states.index'
            ];
        }


        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('settings.cities.index')) {
            $options[] = [
                'label' => 'Cities',
                'value' => 'settings.cities.index'
            ];
        }

        if (!empty($options)) {
            $this->addMassAction([
                'icon' => 'map',
                'title' => 'More',
                'method' => 'GET',
                'action' => 'text-amber-100 bg-primary-100',
                'url' => 'settings.states.index',
                'options' => $options
            ]);
        }
    }
}
