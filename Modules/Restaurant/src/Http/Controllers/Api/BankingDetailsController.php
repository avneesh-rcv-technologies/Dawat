<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Acl\Models\Admin;
use Modules\Restaurant\Http\Requests\BankingDetailsRequest;
use Modules\Restaurant\Models\RestaurantBankingDetail;
use Modules\Restaurant\Services\RestaurantBankingService;

class BankingDetailsController
{
    public function __construct(private RestaurantBankingService $service) {}

    public function show(Request $request): JsonResponse
    {
        abort_unless($request->user() instanceof Admin, 403);
        $restaurant = $this->service->forOwner($request->user());
        $banking = $restaurant->bankingDetail()->first();

        return response()->json([
            'success' => true,
            'data' => $banking ? $this->bankingData($banking) : null,
            'onboarding_step' => $restaurant->onboardingSteps()->where('step_key', 'banking')->first(),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function store(BankingDetailsRequest $request): JsonResponse
    {
        try {
            $banking = $this->service->save($request->user(), $request->validated(), true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        }

        $message = 'Banking details submitted successfully.';
        $response = $this->response($banking, $message, 201);
        if ($request->hasSession()) {
            $request->session()->flash('banking_success', $message);
        }

        return $response;
    }

    public function update(BankingDetailsRequest $request): JsonResponse
    {
        $banking = $this->service->save($request->user(), $request->validated(), false);

        $message = 'Banking details updated successfully.';
        $response = $this->response($banking, $message);
        if ($request->hasSession()) {
            $request->session()->flash('banking_success', $message);
        }

        return $response;
    }

    private function response(RestaurantBankingDetail $banking, string $message = 'Banking details retrieved successfully.', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true, 'message' => $message,
            'data' => $this->bankingData($banking),
        ], $status)->header('Cache-Control', 'no-store, private');
    }

    private function bankingData(RestaurantBankingDetail $banking): array
    {
        $banking->loadMissing(['city', 'state', 'country']);

        return array_merge($banking->toArray(), [
            'routing_number' => $banking->routing_number,
            'account_number_masked' => $banking->maskedAccountNumber(),
            'account_number_last_four' => substr($banking->account_number, -4),
        ]);
    }
}
