<?php

namespace Modules\Orders\Http\Controllers\Statuses;

use Illuminate\Http\Request;
use Modules\Acl\Models\Role;
use Illuminate\Validation\Rule;
use Modules\Orders\Models\Status;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Acl\Services\UserAdminService;
use Modules\Orders\DataView\OrdersStatuses;
use Modules\Admin\Models\LeadStatusesModels;
use Modules\Admin\DataView\Settings\Statuses\LeadStatuses;

class OrdersStatusController extends Controller
{
    public function index(Request $request)
    {
        $lists = fn_datagrid(OrdersStatuses::class)->process();
        return view('orders::order-statuses.index', compact('lists'));
    }

    public function create()
    {
        return view('orders::order-statuses.form');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status_code' => 'required|string|max:1|unique:statuses,status_code,NULL,id,type_code,' . $request->type_code,
            'type_code' => 'required|string|max:1|in:O,L',
            'color' => 'required|string|max:7',
            'new' => 'nullable|boolean',
        ], [
            'status_code.unique' => 'This status code already exists for the selected type.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $status = Status::create([
                'name' => $request->name,
                'status_code' => strtoupper($request->status_code),
                'type_code' => $request->type_code,
                'color' => $request->color,
                'new' => $request->has('new') ? 1 : 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order status created successfully.',
                'redirect_url' => route('admin.orders-statuses.index'),
                'callback' => 'window.location.reload()'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $status = Status::findOrFail($id);
        return view('orders::order-statuses.form', compact('status'));
    }

    public function update(Request $request, $id)
    {
        $status = Status::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status_code' => 'required|string|max:1|unique:statuses,status_code,' . $id . ',id,type_code,' . $request->type_code,
            'type_code' => 'required|string|max:1|in:O,L',
            'color' => 'required|string|max:7',
            'new' => 'nullable|boolean',
        ], [
            'status_code.unique' => 'This status code already exists for the selected type.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            
            $status->update([
                'name' => $request->name,
                'status_code' => strtoupper($request->status_code),
                'type_code' => $request->type_code,
                'color' => $request->color,
                'new' => $request->has('new') ? 1 : 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully.',
                'redirect_url' => route('admin.orders-statuses.index'),
                'callback' => 'window.location.reload()'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $status = Status::findOrFail($id);
            
            // Check if status is being used by any orders
            $ordersCount = \Modules\Orders\Models\Order::where('status', $status->status_code)->count();
            
            if ($ordersCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete this status. It is currently used by {$ordersCount} order(s)."
                ], 422);
            }

            $status->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order status deleted successfully.',
                'redirect_url' => route('admin.orders-statuses.index'),
                'callback' => 'window.location.reload()'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order status: ' . $e->getMessage()
            ], 500);
        }
    }
}
