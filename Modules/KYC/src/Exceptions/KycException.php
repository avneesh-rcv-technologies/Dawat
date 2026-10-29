<?php

namespace Modules\KYC\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KycException extends Exception
{
    protected $errorCode;
    protected $statusCode;
    protected $context;

    public function __construct(
        string $message = 'KYC operation failed',
        string $errorCode = 'KYC_ERROR',
        int $statusCode = 400,
        array $context = [],
        Exception $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
        $this->context = $context;
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $this->getMessage(),
            'error' => [
                'code' => $this->errorCode,
            ]
        ];

        if (!empty($this->context)) {
            if (isset($this->context['validation_errors'])) {
                $validationErrors = $this->context['validation_errors'];
                $flatErrors = [];
                foreach ($validationErrors as $fieldErrors) {
                    if (is_array($fieldErrors)) {
                        foreach ($fieldErrors as $error) {
                            $flatErrors[] = $error;
                        }
                    } elseif ($fieldErrors) {
                        $flatErrors[] = $fieldErrors;
                    }
                }

                $response['message'] = !empty($flatErrors) ? $flatErrors[0] : $this->getMessage();
                $response['errors'] = $validationErrors;
            } else {
                $response['error']['context'] = $this->context;
            }
        }

        return response()->json($response, $this->statusCode);
    }

    /**
     * Get the error code
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get the HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the context data
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Static factory methods for common exceptions
     */
    public static function submissionNotFound(int $id): self
    {
        return new self(
            'This KYC submission is no longer available. Please open the latest submission from the list.',
            'SUBMISSION_NOT_FOUND',
            404,
            ['submission_id' => $id]
        );
    }

    public static function requestNotFound(int $id): self
    {
        return new self(
            "KYC request with ID {$id} not found",
            'REQUEST_NOT_FOUND',
            404,
            ['request_id' => $id]
        );
    }

    public static function typeNotFound(int $id): self
    {
        return new self(
            "KYC type with ID {$id} not found",
            'TYPE_NOT_FOUND',
            404,
            ['type_id' => $id]
        );
    }

    public static function submissionNotEditable(int $id, string $status): self
    {
        return new self(
            "Submission cannot be edited in '{$status}' status",
            'SUBMISSION_NOT_EDITABLE',
            422,
            ['submission_id' => $id, 'status' => $status]
        );
    }

    public static function fileUploadFailed(string $reason): self
    {
        return new self(
            $reason,
            'FILE_UPLOAD_FAILED',
            422,
            ['reason' => $reason]
        );
    }

    public static function invalidAction(string $action): self
    {
        return new self(
            "Invalid action: {$action}",
            'INVALID_ACTION',
            422,
            ['action' => $action]
        );
    }

    public static function validationFailed(array $errors): self
    {
        return new self(
            'Validation failed',
            'VALIDATION_FAILED',
            422,
            ['validation_errors' => $errors]
        );
    }

    public static function notificationFailed(string $reason): self
    {
        return new self(
            "Notification failed: {$reason}",
            'NOTIFICATION_FAILED',
            500,
            ['reason' => $reason]
        );
    }

    public static function fieldsRequired(): self
    {
        return new self(
            'At least one field must be configured for the KYC Type',
            'FIELDS_REQUIRED',
            422
        );
    }

    public static function duplicateFieldKeys(array $duplicateKeys): self
    {
        return new self(
            'Field keys must be unique within the same KYC Type',
            'DUPLICATE_FIELD_KEYS',
            422,
            ['duplicate_keys' => $duplicateKeys]
        );
    }

    public static function typeInUse(int $id, int $submissionsCount, int $requestsCount): self
    {
        return new self(
            'Cannot delete KYC type that is currently in use',
            'TYPE_IN_USE',
            422,
            [
                'type_id' => $id,
                'submissions_count' => $submissionsCount,
                'requests_count' => $requestsCount
            ]
        );
    }
}
