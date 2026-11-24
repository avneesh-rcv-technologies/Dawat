<?php

namespace Modules\Blogs\DataView\Blogs;

use Illuminate\Support\Facades\DB;
use Modules\DataView\DataGrid;

class BlogAuthor extends DataGrid
{
    protected $primaryColumn = 'id';
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';

    public function prepareQueryBuilder()
    {
        $query = DB::table('blog_authors')
            ->select(
                'blog_authors.id',
                'blog_authors.name',
                'blog_authors.slug',
                'blog_authors.designation',
                'blog_authors.email',
                'blog_authors.status',
                'blog_authors.created_at',
                'blog_authors.updated_at'
            );

        $this->addFilter('id', 'blog_authors.id');
        $this->addFilter('name', 'blog_authors.name');
        $this->addFilter('slug', 'blog_authors.slug');
        $this->addFilter('designation', 'blog_authors.designation');
        $this->addFilter('email', 'blog_authors.email');
        $this->addFilter('status', 'blog_authors.status');
        $this->addFilter('created_at', 'blog_authors.created_at');
        $this->addFilter('updated_at', 'blog_authors.updated_at');

        return $query;
    }

    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'id',
            'label' => 'ID',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
            'visibility' => false,
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
            'index' => 'slug',
            'label' => 'Slug',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'designation',
            'label' => 'Designation',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => fn ($row) => $row->designation ?: '-',
        ]);

        $this->addColumn([
            'index' => 'email',
            'label' => 'Email',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => fn ($row) => $row->email ?: '-',
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

    public function prepareActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blog_authors.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'url' => fn ($row) => route('admin.blogs.blog_authors.edit', $row->id),
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blog_authors.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.blogs.blog_authors.destroy', $row->id),
            ]);
        }
    }

    public function prepareMassActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blog_authors.bulk-delete')) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => 'admin.blogs.blog_authors.bulk-delete',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blog_authors.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Author',
                'method' => 'GET',
                'action' => 'text-white bg-blue-500',
                'url' => 'admin.blogs.blog_authors.create',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blog_authors.toggle-status')) {
            $this->addMassAction([
                'icon' => '',
                'title' => 'Status',
                'method' => 'POST',
                'url' => 'admin.blogs.blog_authors.toggle-status',
                'action' => 'bg-gray-500',
                'options' => [
                    ['label' => 'Active', 'value' => 1],
                    ['label' => 'Inactive', 'value' => 0],
                ],
            ]);
        }
    }
}
