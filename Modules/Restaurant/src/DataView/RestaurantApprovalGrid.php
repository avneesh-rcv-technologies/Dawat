<?php

namespace Modules\Restaurant\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Enums\OnboardingStep;

class RestaurantApprovalGrid extends DataGrid
{
    protected $primaryColumn = 'id';
    protected $itemsPerPage  = 20;
    protected $sortOrder     = 'desc';

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        $query = DB::table('restaurants')
            ->leftJoin('admins', 'restaurants.admin_id', '=', 'admins.id')
            ->select(
                'restaurants.id',
                'restaurants.name as restaurant_name',
                'restaurants.status',
                'restaurants.onboarding_status',
                'restaurants.created_at',
                'admins.name as owner_name',
                'admins.email as owner_email'
            )
            ->whereIn('restaurants.approval_status', [
                'pending',
                'approved',
                'rejected',
            ]);

        $pendingSteps = DB::table('restaurant_onboarding_steps')
            ->selectRaw("GROUP_CONCAT(CONCAT(step_key, ':', status) ORDER BY id SEPARATOR '|')")
            ->whereColumn('restaurant_id', 'restaurants.id')
            ->whereIn('status', ['submitted', 'pending', 'under_review', 'resubmitted']);
        $query->selectSub($pendingSteps, 'pending_approval_items');

        $pendingKycDocuments = DB::table('kyc_submissions')
            ->join('kyc_types', 'kyc_types.id', '=', 'kyc_submissions.kyc_type_id')
            ->selectRaw("GROUP_CONCAT(DISTINCT kyc_types.name ORDER BY kyc_types.id SEPARATOR '|')")
            ->whereColumn('kyc_submissions.user_id', 'restaurants.admin_id')
            ->where('kyc_types.type', 'kyc')
            ->whereIn('kyc_submissions.status', ['pending', 'under_review']);
        $query->selectSub($pendingKycDocuments, 'pending_kyc_documents');

        $this->addFilter('id', 'restaurants.id');
        app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)
            ->scope($query, auth('admin')->user(), 'restaurants.admin_id');
        $this->addFilter('restaurant_name', 'restaurants.name');
        $this->addFilter('owner_name', 'admins.name');
        $this->addFilter('owner_email', 'admins.email');
        $this->addFilter('status', 'restaurants.status');
        $this->addFilter('onboarding_status', 'restaurants.onboarding_status');
        $this->addFilter('created_at', 'restaurants.created_at');

        return $query;
    }

    /**
     * Add columns.
     */
    public function prepareColumns()
    {
        // ID
        $this->addColumn([
            'index'      => 'id',
            'label'      => 'ID',
            'type'       => 'integer',
            'sortable'   => true,
            'visibility' => false,
        ]);

        // Restaurant Name
        $this->addColumn([
            'index'      => 'restaurant_name',
            'label'      => 'Restaurant Name',
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
        ]);

        // Owner Name
        $this->addColumn([
            'index'      => 'owner_name',
            'label'      => 'Owner Name',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        // Email
        $this->addColumn([
            'index'      => 'owner_email',
            'label'      => 'Email',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => false,
        ]);

        // Status
        $this->addColumn([
            'index' => 'status',
            'label' => 'Restaurant Status',
            'type' => 'string',
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Draft', 'value' => 'draft'],
                ['label' => 'Active', 'value' => 'active'],
                ['label' => 'Inactive', 'value' => 'inactive'],
                ['label' => 'Closed', 'value' => 'closed'],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                $status = strtolower($row->status ?? '');

                $colors = [
                    'draft' => 'bg-yellow-100 text-yellow-800',
                    'active' => 'bg-green-100 text-green-800',
                    'inactive' => 'bg-red-100 text-red-800',
                    'closed' => 'bg-red-100 text-red-800',
                ];

                $class = $colors[$status] ?? 'bg-gray-100 text-gray-800';

                return sprintf(
                    '<span class="px-2 py-1 text-xs font-semibold rounded-full %s">%s</span>',
                    $class,
                    e($status !== '' ? ucfirst($status) : 'N/A')
                );
            },
        ]);

        // Onboarding Status
        $this->addColumn([
            'index' => 'onboarding_status',
            'label' => 'Onboarding Status',
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => array_map(fn (OnboardingStatus $status) => [
                'label' => $status->label(),
                'value' => $status->value,
            ], [
                OnboardingStatus::DRAFT,
                OnboardingStatus::UNDER_REVIEW,
                OnboardingStatus::ACTION_REQUIRED,
                OnboardingStatus::APPROVED,
            ]),
            'closure' => function ($row) {
                $status = OnboardingStatus::tryFrom($row->onboarding_status ?? '');

                return sprintf(
                    '<span class="px-2 py-1 text-xs font-semibold rounded-full %s">%s</span>',
                    $status?->color() ?? 'bg-gray-100 text-gray-800',
                    e($status?->label() ?? 'Not started')
                );
            },
        ]);

        $this->addColumn([
            'index' => 'pending_approval_items',
            'label' => 'Items Awaiting Approval',
            'type' => 'string',
            'sortable' => false,
            'searchable' => false,
            'filterable' => false,
            'closure' => function ($row) {
                $items = collect(explode('|', (string) ($row->pending_approval_items ?? '')))
                    ->filter()
                    ->map(function (string $item) {
                        [$key, $status] = array_pad(explode(':', $item, 2), 2, '');
                        $step = OnboardingStep::tryFrom($key);
                        $reviewStatus = OnboardingStatus::tryFrom($status)
                            ?? (in_array($status, ['pending', 'resubmitted'], true) ? OnboardingStatus::SUBMITTED : null);
                        if (!$step || !$reviewStatus || $step === OnboardingStep::KYC) {
                            return null;
                        }

                        return $step->label();
                    })
                    ->filter();

                $kycNames = collect(explode('|', (string) ($row->pending_kyc_documents ?? '')))
                    ->filter(fn ($name) => is_string($name) && trim($name) !== '')
                    ->map(fn ($name) => trim($name))
                    ->unique();
                $hasPendingKycStep = collect(explode('|', (string) ($row->pending_approval_items ?? '')))
                    ->contains(fn ($item) => str_starts_with($item, OnboardingStep::KYC->value . ':'));
                if ($hasPendingKycStep && $kycNames->isEmpty()) {
                    $kycNames->push(OnboardingStep::KYC->label());
                }
                $items = $items->merge($kycNames)->unique()->values();

                if ($items->isEmpty()) {
                    return '<span class="text-xs text-gray-500">No pending approvals</span>';
                }

                $visibleItems = $items->take(1);
                $moreCount = $items->count() - $visibleItems->count();
                $allNames = e($items->implode(', '));
                $html = '<div class="approval-pending-items" title="' . $allNames . '">';
                $html .= '<span class="approval-pending-name"><span class="approval-pending-accent" aria-hidden="true"></span><span class="approval-pending-name-text">'
                    . e($visibleItems->first()) . '</span></span>';
                if ($moreCount > 0) {
                    $html .= '<span class="approval-pending-count" aria-label="' . $moreCount . ' more items">+'
                        . $moreCount . '</span>';
                }
                $html .= '</div>';

                return $html;
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Applied',
            'type' => 'date',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
        ]);
    }

    /**
     * Row actions.
     */
    public function prepareActions()
    {
        if (bouncer('admin')->hasPermission('admin.restaurant.approvals.show')) {
            $this->addAction([
                'icon'   => 'visibility',
                'title'  => 'View Details',
                'method' => 'GET',
                'url'    => fn($row) => route('admin.restaurant.approvals.show', $row->id),
            ]);
        }
    }

    /**
     * Mass actions.
     */
    public function prepareMassActions()
    {
        // No mass actions for approvals - each should be reviewed individually
    }
}
