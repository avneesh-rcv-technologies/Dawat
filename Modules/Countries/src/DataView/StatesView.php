<?php

namespace Modules\Countries\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class StatesView extends DataGrid
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
        return DB::table('country_states as cs')
            ->leftJoin('countries as c', 'cs.country_id', '=', 'c.id')
            ->select(
                'cs.id',
                'cs.country_id',
                'cs.country_code',
                'cs.code',
                'cs.name as state_name',
                'cs.latitude',
                'cs.longitude',
                'cs.flag',
                'cs.wikiDataId',
                'c.name as country_name'
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
            'index' => 'country_name',
            'label' => 'Country Name',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => false,
        ]);

        $this->addColumn([
            'index' => 'code',
            'label' => 'State Code',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'state_name',
            'label' => 'State Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addFilter('country_name', 'c.name');
        $this->addFilter('code', 'cs.code');
        $this->addFilter('state_name', 'cs.name');
        $this->addFilter('wikiDataId', 'cs.wikiDataId');

        $this->addColumn([
            'index' => 'latitude',
            'label' => 'Latitude',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'longitude',
            'label' => 'Longitude',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'flag',
            'label' => 'Flag',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
        ]);

        $this->addColumn([
            'index' => 'wikiDataId',
            'label' => 'WikiData ID',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => true,
        ]);
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {

        $options = [];

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('settings.countries.index')) {
            $options[] = [
                'label' => 'Countries',
                'value' => 'settings.countries.index'
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
                'icon' => 'public',
                'title' => 'More',
                'method' => 'GET',
                'action' => 'text-amber-100 bg-primary-100',
                'url' => 'settings.countries.index',
                'options' => $options
            ]);
        }
    }
}
