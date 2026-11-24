<?php

namespace Modules\Blogs\DataView\Blogs;

use Illuminate\Support\Facades\DB;
use Modules\DataView\DataGrid;

class BlogCta extends DataGrid
{
    protected $primaryColumn = 'id';
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';

    public function prepareQueryBuilder()
    {
        $query = DB::table('blog_ctas')
            ->select(
                'blog_ctas.id',
                'blog_ctas.name',
                'blog_ctas.slug',
                'blog_ctas.status',
                'blog_ctas.created_at',
                'blog_ctas.updated_at'
            );

        $this->addFilter('id', 'blog_ctas.id');
        $this->addFilter('name', 'blog_ctas.name');
        $this->addFilter('slug', 'blog_ctas.slug');
        $this->addFilter('status', 'blog_ctas.status');
        $this->addFilter('created_at', 'blog_ctas.created_at');
        $this->addFilter('updated_at', 'blog_ctas.updated_at');

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
            'closure' => fn($row) => '<code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">&#123;&#123;' . e($row->slug) . '&#125;&#125;</code>',
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
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blog_ctas.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'url' => fn($row) => route('admin.blogs.blog_ctas.edit', $row->id),
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blog_ctas.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => fn($row) => route('admin.blogs.blog_ctas.destroy', $row->id),
            ]);
        }
    }

    public function prepareMassActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blog_ctas.bulk-delete')) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => 'admin.blogs.blog_ctas.bulk-delete',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blog_ctas.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create CTA',
                'method' => 'GET',
                'action' => 'text-white bg-blue-500',
                'url' => 'admin.blogs.blog_ctas.create',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blog_ctas.toggle-status')) {
            $this->addMassAction([
                'icon' => '',
                'title' => 'Status',
                'method' => 'POST',
                'url' => 'admin.blogs.blog_ctas.toggle-status',
                'action' => 'bg-gray-500',
                'options' => [
                    ['label' => 'Active', 'value' => 1],
                    ['label' => 'Inactive', 'value' => 0],
                ],
            ]);
        }
    }
}
