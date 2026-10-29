<?php

namespace Modules\KYC\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class KycSubmissionGroupedGrid extends DataGrid
{
    protected $primaryColumn = "user_id";
    protected $itemsPerPage = 15;
    protected $sortOrder = 'desc';
    protected $sortColumn = 'last_submission_date';

    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('kyc_submissions')
            ->join('admins as restaurant_admins', 'kyc_submissions.user_id', '=', 'restaurant_admins.id')
            ->when(function_exists('fn_admin_portal_role_id'), function ($query) {
                $query->where('restaurant_admins.role_id', fn_admin_portal_role_id('restaurant_owner'));
            })
            ->select(
                'kyc_submissions.user_id',
                'restaurant_admins.name as owner_name',
                'restaurant_admins.email as owner_email',
                DB::raw('COUNT(kyc_submissions.id) as total_documents'),
                DB::raw('SUM(CASE WHEN kyc_submissions.status = "pending" THEN 1 ELSE 0 END) as pending_documents'),
                DB::raw('SUM(CASE WHEN kyc_submissions.status = "under_review" THEN 1 ELSE 0 END) as under_review_documents'),
                DB::raw('SUM(CASE WHEN kyc_submissions.status = "approved" THEN 1 ELSE 0 END) as approved_documents'),
                DB::raw('SUM(CASE WHEN kyc_submissions.status = "rejected" THEN 1 ELSE 0 END) as rejected_documents'),
                DB::raw('MAX(kyc_submissions.created_at) as last_submission_date')
            )
            ->groupBy('kyc_submissions.user_id', 'restaurant_admins.name', 'restaurant_admins.email');

        app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)
            ->scope($queryBuilder, auth('admin')->user(), 'kyc_submissions.user_id');
        $this->addFilter('owner_name', 'restaurant_admins.name');
        $this->addFilter('owner_email', 'restaurant_admins.email');

        return $queryBuilder;
    }

    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'owner_name',
            'label' => 'Restaurant Owner',
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
            'index' => 'total_documents',
            'label' => 'Total Docs',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'pending_documents',
            'label' => 'Pending',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return $row->pending_documents > 0 ? '<span class="text-yellow-600 font-bold">' . $row->pending_documents . '</span>' : '0';
            },
        ]);

        $this->addColumn([
            'index' => 'under_review_documents',
            'label' => 'Under Review',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return $row->under_review_documents > 0 ? '<span class="text-blue-600 font-bold">' . $row->under_review_documents . '</span>' : '0';
            },
        ]);

        $this->addColumn([
            'index' => 'approved_documents',
            'label' => 'Approved',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return $row->approved_documents > 0 ? '<span class="text-green-600 font-bold">' . $row->approved_documents . '</span>' : '0';
            },
        ]);

        $this->addColumn([
            'index' => 'last_submission_date',
            'label' => 'Last Submission',
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return '<div class="text-sm text-gray-600">' . date('M d, Y h:i A', strtotime($row->last_submission_date)) . '</div>';
            },
        ]);
    }

    public function prepareActions()
    {
        $this->addAction([
            'icon' => 'folder_open',
            'title' => 'View Documents',
            'method' => 'GET',
            'url' => function ($row) {
                return route('admin.kyc.submissions.index', ['user_id' => $row->user_id]);
            },
        ]);
    }
}
