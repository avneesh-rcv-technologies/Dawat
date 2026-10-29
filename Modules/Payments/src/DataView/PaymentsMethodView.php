<?php

namespace Modules\Payments\DataView;

use Illuminate\Http\Request;
use Modules\DataView\ColumnTypes\Integer;
use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PaymentsMethodView extends DataGrid
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
        return DB::table('payment_configurations')
            ->select(
                'id',
                'payment_method_id',
                'merchant_name',
                'is_active',
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
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'merchant_name',
            'label' => 'Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);
        
       $this->addColumn([
            'index' => 'is_active',
            'label' => 'Status',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'allow_multiple_values' => false,
            'filterable_options' => [
                [
                    'label' => 'Active',
                    'value' => 1,
                ],
                [
                    'label' => 'Disabled',
                    'value' => 0,
                ],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->is_active) {
                    return 'Active';
                }
                return 'Disabled';
            },
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
        if (bouncer()->hasPermission('admin.payment_methods.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit Payment Method',
                'method' => 'GET',
                'is_popup'  => true,
                'url' => function ($row) {
                    return route('admin.payment_methods.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('admin.payment_methods.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.payment_methods.destroy', $row->id);
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
        if (bouncer()->hasPermission('admin.payment_methods.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create',
                'method' => 'GET',
                'is_popup'  => true,
                'action' => 'text-amber-100 bg-primary-100',
                'url' => 'admin.payment_methods.create',
            ]);
        }
    }
}