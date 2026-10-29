<?php

namespace Modules\KYC\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;
use Modules\KYC\Services\ComplianceService;

class ComplianceMissingGrid extends DataGrid
{
    protected $primaryColumn = "row_key";
    protected $itemsPerPage = 15;
    protected $sortColumn = 'owner_id';
    protected $sortOrder = 'asc';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = app(ComplianceService::class)->missingQueryBuilder()
            ->select(
                DB::raw("CONCAT(admins.id, '-', kyc_types.id) as row_key"),
                'admins.id as owner_id',
                'admins.email as owner_email',
                'admins.phone as owner_phone',
                'kyc_types.id as kyc_type_id',
                'kyc_types.name as kyc_type_name',
                'admins.name as owner_name'
            );

        $this->addFilter('owner_id', 'admins.id');
        $this->addFilter('owner_name', 'admins.name');
        $this->addFilter('owner_email', 'owners.email');
        $this->addFilter('kyc_type_name', 'kyc_types.name');

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
        //     'index' => 'owner_id',
        //     'label' => 'Owner ID',
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
            'index' => 'owner_email',
            'label' => 'Email',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="text-sm text-gray-600">' . e($row->owner_email ?: '-') . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'kyc_type_name',
            'label' => 'Missing Compliance',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-red-100 text-red-800">
                    ' . e($row->kyc_type_name) . '
                </span>';
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
        if (bouncer()->hasPermission('admin.kyc.compliance.remind-missing')) {
            $this->addAction([
                'icon' => 'notifications_active',
                'title' => 'Send Reminder',
                'method' => 'POST',
                'url' => function ($row) {
                    return route('admin.kyc.compliance.remind-missing', ['owner' => $row->owner_id, 'type' => $row->kyc_type_id]);
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
        if (bouncer()->hasPermission('admin.kyc.compliance.remind-missing')) {
            $this->addMassAction([
                'icon' => 'notifications_active',
                'title' => 'Send Reminder',
                'method' => 'POST',
                'action' => 'text-white bg-primary hover:bg-primary-600 border-primary',
                'url' => route('admin.kyc.compliance.remind-missing-bulk'),
            ]);
        }
    }
}
