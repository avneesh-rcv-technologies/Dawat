<?php

namespace Modules\Acl\DataView;

use Modules\DataView\DataGrid;
use Illuminate\Support\Facades\DB;

class CustomerGrid extends DataGrid
{
    protected $primaryColumn = 'id';
    protected $itemsPerPage  = 10;
    protected $sortOrder     = 'desc';

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        $query = DB::table('customers')
            ->select(
                'customers.id',
                'customers.name',
                'customers.email',
                'customers.phone',
                'customers.status',
                'customers.guest_method',
                'customers.number_of_guests',
                'customers.preferred_delivery_date',
                'customers.created_at'
            );


        // Filter by restaurant ownership (same pattern as FoodManagements)
        $currentAdmin = auth('admin')->user();

        if ($currentAdmin) {
            $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $currentAdmin->id)->first();

            if ($restaurant) {
                // Restaurant admin: show only customers who ordered from their restaurant
                $query->join('orders', 'customers.id', '=', 'orders.customer_id')
                    ->where('orders.customer_type', \Modules\Acl\Models\Customer::class)
                    ->where('orders.vendor_type', \Modules\Restaurant\Models\Restaurant::class)
                    ->where('orders.vendor_id', $restaurant->id)
                    ->distinct(); // Important: avoid duplicate rows if customer has multiple orders
            }
            // Super admin (no restaurant): show all customers
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
            'label'      => 'Customer Name',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        // Email
        $this->addColumn([
            'index'      => 'email',
            'label'      => 'Email',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        // Phone
        $this->addColumn([
            'index'      => 'phone',
            'label'      => 'Phone',
            'type'       => 'string',
            'sortable'   => false,
            'closure'    => fn($row) => $row->phone ?: '-',
        ]);

        // Guest Method
        $this->addColumn([
            'index'      => 'guest_method',
            'label'      => 'Guest Method',
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                if (empty($row->guest_method)) {
                    return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">N/A</span>';
                }

                $method = $row->guest_method;
                $colors = [
                    'Delivery' => 'bg-green-100 text-green-800',
                    'Pickup'   => 'bg-purple-100 text-purple-800',
                ];

                $class = $colors[$method] ?? 'bg-gray-100 text-gray-800';

                return sprintf(
                    '<span class="px-2 py-1 text-xs font-semibold rounded-full %s">%s</span>',
                    $class,
                    $method
                );
            },
        ]);

        // Number of Guests
        $this->addColumn([
            'index'      => 'number_of_guests',
            'label'      => 'Guests',
            'type'       => 'integer',
            'sortable'   => true,
            'closure'    => fn($row) => $row->number_of_guests ?: '-',
        ]);

        // Status
        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'boolean',
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Active', 'value' => 1],
                ['label' => 'Inactive', 'value' => 0],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                $status = $row->status ? 'active' : 'inactive';
                $colors = [
                    'active'   => 'bg-green-100 text-green-800',
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

        // Created At
        $this->addColumn([
            'index'           => 'created_at',
            'label'           => 'Registered At',
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
        if (bouncer('admin')->hasPermission('admin.customer.view')) {
            $this->addAction([
                'icon'     => 'visibility',
                'title'    => 'View',
                'method'   => 'GET',
                'url'      => fn($row) => route('admin.customer.view', $row->id),
            ]);
        }

        if (bouncer('admin')->hasPermission('admin.customer.edit')) {
            $this->addAction([
                'icon'      => 'edit',
                'title'     => 'Edit',
                'method'    => 'GET',
                'is_popup'  => true,
                'url'       => fn($row) => route('admin.customer.edit', $row->id),
            ]);
        }

        if (bouncer('admin')->hasPermission('admin.customer.delete')) {
            $this->addAction([
                'icon'    => 'delete',
                'title'   => 'Delete',
                'method'  => 'DELETE',
                'url'     => fn($row) => route('admin.customer.delete', $row->id),
                'confirm' => 'Are you sure you want to delete this customer? This action cannot be undone.',
            ]);
        }
    }

    /**
     * Mass actions.
     */
    public function prepareMassActions()
    {
        if (bouncer('admin')->hasPermission('admin.customer.delete')) {
            $this->addMassAction([
                'icon'   => 'delete',
                'title'  => 'Delete Selected',
                'method' => 'POST',
                'action' => 'text-white bg-red-600',
                'url'    => 'admin.customer.bulk-delete',
            ]);
        }

        if (bouncer('admin')->hasPermission('admin.customer.edit')) {
            $this->addMassAction([
                'icon'    => 'toggle_on',
                'title'   => 'Change Status',
                'method'  => 'POST',
                'action'  => 'bg-gray-600',
                'url'     => 'admin.customer.bulk-status',
                'options' => [
                    ['label' => 'Active', 'value' => '1'],
                    ['label' => 'Inactive', 'value' => '0'],
                ],
            ]);
        }

        if (bouncer('admin')->hasPermission('admin.customer.create')) {
            $this->addMassAction([
                'icon'      => 'add',
                'title'     => 'Create Customer',
                'method'    => 'GET',
                'is_popup'  => true,
                'url'       => 'admin.customer.create',
                'action'    => 'btn btn-primary',
            ]);
        }
    }
}
