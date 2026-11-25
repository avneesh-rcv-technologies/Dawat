<?php

namespace Modules\Cart\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Acl\Models\Customer;

class CartV2Identity
{
    public function handle(Request $request, Closure $next)
    {
        $customer = Auth::guard('customer')->user();
        abort_unless($customer instanceof Customer, 401);

        $request->attributes->set('cart_customer', $customer);

        return $next($request);
    }
}
