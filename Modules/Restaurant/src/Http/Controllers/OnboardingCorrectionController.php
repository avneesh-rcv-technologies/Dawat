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
            if (!$changes) OnboardingChanges::reject('registration');
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

        return redirect()->route('restaurant.onboarding.kyc', ['step' => 'registration'])->with('success', 'Registration corrections submitted for review.');
    }

    public function menu(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'], 'items.*.id' => ['required', 'integer', 'distinct'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:5000'],
            'items.*.sale_price' => ['required', 'numeric', 'min:0'],
            'items.*.list_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.food_type' => ['required', 'in:veg,non_veg'],
            'items.*.is_available' => ['required', 'boolean'],
        ]);
        DB::transaction(function () use ($request, $data) {
            [$restaurant, $step] = $this->editableStep($request, OnboardingStep::MENU);
            $changed = false;
            foreach ($data['items'] as $item) {
                $food = \Modules\FoodManagements\Models\FoodItem::where('restaurant_id', $restaurant->id)->lockForUpdate()->findOrFail($item['id']);
                unset($item['id']);
                $item['is_available'] = (bool) $item['is_available'];
                $changes = OnboardingChanges::changedValues($food, $item);
                if ($changes) { $food->update($changes); $changed = true; }
            }
            if (!$changed) OnboardingChanges::reject('menu');
            $this->submit($step);
        });

        return redirect()->route('restaurant.onboarding.kyc', ['step' => 'menu'])->with('success', 'Menu corrections submitted for review.');
    }

    private function editableStep(Request $request, OnboardingStep $key): array
    {
        $restaurant = Restaurant::where('admin_id', $request->user('admin')->id)
            ->whereIn('status', ['draft', 'active'])->lockForUpdate()->firstOrFail();
        $step = $restaurant->onboardingSteps()->where('step_key', $key->value)->lockForUpdate()->firstOrFail();
        abort_unless(in_array($step->status, ['action_required', 'rejected'], true), 422, 'This step is not open for corrections.');

        return [$restaurant, $step];
    }

    private function submit($step): void
    {
        $step->update(['status' => 'submitted', 'submitted_at' => now(), 'reviewed_by' => null,
            'reviewed_at' => null, 'rejection_reason' => null]);
    }
}
