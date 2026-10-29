<?php

namespace Modules\KYC\Traits;

use Modules\KYC\Exceptions\KycException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

trait HandlesKycErrors
{
    /**
     * Handle and log KYC exceptions
     */
    protected function handleKycException(Exception $e, string $operation = 'KYC Operation'): JsonResponse
    {
        // Log the exception
        Log::error("KYC Error in {$operation}", [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        // Handle specific exception types
        if ($e instanceof KycException) {
            return $e->render(request());
        }

        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_FAILED',
                    'message' => 'Validation failed',
                    'validation_errors' => $e->errors()
                ]
            ], 422);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'The requested resource was not found'
                ]
            ], 404);
        }

        // Generic error response
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'INTERNAL_ERROR',
                'message' => config('app.debug') ? $e->getMessage() : 'An internal error occurred'
            ]
        ], 500);
    }

    /**
     * Handle successful KYC operations
     */
    protected function handleKycSuccess($data = null, string $message = 'Operation completed successfully', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validate required parameters
     */
    protected function validateKycParameters(array $required, array $data): void
    {
        $missing = [];
        foreach ($required as $param) {
            if (!isset($data[$param]) || $data[$param] === null || $data[$param] === '') {
                $missing[] = $param;
            }
        }

        if (!empty($missing)) {
            throw KycException::validationFailed([
                'missing_parameters' => $missing
            ]);
        }
    }

    /**
     * Log KYC operation
     */
    protected function logKycOperation(string $operation, array $context = []): void
    {
        Log::info("KYC Operation: {$operation}", $context);
    }
}