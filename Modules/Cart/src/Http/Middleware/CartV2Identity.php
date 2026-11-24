<?php

namespace Modules\Cart\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Customer;

class CartV2Identity
{
    public function handle(Request $request, Closure $next)
    {
        $customer = Auth::guard('customer')->user();
        // A bad customer token must never silently become a guest request.
        abort_if(($request->hasHeader('Authorization') && ! $customer)
            || ($customer !== null && ! $customer instanceof Customer), 401);

        $sessionId = null;
        if (! $customer) {
            $sessionId = $request->header('X-Session-ID');
            if ($sessionId === null && $request->routeIs('api.v2.cart.store')) {
                $sessionId = bin2hex(random_bytes(32));
            }
            if ($sessionId === null) {
                throw ValidationException::withMessages([
                    'session_id' => 'Please send your cart session ID in the X-Session-ID header, or sign in to access your cart.',
                ]);
            }
            if (! preg_match('/\A[a-f0-9]{64}\z/', $sessionId)) {
                throw ValidationException::withMessages([
                    'session_id' => 'Your cart session ID is invalid. Use the session ID returned when you added your first item.',
                ]);
            }
        }

        $request->attributes->set('cart_customer', $customer);
        $request->attributes->set('cart_session_id', $sessionId);
        $response = $next($request);
        if ($sessionId !== null) {
            $response->headers->set('X-Session-ID', $sessionId);
        }

        return $response;
    }
}
