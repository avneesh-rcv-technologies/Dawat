<?php

namespace Modules\DietaryManagment\DataView;

use Illuminate\Support\Facades\DB;
use Modules\DataView\DataGrid;
use Modules\DietaryManagment\Models\RestaurantCertification;
use Modules\DietaryManagment\Services\CertificationAccess;

class CertificationGrid extends DataGrid
{
    protected $primaryColumn = 'id';
    protected $sortColumn = 'submitted_at';
    protected $sortOrder = 'desc';

    public function prepareQueryBuilder()
    {
        $certifications = DB::table('restaurant_certifications as rc')
            ->leftJoin('restaurants', 'restaurants.id', '=', 'rc.restaurant_id')
            ->leftJoin('certification_types as ct', 'ct.id', '=', 'rc.certification_type_id')
            ->leftJoin('dietary_labels as dl', 'dl.id', '=', 'ct.dietary_label_id')
            ->leftJoin('admins', 'admins.id', '=', 'rc.reviewed_by')
            ->select(
                'rc.id', 'restaurants.name as restaurant_name', 'ct.name as certification_name',
                'dl.name as dietary_label', 'rc.file_name', 'rc.status', 'rc.admin_notes',
                'admins.name as reviewer', 'rc.submitted_at', 'rc.reviewed_at'
            );

        app(CertificationAccess::class)->scope($certifications, auth('admin')->user(), 'rc.restaurant_id');

        return DB::query()->fromSub($certifications, 'certifications');
    }

    public function prepareColumns()
    {
        $this->addColumn(['index' => 'id', 'label' => 'ID', 'type' => 'integer', 'visibility' => false]);

        foreach ([
            'restaurant_name' => 'Restaurant', 'certification_name' => 'Certification',
            'dietary_label' => 'Dietary Label', 'file_name' => 'Document', 'reviewer' => 'Reviewed By',
        ] as $index => $label) {
            $this->addColumn([
                'index' => $index, 'label' => $label, 'type' => 'string',
                'sortable' => true, 'searchable' => true,
            ]);
        }

        $this->addColumn([
            'index' => 'status', 'label' => 'Status', 'type' => 'string', 'sortable' => true,
            'filterable' => true, 'filterable_type' => 'dropdown',
            'filterable_options' => array_map(fn ($status) => [
                'label' => ucfirst(str_replace('_', ' ', $status)), 'value' => $status,
            ], RestaurantCertification::STATUSES),
            'closure' => function ($row) {
                $color = match ($row->status) {
                    'approved' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-800',
                    'changes_requested' => 'bg-orange-100 text-orange-800',
                    'submitted', 'under_review' => 'bg-blue-100 text-blue-800',
                    default => 'bg-gray-100 text-gray-700',
                };
                return '<span class="rounded-full px-2.5 py-0.5 text-xs '.$color.'">'
                    .e(ucfirst(str_replace('_', ' ', $row->status))).'</span>';
            },
        ]);

        foreach (['submitted_at' => 'Submitted At', 'reviewed_at' => 'Reviewed At'] as $index => $label) {
            $this->addColumn([
                'index' => $index, 'label' => $label, 'type' => 'datetime', 'sortable' => true,
            ]);
        }

        $this->addColumn([
            'index' => 'admin_notes', 'label' => 'Admin Notes', 'type' => 'string', 'searchable' => true,
        ]);
    }

    public function prepareActions()
    {
        if (bouncer('admin')->hasPermission('admin.dietarymanagment.certifications.show')) {
            $ownerSide = app(CertificationAccess::class)->isOwner(auth('admin')->user());
            $this->addAction(['icon' => 'visibility', 'title' => $ownerSide ? 'View' : 'View / Review', 'method' => 'GET',
                'url' => fn ($row) => route('admin.dietarymanagment.certifications.show', $row->id)]);
        }
    }
}
