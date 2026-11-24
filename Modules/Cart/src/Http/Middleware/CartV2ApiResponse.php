<?php

namespace Modules\Cart\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartV2ApiResponse
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'], true) && $request->isJson() && $request->getContent() !== '') {
            try {
                $body = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
                if (! is_object($body)) {
                    throw new \JsonException('Expected a JSON object.');
                }
            } catch (\JsonException $exception) {
                $message = 'We could not read your request. Please send a valid JSON object and try again.';

                return response()->json(['success' => false, 'message' => $message, 'errors' => ['body' => [$message]]], 400)
                    ->header('Cache-Control', 'private, no-store');
            }
        }

        $response = $next($request);
        if ($response instanceof JsonResponse && $response->getStatusCode() >= 400) {
            $data = $response->getData(true);
            $status = $response->getStatusCode();
            $response->setData([
                'success' => false,
                'message' => match (true) {
                    $status >= 500 => 'An unexpected error occurred. Please try again.',
                    $status === 401 => 'Please sign in to view or update your cart.',
                    $status === 403 => 'You do not have access to this cart. Please sign in with the correct account.',
                    $status === 404 => $this->notFoundMessage($response, $data),
                    ! empty($data['errors']) => collect($data['errors'])->flatten()->first(),
                    default => $data['message'] ?? 'Request failed.',
                },
                ...(! empty($data['errors']) ? ['errors' => $data['errors']] : []),
            ]);
        }
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }

    private function notFoundMessage(JsonResponse $response, array $data): string
    {
        // Model lookup failures can include internal PHP class names in debug mode.
        for ($exception = $response->exception; $exception; $exception = $exception->getPrevious()) {
            if ($exception instanceof ModelNotFoundException) {
                return 'This item is no longer available. Please refresh your cart and try again.';
            }
        }

        return ($data['message'] ?? null) ?: 'This item is no longer available. Please refresh your cart and try again.';
    }
}
