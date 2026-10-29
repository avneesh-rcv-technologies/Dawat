<?php

namespace Modules\KYC\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class ComplianceRecordGrid extends DataGrid
{
    protected $primaryColumn = "id";
    protected $itemsPerPage = 15;
    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('compliance_records')
            ->leftJoin('admins', 'compliance_records.user_id', '=', 'admins.id')
            ->leftJoin('kyc_types', 'compliance_records.kyc_type_id', '=', 'kyc_types.id')
            ->select(
                'compliance_records.id',
                'compliance_records.submission_id',
                'compliance_records.status',
                'compliance_records.valid_from',
                'compliance_records.expires_at',
                'compliance_records.validity_period',
                'compliance_records.reminder_sent_at',
                'compliance_records.expired_notified_at',
                'compliance_records.created_at',
                'kyc_types.name as kyc_type_name',
                'admins.name as owner_name'
            );

        $this->addFilter('id', 'compliance_records.id');
        $this->addFilter('owner_name', 'admins.name');
        $this->addFilter('kyc_type_name', 'kyc_types.name');
        $this->addFilter('status', 'compliance_records.status');
        $this->addFilter('expires_at', 'compliance_records.expires_at');

        return $queryBuilder;
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        // $this->addColumn([
        //     'index' => 'id',
        //     'label' => 'ID',
        //     'type' => 'integer',
        //     'searchable' => true,
        //     'filterable' => true,
        //     'sortable' => true,
        // ]);

        $this->addColumn([
            'index' => 'owner_name',
            'label' => 'Restaurant Owner',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'kyc_type_name',
            'label' => 'Compliance Type',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-primary-100 text-primary">
                    ' . e($row->kyc_type_name) . '
                </span>';
            },
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Active', 'value' => 'active'],
                ['label' => 'Expiring Soon', 'value' => 'expiring_soon'],
                ['label' => 'Expired', 'value' => 'expired'],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                $statusConfig = [
                    'active'        => ['color' => 'green',  'label' => 'Active'],
                    'expiring_soon' => ['color' => 'yellow', 'label' => 'Expiring Soon'],
                    'expired'       => ['color' => 'red',    'label' => 'Expired'],
                ];
                $config = $statusConfig[$row->status] ?? $statusConfig['active'];

                return '<span class="inline-flex items-center px-3 py-1 rounded-full text-left text-xs font-semibold bg-' . $config['color'] . '-100 text-' . $config['color'] . '-800">
                    ' . $config['label'] . '
                </span>';
            },
        ]);

        $this->addColumn([
            'index' => 'valid_from',
            'label' => 'Valid From',
            'type' => 'date',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return $row->valid_from
                    ? '<span class="text-sm text-gray-600">' . date('M d, Y', strtotime($row->valid_from)) . '</span>'
                    : '<span class="text-xs text-gray-400">-</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'expires_at',
            'label' => 'Expires At',
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
            'closure' => function ($row) {
                if (!$row->expires_at) {
                    return '<span class="text-xs text-gray-400">-</span>';
                }
                $isPast = strtotime($row->expires_at) < time();
                $color  = $isPast ? 'text-red-600 font-semibold' : 'text-gray-600';
                return '<span class="text-sm ' . $color . '">' . date('M d, Y', strtotime($row->expires_at)) . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'validity_period',
            'label' => 'Period',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
            'closure' => function ($row) {
                return '<span class="text-sm text-gray-600">' . e(str_replace('_', ' ', (string) $row->validity_period)) . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'reminder_sent_at',
            'label' => 'Last Notified',
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
            'closure' => function ($row) {
                $last = $row->expired_notified_at ?: $row->reminder_sent_at;
                return $last
                    ? '<span class="text-sm text-gray-600">' . date('M d, Y h:i A', strtotime($last)) . '</span>'
                    : '<span class="text-xs text-gray-400">Not notified</span>';
            },
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('admin.kyc.submissions.show')) {
            $this->addAction([
                'icon' => 'open_in_new',
                'title' => 'View Submission',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.kyc.submissions.show', $row->submission_id);
                },
            ]);
        }

        if (bouncer()->hasPermission('admin.kyc.compliance.remind')) {
            $this->addAction([
                'icon' => 'notifications_active',
                'title' => 'Send Reminder',
                'method' => 'POST',
                'url' => function ($row) {
                    return route('admin.kyc.compliance.remind', $row->id);
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
        if (bouncer()->hasPermission('admin.kyc.compliance.remind')) {
            $this->addMassAction([
                'icon' => 'notifications_active',
                'title' => 'Send Reminder',
                'method' => 'POST',
                'action' => 'text-white bg-primary hover:bg-primary-600 border-primary',
                'url' => route('admin.kyc.compliance.remind-bulk'),
            ]);
        }

        if (bouncer()->hasPermission('admin.kyc.compliance.missing')) {
            $this->addMassAction([
                'icon' => 'person_off',
                'title' => 'Owners Without Compliance',
                'method' => 'GET',
                'action' => 'text-white bg-red-600 hover:bg-red-700',
                'url' => 'admin.kyc.compliance.missing',
            ]);
        }
    }
}
