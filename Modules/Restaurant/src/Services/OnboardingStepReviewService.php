<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;

class OnboardingStepReviewService
{
    public function __construct(private Restaurant $restaurants)
    {
    }

    public function requestChanges(int $restaurantId, int $reviewerId, OnboardingStep $stepKey, string $reason): Restaurant
    {
        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 500) {
            throw ValidationException::withMessages(['reason' => 'Enter a reason between 1 and 500 characters.']);
        }

        return DB::transaction(function () use ($restaurantId, $reviewerId, $stepKey, $reason) {
            $restaurant = $this->restaurants->newQuery()->lockForUpdate()->findOrFail($restaurantId);
            $step = $restaurant->onboardingSteps()->where('step_key', $stepKey->value)->lockForUpdate()->first();
            if (!$step || in_array($step->status, ['draft', 'not_started'], true)) {
                throw ValidationException::withMessages(['step_key' => 'This step has not been submitted yet.']);
            }

            if ($stepKey === OnboardingStep::KYC) {
                $documents = \Modules\KYC\Models\KycSubmission::where('user_id', $restaurant->admin_id)
                    ->whereHas('kycType', fn ($query) => $query->where('type', 'kyc')->where('is_active', true))
                    ->lockForUpdate()->get();
                if ($documents->isEmpty()) {
                    throw ValidationException::withMessages(['step_key' => 'No KYC documents have been submitted.']);
                }
                foreach ($documents as $document) {
                    app(\Modules\KYC\Services\KycSubmissionService::class)->requestChanges($document->id, $reviewerId, $reason);
                }
            } else {
                $step->update([
                    'status' => OnboardingStatus::ACTION_REQUIRED->value,
                    'reviewed_by' => $reviewerId, 'reviewed_at' => now(), 'rejection_reason' => $reason,
                ]);
                DB::afterCommit(function () use ($restaurant, $stepKey, $reason) {
                    app(OnboardingRefillNotificationService::class)->sendForRestaurant($restaurant, $stepKey, $reason);
                });
            }

            return $restaurant;
        });
    }

    public function startReview(int $restaurantId, int $reviewerId, OnboardingStep $stepKey): Restaurant
    {
        if (!in_array($stepKey, [OnboardingStep::REGISTRATION, OnboardingStep::MENU, OnboardingStep::BANKING, OnboardingStep::TAX], true)) {
            throw ValidationException::withMessages(['step_key' => 'This step cannot be reviewed here.']);
        }

        return DB::transaction(function () use ($restaurantId, $reviewerId, $stepKey) {
            $restaurant = $this->restaurants->newQuery()->lockForUpdate()->findOrFail($restaurantId);
            $step = $restaurant->onboardingSteps()->where('step_key', $stepKey->value)->lockForUpdate()->first();
            if (!$step || !in_array($step->status, ['submitted', 'pending', 'action_required'], true)) {
                throw ValidationException::withMessages(['step_key' => 'Only a submitted step or a step with a refill request can be started for review.']);
            }
            if ($stepKey === OnboardingStep::BANKING && !$restaurant->bankingDetail()->exists()) {
                throw ValidationException::withMessages(['step_key' => 'No banking details have been submitted.']);
            }
            if ($stepKey === OnboardingStep::TAX && !$restaurant->taxInformation()->where('tax_type', $restaurant->tax_type)->exists()) {
                throw ValidationException::withMessages(['step_key' => 'No tax information has been submitted.']);
            }

            $step->update([
                'status' => OnboardingStatus::UNDER_REVIEW->value,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => null,
                'rejection_reason' => null,
            ]);

            return $restaurant;
        });
    }

    public function reviewBanking(int $restaurantId, int $reviewerId, bool $approved, ?string $reason = null): Restaurant
    {
        $reason = trim($reason ?? '');
        if (!$approved && $reason === '') {
            throw ValidationException::withMessages(['rejection_reason' => 'A rejection reason is required.']);
        }
        if (mb_strlen($reason) > 500) {
            throw ValidationException::withMessages(['rejection_reason' => 'The rejection reason must not exceed 500 characters.']);
        }

        return DB::transaction(function () use ($restaurantId, $reviewerId, $approved, $reason) {
            $restaurant = $this->restaurants->newQuery()->lockForUpdate()->findOrFail($restaurantId);
            $step = $restaurant->onboardingSteps()->where('step_key', OnboardingStep::BANKING->value)->lockForUpdate()->first();
            if (!$step || $step->status !== OnboardingStatus::UNDER_REVIEW->value
                || !$restaurant->bankingDetail()->exists()) {
                throw ValidationException::withMessages(['step_key' => 'Start review before approving or rejecting banking details.']);
            }

            $reviewedAt = now();
            $step->update([
                'status' => $approved ? OnboardingStatus::APPROVED->value : OnboardingStatus::REJECTED->value,
                'reviewed_by' => $reviewerId, 'reviewed_at' => $reviewedAt,
                'rejection_reason' => $approved ? null : $reason,
            ]);
            $owner = $restaurant->admin;
            DB::afterCommit(function () use ($restaurant, $owner, $approved, $reason, $reviewedAt) {
                if (!$owner) return;
                try {
                    app(\Modules\Notifications\Services\NotificationService::class)->trigger(
                        'Restaurant', $approved ? 'RestaurantBankingApproved' : 'RestaurantBankingRejected', $owner, [
                            'restaurant_name' => $restaurant->name, 'restaurant_id' => $restaurant->id,
                            'reviewed_at' => $reviewedAt->format('Y-m-d H:i:s'),
                            'rejection_reason' => $approved ? '' : $reason,
                        ]
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Unable to send banking review notification.', ['restaurant_id' => $restaurant->id, 'exception_class' => get_class($e)]);
                }
            });

            return $restaurant;
        });
    }

    public function reviewMenu(int $restaurantId, int $reviewerId, bool $approved, ?string $reason = null): Restaurant
    {
        $reason = trim($reason ?? '');
        if (!$approved && ($reason === '' || mb_strlen($reason) > 500)) {
            throw ValidationException::withMessages(['reason' => 'Enter a reason between 1 and 500 characters.']);
        }
        return DB::transaction(function () use ($restaurantId, $reviewerId, $approved, $reason) {
            $restaurant = $this->restaurants->newQuery()->lockForUpdate()->findOrFail($restaurantId);
            $step = $restaurant->onboardingSteps()->where('step_key', OnboardingStep::MENU->value)->lockForUpdate()->first();
            if (!$step || $step->status !== 'under_review') {
                throw ValidationException::withMessages(['step_key' => 'Start review before approving or rejecting menu details.']);
            }
            $step->update(['status' => $approved ? 'approved' : 'rejected', 'reviewed_by' => $reviewerId,
                'reviewed_at' => now(), 'rejection_reason' => $approved ? null : $reason]);
            return $restaurant;
        });
    }

    public function reviewTax(int $restaurantId, int $reviewerId, bool $approved, ?string $reason = null): Restaurant
    {
        $reason = trim($reason ?? '');
        if (!$approved && $reason === '') {
            throw ValidationException::withMessages(['rejection_reason' => 'A rejection reason is required.']);
        }
        if (mb_strlen($reason) > 500) {
            throw ValidationException::withMessages(['rejection_reason' => 'The rejection reason must not exceed 500 characters.']);
        }

        return DB::transaction(function () use ($restaurantId, $reviewerId, $approved, $reason) {
            $restaurant = $this->restaurants->newQuery()->lockForUpdate()->findOrFail($restaurantId);
            $step = $restaurant->onboardingSteps()->where('step_key', OnboardingStep::TAX->value)->lockForUpdate()->first();
            if (!$step || $step->status !== OnboardingStatus::UNDER_REVIEW->value
                || !$restaurant->taxInformation()->where('tax_type', $restaurant->tax_type)->exists()) {
                throw ValidationException::withMessages(['step_key' => 'Start review before approving or rejecting tax information.']);
            }
            $reviewedAt = now();
            $type = $restaurant->tax_type;
            $step->update([
                'status' => $approved ? OnboardingStatus::APPROVED->value : OnboardingStatus::REJECTED->value,
                'reviewed_by' => $reviewerId, 'reviewed_at' => $reviewedAt,
                'rejection_reason' => $approved ? null : $reason,
            ]);

            DB::afterCommit(function () use ($restaurant, $type, $approved, $reason, $reviewedAt) {
                app(RestaurantTaxNotificationService::class)->send($restaurant, $approved ? 'RestaurantTaxApproved' : 'RestaurantTaxRejected', [
                    'tax_type' => $type, 'reviewed_at' => $reviewedAt->format('Y-m-d H:i:s'),
                    'rejection_reason' => $approved ? '' : $reason,
                ]);
            });

            return $restaurant;
        });
    }

    public function reviewRegistration(int $restaurantId, int $reviewerId, bool $approved, ?string $reason = null): Restaurant
    {
        $reason = trim($reason ?? '');
        if (!$approved && $reason === '') {
            throw ValidationException::withMessages(['rejection_reason' => 'A rejection reason is required.']);
        }

        return DB::transaction(function () use ($restaurantId, $reviewerId, $approved, $reason) {
            $restaurant = $this->restaurants->newQuery()->lockForUpdate()->findOrFail($restaurantId);
            $step = $restaurant->onboardingSteps()
                ->where('step_key', OnboardingStep::REGISTRATION->value)
                ->lockForUpdate()->first();

            if (!$step || $step->status !== OnboardingStatus::UNDER_REVIEW->value) {
                throw ValidationException::withMessages(['step_key' => 'Start review before approving or rejecting registration details.']);
            }

            $reviewedAt = now();
            $step->update([
                'status' => $approved ? OnboardingStatus::APPROVED->value : OnboardingStatus::REJECTED->value,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => $reviewedAt,
                'rejection_reason' => $approved ? null : $reason,
            ]);
            $restaurant->update([
                'approval_status' => $approved ? 'approved' : 'rejected',
                'approved_by' => $reviewerId,
                'approved_at' => $approved ? $reviewedAt : null,
                'rejection_reason' => $approved ? null : $reason,
                'onboarding_step' => $approved ? OnboardingStep::MENU->order() : OnboardingStep::REGISTRATION->order(),
            ]);

            return $restaurant;
        });
    }
}
