<?php

namespace Modules\Rating\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class RatingsGrid extends DataGrid
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
        return DB::table('ratings')
            ->leftJoin('users', 'ratings.user_id', '=', 'users.id')
            ->select(
                'ratings.id',
                'ratings.ratable_type',
                'ratings.ratable_id',
                'ratings.user_id',
                'ratings.guest_name',
                'ratings.guest_email',
                'ratings.rating',
                'ratings.review',
                'ratings.is_approved',
                'ratings.status',
                'ratings.created_at',
                'users.name as user_name'
            );
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
            'index' => 'reviewer',
            'label' => 'Reviewer',
            'type' => 'string',
            'searchable' => false,
            'sortable' => false,
            'closure' => function ($row) {
                if ($row->user_id) {
                    return '<div class="flex items-center">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            User
                        </span>
                        <span class="ml-2">' . e($row->user_name) . '</span>
                    </div>';
                } else {
                    return '<div class="flex items-center">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                            Guest
                        </span>
                        <span class="ml-2">' . e($row->guest_name ?? $row->guest_email ?? 'Anonymous') . '</span>
                    </div>';
                }
            },
        ]);

        $this->addColumn([
            'index' => 'ratable_type',
            'label' => 'Item Type',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">' 
                    . e(class_basename($row->ratable_type)) . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'ratable_id',
            'label' => 'Item ID',
            'type' => 'integer',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'rating',
            'label' => 'Rating',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'allow_multiple_values' => true,
            'filterable_options' => [
                ['label' => '⭐ 1 Star', 'value' => 1],
                ['label' => '⭐⭐ 2 Stars', 'value' => 2],
                ['label' => '⭐⭐⭐ 3 Stars', 'value' => 3],
                ['label' => '⭐⭐⭐⭐ 4 Stars', 'value' => 4],
                ['label' => '⭐⭐⭐⭐⭐ 5 Stars', 'value' => 5],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                $stars = str_repeat('⭐', $row->rating);
                return '<span class="text-lg">' . $stars . '</span> <span class="text-sm text-gray-600">(' . $row->rating . '/5)</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'review',
            'label' => 'Review',
            'type' => 'string',
            'searchable' => true,
            'sortable' => false,
            'closure' => function ($row) {
                if ($row->review) {
                    return '<div class="max-w-md truncate" title="' . e($row->review) . '">' . e(substr($row->review, 0, 100)) . '...</div>';
                }
                return '<span class="text-gray-400 italic">No review text</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'is_approved',
            'label' => 'Approval Status',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'allow_multiple_values' => false,
            'filterable_options' => [
                ['label' => 'Approved', 'value' => 1],
                ['label' => 'Pending', 'value' => 0],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                return $row->is_approved
                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Approved</span>'
                    : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'allow_multiple_values' => false,
            'filterable_options' => [
                ['label' => 'Active', 'value' => 1],
                ['label' => 'Inactive', 'value' => 0],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                return $row->status
                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>'
                    : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Created At',
            'type' => 'date',
            'searchable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('ratings.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'is_popup' => true,
                'url' => function ($row) {
                    return route('ratings.edit', $row->id);
                },
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('ratings.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('ratings.destroy', $row->id);
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
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('ratings.bulk-delete')) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'POST',
                'action' => 'text-amber-100 bg-primary-100',
                'url' => 'ratings.bulk-delete'
            ]);
        }
    }
}
