<?php

namespace Modules\Notifications\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class LogsGrid extends DataGrid
{
    protected $primaryColumn = "id";

    protected $itemsPerPage = 100;

    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
     *v c 
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('notifications_log')
            ->select(
                'id',
                'event_code',
                'channel_name',
                'notifiable_type',
                'notifiable_id',
                'status',
                'sent_at',
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
            'visibility' => false
        ]);

        $this->addColumn([
            'index' => 'event_code',
            'label' => 'Event',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'channel_name',
            'label' => 'Channel',
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
           
        ]);

        $this->addColumn([
            'index' => 'notifiable_type',
            'label' => 'Notify Model',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true
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
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('notification-logs.show')) {
            $this->addAction([
                'icon' => 'visibility',
                'title' => 'Open',
                'method' => 'GET',
                'is_popup' => true,
                'url' => function ($row) {
                    return route('notification-logs.show', $row->id);
                },
            ]);
        }

        // if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('notification-logs.destroy')) {
        //     $this->addAction([
        //         'icon' => 'delete',
        //         'title' => 'Delete',
        //         'method' => 'DELETE',
        //         'url' => function ($row) {
        //             return route('notification-logs.destroy', $row->id);
        //         },
        //     ]);
        // }
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        // no mass action
    }
}