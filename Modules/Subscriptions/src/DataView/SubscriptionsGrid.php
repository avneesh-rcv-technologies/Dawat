<?php

namespace Modules\Subscriptions\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class SubscriptionsGrid extends DataGrid
{
    protected $primaryColumn = "id";

    protected $itemsPerPage = 10;

    protected $sortOrder = 'desc';

    protected $sortColumn = 'created_at';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
    
        return DB::table('subscriptions')
            ->leftJoin('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->select(
                'subscriptions.id',
                'subscriptions.subscribable_type',
                'subscriptions.subscribable_id',
                'subscriptions.status',
                'subscriptions.start_date',
                'subscriptions.current_period_end',
                'subscriptions.cancel_at_period_end',
                'subscriptions.created_at',
                'plans.name as plan_name',
                'plans.price as plan_price',
                'plans.billing_cycle'
            )
            ->whereNull('subscriptions.deleted_at');
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
            'index' => 'subscribable_type',
            'label' => 'Subscriber Type',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                $type = class_basename($row->subscribable_type);
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">'
                    . $type . '</span>';
            }
        ]);

        $this->addColumn([
            'index' => 'subscribable_id',
            'label' => 'Subscriber ID',
            'type' => 'integer',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'plan_name',
            'label' => 'Plan',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'plan_price',
            'label' => 'Price',
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return '$' . number_format($row->plan_price, 2) . ' / ' . $row->billing_cycle;
            }
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                $colors = [
                    'active' => 'bg-green-100 text-green-800',
                    'trialing' => 'bg-blue-100 text-blue-800',
                    'past_due' => 'bg-yellow-100 text-yellow-800',
                    'paused' => 'bg-gray-100 text-gray-800',
                    'canceled' => 'bg-red-100 text-red-800',
                    'expired' => 'bg-red-100 text-red-800',
                ];

                $class = $colors[$row->status] ?? 'bg-gray-100 text-gray-800';
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $class . '">'
                    . ucfirst($row->status) . '</span>';
            }
        ]);

        $this->addColumn([
            'index' => 'start_date',
            'label' => 'Start Date',
            'type' => 'date',
            'searchable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'current_period_end',
            'label' => 'Period End',
            'type' => 'date',
            'searchable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'cancel_at_period_end',
            'label' => 'Auto Cancel',
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return $row->cancel_at_period_end
                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Yes</span>'
                    : '';
            }
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
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.view')) {
            $this->addAction([
                'title' => 'View',
                'method' => 'GET',
                'icon' => 'open_in_new',
                'url' => function ($row) {
                    return route('subscriptions.show', $row->id);
                },

            ]);
        }
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.edit')) {
            $this->addAction([
                'title' => 'Edit',
                'method' => 'GET',
                'icon' => 'edit',
                'url' => function ($row) {
                    return route('subscriptions.edit', $row->id);
                },

            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.cancel')) {
            $this->addAction([
                'title' => 'Cancel',
                'method' => 'POST',
                'icon' => 'cancel',
                'url' => function ($row) {
                    return route('subscriptions.cancel', $row->id);
                },

            ]);
        }
    }

    public function prepareMassActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.destroy')) {
            $this->addMassAction([
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => 'subscriptions.massDestroy'
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.cancel')) {
            $this->addMassAction([
                'title' => 'Bulk Cancel',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => 'subscriptions.massCancel'
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('subscriptions.resume')) {
            $this->addMassAction([
                'title' => 'Bulk Resume',
                'method' => 'POST',
                'action' => 'text-white bg-green-600',
                'url' => 'subscriptions.massResume'
            ]);
        }
    }
}
