<?php

namespace Modules\KYC\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\KYC\Exceptions\KycException;
use Illuminate\Support\Facades\Log;
use Exception;

class KycErrorHandlerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (Exception $e) {
            // Log all KYC-related errors
            Log::error('KYC Middleware caught exception', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);

            // If it's already a KycException, let it handle itself
            if ($e instanceof KycException) {
                return $e->render($request);
            }

            // For API requests, return JSON error response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INTERNAL_ERROR',
                        'message' => config('app.debug') ? $e->getMessage() : 'An internal error occurred'
                    ]
                ], 500);
            }

            // For web requests, redirect back with error
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}