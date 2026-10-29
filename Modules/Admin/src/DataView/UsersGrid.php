<?php

namespace Modules\Admin\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class UsersGrid extends DataGrid
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
        return DB::table('admins')
            ->select(
                'id',
                'name',
                'email',
                'status',
                'role_id',
                'created_at'
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
            'index' => 'name',
            'label' => 'Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'email',
            'label' => "Email",
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
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
                [
                    'label' => 'Active',
                    'value' => 1,
                ],
                [
                    'label' => 'Inactive',
                    'value' => 0,
                ],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                return $row->status
                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>'
                    : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>';
            },
        ]);

        $roles = DB::table('roles')->pluck('name', 'id');
        $this->addColumn([
            'index' => 'role_id',
            'label' => 'Role name',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable' => false,
            'closure' => function ($row) use ($roles) {
                return $roles[$row->role_id] ?? 'Unknown';
            },
            'filterable_type' => 'dropdown',
            'filterable_options' => collect($roles)->map(function ($name, $id) {
                return [
                    'label' => $name,
                    'value' => $id
                ];
            })->values()->toArray(),
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
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('users.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'is_popup' => true,
                'url' => function ($row) {
                    return route('users.edit', $row->id);
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
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('users.toggle-status')) {
            $this->addMassAction([
                'icon' => '',
                'title' => 'Status',
                'method' => 'POST',
                'url' => 'users.toggle-status',
                'action' => 'bg-gray-500',
                'options' => [
                    [
                        'label' => 'Active',
                        'value' => 1,
                    ],
                    [
                        'label' => 'Inactive',
                        'value' => 0,
                    ],
                ],
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('users.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create User',
                'is_popup' => true,
                'method' => 'GET',
                'action' => 'text-white bg-blue-500',
                'url' => 'users.create',
            ]);
        }
    }
}
