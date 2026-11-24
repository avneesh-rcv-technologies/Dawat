<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;

class RestaurantOnboardingCompletionService
{
    public function __construct(private Restaurant $restaurants) {}

    public function complete(Restaurant $restaurant): Restaurant
    {
        return DB::transaction(function () use ($restaurant) {
            $restaurant = $this->restaurants->newQuery()->lockForUpdate()->findOrFail($restaurant->getKey());
            $pending = $this->pendingSteps($restaurant, true);
            if ($pending !== []) {
                throw ValidationException::withMessages([
                    'onboarding_status' => 'Approve all required onboarding sections first: '.implode(', ', $pending).'.',
                ]);
            }
            $oldStatus = $restaurant->onboarding_status;
            $previouslyCompleted = $restaurant->onboardingStatusHistories()
                ->whereNull('step_key')
                ->where('action', 'completed')
                ->where('new_status', OnboardingStatus::APPROVED->value)
                ->exists();

            if (! $previouslyCompleted) {
                $restaurant->fill([
                    'is_takeaway' => true,
                    'is_delivery' => true,
                    'is_accepting_orders' => true,
                    'allow_order_cancellation' => true,
                    // These are the status codes used by the cancellation policy UI.
                    'cancellable_statuses' => ['O', 'P', 'N', 'H'],
                    // Initialize delivery policy without replacing configured values.
                    'delivery_radius_miles' => $restaurant->delivery_radius_miles ?? 350,
                    'delivery_cost_per_mile' => $restaurant->delivery_cost_per_mile ?? '0.00',
                    'free_delivery_enabled' => $restaurant->free_delivery_enabled ?? false,
                    'free_delivery_threshold' => $restaurant->free_delivery_threshold ?? null,
                ]);

                $hours = $restaurant->hours()->firstOrNew();
                if (empty($hours->hours) && ! $hours->is_open_whole_day) {
                    $hours->fill([
                        'hours' => array_fill_keys(
                            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                            [['open' => true, 'from' => '09:00', 'to' => '22:00']]
                        ),
                        'is_open_whole_day' => false,
                    ])->save();
                }
            }

            $restaurant->fill([
                'onboarding_status' => OnboardingStatus::APPROVED->value,
                'status' => 'active',
            ])->save();

            if (! $previouslyCompleted) {
                app(OnboardingHistoryService::class)->record(
                    $restaurant->id, null, 'completed', $oldStatus?->value, OnboardingStatus::APPROVED->value
                );
            }

            if (! $previouslyCompleted || $oldStatus !== OnboardingStatus::APPROVED) {
                $approvedAt = now()->format('Y-m-d H:i:s');
                DB::afterCommit(function () use ($restaurant, $approvedAt) {
                    app(FinalOnboardingApprovedService::class)->send($restaurant, $approvedAt);
                });
            }

            return $restaurant;
        });
    }

    /** Use persisted step states; uploaded dietary certificates must also be approved. */
    public function pendingSteps(Restaurant $restaurant, bool $lock = false): array
    {
        $required = OnboardingStep::required();
        $certificateStatus = $restaurant->dietaryCertificateStatus();
        if ($certificateStatus !== null) {
            $required[] = OnboardingStep::DIETARY_CERTIFICATES;
        }
        $query = $restaurant->onboardingSteps()->whereIn('step_key', array_map(fn ($step) => $step->value, $required));
        if ($lock) {
            $query->lockForUpdate();
        }
        $statuses = $query->pluck('status', 'step_key');
        if ($certificateStatus !== null) {
            $statuses->put(OnboardingStep::DIETARY_CERTIFICATES->value, $certificateStatus);
        }

        return array_values(array_map(fn ($step) => $step->label(), array_filter($required,
            fn ($step) => $statuses->get($step->value) !== OnboardingStatus::APPROVED->value)));
    }
}
