<?php

namespace Modules\KYC\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class KycTypeGrid extends DataGrid
{
    protected $primaryColumn = "id";
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('kyc_types')
            ->leftJoin('admins', 'kyc_types.created_by', '=', 'admins.id')
            ->select(
                'kyc_types.id',
                'kyc_types.name',
                'kyc_types.description',
                'kyc_types.is_active',
                'kyc_types.created_at',
                'kyc_types.updated_at',
                'admins.name as creator_name'
            )
            ->selectRaw('(SELECT COUNT(*) FROM kyc_fields WHERE kyc_fields.kyc_type_id = kyc_types.id) as fields_count')
            ->selectRaw('(SELECT COUNT(*) FROM kyc_submissions WHERE kyc_submissions.kyc_type_id = kyc_types.id) as submissions_count');
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
            'index' => 'name',
            'label' => 'KYC Type',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return '<div class="flex flex-col">
                    <div class="text-sm font-semibold text-gray-900">' . e($row->name) . '</div>
                    <div class="text-xs text-gray-500">' . (e($row->description) ?: 'No description') . '</div>
                </div>';
            },
        ]);

        $this->addColumn([
            'index' => 'fields_count',
            'label' => 'Fields',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                $color = $row->fields_count > 0 ? 'primary' : 'gray';
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-' . $color . '-100 text-' . ($color === 'primary' ? 'primary-800' : 'gray-800') . '">
                    ' . $row->fields_count . ' field' . ($row->fields_count != 1 ? 's' : '') . '
                </span>';
            },
        ]);

        $this->addColumn([
            'index' => 'submissions_count',
            'label' => 'Submissions',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->submissions_count > 0) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        ' . $row->submissions_count . ' submission' . ($row->submissions_count != 1 ? 's' : '') . '
                    </span>';
                }
                return '<span class="text-xs text-gray-400">No submissions</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'is_active',
            'label' => 'Status',
            'type' => 'boolean',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Active', 'value' => 1],
                ['label' => 'Inactive', 'value' => 0],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->is_active) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <svg class="mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3"/>
                        </svg>
                        Active
                    </span>';
                } else {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <svg class="mr-1.5 h-2 w-2 text-red-400" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3"/>
                        </svg>
                        Inactive
                    </span>';
                }
            },
        ]);

        $this->addColumn([
            'index' => 'creator_name',
            'label' => 'Created By',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="text-sm text-gray-700">' . e($row->creator_name ?: 'System') . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Created At',
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
            'index' => 'updated_at',
            'label' => 'Last Updated',
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->updated_at && $row->updated_at !== $row->created_at) {
                    $updatedAt = \Carbon\Carbon::parse($row->updated_at);
                    return '<div class="text-sm text-gray-600">
                        <div>' . $updatedAt->format('M d, Y') . '</div>
                        <div class="text-xs text-gray-400">' . $updatedAt->diffForHumans() . '</div>
                    </div>';
                }
                return '<span class="text-xs text-gray-400">Not updated</span>';
            },
        ]);

        // Map filterable/searchable columns to their fully qualified names to avoid ambiguity
        $this->addFilter('id', 'kyc_types.id');
        $this->addFilter('name', 'kyc_types.name');
        $this->addFilter('is_active', 'kyc_types.is_active');
        $this->addFilter('creator_name', 'admins.name');
        $this->addFilter('created_at', 'kyc_types.created_at');
        $this->addFilter('updated_at', 'kyc_types.updated_at');
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('admin.kyc.types.fields')) {
            $this->addAction([
                'icon' => 'list',
                'title' => 'Manage Fields',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.kyc.types.fields', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('admin.kyc.types.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.kyc.types.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('admin.kyc.types.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.kyc.types.destroy', $row->id);
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
        $this->addMassAction([
            'icon' => 'delete',
            'title' => 'Bulk Delete',
            'method' => 'POST',
            'action' => 'text-white bg-red-600',
            'url' => 'admin.kyc.types.bulk-delete',
        ]);

        if (bouncer()->hasPermission('admin.kyc.types.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Add New KYC Type',
                'method' => 'GET',
                'action' => 'text-white bg-primary hover:bg-primary-600',
                'url' => 'admin.kyc.types.create',
            ]);
        }
    }
}
