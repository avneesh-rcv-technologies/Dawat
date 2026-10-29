<?php

namespace Modules\Twilio\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;
use Modules\Twilio\Models\ComposeCampaign;

class ComposeGrid extends DataGrid
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
        return DB::table('twilio_compose_campaigns')
            ->leftJoin('admins', 'twilio_compose_campaigns.admin_id', '=', 'admins.id')
            ->select([
                'twilio_compose_campaigns.id',
                'twilio_compose_campaigns.name',
                'twilio_compose_campaigns.status',
                'twilio_compose_campaigns.total_numbers',
                'twilio_compose_campaigns.sent_count',
                'twilio_compose_campaigns.failed_count',
                'twilio_compose_campaigns.created_at',
                'admins.name as admin_name'
            ])
            ->orderBy('twilio_compose_campaigns.created_at', 'desc');
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
            'index' => 'name',
            'label' => 'Campaign Name',
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
        ]);

        $this->addColumn([
            'index' => 'total_numbers',
            'label' => 'Total Numbers',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'sent_count',
            'label' => 'Sent',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'failed_count',
            'label' => 'Failed',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
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
            'label' => 'Created',
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
                return route('admin.twilio.compose.edit', $row->id);
            },
        ]);

        $this->addAction([
            'icon' => 'visibility',
            'title' => 'View Details',
            'method' => 'GET',
            'url' => function ($row) {
                return route('admin.twilio.compose.show', $row->id);
            }
        ]);

        $this->addAction([
            'icon' => 'delete',
            'title' => 'Delete',
            'method' => 'DELETE',
            'url' => function ($row) {
                return route('admin.twilio.compose.destroy', $row->id);
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
            'title' => 'Compose',
            'method' => 'GET',
            'action' => 'text-amber-100 bg-primary-100',
            'url' => 'admin.twilio.compose.create',
        ]);

        $this->addMassAction([
            'icon' => 'delete',
            'title' => 'Delete Selected',
            'method' => 'POST',
            'action' => 'text-red-500 bg-red-100',
            'url' => 'admin.twilio.compose.mass-delete',
        ]);
    }
}
