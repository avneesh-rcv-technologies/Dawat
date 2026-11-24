<?php

namespace Modules\Restaurant\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JsonException;
use Symfony\Component\HttpFoundation\InputBag;

class FoodItemV2ApiResponse
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');
        if ($error = $this->prepareJsonBody($request)) {
            return $error->header('Cache-Control', 'private, no-store');
        }
        // Laravel's routing pipeline renders exceptions before returning to this middleware.
        $response = $next($request);
        if ($response instanceof JsonResponse && $response->getStatusCode() >= 400) {
            $status = $response->getStatusCode();
            $data = $response->getData(true);
            $errors = $data['errors'] ?? [];
            $message = match (true) {
                $status >= 500 => 'An unexpected error occurred. Please try again.',
                $status === 401 => 'Unauthenticated.',
                $status === 404 => 'The requested restaurant, food item, or serving option was not found.',
                ! empty($errors) => collect($errors)->flatten()->first(),
                default => $data['message'] ?? 'Request failed.',
            };
            $response->setData([
                'success' => false, 'message' => $message,
                ...(! empty($errors) ? ['errors' => $errors] : []),
            ]);
        }
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }

    private function prepareJsonBody(Request $request): ?JsonResponse
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            return null;
        }
        $contentType = strtolower($request->header('Content-Type', ''));
        if (str_starts_with($contentType, 'multipart/form-data')
            && in_array($request->getRealMethod(), ['PUT', 'PATCH'], true) && $request->all() === []) {
            return $this->invalidJson('PHP did not parse this multipart upload. Send POST multipart/form-data to the food item URL, optionally with _method=PATCH.');
        }
        if (str_starts_with($contentType, 'multipart/form-data') || $request->allFiles()) {
            return null;
        }
        $body = trim($request->getContent());
        if ($body === '') {
            return null;
        }
        $isJson = $request->isJson();
        // Postman raw/Text or missing Content-Type can otherwise produce empty form input.
        if (! $isJson && ! str_starts_with($body, '{') && ! str_starts_with($body, '[')
            && ! str_starts_with($contentType, 'text/plain')) {
            return null;
        }
        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return $this->invalidJson('Invalid JSON request body. Send a complete JSON object, including the final closing brace, with Content-Type: application/json.');
        }
        if (! is_array($data) || (! str_starts_with($body, '{') && $data !== [])) {
            return $this->invalidJson('The request body must be a JSON object containing the food item fields.');
        }
        if (! $isJson) {
            $request->headers->set('Content-Type', 'application/json');
            $request->setJson(new InputBag($data));
            // Match the normal Laravel input normalization after recovering raw JSON.
            (new TrimStrings())->handle($request, fn ($request) =>
                (new ConvertEmptyStringsToNull())->handle($request, fn () => null));
        }

        return null;
    }

    private function invalidJson(string $message): JsonResponse
    {
        return response()->json([
            'success' => false, 'message' => $message, 'errors' => ['body' => [$message]],
        ], 400);
    }
}
