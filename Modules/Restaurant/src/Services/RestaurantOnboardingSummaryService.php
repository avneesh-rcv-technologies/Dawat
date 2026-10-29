<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Str;
use Modules\FoodManagements\Models\FoodItem;
use Modules\KYC\Models\KycSubmission;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;

class RestaurantOnboardingSummaryService
{
    public function build(Restaurant $restaurant, array $pendingRequests = [], ?array $visibleSteps = null): array
    {
        $steps = array_values(array_filter(OnboardingStep::ordered(), fn ($step) => $visibleSteps === null || in_array($step, $visibleSteps, true)));
        $restaurant->loadMissing(['admin', 'onboardingSteps.reviewer']);
        if (in_array(OnboardingStep::BANKING, $steps, true)) {
            $restaurant->loadMissing('bankingDetail');
        }
        $records = $restaurant->onboardingSteps->keyBy('step_key');
        $menu = in_array(OnboardingStep::MENU, $steps, true) ? FoodItem::where('restaurant_id', $restaurant->id)->orderBy('id')
            ->get(['id', 'name', 'description', 'sale_price', 'list_price', 'food_type', 'is_available']) : collect();
        $submissions = in_array(OnboardingStep::KYC, $steps, true) && $restaurant->admin_id ? KycSubmission::where('user_id', $restaurant->admin_id)
            ->whereHas('kycType', fn ($query) => $query->where('type', 'kyc'))
            ->with(['kycType', 'data.field', 'files.field'])->latest('id')->get()->unique('kyc_type_id') : collect();

        return array_map(function (OnboardingStep $step) use ($restaurant, $records, $menu, $submissions, $pendingRequests) {
            $record = $records->get($step->value);
            $fields = match ($step) {
                OnboardingStep::REGISTRATION => $this->registrationFields($restaurant),
                OnboardingStep::MENU => $menu->map(fn ($item) => [
                    'label' => $item->name,
                    'value' => collect([
                        $item->description,
                        $item->food_type ? 'Food type: ' . $item->food_type : null,
                        $item->list_price !== null ? 'List price: ' . $item->list_price : null,
                        $item->sale_price !== null ? 'Sale price: ' . $item->sale_price : null,
                        'Available: ' . ($item->is_available ? 'Yes' : 'No'),
                    ])->filter(fn ($value) => filled($value))->implode("\n"),
                ])->all(),
                OnboardingStep::BANKING => $this->bankingFields($restaurant),
                OnboardingStep::TAX => [],
                OnboardingStep::KYC => $this->kycFields($submissions, $pendingRequests),
            };

            return [
                'key' => $step->value, 'number' => $step->order(), 'label' => $step->label(),
                'status' => $record?->status === 'action_required' ? 'Refill requested' : (OnboardingStatus::tryFrom($record?->status ?? '')?->label()
                    ?? ($record ? Str::headline($record->status) : 'Not started')),
                'submitted_at' => $record?->submitted_at?->format('M d, Y h:i A'),
                'reviewed_at' => $record?->reviewed_at?->format('M d, Y h:i A'),
                'reviewer' => $record?->reviewer?->name,
                'rejection_reason' => $record?->rejection_reason,
                'fields' => $fields,
            ];
        }, $steps);
    }

    private function registrationFields(Restaurant $restaurant): array
    {
        return $this->fields([
            'Restaurant name' => $restaurant->name,
            'Owner' => $restaurant->admin?->name,
            'Email' => $restaurant->admin?->email,
            'Email verified' => $restaurant->admin?->email_verified_at ? 'Yes' : 'No',
            'Phone' => $restaurant->phone,
            'Address line 1' => $restaurant->address_line1,
            'Address line 2' => $restaurant->address_line2,
            'City' => $restaurant->city,
            'State' => $restaurant->state,
            'Country' => $restaurant->country,
            'Postal code' => $restaurant->postal_code,
            'Description' => $restaurant->getRawOriginal('description') !== null ? $restaurant->description : null,
            'Business license' => $restaurant->business_license,
        ]);
    }

    private function bankingFields(Restaurant $restaurant): array
    {
        $banking = $restaurant->bankingDetail;
        if (!$banking) return [];

        return $this->fields([
            'Account holder' => $banking->account_holder_name,
            'Account number' => $banking->maskedAccountNumber(),
            'Legal business name' => $banking->business_legal_name,
            'Doing business as' => $banking->doing_business_as,
            'Business address' => $banking->address,
            'City' => $banking->locationName('city'),
            'State' => $banking->locationName('state'),
            'Country' => $banking->locationName('country'),
            'Postal code' => $banking->postal_code,
        ]);
    }

    private function kycFields($submissions, array $pendingRequests): array
    {
        $fields = [];
        foreach ($submissions as $submission) {
            $name = $submission->kycType?->name ?? 'Document';
            $fields[] = ['label' => $name, 'value' => Str::headline($submission->status)];
            foreach ($submission->data as $data) {
                if ($data->field?->field_type === 'file') continue;
                $value = $data->field_value;
                if (filled($value)) {
                    $fields[] = [
                        'label' => $name . ' — ' . ($data->field?->field_name ?? Str::headline($data->field_key ?? 'Field')),
                        'value' => is_array($value) ? implode(', ', $value) : (string) $value,
                    ];
                }
            }
            foreach ($submission->files->groupBy('field_id') as $files) {
                $fields[] = [
                    'label' => $name . ' — ' . ($files->first()->field?->field_name ?? 'Documents'),
                    'value' => $files->count() . ' file(s) uploaded.',
                ];
            }
            if ($submission->rejection_reason) {
                $fields[] = ['label' => $name . ' — Review notes', 'value' => $submission->rejection_reason];
            }
        }
        foreach ($pendingRequests as $request) {
            $fields[] = [
                'label' => 'Requested: ' . ($request->kycType?->name ?? $request->title),
                'value' => trim(($request->message ?? '') . ($request->due_date ? ' Due: ' . $request->due_date->format('M d, Y') : '')),
            ];
        }

        return $fields;
    }

    private function fields(array $values): array
    {
        return collect($values)->filter(fn ($value) => filled($value))
            ->map(fn ($value, $label) => ['label' => $label, 'value' => (string) $value])->values()->all();
    }
}
