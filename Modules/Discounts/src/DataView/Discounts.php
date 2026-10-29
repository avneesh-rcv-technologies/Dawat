<?php

namespace Modules\Discounts\DataView;

use Illuminate\Http\Request;
use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class Discounts extends DataGrid
{
    protected $primaryColumn = "id";

    protected $itemsPerPage = 10;

    protected $sortOrder = 'asc';

    /**
     * Prepare query builder with additional useful information
     */
    public function prepareQueryBuilder()
    {
        return DB::table('discounts')
            ->select(
                'discounts.id',
                'discounts.name',
                'discounts.type',
                'discounts.amount',
                'discounts.apply_to',
                'discounts.is_active',
                'discounts.starts_at',
                'discounts.expires_at',
                'discounts.created_at',
                DB::raw('(SELECT COUNT(*) FROM coupons WHERE coupons.discount_id = discounts.id) as coupons_count'),
                DB::raw('(SELECT COUNT(*) FROM discount_rules WHERE discount_rules.discount_id = discounts.id) as rules_count'),
                DB::raw('(SELECT COALESCE(SUM(times_used), 0) FROM coupons WHERE coupons.discount_id = discounts.id) as total_usage')
            );
    }

    /**
     * Add optimized columns for better overview
     */
    public function prepareColumns()
    {

        $this->addColumn([
            'index' => 'name',
            'label' => 'Discount Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true
        ]);

        $this->addColumn([
            'index' => 'type',
            'label' => 'Type',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Fixed', 'value' => 'F'],
                ['label' => 'Percentage', 'value' => 'P']
            ],
            'sortable' => true,
            'closure' => function ($row) {
                return ($row->type === 'P') ? 'Percentage' : 'Fixed';
            }
        ]);


        $this->addColumn([
            'index' => 'amount',
            'label' => 'Value',
            'type' => 'decimal',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {

                if ($row->type == 'Percentage') {
                    return $row->amount . '%';
                } else {
                    return number_format($row->amount, 2);
                }
            }
        ]);

        $this->addColumn([
            'index' => 'apply_to',
            'label' => 'Applies To',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Subtotal', 'value' => 'subtotal'],
                ['label' => 'Total', 'value' => 'total'],
                ['label' => 'Shipping', 'value' => 'shipping'],
            ],
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'coupons_count',
            'label' => 'Coupons',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return $row->coupons_count > 0
                    ? '<span class="bg-blue-100 text-blue-600 px-2 py-1 rounded">' . $row->coupons_count . '</span>'
                    : '-';
            }
        ]);

        $this->addColumn([
            'index' => 'rules_count',
            'label' => 'Rules',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return $row->rules_count > 0
                    ? '<span class="bg-blue-100 text-blue-600 px-2 py-1 rounded">' . $row->rules_count . '</span>'
                    : '-';
            }
        ]);

        $this->addColumn([
            'index' => 'total_usage',
            'label' => 'Usage',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                $usage = $row->total_usage ?? 0;
                return $usage > 0
                    ? '<span class="bg-green-100 text-green-600 px-2 py-1 rounded">' . $usage . '</span>'
                    : '<span class="bg-gray-100 text-gray-600 px-2 py-1 rounded">0</span>';
            }
        ]);

        $this->addColumn([
            'index' => 'is_active',
            'label' => 'Status',
            'type' => 'boolean',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Active', 'value' => '1'],
                ['label' => 'Inactive', 'value' => '0'],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                return $row->is_active
                    ? '<span class="bg-blue-100 text-blue-600 px-2 py-1 rounded">Active</span>'
                    : '<span class="bg-red-100 text-red-600 px-2 py-1 rounded">Inactive</span>';
            }
        ]);

        $this->addColumn([
            'index' => 'starts_at',
            'label' => 'Start Date',
            'type' => 'date',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true
        ]);

        $this->addColumn([
            'index' => 'expires_at',
            'label' => 'End Date',
            'type' => 'date',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true
        ]);
    }

    /**
     * Prepare actions with additional options
     */
    public function prepareActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('discount.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'url' => fn($row) => route('discount.edit', $row->id),
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('discount.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => fn($row) => route('discount.destroy', $row->id),
            ]);
        }
    }

    /**
     * Prepare mass actions with additional options
     */
    public function prepareMassActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('discount.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Discount',
                'method' => 'GET',
                'action' => 'text-white bg-[var(--color-primary-dark)]',
                'url' => 'discount.create',
            ]);
        }
    }
}
