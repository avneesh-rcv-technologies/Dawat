<?php

namespace Modules\Twilio\DataView;

use Clue\Redis\Protocol\Model\Request;
use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class TwilioPhoneMessageGrid extends DataGrid
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
        $builder = DB::table('twilio_messages')
            ->leftJoin('twilio_phone_numbers', 'twilio_messages.phone_number_id', '=', 'twilio_phone_numbers.id')
            ->leftJoin('admins', 'twilio_messages.admin_id', '=', 'admins.id')
            ->select(
                'twilio_messages.id',
                'twilio_messages.to',
                'twilio_messages.body',
                'twilio_messages.status',
                'twilio_messages.delivery_sid',
                'twilio_messages.sent_at',
                'twilio_messages.response_at',
                'twilio_messages.response',
                'twilio_phone_numbers.phone_number',
                'admins.name as admin_name',
                'twilio_messages.created_at',
                'twilio_messages.error'
            );
            
        // Apply optional filters from URL query parameters
        if ($phoneNumberId = request()->get('phone_number_id')) {
            $builder->where('twilio_messages.phone_number_id', $phoneNumberId);
        }

        return $builder;
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
            'index' => 'phone_number_id',
            'label' => 'Phone Number ID',
            'type' => 'integer',
            'visibility' => false,
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
        ]);

        $this->addColumn([
            'index' => 'phone_number',
            'label' => 'Phone Number',
            'type' => 'string',
            'searchable' => false,
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
            'index' => 'delivery_sid',
            'label' => 'Delivery SID',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
        ]);

        $this->addColumn([
            'index' => 'error',
            'label' => 'Message',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
        ]);

        $this->addColumn([
            'index' => 'sent_at',
            'label' => 'Sent At',
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'response_at',
            'label' => 'Response At',
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => false,
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
        $this->addAction([
            'icon' => 'open_in_new',
            'title' => 'View',
            'method' => 'GET',
            'url' => function ($row) {
                return route('admin.twilio.message', $row->id);
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
        
    }
}
