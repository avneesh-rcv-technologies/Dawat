<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Admin;
use Modules\Restaurant\Models\TaxInformation;
use Modules\Restaurant\Services\RestaurantTaxService;

class TaxInformationController
{
    public function __construct(private RestaurantTaxService $service) {}

    public function show(Request $request): JsonResponse
    {
        abort_unless($request->user() instanceof Admin, 403);
        $validated = $request->validate([
            'type' => ['sometimes', 'required', Rule::in(['individual', 'business'])],
        ]);
        $restaurant = $this->service->forOwner($request->user());
        $type = $validated['type'] ?? null;
        if ($type !== null) {
            $data = $this->taxData($restaurant->taxInformation()->where('tax_type', $type)->first());
        } else {
            $information = $restaurant->taxInformation()->get()->keyBy('tax_type');
            $data = [
                'individual' => $this->taxData($information->get('individual')),
                'business' => $this->taxData($information->get('business')),
            ];
        }
        $restaurantAddress = $this->restaurantAddress($restaurant);
        return response()->json([
            'success' => true,
            'data' => $data,
            'restaurant_address' => $restaurantAddress,
            'tax_type' => $restaurant->tax_type ?? 'individual',
            'tax_type_locked' => false,
            'onboarding_step' => $restaurant->onboardingSteps()->where('step_key', 'tax')->first(),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function store(Request $request): JsonResponse
    {
        return $this->save($request, true);
    }

    public function update(Request $request): JsonResponse
    {
        return $this->save($request, false);
    }

    private function save(Request $request, bool $creating): JsonResponse
    {
        abort_unless($request->user() instanceof Admin, 403);
        try {
            $tax = $this->service->save($request->user(), $request->all(), $creating);
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false, 'message' => 'Please check your tax information.',
                'errors' => $exception->errors(),
            ], 422);
        }

        $message = $creating ? 'Tax information submitted successfully.' : 'Tax information updated successfully.';
        $response = response()->json([
            'success' => true,
            'message' => $message,
            'data' => $this->taxData($tax),
            'tax_type' => $tax->taxable->tax_type,
            'tax_type_locked' => false,
            'onboarding_step' => $tax->taxable->onboardingSteps()->where('step_key', 'tax')->first(),
        ], $creating ? 201 : 200)->header('Cache-Control', 'no-store, private');
        if ($request->hasSession()) {
            $request->session()->flash('tax_success', $message);
        }

        return $response;
    }

    private function taxData(?TaxInformation $tax): ?array
    {
        if (!$tax) return null;
        $identifier = $tax->tax_type === 'individual' ? $tax->ssn : $tax->ein;

        return array_merge(array_intersect_key($tax->toArray(), array_flip(array_merge(
            RestaurantTaxService::inputFields($tax->tax_type), ['id', 'created_at', 'updated_at']
        ))), [
            'tax_identifier_masked' => filled($identifier) ? '•••• ' . substr($identifier, -4) : null,
        ]);
    }

    /**
     * The W-9 form uses these values when the partner elects to use the
     * restaurant address as its mailing address.
     */
    private function restaurantAddress($restaurant): array
    {
        $address = $restaurant->addresses()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();

        return [
            'address' => $address?->address_line_1 ?? $restaurant->address_line1,
            'country_id' => $address?->country_id ?? $restaurant->country_id,
            'state_id' => $address?->state_id ?? $restaurant->state_id,
            'city_id' => $address?->city_id ?? $restaurant->city_id,
            'zip_code' => $address?->zip_code ?? $restaurant->postal_code,
        ];
    }
}
