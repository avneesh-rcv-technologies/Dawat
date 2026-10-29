<?php

namespace Modules\Twilio\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class SmsTemplateGrid extends DataGrid
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
        return DB::table('sms_templates')
            ->select(
                'id',
                'name',
                'subject',
                'content as body',
                'is_active',
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
            'index' => 'subject',
            'label' => 'Subject',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'body',
            'label' => 'Body',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'is_active',
            'label' => 'Status',
            'type' => 'boolean',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Created At',
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => true,
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
        if (bouncer()->hasPermission('sms-templates.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Update Template',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('sms-templates.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('sms-templates.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('sms-templates.destroy', $row->id);
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
        if (bouncer()->hasPermission('sms-templates.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Template',
                'method' => 'GET',
                'action' => 'text-amber-100 bg-primary-100',
                'url' => 'sms-templates.create',
            ]);
        }
    }
}