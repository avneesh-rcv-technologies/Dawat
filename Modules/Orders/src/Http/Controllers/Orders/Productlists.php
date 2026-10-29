<?php

namespace Modules\Orders\Http\Controllers\Orders;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Catalog\Models\Product;
use Modules\Orders\DataView\Orders\OrdersProducts;
use Modules\Orders\Models\Order;

class Productlists extends Controller
{
    public function index(Request $request, $id = 0)
    {
        
        $lists = Order::where('id', $id)->first();
        return view('orders::orders.popups.products', compact('lists'));

    }
} 