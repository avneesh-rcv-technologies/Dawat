<?php

namespace Modules\Acl\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Acl\Http\Requests\CustomerContactRequest;
use Modules\Acl\Services\ContactService;

class CustomerContactController extends Controller
{
    protected ContactService $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->middleware('auth:sanctum');
        $this->contactService = $contactService;
    }

    /**
     * Get available categories
     */
    public function categories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $this->contactService->getCategories(),
            ],
        ]);
    }

    /**
     * Submit contact form (works for both guest and authenticated customers)
     */
    public function store(CustomerContactRequest $request): JsonResponse
    {
        try {
            $customer = auth('sanctum')->user();
            $message = $this->contactService->createCustomerMessage($customer, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Your message has been sent successfully. We\'ll get back to you soon!',
                'data' => [
                    'reference_number' => $message->reference_number,
                    'submitted_at' => $message->created_at->toIso8601String(),
                    'estimated_response_time' => '24-48 hours',
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get customer's message history
     */
    public function index(): JsonResponse
    {
        try {
            $customer = auth('sanctum')->user();
            $messages = $this->contactService->getCustomerMessages($customer->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'messages' => $messages->items(),
                    'pagination' => [
                        'current_page' => $messages->currentPage(),
                        'per_page' => $messages->perPage(),
                        'total' => $messages->total(),
                        'last_page' => $messages->lastPage(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve messages.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get single message by reference number
     */
    public function show(string $referenceNumber): JsonResponse
    {
        try {
            $customer = auth('sanctum')->user();
            $message = $this->contactService->getMessageByReference($referenceNumber, $customer->id);

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => $message,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve message.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
