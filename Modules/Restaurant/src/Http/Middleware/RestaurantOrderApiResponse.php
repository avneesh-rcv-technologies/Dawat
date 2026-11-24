<?php

namespace Modules\Restaurant\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestaurantOrderApiResponse
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');
        $response = $next($request);
        if ($response instanceof JsonResponse && $response->getStatusCode() >= 400) {
            $data = $response->getData(true);
            $message = match ($response->getStatusCode()) {
                401 => 'Unauthenticated.',
                404 => 'Restaurant or order not found.',
                500 => 'Unable to process this request. Please try again.',
                default => $data['message'] ?? 'Request failed.',
            };
            $response->setData(['success' => false, 'message' => $message,
                ...(isset($data['errors']) ? ['errors' => $data['errors']] : [])]);
        }
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
