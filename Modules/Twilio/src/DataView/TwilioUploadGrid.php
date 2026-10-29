<?php

namespace Modules\Twilio\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class TwilioUploadGrid extends DataGrid
{
    protected $primaryColumn = 'id';
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('twilio_phone_uploads')
            ->leftJoin('admins', 'twilio_phone_uploads.uploaded_by', '=', 'admins.id')
            ->select(
                'twilio_phone_uploads.id',
                'twilio_phone_uploads.filename',
                'twilio_phone_uploads.status',
                'twilio_phone_uploads.row_count',
                'admins.name as admin_name',
                'twilio_phone_uploads.created_at',
                'twilio_phone_uploads.retry_count'
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
            'index' => 'filename',
            'label' => 'File Name',
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
            'index' => 'row_count',
            'label' => 'Rows',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);
        $this->addColumn([
            'index' => 'retry_count',
            'label' => 'Retries',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);
        $this->addColumn([
            'index' => 'admin_name',
            'label' => 'Admin',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => false,
        ]);
        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Uploaded At',
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
            'icon' => 'open_in_new',
            'title' => 'View Progress',
            'method' => 'GET',
            'url' => function ($row) {
                return route('admin.twilio.upload-progress', $row->id);
            },
        ]);
        $this->addAction([
            'icon' => 'delete',
            'title' => 'Delete',
            'method' => 'DELETE',
            'url' => function ($row) {
                return route('admin.twilio.destroy', $row->id);
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
            'icon' => 'delete',
            'title' => 'Bulk Delete',
            'method' => 'POST',
            'action' => 'text-red-500 bg-red-100',
            'url' => 'admin.twilio.uploads.bulk-delete',
        ]);
    }
}
