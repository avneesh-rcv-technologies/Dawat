<?php

namespace Modules\DietaryManagment\DataView;

use Illuminate\Support\Facades\DB;
use Modules\DataView\DataGrid;

class DietaryLabelGrid extends DataGrid
{
    protected $primaryColumn = 'id';
    protected $sortColumn = 'display_order';
    protected $sortOrder = 'asc';

    public function prepareQueryBuilder()
    {
        return DB::table('dietary_labels')->select(
            'id', 'name', 'code', 'slug', 'icon', 'description',
            'backend_value', 'is_active', 'is_certification_required', 'display_order', 'created_at'
        );
    }

    public function prepareColumns()
    {
        $this->addColumn(['index' => 'id', 'label' => 'ID', 'type' => 'string', 'visibility' => false]);
        foreach (['name' => 'Name', 'code' => 'Code', 'slug' => 'Slug', 'backend_value' => 'System Identifier'] as $index => $label) {
            $this->addColumn([
                'index' => $index, 'label' => $label, 'type' => 'string',
                'sortable' => true, 'searchable' => true,
            ]);
        }
        $this->addColumn([
            'index' => 'icon', 'label' => 'Icon', 'type' => 'string',
            'closure' => fn ($row) => view('dietarymanagment::labels.partials.icon', ['icon' => $row->icon])->render(),
        ]);
        $this->addColumn([
            'index' => 'is_certification_required', 'label' => 'Certification Required', 'type' => 'boolean',
            'sortable' => true, 'filterable' => true, 'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Yes', 'value' => 1], ['label' => 'No', 'value' => 0],
            ],
            'closure' => fn ($row) => $row->is_certification_required ? 'Yes' : 'No',
        ]);
        $this->addColumn([
            'index' => 'display_order', 'label' => 'Display Order', 'type' => 'integer', 'sortable' => true,
        ]);
        $this->addColumn([
            'index' => 'is_active', 'label' => 'Status', 'type' => 'boolean', 'sortable' => true,
            'filterable' => true, 'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Active', 'value' => 1], ['label' => 'Inactive', 'value' => 0],
            ],
            'closure' => fn ($row) => $row->is_active
                ? '<span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs text-green-800">Active</span>'
                : '<span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs text-red-800">Inactive</span>',
        ]);
    }

    public function prepareActions()
    {
        if (bouncer()->hasPermission('admin.dietarymanagment.labels.edit')) {
            $this->addAction([
                'icon' => 'edit', 'title' => 'Edit', 'method' => 'GET',
                'url' => fn ($row) => route('admin.dietarymanagment.labels.edit', $row->id),
            ]);
        }
        if (bouncer()->hasPermission('admin.dietarymanagment.labels.destroy')) {
            $this->addAction([
                'icon' => 'delete', 'title' => 'Delete', 'method' => 'DELETE',
                'url' => fn ($row) => route('admin.dietarymanagment.labels.destroy', $row->id),
            ]);
        }
    }

    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('admin.dietarymanagment.labels.create')) {
            $this->addMassAction([
                'icon' => 'add', 'title' => 'Create Dietary Label', 'method' => 'GET',
                'is_popup' => false, 'action' => 'bg-black text-white',
                'url' => 'admin.dietarymanagment.labels.create',
            ]);
        }
    }
}
