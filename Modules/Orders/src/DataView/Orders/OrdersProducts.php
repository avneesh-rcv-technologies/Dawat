<?php

namespace Modules\Orders\DataView\Orders;

use Illuminate\Http\Request;
use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class OrdersProducts extends DataGrid
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
        return DB::table('products')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.sale_price',
                'products.stock_quantity'
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
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => 'Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'sku',
            'label' => 'SKU',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'price',
            'label' => 'Price',
            'type' => 'decimal',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return number_format($row->price, 2);
            },
        ]);

        $this->addColumn([
            'index' => 'sale_price',
            'label' => 'Sale Price',
            'type' => 'decimal',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return $row->sale_price ? number_format($row->sale_price, 2) : '-';
            },
        ]);

        $this->addColumn([
            'index' => 'stock_quantity',
            'label' => 'Stock',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('admin.catalog.products.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.catalog.products.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('admin.catalog.products.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.catalog.products.destroy', $row->id);
                },
            ]);
        }
    }
}
