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
use Modules\Restaurant\Support\PlatformCommission;

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
        if ($ownerSubmission) {
            RestaurantOnboardingAccessService::assertStepEditable($restaurant, OnboardingStep::BANKING);
        }

        $banking = $restaurant->bankingDetail()->first();
        if ($creating && $banking) {
            throw ValidationException::withMessages(['banking' => 'Banking details already submitted.']);
        }
        if (!$creating && !$banking) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())->setModel(RestaurantBankingDetail::class);
        }
        unset($data['account_number_confirmation']);

        // The commission agreement is stored on the restaurant, not on the banking record.
        $acceptsCommission = filter_var($data[PlatformCommission::INPUT] ?? false, FILTER_VALIDATE_BOOLEAN);
        unset($data[PlatformCommission::INPUT]);
        $commissionChanged = false;
        if ($ownerSubmission && PlatformCommission::isRequired()) {
            if (!$acceptsCommission) {
                throw ValidationException::withMessages([
                    PlatformCommission::INPUT => 'Please agree to the platform commission to continue.',
                ]);
            }
            $commissionChanged = $acceptsCommission && !PlatformCommission::acceptedCurrent($restaurant);
        }
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
            // Agreeing to a new commission rate counts as a change on its own.
            if (!$data && !$commissionChanged) OnboardingChanges::reject('banking');
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
        if ($ownerSubmission && $acceptsCommission) {
            PlatformCommission::accept($restaurant);
            // Capture the recorded acceptance, so later setting changes cannot change this email.
            $acceptance = [
                'owner_name' => $owner->name, 'restaurant_name' => $restaurant->name,
                'commission_percentage' => (float) $restaurant->platform_commission_percentage,
                'accepted_at' => $restaurant->platform_commission_accepted_at->format('M d, Y h:i A T'),
            ];
            DB::afterCommit(function () use ($owner, $restaurant, $acceptance) {
                try {
                    $sent = $this->notifications->trigger('Restaurant', 'RestaurantCommissionAccepted', $owner, $acceptance);
                    if (!$sent) Log::warning('Commission acceptance email could not be queued.', ['restaurant_id' => $restaurant->id]);
                } catch (\Throwable $e) {
                    Log::warning('Unable to send commission acceptance email.', ['restaurant_id' => $restaurant->id, 'exception_class' => get_class($e)]);
                }
            });
        }
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

        $submittedAt = $step->submitted_at->format('M d, Y h:i A T');
        DB::afterCommit(function () use ($owner, $restaurant, $creating, $submittedAt) {
            try {
                $this->notifications->trigger('Restaurant', $creating ? 'RestaurantBankingSubmitted' : 'RestaurantBankingResubmitted', $owner, [
                    'restaurant_name' => $restaurant->name,
                    'restaurant_id' => $restaurant->id,
                    'updated_at' => $submittedAt,
                    'submitted_at' => $submittedAt,
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
