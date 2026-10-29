<?php

namespace Modules\Restaurant\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Enums\OnboardingStatus;

class RestaurantGrid extends DataGrid
{
    protected $primaryColumn = 'id';
    protected $itemsPerPage  = 10;
    protected $sortOrder     = 'desc';

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        $query = DB::table('restaurants')
            ->select(
                'id',
                'name',
                'slug',
                'city',
                'phone',
                'onboarding_status',
                'food_type',
                'status',
                'is_accepting_orders',
                'order_time_limit',
                'created_at'
            )->whereIn('status', ['active', 'draft', 'inactive'])
            ->where('onboarding_status', OnboardingStatus::APPROVED->value);

        // Get current authenticated admin user
        $currentAdmin = auth('admin')->user();

        if ($currentAdmin) {
            // Role ID 1: Super Admin - Show all restaurants
            // Role ID 2: Restaurant Owner - Show only their restaurant
            if ($currentAdmin->role_id == 2) {
                $query->where('admin_id', $currentAdmin->id);
            }
            // For role_id == 1 or any other role, show all restaurants (no additional filter)
        }

        return $query;
    }

    /**
     * Add columns.
     */
    public function prepareColumns()
    {
        // ID
        $this->addColumn([
            'index'      => 'id',
            'label'      => 'ID',
            'type'       => 'integer',
            'sortable'   => true,
            'visibility' => false,
        ]);

        // Name
        $this->addColumn([
            'index'      => 'name',
            'label'      => 'Restaurant Name',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        // City
        // $this->addColumn([
        //     'index'      => 'city',
        //     'label'      => 'City',
        //     'type'       => 'string',
        //     'searchable' => true,
        //     'filterable' => true,
        //     'sortable'   => true,
        // ]);

        // Phone
        $this->addColumn([
            'index'      => 'phone',
            'label'      => 'Phone',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => false,
            'closure'    => fn($row) => $row->phone ?: '-',
        ]);

        // Accepting Orders
        $this->addColumn([
            'index'      => 'is_accepting_orders',
            'label'      => 'Accepting Orders',
            'type'       => 'boolean',
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Yes', 'value' => 1],
                ['label' => 'No', 'value' => 0],
            ],
            'sortable'   => true,
            'closure'    => fn($row) => $row->is_accepting_orders ? 'Yes' : 'No',
        ]);

        // Status
        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Draft', 'value' => 'draft'],
                ['label' => 'Active', 'value' => 'active'],
                ['label' => 'Inactive', 'value' => 'inactive'],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                $status = strtolower($row->status);

                $colors = [
                    'active' => 'bg-green-100 text-green-800',
                    'draft' => 'bg-yellow-100 text-yellow-800',
                    'inactive' => 'bg-red-100 text-red-800',
                ];

                $class = $colors[$status] ?? 'bg-gray-100 text-gray-800';

                return sprintf(
                    '<span class="px-2 py-1 text-xs font-semibold rounded-full %s">%s</span>',
                    $class,
                    ucfirst($status)
                );
            },
        ]);

        // Onboarding Status
        $this->addColumn([
            'index' => 'onboarding_status',
            'label' => 'Onboarding',
            'type' => 'string',
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => OnboardingStatus::APPROVED->label(), 'value' => OnboardingStatus::APPROVED->value],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                $raw = $row->onboarding_status ?? 'draft';
                $enum = OnboardingStatus::tryFrom($raw) ?? OnboardingStatus::DRAFT;

                $class = $enum->color();
                $label = $enum->label();

                return sprintf(
                    '<span class="px-2 py-1 text-xs font-semibold rounded-full %s">%s</span>',
                    $class,
                    $label
                );
            },
        ]);

        // Food Type
        $this->addColumn([
            'index'      => 'food_type',
            'label'      => 'Food Type',
            'type'       => 'string',
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'All', 'value' => 'all'],
                ['label' => 'Vegetarian', 'value' => 'veg'],
                ['label' => 'Non Vegetarian', 'value' => 'non_veg'],
                ['label' => 'Vegan', 'value' => 'vegan'],
            ],
            'sortable'   => true,
            'closure'    => function ($row) {
                // $type = strtolower($row->food_type);

                $type = empty($row->food_type)
                    ? 'not selected'
                    : strtolower($row->food_type);


                $colors = [
                    'veg'      => 'bg-green-100 text-green-800',
                    'vegan'    => 'bg-green-100 text-green-800',
                    'all'      => 'bg-green-100 text-green-800',
                    'both'     => 'bg-green-100 text-green-800',
                    'non_veg'  => 'bg-red-100 text-red-800',
                    'non-veg'  => 'bg-red-100 text-red-800',
                ];

                $class = $colors[$type] ?? 'bg-gray-100 text-gray-800';

                return sprintf(
                    '<span class="px-2 py-1 text-xs font-semibold rounded-full %s">%s</span>',
                    $class,
                    ucfirst(str_replace('_', ' ', $type))
                );
            },
        ]);


        // Created At
        $this->addColumn([
            'index'           => 'created_at',
            'label'           => 'Created At',
            'type'            => 'date',
            'sortable'        => true,
            'filterable'      => true,
            'filterable_type' => 'date_range',
        ]);
    }

    /**
     * Row actions.
     */
    public function prepareActions()
    {
        $currentAdmin = auth('admin')->user();

        if (bouncer('admin')->hasPermission('admin.restaurant.edit')) {
            $this->addAction([
                'icon'   => 'edit',
                'title'  => 'Edit',
                'method' => 'GET',
                'url'    => fn($row) => route('admin.restaurant.edit', $row->id),
            ]);
        }

        if (bouncer('admin')->hasPermission('admin.restaurant.view')) {
            $this->addAction([
                'icon'     => 'visibility',
                'title'    => 'View',
                'method'   => 'GET',
                'url'      => fn($row) => route('admin.restaurant.view', $row->id),
            ]);
        }

        // Only super admins (role_id = 1) can delete restaurants
        // Restaurant owners (role_id = 2) cannot delete their own restaurant
        if (bouncer('admin')->hasPermission('admin.restaurant.delete') && $currentAdmin && $currentAdmin->role_id == 1) {
            $this->addAction([
                'icon'   => 'delete',
                'title'  => 'Delete',
                'method' => 'DELETE',
                'url'    => fn($row) => route('admin.restaurant.delete', $row->id),
                'confirm' => 'Are you sure you want to delete this restaurant? This action cannot be undone.',
            ]);
        }
    }

    /**
     * Mass actions.
     */
    public function prepareMassActions()
    {
        $currentAdmin = auth('admin')->user();

        // Only super admins (role_id = 1) can perform bulk delete
        if (bouncer('admin')->hasPermission('admin.restaurant.delete') && $currentAdmin && $currentAdmin->role_id == 1) {
            $this->addMassAction([
                'icon'   => 'delete',
                'title'  => 'Delete Selected',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url'    => 'admin.restaurant.bulk-delete',
            ]);
        }

        // Both super admins and restaurant owners can change status
        if (bouncer('admin')->hasPermission('admin.restaurant.edit')) {
            $this->addMassAction([
                'icon'   => 'toggle_on',
                'title'  => 'Change Status',
                'method' => 'POST',
                'action' => 'bg-gray-600',
                'url'    => 'admin.restaurant.bulk-status',
                'options' => [
                    ['label' => 'Active', 'value' => 'active'],
                    ['label' => 'Inactive', 'value' => 'inactive'],
                    ['label' => 'Draft', 'value' => 'draft'],
                ],
            ]);
        }

        // Any admin with create permission can create new restaurants
        if (bouncer('admin')->hasPermission('admin.restaurant.create') && $currentAdmin) {
            $this->addMassAction([
                'icon'   => 'add',
                'title'  => 'Create Restaurant',
                'method' => 'GET',
                'url'    => 'admin.restaurant.create',
                'action'  => 'btn btn-primary',
            ]);
        }
    }
}
