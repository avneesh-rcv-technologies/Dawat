<?php

namespace Modules\Blogs\DataView\Blogs;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;
use Modules\Blogs\Models\BlogCategory;

class Blog extends DataGrid
{
    protected $primaryColumn = "id";
    protected $sortColumn = "blogs.published_at";
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';


    public function prepareQueryBuilder()
    {
        $query = DB::table('blogs')
            ->leftJoin('blog_category_pivot', 'blogs.id', '=', 'blog_category_pivot.blog_id')
            ->leftJoin('blog_categories', 'blog_category_pivot.category_id', '=', 'blog_categories.id')
            ->leftJoin('blog_authors', 'blogs.author_id', '=', 'blog_authors.id')
            ->select(
                'blogs.id',
                'blogs.title',
                'blogs.author_name',
                'blogs.author_id',
                'blog_authors.name as author_display_name',
                'blogs.post_url',
                'blogs.locale',
                'blogs.status',
                'blogs.published_at',
                'blogs.scheduled_at',
                'blogs.scheduled_timezone',
                'blogs.sort_order',
                'blogs.created_at',
                'blogs.updated_at',
                DB::raw("GROUP_CONCAT(DISTINCT blog_categories.name SEPARATOR ', ') as category_names"),
                DB::raw("GROUP_CONCAT(DISTINCT blog_categories.id) as category_ids")
            )
            ->groupBy(
                'blogs.id',
                'blogs.title',
                'blogs.author_name',
                'blogs.author_id',
                'blog_authors.name',
                'blogs.post_url',
                'blogs.locale',
                'blogs.status',
                'blogs.published_at',
                'blogs.scheduled_at',
                'blogs.scheduled_timezone',
                'blogs.sort_order',
                'blogs.created_at',
                'blogs.updated_at'
            );

        $this->addFilter('id', 'blogs.id');
        $this->addFilter('title', 'blogs.title');
        $this->addFilter('author_name', DB::raw("COALESCE(NULLIF(blog_authors.name, ''), blogs.author_name)"));
        $this->addFilter('author_id', 'blogs.author_id');
        $this->addFilter('post_url', 'blogs.post_url');
        $this->addFilter('locale', 'blogs.locale');
        $this->addFilter('status', 'blogs.status');
        $this->addFilter('category_names', 'blog_categories.name');
        $this->addFilter('published_at', 'blogs.published_at');
        $this->addFilter('scheduled_at', 'blogs.scheduled_at');
        $this->addFilter('sort_order', 'blogs.sort_order');
        $this->addFilter('created_at', 'blogs.created_at');
        $this->addFilter('updated_at', 'blogs.updated_at');


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
            'index' => 'title',
            'label' => 'Title',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);


        $this->addColumn([
            'index' => 'author_name',
            'label' => 'Author',
            'type' => 'string',
            'filterable' => true,
            'closure' => fn($row) => $row->author_display_name ?: ($row->author_name ?: '-'),
        ]);


        $this->addColumn([
            'index' => 'category_names',
            'label' => 'Categories',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'allow_multiple_values' => false,
            'filterable_options' => BlogCategory::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($category) => [
                    'value' => $category->name,
                    'label' => $category->name,
                ])
                ->values()
                ->toArray(),
            'closure' => fn($row) => $row->category_names ?? '-',
        ]);


        $this->addColumn([
            'index' => 'post_url',
            'label' => 'Post URL',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
        ]);





        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'boolean',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'allow_multiple_values' => false,
            'filterable_options' => [
                ['label' => 'Published', 'value' => 'published'],
                ['label' => 'Draft', 'value' => 'draft'],
                ['label' => 'Scheduled', 'value' => 'scheduled'],
                ['label' => 'Archived', 'value' => 'archived'],
            ],
            'sortable' => true,
            'closure' => fn($row) => match ($row->status) {
                'published' => '<span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Published</span>',
                'draft' => '<span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Draft</span>',
                'scheduled' => '<span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Scheduled</span>',
                default => '<span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Archived</span>',
            }
        ]);

        $this->addColumn([
            'index' => 'scheduled_at',
            'label' => 'Scheduled At',
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
            'closure' => function ($row) {
                if (empty($row->scheduled_at)) {
                    return '-';
                }

                $timezone = $row->scheduled_timezone ?: config('app.timezone', 'UTC');

                try {
                    $localTime = \Carbon\Carbon::parse($row->scheduled_at, 'UTC')
                        ->timezone($timezone)
                        ->format('Y-m-d H:i');
                } catch (\Throwable $e) {
                    $localTime = \Carbon\Carbon::parse($row->scheduled_at)->format('Y-m-d H:i');
                    $timezone = config('app.timezone', 'UTC');
                }

                return e($localTime) . ' <span class="text-xs text-gray-500">(' . e($timezone) . ')</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'sort_order',
            'label' => 'Sort Order',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);


        $this->addColumn([
            'index' => 'published_at',
            'label' => 'Published At',
            'type' => 'date',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true,
            'closure' => fn($row) => $row->published_at
                ? \Carbon\Carbon::parse($row->published_at)->format('Y-m-d H:i')
                : '-',
        ]);


        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Created At',
            'type' => 'date',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true,
        ]);



    }


    public function prepareActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blogs.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'url' => fn($row) => route('admin.blogs.blogs.edit', $row->id),
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blogs.preview')) {
            $this->addAction([
                'icon' => 'visibility',
                'title' => 'Preview',
                'method' => 'GET',
                'url' => fn($row) => route('admin.blogs.blogs.preview', $row->id),
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blogs.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => fn($row) => route('admin.blogs.blogs.destroy', $row->id),
            ]);
        }
    }


    public function prepareMassActions()
    {
        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blogs.bulk-delete')) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url' => 'admin.blogs.blogs.bulk-delete',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blogs.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Blog',
                'method' => 'GET',
                'action' => 'text-white bg-blue-500',
                'url' => 'admin.blogs.blogs.create',
            ]);
        }

        if (bouncer(\Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('admin.blogs.blogs.toggle-status')) {
            $this->addMassAction([
                'icon' => '',
                'title' => 'Status',
                'method' => 'POST',
                'url' => 'admin.blogs.blogs.toggle-status',
                'action' => 'bg-gray-500',
                'options' => [
                    ['label' => 'Published', 'value' => 'published'],
                    ['label' => 'Draft', 'value' => 'draft'],
                    ['label' => 'Scheduled', 'value' => 'scheduled'],
                    ['label' => 'Archived', 'value' => 'archived'],
                ],
            ]);
        }
    }
}
