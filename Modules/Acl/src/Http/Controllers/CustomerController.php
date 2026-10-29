<?php

namespace Modules\Acl\Http\Controllers;

use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Acl\DataView\CustomerGrid;
use Modules\Acl\Services\CustomerService;
use Modules\Orders\Models\Order;
use Modules\Restaurant\Models\Restaurant;
use Modules\Notifications\Services\NotificationService;


class CustomerController extends Controller
{
    protected CustomerService $customerService;
    protected NotificationService $notificationService; // Add this property


    public function __construct(CustomerService $customerService,  NotificationService $notificationService)
    {
        $this->customerService = $customerService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $lists = fn_datagrid(CustomerGrid::class)->process();

        // Get date range from request or use defaults
        $dateFrom = $request->input('date_from', date('Y-m-d', strtotime('-30 days')));
        $dateTo = $request->input('date_to', date('Y-m-d'));

        // Get analytics data
        $analyticsResponse = $this->analytics($request);
        $analyticsData = $analyticsResponse->getData();

        return view('acl::customer.index', compact('lists', 'dateFrom', 'dateTo', 'analyticsData'));
    }

    public function create()
    {
        return view('acl::customer.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150|regex:/^[a-zA-Z ]+$/',
            'email' => 'required|string|email:dns|unique:customers,email',
            'password' => ['required', 'string', new StrongPassword()],
            'phone' => [
                'nullable',
                'string',
                'min:10',
                'max:20',
                'regex:/^\+?[0-9\s\-()]+$/',
            ],
            'status' => 'nullable|boolean',
            'email_verified' => 'nullable|boolean',
            'guest_method' => 'nullable|in:Delivery,Pickup',
            'number_of_guests' => 'nullable|integer|min:1',
            'preferred_delivery_date' => 'nullable|date',
        ]);

        try {
            $customer =    $this->customerService->create($data);
            $this->notificationService->trigger(
                'Acl',
                'CustomerCreated',
                $customer->email,
                $customer
            );
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully!',
                'redirect_url' => route('admin.customer.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e->getMessage()
            ]);
        }
    }

    public function edit(int $id)
    {
        try {
            $customer = $this->customerService->findWithRelations($id);
            return view('acl::customer.form', compact('customer'));
        } catch (\Exception $e) {
            return redirect()->route('admin.customer.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150|regex:/^[a-zA-Z ]+$/',
            'email'    => 'required|string|email:dns|unique:customers,email,' . $id,
            'password' => ['nullable', 'string', new StrongPassword()],
            'phone'    => [
                'nullable',
                'string',
                'min:10',
                'max:20',
                'regex:/^\+?[0-9\s\-()]+$/',
            ],
            'status'   => 'nullable|boolean',
            'email_verified' => 'nullable|boolean',
            'guest_method' => 'nullable|in:Delivery,Pickup',
            'number_of_guests' => 'nullable|integer|min:1',
            'preferred_delivery_date' => 'nullable|date',
        ]);

        try {
            $this->customerService->update($id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully!',
                'redirect_url' => route('admin.customer.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e->getMessage()
            ]);
        }
    }

    public function view($id)
    {
        try {
            $data = $this->customerService->getCustomerView($id);
            return view('acl::customer.view', $data);
        } catch (\Exception $e) {
            return redirect()->route('admin.customer.index')
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->customerService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully',
                'redirect_url' => route('admin.customer.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete customer',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = array_filter((array) $request->input('ids'));

            if (count($ids) === 0) {
                return redirect()->route('admin.customer.index')
                    ->with('error', 'Please select at least one customer');
            }

            $deletedCount = $this->customerService->bulkDelete($ids);

            return redirect()->route('admin.customer.index')
                ->with('success', $deletedCount . ' customer(s) deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.customer.index')
                ->with('error', $e->getMessage());
        }
    }

    public function bulkStatus(Request $request)
    {
        try {
            $ids = array_filter((array) $request->input('ids'));
            $status = $request->input('status');

            if (count($ids) === 0) {
                return redirect()->route('admin.customer.index')
                    ->with('error', 'Please select at least one customer');
            }

            if ($status === null) {
                return redirect()->route('admin.customer.index')
                    ->with('error', 'No status selected');
            }

            $updatedCount = $this->customerService->bulkUpdateStatus($ids, (bool) $status);

            $statusText = $status ? 'Active' : 'Inactive';
            return redirect()->route('admin.customer.index')
                ->with('success', $updatedCount . ' customer(s) updated to ' . $statusText);
        } catch (\Exception $e) {
            return redirect()->route('admin.customer.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Get customer analytics
     */
    public function analytics(Request $request)
    {
        $dateFrom = $request->input('date_from', date('Y-m-d', strtotime('-30 days')));
        $dateTo = $request->input('date_to', date('Y-m-d'));
        $currentAdmin = auth('admin')->user();
        $restaurant = $currentAdmin
            ? Restaurant::where('admin_id', $currentAdmin->id)->first()
            : null;

        $customersQuery = \Modules\Acl\Models\Customer::query();

        if ($restaurant) {
            $customersQuery->whereHas('orders', function ($query) use ($restaurant) {
                $query->where('vendor_type', Restaurant::class)
                    ->where('vendor_id', $restaurant->id);
            });
        }

        $customersQuery->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        // Total customers
        $totalCustomers = (clone $customersQuery)->count();
        $activeCustomers = (clone $customersQuery)->where('status', 1)->count();
        $inactiveCustomers = (clone $customersQuery)->where('status', 0)->count();

        // Customers with orders
        $customersWithOrders = (clone $customersQuery)
            ->whereHas('orders')
            ->count();

        $totalRevenue = \Modules\Acl\Models\Customer::query()
            ->join('orders', function ($join) {
                $join->on('customers.id', '=', 'orders.customer_id')
                    ->where('orders.customer_type', '=', 'Modules\\Acl\\Models\\Customer');
            })
            ->when($restaurant, function ($query) use ($restaurant) {
                $query->where('orders.vendor_type', Restaurant::class)
                    ->where('orders.vendor_id', $restaurant->id);
            })
            ->whereBetween('orders.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->where('orders.payment_status', Order::PAYMENT_PAID)
            ->sum('orders.total');

        // New customers (registered in date range)
        $newCustomers = $totalCustomers;

        // Customer registration trend (last 7 days)
        $registrationTrend = \Modules\Acl\Models\Customer::query()
            ->select(
                \DB::raw('DATE(created_at) as date'),
                \DB::raw('COUNT(*) as count')
            );

        if ($restaurant) {
            $registrationTrend->whereHas('orders', function ($query) use ($restaurant) {
                $query->where('vendor_type', Restaurant::class)
                    ->where('vendor_id', $restaurant->id);
            });
        }

        $registrationTrend = $registrationTrend
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get()
            ->reverse()
            ->values();

        // Top customers by orders
        $topCustomersByOrders = \Modules\Acl\Models\Customer::query()
            ->when($restaurant, function ($query) use ($restaurant) {
                $query->withCount(['orders as orders_count' => function ($orderQuery) use ($restaurant) {
                    $orderQuery->where('vendor_type', Restaurant::class)
                        ->where('vendor_id', $restaurant->id);
                }])->whereHas('orders', function ($orderQuery) use ($restaurant) {
                    $orderQuery->where('vendor_type', Restaurant::class)
                        ->where('vendor_id', $restaurant->id);
                });
            }, function ($query) {
                $query->withCount('orders');
            })
            ->orderByDesc('orders_count')
            ->limit(10)
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'orders_count' => $customer->orders_count,
                ];
            });

        // Top customers by spending
        $topCustomersBySpending = \Modules\Acl\Models\Customer::query()
            ->join('orders', function ($join) {
                $join->on('customers.id', '=', 'orders.customer_id')
                    ->where('orders.customer_type', '=', 'Modules\\Acl\\Models\\Customer');
            })
            ->when($restaurant, function ($query) use ($restaurant) {
                $query->where('orders.vendor_type', Restaurant::class)
                    ->where('orders.vendor_id', $restaurant->id);
            })
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->select('customers.id', 'customers.name', 'customers.email', \DB::raw('SUM(orders.total) as total_spent'))
            ->groupBy('customers.id', 'customers.name', 'customers.email')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'customers' => [
                    'total' => $totalCustomers,
                    'active' => $activeCustomers,
                    'inactive' => $inactiveCustomers,
                    'with_orders' => $customersWithOrders,
                    'total_revenue' => round($totalRevenue, 2),
                    'total_revenue_formatted' => fn_convert_currency($totalRevenue, fn_get_setting('general.currency', 'USD')),
                    'new' => $newCustomers,
                    'active_rate' => $totalCustomers > 0 ? round(($activeCustomers / $totalCustomers) * 100, 2) : 0,
                ],
                'registration_trend' => $registrationTrend,
                'top_by_orders' => $topCustomersByOrders,
                'top_by_spending' => $topCustomersBySpending,
            ]
        ]);
    }
}
