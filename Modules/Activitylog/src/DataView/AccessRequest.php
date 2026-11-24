<?php

namespace Modules\Activitylog\DataView;

use Illuminate\Support\Facades\DB;
use Modules\Activitylog\Models\AdminAccessRequest;
use Modules\DataView\DataGrid;

class AccessRequest extends DataGrid
{
    protected $primaryColumn = 'id';

    protected $itemsPerPage = 25;

    protected $sortOrder = 'desc';

    protected $sortColumn = 'created_at';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $query = DB::table('admin_access_requests as aar')
            ->leftJoin('admins as request_admins', function ($join) {
                $join->on('aar.requested_by_id', '=', 'request_admins.id')
                    ->where('aar.requested_by_type', 'LIKE', '%Admin%');
            })
            ->leftJoin('admins as review_admins', function ($join) {
                $join->on('aar.reviewed_by_id', '=', 'review_admins.id')
                    ->where('aar.reviewed_by_type', 'LIKE', '%Admin%');
            })
            ->select(
                'aar.id',
                'aar.ip_address',
                'aar.requested_route',
                'aar.reason',
                'aar.status',
                'aar.review_note',
                'aar.created_at',
                'aar.reviewed_at',
                'aar.requested_by_id',
                'aar.requested_by_type',
                'aar.reviewed_by_id',
                'aar.reviewed_by_type',
                'request_admins.name as requested_by_name',
                'review_admins.name as reviewed_by_name'
            );

        $this->addFilter('id', 'aar.id');
        $this->addFilter('ip_address', 'aar.ip_address');
        $this->addFilter('requested_route', 'aar.requested_route');
        $this->addFilter('status', 'aar.status');
        $this->addFilter('requested_by_name', 'request_admins.name');
        $this->addFilter('reviewed_by_name', 'review_admins.name');
        $this->addFilter('created_at', 'aar.created_at');

        return $query;
    }

    /**
     * Add columns.
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
            'index' => 'ip_address',
            'label' => 'IP Address',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'requested_route',
            'label' => 'Requested Route',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return e($row->requested_route ?: '-');
            },
        ]);

        $this->addColumn([
            'index' => 'reason',
            'label' => 'Reason',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => false,
            'closure' => function ($row) {
                return '<span title="' . e((string) $row->reason) . '">' . e((string) $row->reason) . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'filterable_type' => 'dropdown',
            'allow_multiple_values' => false,
            'filterable_options' => [
                ['label' => 'Approved', 'value' => 1],
                ['label' => 'Rejected', 'value' => 2],
                ['label' => 'Revoked', 'value' => 3],
            ],
            'sortable' => false,
            'closure' => function ($row) {
                $class = match ((string) $row->status) {
                    AdminAccessRequest::STATUS_APPROVED => 'bg-emerald-100 text-emerald-700',
                    AdminAccessRequest::STATUS_REJECTED => 'bg-rose-100 text-rose-700',
                    AdminAccessRequest::STATUS_REVOKED => 'bg-gray-100 text-gray-700',
                    default => 'bg-amber-100 text-amber-700',
                };

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ' . $class . '">'
                    . e(ucfirst((string) $row->status))
                    . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'requested_by_name',
            'label' => 'Requested By',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->requested_by_name) {
                    return '<div class="font-medium text-gray-800">' . e($row->requested_by_name) . '</div>';
                }

                return '<span class="text-gray-500">System / Guest</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Requested At',
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'reviewed_by_name',
            'label' => 'Reviewed By',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                if ((string) $row->status === AdminAccessRequest::STATUS_PENDING) {
                    return '<span class="text-xs text-gray-500">Pending review</span>';
                }

                return $row->reviewed_by_name
                    ? '<span class="text-gray-800">' . e($row->reviewed_by_name) . '</span>'
                    : '<span class="text-gray-500">-</span>';
            },
        ]);
    }


    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {

       if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('activity-log.access-requests.toggle-status')) {
            $this->addMassAction([
                'icon' => '',
                'title' => 'Status',
                'method' => 'POST',
                'url' => 'activity-log.access-requests.toggle-status',
                'action' => 'bg-gray-500',
                'options' => [
                    [
                        'label' => 'Approve',
                        'value' => 1,
                    ],
                    [
                        'label' => 'Reject',
                        'value' => 2,
                    ],
                    [
                        'label' => 'Revoke',
                        'value' => 3,
                    ],
                ],
            ]);
        }
    }



}
