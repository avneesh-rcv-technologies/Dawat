# DataGrid Module
- using this module you can manage data grid easliy with all neccessary features: Export, Sorting, Pagination, Filteration, Searching.

## How to use
- add this module in your `composer.json` file.
- Create own DataGrid Controller for your listings for example.
    - I want to show `users` listing then i will create a Grid with `UsersGrid` name then extend `DataGrid`.
    - After that i will add coulmns that i want to show with sorting, filter, searching, etc action mention in column
    - For massactions we will also add columns
```
<?php

namespace Modules\Admin\DataView\Settings;

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
            'searchable' => true,
            'filterable' => false,
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
                    'label' => 'Disabled',
                    'value' => 0,
                ],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->status) {
                    return 'Active';
                }
                return 'Disabled';
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
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('settings.users.show')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'is_popup'  => true,
                'url' => function ($row) {
                    return route('settings.users.show', $row->id);
                },
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('settings.users.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('settings.users.destroy', $row->id);
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

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('settings.users.toggle-status')) {
            $this->addMassAction([
                'icon' => 'settings',
                'title' => 'Status',
                'method' => 'POST',
                'url' => 'settings.users.toggle-status',
                'action' => 'bg-gray-500',
                'options' => [
                    [
                        'label' => 'Active',
                        'value' => 1,
                    ],
                    [
                        'label' => 'Disabled',
                        'value' => 0,
                    ],
                ],
            ]);
        }
    }
}
```