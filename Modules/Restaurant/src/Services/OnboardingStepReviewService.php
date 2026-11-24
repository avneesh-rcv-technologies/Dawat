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

    public function requestChanges(int $restaurantId, int $reviewerId, OnboardingStep $stepKey, string $reason, ?int $submissionId = null): Restaurant
    {
        if ($stepKey === OnboardingStep::DIETARY_CERTIFICATES) {
            throw ValidationException::withMessages(['step_key' => 'Review each dietary certificate separately in the Dietary Certificates tab.']);
        }
        if ($submissionId !== null && $stepKey !== OnboardingStep::KYC) {
            throw ValidationException::withMessages(['submission_id' => 'Document selection is only available for KYC.']);
        }
        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 500) {
            throw ValidationException::withMessages(['reason' => 'Enter a reason between 1 and 500 characters.']);
        }

        return DB::transaction(function () use ($restaurantId, $reviewerId, $stepKey, $reason, $submissionId) {
            $restaurant = $this->restaurants->newQuery()->lockForUpdate()->findOrFail($restaurantId);
            $step = $restaurant->onboardingSteps()->where('step_key', $stepKey->value)->lockForUpdate()->first();
            if (!$step || in_array($step->status, ['draft', 'not_started'], true)) {
                throw ValidationException::withMessages(['step_key' => 'This step has not been submitted yet.']);
            }
            if (in_array($step->status, ['action_required', 'rejected'], true)) {
                throw ValidationException::withMessages(['step_key' => 'A change request is already open. Wait for the owner to resubmit this step.']);
            }

            if ($stepKey === OnboardingStep::KYC) {
                $documents = \Modules\KYC\Models\KycSubmission::where('user_id', $restaurant->admin_id)
                    ->whereHas('kycType', fn ($query) => $query->where('type', 'kyc')->where('is_active', true))
                    ->where('status', '!=', 'draft')->orderByDesc('id')
                    ->lockForUpdate()->get()->unique('kyc_type_id');
                if ($submissionId !== null) {
                    $documents = $documents->where('id', $submissionId);
                    if ($documents->isEmpty()) {
                        throw ValidationException::withMessages(['submission_id' => 'Select a current submitted document belonging to this restaurant.']);
                    }
                }
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
        if (!in_array($stepKey, [OnboardingStep::REGISTRATION, OnboardingStep::BANKING, OnboardingStep::TAX], true)) {
            throw ValidationException::withMessages(['step_key' => 'This step cannot be reviewed here.']);
        }

        return DB::transaction(function () use ($restaurantId, $reviewerId, $stepKey) {
            $restaurant = $this->restaurants->newQuery()->lockForUpdate()->findOrFail($restaurantId);
            $step = $restaurant->onboardingSteps()->where('step_key', $stepKey->value)->lockForUpdate()->first();
            if (!$step || !in_array($step->status, ['submitted', 'pending'], true)) {
                throw ValidationException::withMessages(['step_key' => 'Only a submitted step can be started for review. Wait for the owner to resubmit any requested corrections.']);
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
            if (!$step || !in_array($step->status, ['submitted', 'pending', 'under_review'], true)
                || !$restaurant->bankingDetail()->exists()) {
                throw ValidationException::withMessages(['step_key' => 'Only submitted banking details can be approved or rejected.']);
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
            if (!$step || !in_array($step->status, ['submitted', 'pending', 'under_review'], true)
                || !$restaurant->taxInformation()->where('tax_type', $restaurant->tax_type)->exists()) {
                throw ValidationException::withMessages(['step_key' => 'Only submitted tax information can be approved or rejected.']);
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

            if (!$step || !in_array($step->status, ['submitted', 'pending', 'under_review'], true)) {
                throw ValidationException::withMessages(['step_key' => 'Only submitted registration details can be approved or rejected.']);
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
                'onboarding_step' => $approved ? OnboardingStep::BANKING->order() : OnboardingStep::REGISTRATION->order(),
            ]);

            return $restaurant;
        });
    }
}
