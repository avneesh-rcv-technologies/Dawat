<?php

namespace Modules\Countries\DataView;

use Modules\Countries\Models\City;
use Illuminate\Support\Facades\DB;
use Modules\DataView\DataGrid;

class CitiesView extends DataGrid
{
    protected $primaryColumn = "id";
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';

    public static function getAllCities()
    {
        return City::with(['state', 'country'])->get();
    }

    public static function getCityById($id)
    {
        return City::with(['state', 'country'])->findOrFail($id);
    }

    public function prepareQueryBuilder()
    {
        return DB::table('cities as c')
            ->leftJoin('country_states as cs', 'c.state_id', '=', 'cs.id')
            ->leftJoin('countries as co', 'c.country_id', '=', 'co.id')
            ->select(
                'c.id',
                'c.state_id',
                'c.country_id',
                'c.name as city_name',
                'cs.name as state_name',
                'co.name as country_name'
                // 'latitude',
                // 'longitude',
                // 'flag',
                // 'wikiDataId'
            );
    }

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
            'index' => 'city_name',
            'label' => 'City Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'state_name',
            'label' => 'State',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => false,
        ]);

        $this->addColumn([
            'index' => 'country_name',
            'label' => 'Country',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => false,
        ]);

        $this->addFilter('city_name', 'c.name');
        $this->addFilter('state_name', 'cs.name');
        $this->addFilter('country_name', 'co.name');

      
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


        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('settings.countries.index')) {
            $options[] = [
                'label' => 'Country',
                'value' => 'settings.countries.index'
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
