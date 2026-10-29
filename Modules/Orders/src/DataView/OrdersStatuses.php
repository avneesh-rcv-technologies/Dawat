<?php

namespace Modules\Orders\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;
use Modules\Orders\Enums\OrderStatus;

class OrdersStatuses extends DataGrid
{
    protected $primaryColumn = "id";
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
     *v c 
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('statuses')
            ->select(
                'id',
                'name',
                'type_code',
                'color',
                'status_code',
                'created_at'
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
            'searchable' => true,
            'filterable' => false,
            'sortable' => true
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
            'index' => 'color',
            'label' => 'Color',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
           
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Created At',
            'type' => 'date',
            'searchable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
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
        if (bouncer()->hasPermission('admin.orders-statuses.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'modal' => true,
                'is_popup'  => true,
                'url' => function ($row) {
                    if(fn_get_order_status($row->status_code, 'O')) {                    
                        return route('admin.orders-statuses.edit', $row->id);
                    }
                },
            ]);
        }

        if (bouncer()->hasPermission('admin.orders-statuses.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                   if(fn_get_order_status($row->status_code, 'O')) {                    
                        return route('admin.orders-statuses.destroy', $row->id);
                    }
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

        if (bouncer()->hasPermission('admin.orders-statuses.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Status',
                'method' => 'GET',
                'is_popup'  => true,
                'action' => 'text-amber-100 bg-primary-100',
                'url' => 'admin.orders-statuses.create',
            ]);
        }
        
    }
}