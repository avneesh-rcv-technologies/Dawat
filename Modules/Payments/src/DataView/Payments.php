<?php

namespace Modules\Payments\DataView;

use Illuminate\Http\Request;
use Modules\DataView\ColumnTypes\Integer;
use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\Payments\Models\PaymentConfiguration;
use Modules\Payments\Models\PaymentMethod;
use Modules\Payments\Enums\PaymentStatus;

class Payments extends DataGrid
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
        return DB::table('payments')
            ->select(
                'id',
                'order_id',
                'payment_method_id',
                'transaction_id',
                'amount',
                'currency',
                'status',
                'ip_address',
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

        // $this->addColumn([
        //     'index' => 'order_id',
        //     'label' => 'Name',
        //     'type' => 'string',
        //     'searchable' => true,
        //     'filterable' => true,
        //     'sortable' => true,
        // ]);

        // $this->addColumn([
        //     'index' => 'transaction_id',
        //     'label' => 'Transaction ID',
        //     'type' => 'string',
        //     'searchable' => true,
        //     'filterable' => true,
        //     'sortable' => true,
        // ]);

        $this->addColumn([
            'index' => 'payment_method_id',
            'label' => 'Payment Method',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
             'closure' => function ($row) {
                return PaymentConfiguration::select('merchant_name')->where('payment_method_id', $row->payment_method_id)->first()->merchant_name;
            },
        ]);

         $this->addColumn([
            'index' => 'amount',
            'label' => 'Amount',
            'type' => 'decimal',
            'filterable' => false,
            'searchable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'currency',
            'label' => 'Currency',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => false,
        ]);
        
       $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => array_map(fn (PaymentStatus $status) => [
                'label' => $status->label(),
                'value' => $status->value,
            ], PaymentStatus::cases()),
            'allow_multiple_values' => false,
            'sortable' => true,
            'closure' => function ($row) {
                $status = PaymentStatus::tryFrom($row->status ?? '');
                $label = e($status?->label() ?? \Illuminate\Support\Str::headline($row->status ?? 'Unknown'));
                $style = $status?->class() ?? 'background-color: #6B7280; color: #ffffff;';

                return "<span style='{$style}' class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'>{$label}</span>";
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
        if (bouncer()->hasPermission('admin.payments.show')) {
            $this->addAction([
                'icon' => 'visibility',
                'title' => 'View Detail',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.payments.show', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('admin.payments.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.payments.destroy', $row->id);
                },
            ]);
        }
    }
}
