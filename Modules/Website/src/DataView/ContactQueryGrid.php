<?php

namespace Modules\Website\DataView;

use Illuminate\Support\Facades\DB;
use Modules\DataView\DataGrid;

class ContactQueryGrid extends DataGrid
{
    protected $sortOrder = 'desc';

    public function prepareQueryBuilder()
    {
        return DB::table('contact_queries')
            ->select('id', 'first_name', 'last_name', 'email', 'message', 'created_at');
    }

    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'id',
            'label' => 'ID',
            'type' => 'integer',
            'sortable' => true,
        ]);

        foreach (['first_name' => 'First Name', 'last_name' => 'Last Name', 'email' => 'Email', 'message' => 'Message'] as $index => $label) {
            $column = [
                'index' => $index,
                'label' => $label,
                'type' => 'string',
                'searchable' => true,
                'filterable' => true,
                'sortable' => $index !== 'message',
            ];

            if ($index === 'message') {
                $column['closure'] = fn ($row) => '<div class="contact-query-message-preview">'.$row->message.'</div>';
            }

            $this->addColumn($column);
        }

        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Submitted At',
            'type' => 'date',
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true,
        ]);
    }

    public function prepareActions()
    {
        if (bouncer('admin')->hasPermission('website.contact-queries.show')) {
            $this->addAction([
                'icon' => 'visibility',
                'title' => 'View Details',
                'method' => 'GET',
                'url' => fn ($row) => route('website.contact-queries.show', $row->id),
            ]);
        }

        if (bouncer('admin')->hasPermission('website.contact-queries.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => fn ($row) => route('website.contact-queries.destroy', $row->id),
            ]);
        }
    }

    public function prepareMassActions()
    {
        if (bouncer('admin')->hasPermission('website.contact-queries.bulk-delete')) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => 'website.contact-queries.bulk-delete',
            ]);
        }
    }

    protected function formatData(): array
    {
        $data = parent::formatData();
        $data['meta']['row_actions_view'] = 'website::admin.contact-queries.actions';

        return $data;
    }

    protected function sanitizeRow($row): \stdClass
    {
        // The shared table renders HTML. Escape submitted text while preserving its contents.
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $row->{$key} = e($value);
            }
        }

        return $row;
    }
}
