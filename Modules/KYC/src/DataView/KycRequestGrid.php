<?php

namespace Modules\KYC\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class KycRequestGrid extends DataGrid
{
    protected $primaryColumn = "id";
    protected $itemsPerPage = 15;
    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    // public function prepareQueryBuilder()
    // {
    //     return DB::table('kyc_requests')
    //         ->leftJoin('kyc_types', 'kyc_requests.kyc_type_id', '=', 'kyc_types.id')
    //         ->leftJoin('admins', 'kyc_requests.requested_by', '=', 'admins.id')
    //         ->select(
    //             'kyc_requests.id',
    //             'kyc_requests.title',
    //             'kyc_requests.message',
    //             'kyc_requests.due_date',
    //             'kyc_requests.created_at',
    //             'kyc_types.name as kyc_type_name',
    //             'admins.name as requester_name'
    //         );
    // }

    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('kyc_requests')
            ->leftJoin('kyc_types', 'kyc_requests.kyc_type_id', '=', 'kyc_types.id')
            ->leftJoin('admins', 'kyc_requests.requested_by', '=', 'admins.id')
            ->select(
                'kyc_requests.id',
                'kyc_requests.title as request_title',
                'kyc_requests.message as request_message',
                'kyc_requests.due_date as request_due_date',
                'kyc_requests.created_at as request_created_at',
                'kyc_requests.kyc_type_id',
                'kyc_requests.requested_by',
                'kyc_types.name as kyc_type_name',
                'admins.name as requester_name'
            )
            ->orderBy('kyc_requests.created_at', 'desc');

        // Add filters for searchable/filterable columns
        $this->addFilter('id', 'kyc_requests.id');
        $this->addFilter('request_title', 'kyc_requests.title');
        $this->addFilter('request_message', 'kyc_requests.message');
        $this->addFilter('kyc_type_name', 'kyc_types.name');
        $this->addFilter('requester_name', 'admins.name');
        $this->addFilter('request_due_date', 'kyc_requests.due_date');
        $this->addFilter('request_created_at', 'kyc_requests.created_at');

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
            'index' => 'request_title',
            'label' => 'Request Title',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return '<div class="flex flex-col">
                    <div class="text-sm font-semibold text-gray-900">' . e($row->request_title) . '</div>
                    <div class="text-xs text-gray-500 truncate max-w-xs">' . (e($row->request_message) ?: 'No message') . '</div>
                </div>';
            },
        ]);

        $this->addColumn([
            'index' => 'kyc_type_name',
            'label' => 'KYC Type',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-emerald-100 text-emerald-700">
                    ' . e($row->kyc_type_name) . '
                </span>';
            },
        ]);

        $this->addColumn([
            'index' => 'requester_name',
            'label' => 'Requested By',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return '<div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8">
                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-primary to-primary-500 flex items-center justify-center text-white font-semibold text-xs">
                            ' . strtoupper(substr($row->requester_name, 0, 2)) . '
                        </div>
                    </div>
                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900">' . e($row->requester_name) . '</div>
                    </div>
                </div>';
            },
        ]);

        $this->addColumn([
            'index' => 'request_due_date',
            'label' => 'Due Date',
            'type' => 'date',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->request_due_date) {
                    $dueDate = \Carbon\Carbon::parse($row->request_due_date)->startOfDay();
                    $today   = now()->startOfDay();

                    $daysLeft = $today->diffInDays($dueDate, false); // <-- FIX

                    if ($daysLeft < 0) {
                        return '<div class="flex items-center">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                <span class="material-icons-outlined text-xs mr-1">warning</span>
                                ' . $dueDate->format('M d, Y') . '
                            </span>
                        </div>';
                    } else {
                        return '<div class="flex items-center">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                <span class="material-icons-outlined text-xs mr-1">schedule</span>
                                ' . $dueDate->format('M d, Y') . '
                            </span>
                            <span class="ml-2 text-xs text-gray-500">' . $daysLeft . ' days left</span>
                        </div>';
                    }
                }

                return '<span class="text-xs text-gray-400">No deadline</span>';
            },
        ]);


        $this->addColumn([
            'index' => 'request_created_at',
            'label' => 'Created At',
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
            'closure' => function ($row) {
                return '<div class="text-sm text-gray-600">
                    <div>' . date('M d, Y', strtotime($row->request_created_at)) . '</div>
                    <div class="text-xs text-gray-400">' . date('h:i A', strtotime($row->request_created_at)) . '</div>
                </div>';
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
        if (bouncer()->hasPermission('admin.kyc.requests.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.kyc.requests.destroy', $row->id);
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
        if (bouncer()->hasPermission('admin.kyc.requests.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create New Request',
                'method' => 'GET',
                'action' => 'text-white bg-primary hover:bg-primary-600',
                'url' => 'admin.kyc.requests.create',
            ]);
        }

        // Add a bulk-delete mass action so the dataview shows bulk-select checkboxes
        if (bouncer()->hasPermission('admin.kyc.requests.destroy')) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => route('admin.kyc.requests.bulk-destroy'),
            ]);
        }
    }
}
