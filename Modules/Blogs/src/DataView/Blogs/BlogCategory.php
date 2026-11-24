<?php

namespace Modules\Blogs\DataView\Blogs;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class BlogCategory extends DataGrid
{
    protected $primaryColumn = "id";
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        $query = DB::table('blog_categories as bc')
            ->leftJoin('blog_categories as parent', 'bc.parent_id', '=', 'parent.id')
            ->select(
                'bc.id',
                'bc.name',
                'bc.slug',
                'bc.description',
                'bc.position',
                'bc.status',
                'bc.banner_path',
                'bc.parent_id',
                'parent.name as parent_name',

                'bc.created_at',
                'bc.updated_at'
            );
        $this->addFilter('id', 'bc.id');
        $this->addFilter('name', 'bc.name');
        $this->addFilter('slug', 'bc.slug');
        $this->addFilter('position', 'bc.position');
        $this->addFilter('status', 'bc.status');
        $this->addFilter('parent_name', 'parent.name');
        $this->addFilter('created_at', 'bc.created_at');
        $this->addFilter('updated_at', 'bc.updated_at');


        return $query;
    }
    /**
     * Add columns.
     */
    public function prepareColumns()
    {
        $this->addColumn([

            'index' => 'id',
            'visibility' => false,
            'label' => 'ID',
            'type' => 'integer',
           'searchable' => false,
            'filterable' => false,
            'sortable' => false,
            
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => 'Category Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'slug',
            'label' => 'Slug',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);


        $this->addColumn([
            'index' => 'position',
            'label' => 'Position',
            'type' => 'integer',
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'allow_multiple_values' => false,
            'filterable_options' => [
                [
                    'label' => 'Active',
                    'value' => 1,
                ],
                [
                    'label' => 'Disabled',
                    'value' => 0,
                ],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                return $row->status
                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>'
                    : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Disable</span>';
            },
        ]);


        $this->addColumn([
            'index' => 'updated_at',
            'label' => 'Updated At',
            'type' => 'date',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true,
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blog_categories.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'url' => fn($row) => route('admin.blogs.blog_categories.edit', $row->id),
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blog_categories.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => fn($row) => route('admin.blogs.blog_categories.destroy', $row->id),
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blog_categories.bulk-delete')) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => 'admin.blogs.blog_categories.bulk-delete',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blog_categories.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Category',
                'method' => 'GET',
                'action' => 'text-white bg-blue-500',
                'url' => 'admin.blogs.blog_categories.create',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blog_categories.toggle-status')) {
            $this->addMassAction([
                'title' => 'Change Status',
                'method' => 'POST',
                'url' => 'admin.blogs.blog_categories.toggle-status',
                'action' => 'bg-gray-500',
                'options' => [
                    ['label' => 'Active', 'value' => 1],
                    ['label' => 'Inactive', 'value' => 0],
                ],
            ]);
        }
    }
}
