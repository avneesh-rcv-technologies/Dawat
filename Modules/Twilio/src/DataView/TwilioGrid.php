<?php

namespace Modules\Twilio\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class TwilioGrid extends DataGrid
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
        // Build a derived table containing the latest message per phone number
        $lastMessagesSub = "(
            SELECT tm.phone_number_id,
                   tm.body AS message,
                   tm.delivery_sid AS delivery_sid,
                   tm.status AS status,
                   tm.sent_at AS sent_at,
                   tm.response_at AS response_at,
                   tm.response AS response
            FROM twilio_messages tm
            INNER JOIN (
                SELECT phone_number_id, MAX(created_at) AS max_created_at
                FROM twilio_messages
                GROUP BY phone_number_id
            ) lm ON tm.phone_number_id = lm.phone_number_id AND tm.created_at = lm.max_created_at
        ) as last_messages";

        return DB::table('twilio_phone_numbers')
            ->leftJoin('twilio_phone_uploads', 'twilio_phone_numbers.upload_id', '=', 'twilio_phone_uploads.id')
            ->leftJoin(DB::raw($lastMessagesSub), 'last_messages.phone_number_id', '=', 'twilio_phone_numbers.id')
            ->leftJoin('admins', 'twilio_phone_numbers.admin_id', '=', 'admins.id')
            ->select(
                'twilio_phone_numbers.id',
                'twilio_phone_numbers.phone_number',
                'twilio_phone_numbers.retry_count',
                // prefer latest message fields when available
                DB::raw('COALESCE(last_messages.status, twilio_phone_numbers.status) as status'),
                DB::raw('COALESCE(last_messages.delivery_sid, twilio_phone_numbers.delivery_sid) as delivery_sid'),
                DB::raw('COALESCE(last_messages.message, twilio_phone_numbers.message) as message'),
                DB::raw('COALESCE(last_messages.sent_at, twilio_phone_numbers.sent_at) as sent_at'),
                DB::raw('COALESCE(last_messages.response_at, twilio_phone_numbers.response_at) as response_at'),
                DB::raw('COALESCE(last_messages.response, twilio_phone_numbers.response) as response'),
                'twilio_phone_uploads.filename',
                'admins.name as admin_name',
                'twilio_phone_numbers.created_at'
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
            'index' => 'phone_number',
            'label' => 'Phone Number',
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
            'index' => 'message',
            'label' => 'Message',
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => false,
        ]);

        $this->addColumn([
            'index' => 'retry_count',
            'label' => 'Send',
            'type' => 'integer',
            'searchable' => true,
            'filterable' => false,
            'sortable' => false,
        ]);

        $this->addColumn([
            'index' => 'sent_at',
            'label' => 'Sent At',
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => true,
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

        $this->addAction([
            'icon' => 'open_in_new',
            'title' => 'View',
            'method' => 'GET',
            'url' => function ($row) {
                return route('admin.twilio.show', $row->id).'?filters[phone_number_id]='.$row->id;
            },
        ]);

        // $this->addAction([
        //     'icon' => 'send',
        //     'title' => 'Send',
        //     'method' => 'POST',
        //     'url' => function ($row) {
        //         return route('admin.twilio.send', $row->id);
        //     },
        // ]);

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
            'icon' => 'add',
            'title' => 'Import Phone Numbers',
            'method' => 'GET',
            'is_popup' => true,
            'action' => 'text-amber-100 bg-primary-100',
            'url' => 'admin.twilio.upload.create',
        ]);

        $this->addMassAction([
            'icon' => 'send',
            'title' => 'Send SMS',
            'method' => 'POST',
            'action' => 'text-amber-100 bg-primary-100',
            'url' => 'admin.twilio.mass-send',
        ]);

        $this->addMassAction([
            'icon' => 'delete',
            'title' => 'Bulk Delete',
            'method' => 'POST',
            'action' => 'text-red-500 bg-red-100',
            'url' => 'admin.twilio.bulk-delete',
        ]);

        $this->addMassAction([
            'icon' => 'download',
            'title' => 'Export',
            'method' => 'POST',
            'action' => 'text-amber-100 bg-primary-100',
            'url' => 'admin.twilio.mass-export',
        ]);
    }
}
