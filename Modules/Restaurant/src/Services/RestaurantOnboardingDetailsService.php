<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Str;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;

class RestaurantOnboardingDetailsService
{
    public function build(Restaurant $restaurant): array
    {
        $restaurant->loadMissing([
            'onboardingSteps.reviewer',
            'onboardingStatusHistories' => fn ($query) => $query
                ->whereIn('action', ['submitted', 'resubmitted'])->where('new_status', 'submitted'),
        ]);
        $records = $restaurant->onboardingSteps->keyBy('step_key');
        $resubmissions = $restaurant->onboardingStatusHistories
            ->filter(fn ($history) => $history->isResubmission())
            ->sortByDesc(fn ($history) => [$history->created_at?->timestamp ?? 0, $history->id])
            ->groupBy('step_key');
        $steps = array_map(function (OnboardingStep $step) use ($records, $resubmissions) {
            $record = $records->get($step->value);
            $status = $record?->status ?? 'not_started';
            $resubmittedAt = $resubmissions->get($step->value)?->first()?->created_at;

            return [
                'key' => $step->value,
                'number' => $step->order(),
                'label' => $step->label(),
                'status' => $status,
                'status_label' => $status === 'action_required' ? 'Refill requested' : ($record ? (OnboardingStatus::tryFrom($status)?->label() ?? Str::headline($status)) : 'Not started'),
                'refill_requested' => $status === 'action_required',
                'can_skip' => in_array($step, OnboardingStep::canSkip(), true),
                'is_approved' => $status === OnboardingStatus::APPROVED->value,
                'submitted_at' => $record?->submitted_at?->toIso8601String(),
                'reviewed_at' => $record?->reviewed_at?->toIso8601String(),
                'reviewer' => $record?->reviewer ? [
                    'id' => $record->reviewer->id, 'name' => $record->reviewer->name,
                ] : null,
                'rejection_reason' => $record?->rejection_reason,
                'resubmitted' => $resubmittedAt !== null,
                'resubmitted_at' => $resubmittedAt?->toIso8601String(),
            ];
        }, OnboardingStep::ordered());
        $required = collect($steps)->where('can_skip', false);
        $approved = $required->where('is_approved', true)->count();
        $next = $required->firstWhere('is_approved', false);

        return [
            'restaurant' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'status' => $restaurant->status,
                'approval_status' => $restaurant->approval_status,
                'onboarding_status' => $restaurant->onboarding_status?->value,
                'onboarding_status_label' => $restaurant->onboarding_status?->label(),
                'onboarding_step' => $restaurant->onboarding_step,
                'tax_type' => $restaurant->tax_type,
                'submitted_at' => $restaurant->submitted_at?->toIso8601String(),
                'approved_at' => $restaurant->approved_at?->toIso8601String(),
                'rejection_reason' => $restaurant->rejection_reason,
            ],
            'progress' => [
                'total_steps' => count($steps),
                'required_steps' => $required->count(),
                'approved_required_steps' => $approved,
                'percentage' => $required->isEmpty() ? 100 : (int) round($approved / $required->count() * 100),
                'is_complete' => $approved === $required->count(),
            ],
            'next_step' => $next ? ['key' => $next['key'], 'number' => $next['number'], 'label' => $next['label']] : null,
            'steps' => $steps,
        ];
    }
}
