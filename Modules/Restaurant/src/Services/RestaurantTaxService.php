<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Admin;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Models\TaxInformation;

class RestaurantTaxService
{
    public function __construct(private Restaurant $restaurants) {}

    public function forOwner(Admin $owner, bool $lock = false): Restaurant
    {
        $query = Restaurant::query()->where('admin_id', $owner->id);
        if ($lock) {
            $query->lockForUpdate();
        }
        $restaurant = $query->firstOrFail();
        abort_if(in_array($restaurant->status, ['inactive', 'closed'], true), 403);

        return $restaurant;
    }

    public function save(Admin $owner, array $input, bool $creating): TaxInformation
    {
        return DB::transaction(fn () => $this->persist($this->forOwner($owner, true), $input, $creating));
    }

    public function lockedEntityType(Restaurant $restaurant): ?string
    {
        $types = $restaurant->taxInformation()->orderBy('id')->pluck('tax_type');

        return $types->contains($restaurant->tax_type) ? $restaurant->tax_type : $types->first();
    }

    public function updateForRestaurant(int $restaurantId, array $input): TaxInformation
    {
        return DB::transaction(function () use ($restaurantId, $input) {
            $restaurant = $this->restaurants->newQuery()->lockForUpdate()->findOrFail($restaurantId);

            return $this->persist($restaurant, $input, false, true);
        });
    }

    public function selectTypeForRestaurant(int $restaurantId, string $type): Restaurant
    {
        Validator::make(['tax_type' => $type], [
            'tax_type' => ['required', Rule::in(['individual', 'business'])],
        ])->validate();

        return DB::transaction(function () use ($restaurantId, $type) {
            $restaurant = $this->restaurants->newQuery()->lockForUpdate()->findOrFail($restaurantId);
            if ($restaurant->tax_type === $type) {
                return $restaurant;
            }
            $hasDetails = $restaurant->taxInformation()->where('tax_type', $type)->exists();
            if (! $hasDetails) {
                throw ValidationException::withMessages(['tax_type' => ucfirst($type).' tax information has not been submitted yet.']);
            }
            $restaurant->update(['tax_type' => $type]);
            $restaurant->onboardingSteps()->updateOrCreate(
                ['step_key' => OnboardingStep::TAX->value],
                [
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'reviewed_by' => null, 'reviewed_at' => null, 'rejection_reason' => null,
                ]
            );

            DB::afterCommit(function () use ($restaurant, $type) {
                app(RestaurantTaxNotificationService::class)->send($restaurant, 'RestaurantTaxSubmitted', [
                    'tax_type' => $type, 'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
            });

            return $restaurant;
        });
    }

    private function persist(Restaurant $restaurant, array $input, bool $creating, bool $adminEdit = false): TaxInformation
    {
        $type = Validator::make($input, [
            'tax_type' => ['required', Rule::in(['individual', 'business'])],
        ])->validate()['tax_type'];
        $tax = $restaurant->taxInformation()->where('tax_type', $type)->first();
        if ($creating && $tax) {
            throw ValidationException::withMessages(['tax_information' => ucfirst($type).' tax information already submitted.']);
        }
        // An update may switch to the other entity type. In that case, create
        // that type's record and make it the restaurant's selected tax profile.
        $newEntityRecord = ! $tax;
        $tax ??= $restaurant->taxInformation()->make();
        $existing = [];
        foreach ($tax->getFillable() as $field) {
            $existing[$field] = $tax->getAttribute($field);
        }
        $existing = array_intersect_key($existing, array_flip(self::inputFields($type)));
        unset($input['_token'], $input['_method']);
        if ($adminEdit) {
            $input = array_intersect_key($input, array_flip(self::inputFields($type)));
        }
        $data = array_replace(($creating || $newEntityRecord) ? array_merge([
            'same_mailing_address' => true,
            'receive_1099_electronically' => false,
        ], $type === 'business' ? ['backup_tax_withholding' => false] : []) : $existing, $input);
        $validated = Validator::make($data, self::rules($data), self::messages(), self::attributes())->validate();
        foreach (($validated['tax_type'] === 'individual'
            ? ['doing_business_as', 'ein'] : ['full_name', 'ssn']) as $field) {
            $validated[$field] = null;
        }
        if ($type === 'individual') {
            $validated['backup_tax_withholding'] = false;
            $validated['tax_certification'] = false;
            $validated['tax_certified_at'] = null;
        }
        if ($validated['same_mailing_address']) {
            foreach (['mailing_address', 'mailing_city', 'mailing_state', 'mailing_zip_code'] as $field) {
                $validated[$field] = null;
            }
        }
        $typeChanged = $restaurant->tax_type !== $type;
        if (!$adminEdit && !$newEntityRecord) {
            $changes = OnboardingChanges::changedValues($tax, $validated);
            if (!$changes && !$typeChanged) OnboardingChanges::reject('tax_information');
            $validated = $changes;
        }
        $tax->fill($validated);
        if ($adminEdit) {
            if (!$tax->isDirty()) {
                return $tax;
            }
            $tax->save();
            app(OnboardingHistoryService::class)->detailsUpdated($restaurant, OnboardingStep::TAX->value);

            return $tax->fresh();
        }
        if ($typeChanged) {
            $restaurant->update(['tax_type' => $type]);
        }
        if (! $adminEdit) {
            $tax->tax_certified_at = $type === 'business' ? now() : null;
        }
        $tax->save();
        $step = $restaurant->onboardingSteps()->updateOrCreate(
            ['step_key' => OnboardingStep::TAX->value],
            [
                'status' => 'submitted', 'submitted_at' => now(),
                'reviewed_by' => null, 'reviewed_at' => null, 'rejection_reason' => null,
            ]
        );

        // Repeating a submission within the same timestamp precision still needs history.
        if (!$creating && !$step->wasRecentlyCreated && !$step->wasChanged(['status', 'submitted_at', 'reviewed_by', 'reviewed_at', 'rejection_reason'])) {
            app(OnboardingHistoryService::class)->detailsUpdated($restaurant, OnboardingStep::TAX->value, 'resubmitted');
        }

        DB::afterCommit(function () use ($restaurant, $type, $creating) {
            app(RestaurantTaxNotificationService::class)->send($restaurant, $creating ? 'RestaurantTaxSubmitted' : 'RestaurantTaxResubmitted', [
                'tax_type' => $type, 'updated_at' => now()->format('Y-m-d H:i:s'),
            ]);
        });

        return $tax->fresh();
    }

    public static function messages(): array
    {
        return [
            'missing' => 'The :attribute field is not available for the selected entity type.',
            'required' => 'Please complete the :attribute field.',
            'full_name.required' => 'Please enter your full name.',
            'company_legal_name.required' => 'Please enter the business legal entity name.',
            'ssn.regex' => 'Enter a 9-digit SSN, with or without hyphens (123-45-6789).',
            'ein.regex' => 'Enter a 9-digit EIN, with or without a hyphen (12-3456789).',
            'country_id.exists' => 'Please select a valid country.',
            'state_id.exists' => 'Please select a state belonging to the selected country.',
            'city_id.exists' => 'Please select a city belonging to the selected state.',
            'tax_certification.accepted' => 'Please certify that your tax information is correct before submitting.',
        ];
    }

    public static function attributes(): array
    {
        return [
            'tax_type' => 'entity type', 'full_name' => 'full name', 'ssn' => 'SSN', 'ein' => 'EIN',
            'company_legal_name' => 'business legal entity name', 'doing_business_as' => 'doing business as',
            'country_id' => 'country', 'state_id' => 'state', 'city_id' => 'city', 'zip_code' => 'zip or postal code',
            'mailing_address' => 'mailing address', 'mailing_city' => 'mailing city',
            'mailing_state' => 'mailing state', 'mailing_zip_code' => 'mailing zip or postal code',
            'tax_certification' => 'tax certification',
        ];
    }

    public static function inputFields(string $type): array
    {
        return array_merge([
            'tax_type', 'company_legal_name', 'address', 'country_id', 'state_id', 'city_id', 'zip_code',
            'same_mailing_address', 'receive_1099_electronically',
            'mailing_address', 'mailing_city', 'mailing_state', 'mailing_zip_code',
        ], $type === 'individual' ? ['full_name', 'ssn']
            : ['doing_business_as', 'ein', 'backup_tax_withholding', 'tax_certification']);
    }

    public static function rules(array $data): array
    {
        $individual = ($data['tax_type'] ?? null) === 'individual';
        $rules = [
            'tax_type' => ['required', Rule::in(['individual', 'business'])],
            'full_name' => $individual ? ['required', 'string', 'max:100'] : ['missing'],
            'ssn' => $individual ? ['required', 'string', 'regex:/^(?:[0-9]{9}|[0-9]{3}-[0-9]{2}-[0-9]{4})$/D'] : ['missing'],
            'company_legal_name' => ['required', 'string', 'max:150'],
            'doing_business_as' => $individual ? ['missing'] : ['nullable', 'string', 'max:150'],
            'ein' => $individual ? ['missing'] : ['required', 'string', 'regex:/^(?:[0-9]{9}|[0-9]{2}-[0-9]{7})$/D'],
            'address' => ['required', 'string', 'max:150'],
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'state_id' => ['required', 'integer', Rule::exists('country_states', 'id')->where('country_id', is_scalar($data['country_id'] ?? null) ? $data['country_id'] : null)],
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')->where('state_id', is_scalar($data['state_id'] ?? null) ? $data['state_id'] : null)->where('country_id', is_scalar($data['country_id'] ?? null) ? $data['country_id'] : null)],
            'zip_code' => ['required', 'string', 'max:20'],
            'same_mailing_address' => ['required', 'boolean'],
            'mailing_address' => ['exclude_if:same_mailing_address,true', 'required', 'string', 'max:150'],
            'mailing_city' => ['exclude_if:same_mailing_address,true', 'required', 'string', 'max:100'],
            'mailing_state' => ['exclude_if:same_mailing_address,true', 'required', 'string', 'max:100'],
            'mailing_zip_code' => ['exclude_if:same_mailing_address,true', 'required', 'string', 'max:20'],
            'receive_1099_electronically' => ['required', 'boolean'],
            'backup_tax_withholding' => $individual ? ['missing'] : ['required', 'boolean'],
            'tax_certification' => $individual ? ['missing'] : ['required', 'accepted'],
            'restaurant_id' => ['prohibited'], 'admin_id' => ['prohibited'],
            'taxable_id' => ['prohibited'], 'taxable_type' => ['prohibited'],
            'status' => ['prohibited'], 'reviewed_by' => ['prohibited'],
        ];
        foreach (array_diff(array_keys($data), self::inputFields($data['tax_type'] ?? 'business')) as $field) {
            $rules[$field] = ['missing'];
        }

        return $rules;
    }
}
