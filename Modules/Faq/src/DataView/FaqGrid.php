<?php

namespace Modules\Faq\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class FaqGrid extends DataGrid
{
    protected $primaryColumn = "id"; 
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';

   
    public function prepareQueryBuilder()
    {
        return DB::table('faqs')
            ->select(
                DB::raw('MIN(id) as id'),
                'group_name',
                DB::raw('COUNT(*) as total_faqs'),
                DB::raw('MIN(created_at) as created_at')
            )
            ->groupBy('group_name');
    }

   
    public function prepareColumns()
    {
        
        $this->addColumn([
            'index' => 'id',
            'visibility' => false,
            'label' => 'ID',
            'type'  => 'integer',
            'searchable' => true,
            'filterable' => false,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'total_faqs',
            'label' => 'Total FAQs',
            'type'  => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
        ]);

       
        $this->addColumn([
            'index' => 'group_name',
            'label' => 'Group Name',
            'type'  => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
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
        
        $this->addFilter('id', 'faqs.id');
        $this->addFilter('group_name', 'faqs.group_name');
        $this->addFilter('sort_order', 'faqs.sort_order');
        $this->addFilter('created_at', 'faqs.created_at');
    }

   
    public function prepareActions()
    {
        if (bouncer()->hasPermission('admin.faqs.group.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'url' => fn($row) => route(
                    'admin.faqs.group.edit',
                    ['group' => $row->group_name]
                ),
            ]);
        }

        if (bouncer()->hasPermission('admin.faqs.group.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => fn($row) => route(
                    'admin.faqs.group.destroy',
                    ['group' => $row->group_name]
                ),
            ]);
        }
    }

   
    public function prepareMassActions()
    {
      
        if (bouncer()->hasPermission('admin.faqs.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Faq',
                'method' => 'GET',
                'action' => 'text-amber-100 bg-primary-100',
                'url' => 'admin.faqs.create',
            ]);
        }
    }
}
