<?php

namespace Modules\Twilio\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class AutomationDataGrid extends DataGrid
{
    protected $primaryColumn = 'id';
    protected $itemsPerPage = 15;
    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('twilio_messaging_automation')
            ->leftJoin('admins', 'twilio_messaging_automation.admin_id', '=', 'admins.id')
            ->select(
                'twilio_messaging_automation.id',
                'twilio_messaging_automation.keyword',
                'twilio_messaging_automation.response_type',
                'twilio_messaging_automation.is_active',
                'twilio_messaging_automation.priority',
                'twilio_messaging_automation.exact_match',
                'twilio_messaging_automation.case_sensitive',
                'twilio_messaging_automation.created_at',
                'twilio_messaging_automation.updated_at',
                'admins.name as admin_name'
            );
    }

    /**
     * Prepare columns.
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
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'keyword',
            'label' => 'Keyword',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'response_type',
            'label' => 'Response Type',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'formatter' => function($value) {
                return $value === 'chatgpt' ? 'ChatGPT' : 'Text';
            }
        ]);

        $this->addColumn([
            'index' => 'is_active',
            'label' => 'Status',
            'type' => 'boolean',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'formatter' => function($value) {
                return $value ? 'Active' : 'Inactive';
            }
        ]);

        $this->addColumn([
            'index' => 'priority',
            'label' => 'Priority',
            'type' => 'integer',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'admin_name',
            'label' => 'Created By',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => false,
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Created At',
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'updated_at',
            'label' => 'Updated At',
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
        $this->addAction([
            'icon' => 'edit',
            'title' => 'Edit',
            'method' => 'GET',
            'url' => function ($row) {
                return route('admin.automation.edit', $row->id);
            },
        ]);

        $this->addAction([
            'icon' => 'delete',
            'title' => 'Delete',
            'method' => 'DELETE',
            'url' => function ($row) {
                return route('admin.automation.destroy', $row->id);
            },
        ]);
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        $this->addMassAction([
            'icon' => 'add',
            'title' => 'Create New Rule',
            'method' => 'GET',
            'action' => 'text-amber-100 bg-primary-100',
            'url' => 'admin.automation.create',
        ]);

        $this->addMassAction([
            'icon' => 'delete',
            'title' => 'Delete Selected',
            'method' => 'POST',
            'action' => 'text-red-500 bg-red-100',
            'url' => 'admin.automation.bulk-delete',
        ]);
    }

}
