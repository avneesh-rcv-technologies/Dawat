<?php

namespace Modules\Restaurant\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Http\Requests\OwnerRegistrationCorrectionRequest;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\OnboardingChanges;

class OnboardingCorrectionController
{
    public function registration(OwnerRegistrationCorrectionRequest $request)
    {
        DB::transaction(function () use ($request) {
            [$restaurant, $step] = $this->editableStep($request, OnboardingStep::REGISTRATION);
            $data = $request->validated();
            unset($data['step_key']);
            $location = array_intersect_key($data, array_flip(['country_id', 'state_id', 'city_id']));
            unset($data['country_id'], $data['state_id'], $data['city_id']);
            $data['country'] = \Modules\Countries\Models\Country::findOrFail($location['country_id'])->name;
            $data['state'] = \Modules\Countries\Models\CountryState::findOrFail($location['state_id'])->name;
            $data['city'] = \Modules\Countries\Models\City::findOrFail($location['city_id'])->name;
            $changes = OnboardingChanges::changedValues($restaurant, $data);
            if (!$changes && $step->status !== 'draft') OnboardingChanges::reject('registration');
            $restaurant->update($changes);
            $addressData = $location + [
                'address_line_1' => $data['address_line1'] ?? '', 'address_line_2' => $data['address_line2'] ?? null,
                'zip_code' => $data['postal_code'] ?? null, 'city_name' => $data['city'],
            ];
            $address = $restaurant->addresses()->orderByDesc('is_default')->orderBy('id')->first();
            if ($address) $address->update($addressData);
            else $restaurant->addresses()->create($addressData + ['address_type' => 'primary', 'is_default' => true]);
            $this->submit($step);
        });

        return redirect()->route('restaurant.onboarding.kyc', ['step' => 'banking'])->with('success', 'Registration details saved. Please complete your banking information.');
    }

    private function editableStep(Request $request, OnboardingStep $key): array
    {
        $restaurant = Restaurant::where('admin_id', $request->user('admin')->id)
            ->whereIn('status', ['draft', 'active'])->lockForUpdate()->firstOrFail();
        $step = $restaurant->onboardingSteps()->where('step_key', $key->value)->lockForUpdate()->firstOrFail();
        abort_unless(\Modules\Restaurant\Services\RestaurantOnboardingAccessService::isStepEditable($step->status), 422, 'This step is read only while under review or approved.');

        return [$restaurant, $step];
    }

    private function submit($step): void
    {
        $step->update(['status' => 'submitted', 'submitted_at' => now(), 'reviewed_by' => null,
            'reviewed_at' => null, 'rejection_reason' => null]);
    }
}
