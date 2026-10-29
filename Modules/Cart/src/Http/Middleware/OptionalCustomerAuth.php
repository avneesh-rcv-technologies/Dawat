<?php

namespace Modules\Cart\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OptionalCustomerAuth
{
    /**
     * Handle an incoming request.
     * 
     * This middleware attempts to authenticate the customer using the customer guard,
     * but doesn't fail if no authentication is provided (for guest users).
     */
    public function handle(Request $request, Closure $next)
    {
        // Try to authenticate using customer guard
        if ($request->bearerToken()) {
            try {
                // Attempt to authenticate with customer guard
                $user = Auth::guard('customer')->user();
                
                if ($user) {
                    // Set the authenticated user for the request
                    Auth::guard('customer')->setUser($user);
                    $request->setUserResolver(function () use ($user) {
                        return $user;
                    });
                }
            } catch (\Exception $e) {
                // If authentication fails, continue as guest
                // Don't throw error, just log it
                Log::info('Cart API: Optional authentication failed', [
                    'error' => $e->getMessage(),
                    'token' => substr($request->bearerToken(), 0, 20) . '...'
                ]);
            }
        }
        
        return $next($request);
    }
}