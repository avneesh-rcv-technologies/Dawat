<?php

namespace Modules\Subscriptions\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class PlansGrid extends DataGrid
{
    protected $primaryColumn = "id";

    protected $itemsPerPage = 10;

    protected $sortOrder = 'asc';

    protected $sortColumn = 'sort_order';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('plans')
            ->select(
                'id',
                'name',
                'description',
                'price',
                'billing_cycle',
                'trial_days',
                'is_active',
                'is_popular',
                'sort_order',
                'created_at'
            )
            ->whereNull('deleted_at');
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
            'index' => 'name',
            'label' => 'Plan Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'price',
            'label' => 'Price',
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return '$' . number_format($row->price, 2);
            }
        ]);

        $this->addColumn([
            'index' => 'billing_cycle',
            'label' => 'Billing Cycle',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">'
                    . ucfirst($row->billing_cycle) . '</span>';
            }
        ]);

        $this->addColumn([
            'index' => 'trial_days',
            'label' => 'Trial Days',
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'is_popular',
            'label' => 'Popular',
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return $row->is_popular
                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">★ Popular</span>'
                    : '';
            }
        ]);

        $this->addColumn([
            'index' => 'is_active',
            'label' => 'Status',
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                $class = $row->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                $text = $row->is_active ? 'Active' : 'Inactive';
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $class . '">' . $text . '</span>';
            }
        ]);

        $this->addColumn([
            'index' => 'sort_order',
            'label' => 'Order',
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Created At',
            'type' => 'date',
            'searchable' => false,
            'sortable' => true,
        ]);
    }

    /**
     * Add actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.plans.edit')) {
            $this->addAction([
                'title' => 'View',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('subscriptions.plans.show', $row->id);
                },
                'icon' => 'visibility',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.plans.edit')) {
            $this->addAction([
                'title' => 'Edit',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('subscriptions.plans.edit', $row->id);
                },
                'icon' => 'edit',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.plans.destroy')) {
            $this->addAction([
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('subscriptions.plans.destroy', $row->id);
                },
                'icon' => 'delete',
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
        if(bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.plans.create')) {
            $this->addMassAction([
                'title' => 'Create Plan',
                'method' => 'GET',
                // 'is_popup' => true,
                'action' => 'bg-dark',
                'url' => 'subscriptions.plans.create',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.plans.destroy')) {
            $this->addMassAction([
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => 'subscriptions.plans.massDestroy',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.plans.edit')) {
            $this->addMassAction([
                'title' => 'Bulk Activate',
                'method' => 'POST',
                'action' => 'text-white bg-green-600',
                'url' => 'subscriptions.plans.massActivate',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.plans.edit')) {
            $this->addMassAction([
                'title' => 'Bulk Deactivate',
                'method' => 'POST',
                'action' => 'text-white bg-yellow-600',
                'url' => 'subscriptions.plans.massDeactivate',
            ]);
        }
    }
}
