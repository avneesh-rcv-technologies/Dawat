<?php

namespace Modules\EmailNotification\DataView;

use Illuminate\Http\Request;
use Modules\DataView\ColumnTypes\Integer;
use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class Template extends DataGrid
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
        return DB::table('email_templates')
            ->select(
                'id',
                'name',
                'subject',
                'status',
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
       'visibility'=>false,
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
            'index' => 'subject',
            'label' => 'Subject',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return ($row->status) ? 'Active' : 'Inactive';
            },
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                [
                    'label' => 'Active',
                    'value' => 1
                ],

                [
                    'label' => 'Inactive',
                    'value' => 0
                ]
            ],
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
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('notification.email.templates.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Update Template',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('notification.email.templates.edit', $row->id);
                },
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('notification.email.templates.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('notification.email.templates.destroy', $row->id);
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
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('notification.email.templates.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Template',
                'method' => 'GET',
                'action' => 'text-amber-100 bg-primary-100',
                'url' => 'notification.email.templates.create',
            ]);
        }
    }
}
