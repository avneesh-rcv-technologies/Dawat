<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Admin;
use Modules\Notifications\Services\NotificationService;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Models\RestaurantBankingDetail;

class RestaurantBankingService
{
    public function __construct(private NotificationService $notifications, private Restaurant $restaurants) {}

    public function forOwner(Admin $owner, bool $lock = false): Restaurant
    {
        $query = Restaurant::query()->where('admin_id', $owner->id);
        if ($lock) $query->lockForUpdate();
        $restaurant = $query->firstOrFail();
        abort_if(in_array($restaurant->status, ['inactive', 'closed'], true), 403);

        return $restaurant;
    }

    public function save(Admin $owner, array $data, bool $creating): RestaurantBankingDetail
    {
        return DB::transaction(function () use ($owner, $data, $creating) {
            $restaurant = $this->forOwner($owner, true);
            return $this->persist($restaurant, $owner, $data, $creating, true);
        });
    }

    public function updateForRestaurant(int $restaurantId, array $data): RestaurantBankingDetail
    {
        return DB::transaction(function () use ($restaurantId, $data) {
            $restaurant = $this->restaurants->newQuery()->lockForUpdate()->findOrFail($restaurantId);
            $owner = $restaurant->admin()->firstOrFail();

            return $this->persist($restaurant, $owner, $data, false);
        });
    }

    private function persist(Restaurant $restaurant, Admin $owner, array $data, bool $creating, bool $ownerSubmission = false): RestaurantBankingDetail
    {
        if ($ownerSubmission && $restaurant->onboardingSteps()->where('step_key', OnboardingStep::BANKING->value)
            ->value('status') === OnboardingStatus::APPROVED->value) {
            throw ValidationException::withMessages([
                'banking' => 'Approved banking information cannot be updated.',
            ]);
        }

        $banking = $restaurant->bankingDetail()->first();
        if ($creating && $banking) {
            throw ValidationException::withMessages(['banking' => 'Banking details already submitted.']);
        }
        if (!$creating && !$banking) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())->setModel(RestaurantBankingDetail::class);
        }
        unset($data['account_number_confirmation']);
        $banking ??= $restaurant->bankingDetail()->make();
        $location = array_replace([
            'country_id' => $banking->country_id,
            'state_id' => $banking->state_id,
            'city_id' => $banking->city_id,
        ], array_intersect_key($data, array_flip(['country_id', 'state_id', 'city_id'])));
        Validator::make($location, self::locationRules($location), [
            'country_id.required' => 'Please select a country.',
            'country_id.exists' => 'Please select a valid country.',
            'state_id.required' => 'Please select a state.',
            'state_id.exists' => 'Please select a state belonging to the selected country.',
            'city_id.required' => 'Please select a city.',
            'city_id.exists' => 'Please select a city belonging to the selected state.',
        ])->validate();
        if (!$creating && $ownerSubmission) {
            $data = OnboardingChanges::changedValues($banking, $data);
            if (!$data) OnboardingChanges::reject('banking');
        }
        $banking->fill($data);
        // Older installations also require the location names alongside the IDs.
        foreach (['city' => 'cities', 'state' => 'country_states'] as $column => $table) {
            if (Schema::hasColumn($banking->getTable(), $column)) {
                $banking->setAttribute($column, DB::table($table)->where('id', $location[$column.'_id'])->value('name'));
            }
        }
        if (!$creating && !$ownerSubmission && !$banking->isDirty()) return $banking;
        $banking->save();
        if (!$creating && !$ownerSubmission) {
            return $banking;
        }

        $step = $restaurant->onboardingSteps()->updateOrCreate(
            ['step_key' => OnboardingStep::BANKING->value],
            [
                'status' => OnboardingStatus::SUBMITTED->value,
                'submitted_at' => now(), 'reviewed_by' => null, 'reviewed_at' => null, 'rejection_reason' => null,
            ]
        );

        // Repeating a submission within the same timestamp precision still needs history.
        if (!$creating && !$step->wasRecentlyCreated && !$step->wasChanged(['status', 'submitted_at', 'reviewed_by', 'reviewed_at', 'rejection_reason'])) {
            app(OnboardingHistoryService::class)->detailsUpdated($restaurant, OnboardingStep::BANKING->value, 'resubmitted');
        }

        DB::afterCommit(function () use ($owner, $restaurant, $creating) {
            try {
                $this->notifications->trigger('Restaurant', $creating ? 'RestaurantBankingSubmitted' : 'RestaurantBankingResubmitted', $owner, [
                    'restaurant_name' => $restaurant->name,
                    'restaurant_id' => $restaurant->id,
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Unable to send restaurant banking notification.', ['restaurant_id' => $restaurant->id]);
            }
        });

        return $banking;
    }

    public static function locationRules(array $location): array
    {
        $country = is_scalar($location['country_id'] ?? null) ? $location['country_id'] : null;
        $state = is_scalar($location['state_id'] ?? null) ? $location['state_id'] : null;

        return [
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'state_id' => ['required', 'integer', Rule::exists('country_states', 'id')->where('country_id', $country)],
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')->where('country_id', $country)->where('state_id', $state)],
        ];
    }
}
