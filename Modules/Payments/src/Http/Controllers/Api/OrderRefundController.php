<?php

namespace Modules\Payments\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Acl\Models\Customer;
use Modules\Orders\Models\Order;

class OrderRefundController extends Controller
{
    public function index(Request $request, int $orderId)
    {
        $customer = $request->user('customer') ?? $request->user();
        abort_unless($customer instanceof Customer, 403);
        $order = Order::where('customer_type', $customer->getMorphClass())
            ->where('customer_id', $customer->id)->findOrFail($orderId);

        return response()->json(['success' => true, 'data' => $order->refunds()->orderBy('id')->get()->map->customerData()->all()]);
    }
}
