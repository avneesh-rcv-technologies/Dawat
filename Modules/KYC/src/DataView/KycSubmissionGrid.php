<?php

namespace Modules\KYC\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class KycSubmissionGrid extends DataGrid
{
    protected $primaryColumn = "id";
    protected $itemsPerPage = 15;
    protected $sortOrder = 'desc';

    public $userId;

    public function __construct($userId = null)
    {
        $this->userId = $userId;
    }

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('kyc_submissions')
            ->leftJoin('admins as restaurant_admins', 'kyc_submissions.user_id', '=', 'restaurant_admins.id')
            ->leftJoin('kyc_types', 'kyc_submissions.kyc_type_id', '=', 'kyc_types.id')
            ->leftJoin('admins as reviewers', 'kyc_submissions.reviewed_by', '=', 'reviewers.id')
            ->select(
                'kyc_submissions.id',
                'kyc_submissions.user_id',
                'kyc_submissions.status',
                'kyc_submissions.created_at',
                'kyc_submissions.reviewed_at',
                'kyc_submissions.rejection_reason',
                'kyc_types.name as kyc_type_name',
                'reviewers.name as reviewer_name',
                'restaurant_admins.email as owner_email',
                DB::raw("restaurant_admins.name as owner_name")
            );

        if ($this->userId) {
            $queryBuilder->where('kyc_submissions.user_id', $this->userId);
        }
        app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)
            ->scope($queryBuilder, auth('admin')->user(), 'kyc_submissions.user_id');

        $this->addFilter('id', 'kyc_submissions.id');
        $this->addFilter('status', 'kyc_submissions.status');
        $this->addFilter('created_at', 'kyc_submissions.created_at');
        $this->addFilter('reviewed_at', 'kyc_submissions.reviewed_at');
        $this->addFilter('owner_name', DB::raw("COALESCE(restaurant_admins.name, '')"));
        $this->addFilter('owner_email', 'restaurant_admins.email');
        $this->addFilter('kyc_type_name', 'kyc_types.name');
        $this->addFilter('reviewer_name', 'reviewers.name');

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

        // $this->addColumn([
        //     'index' => 'user_id',
        //     'label' => 'User',
        //     'type' => 'integer',
        //     'searchable' => true,
        //     'filterable' => true,
        //     'sortable' => true,
        //     'closure' => function ($row) {
        //         return '<div class="flex items-center">
        //             <div class="flex-shrink-0 h-8 w-8">
        //                 <div class="h-8 w-8 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold text-xs">
        //                     U' . $row->user_id . '
        //                 </div>
        //             </div>
        //             <div class="ml-3">
        //                 <div class="text-sm font-medium text-gray-900">User #' . $row->user_id . '</div>
        //             </div>
        //         </div>';
        //     },
        // ]);
        $this->addColumn([
            'index' => 'owner_name',
            'label' => 'User',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                $name = $row->owner_name ?: 'User #' . $row->user_id;
                $email = $row->owner_email ? '<div class="text-xs text-gray-500">' . e($row->owner_email) . '</div>' : '<div class="text-xs text-gray-400">No email</div>';
                return '<div class="space-y-1">
                    <div class="text-sm font-semibold text-gray-900">' . e($name) . '</div>
                    ' . $email . '
                </div>';
            },
        ]);

        $this->addColumn([
            'index' => 'kyc_type_name',
            'label' => 'KYC Type',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-indigo-100 text-indigo-800">
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
                ['label' => 'Pending', 'value' => 'pending'],
                ['label' => 'Under Review', 'value' => 'under_review'],
                ['label' => 'Approved', 'value' => 'approved'],
                ['label' => 'Rejected', 'value' => 'rejected'],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                $statusConfig = [
                    'pending' => ['color' => 'yellow', 'icon' => 'clock', 'label' => 'Pending'],
                    'under_review' => ['color' => 'blue', 'icon' => 'eye', 'label' => 'Under Review'],
                    'approved' => ['color' => 'green', 'icon' => 'check_circle', 'label' => 'Approved'],
                    'rejected' => ['color' => 'red', 'icon' => 'cancel', 'label' => 'Rejected'],
                ];

                $config = $statusConfig[$row->status] ?? $statusConfig['pending'];

                $reasonHtml = '';
                if ($row->status === 'rejected' && !empty($row->rejection_reason)) {
                    $reasonHtml = '<div class="text-xs text-red-600 font-normal mt-1 max-w-xs break-words">' . e($row->rejection_reason) . '</div>';
                }

                return '<div><span class="inline-flex items-center px-3 py-1 rounded-full text-left text-xs font-semibold bg-' . $config['color'] . '-100 text-' . $config['color'] . '-800">
                    ' . $config['label'] . '
                </span>' . $reasonHtml . '</div>';
            },
        ]);

        $this->addColumn([
            'index' => 'reviewer_name',
            'label' => 'Reviewed By',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->reviewer_name) {
                    return '<span class="text-sm text-gray-700">' . e($row->reviewer_name) . '</span>';
                }
                return '<span class="text-xs text-gray-400">Not reviewed</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Submitted At',
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
            'closure' => function ($row) {
                return '<div class="text-sm text-gray-600">
                    <div>' . date('M d, Y', strtotime($row->created_at)) . '</div>
                    <div class="text-xs text-gray-400">' . date('h:i A', strtotime($row->created_at)) . '</div>
                </div>';
            },
        ]);

        $this->addColumn([
            'index' => 'reviewed_at',
            'label' => 'Reviewed At',
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->reviewed_at) {
                    return '<span class="text-sm text-gray-600">' . date('M d, Y h:i A', strtotime($row->reviewed_at)) . '</span>';
                }
                return '<span class="text-xs text-gray-400">-</span>';
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
                'title' => 'View Details',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.kyc.submissions.show', $row->id);
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
        // Bulk review actions (mark under review / approve / reject)
        if (bouncer()->hasPermission('admin.kyc.submissions.review')) {
            $this->addMassAction([
                'icon' => '',
                'title' => 'Review',
                'method' => 'POST',
                'url' => route('admin.kyc.submissions.bulk-review'),
                'action' => 'bg-emerald-600 text-white',
                'options' => [
                    ['label' => 'Mark Under Review', 'value' => 'under_review'],
                    ['label' => 'Approve', 'value' => 'approve'],
                    ['label' => 'Reject', 'value' => 'reject'],
                ],
            ]);
        }
    }
}
