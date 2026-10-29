<?php

namespace Modules\Restaurant\Services;

class OnboardingStatusPresentation
{
    public static function status(?string $status): array
    {
        return match ($status) {
            'approved', 'active' => ['tone' => 'approved', 'message' => 'This step is approved. No further action is needed.', 'action' => null],
            'rejected' => ['tone' => 'rejected', 'message' => 'Review the feedback below, correct your information, and submit it again.', 'action' => 'Update & resubmit'],
            'action_required' => ['tone' => 'attention', 'message' => 'A refill has been requested for this onboarding step. Read the reason, update the requested details or documents, and resubmit for review.', 'action' => 'Refill & resubmit'],
            'under_review' => ['tone' => 'review', 'message' => 'Our team is reviewing your submission. No action is needed unless we request changes.', 'action' => 'View submission'],
            'submitted', 'pending', 'resubmitted' => ['tone' => 'pending', 'message' => 'Your information has been submitted and is waiting for review.', 'action' => 'View submission'],
            'suspended' => ['tone' => 'suspended', 'message' => 'This step is on hold. Please contact the administrator for assistance.', 'action' => 'View details'],
            default => ['tone' => 'draft', 'message' => 'Add your information and submit this step for review.', 'action' => 'Start'],
        };
    }

    public static function notice(array $timeline): array
    {
        $status = $timeline['restaurant']['onboarding_status'] ?? 'draft';
        $steps = collect($timeline['steps']);
        $attention = $steps->whereIn('status', ['rejected', 'action_required']);
        $waiting = $steps->whereIn('status', ['submitted', 'pending', 'resubmitted', 'under_review']);

        if ($status === 'suspended') {
            return ['tone' => 'suspended', 'title' => 'Onboarding is on hold',
                'message' => 'Please contact the administrator for assistance before continuing.'];
        }
        if ($attention->isNotEmpty() || in_array($status, ['action_required', 'rejected'], true)) {
            return ['tone' => $status === 'rejected' ? 'rejected' : 'attention', 'title' => $attention->contains('status', 'action_required') ? 'Onboarding step alert — Refill requested' : 'Your attention is needed',
                'message' => $attention->isNotEmpty()
                    ? 'Please update ' . $attention->pluck('label')->implode(', ') . '. Read the review feedback for each step, make the corrections, and submit again.'
                    : 'Review the feedback for your restaurant, update the requested information, and submit it again.'];
        }
        if ($timeline['progress']['is_complete']) {
            return ['tone' => 'approved', 'title' => 'All required steps are approved',
                'message' => in_array($status, ['approved', 'active'], true)
                    ? 'Your required onboarding information has been approved.'
                    : 'Your required information has been approved. Your restaurant is awaiting final onboarding approval and activation.'];
        }
        if ($waiting->isNotEmpty() || in_array($status, ['submitted', 'under_review'], true)) {
            return ['tone' => $status === 'under_review' || $waiting->contains('status', 'under_review') ? 'review' : 'pending',
                'title' => 'Your information is awaiting review',
                'message' => ($waiting->isNotEmpty() ? $waiting->pluck('label')->implode(', ') . ': submitted for review. ' : '')
                    . 'You can complete any remaining draft steps while our team reviews your submission.'];
        }

        return ['tone' => 'draft', 'title' => 'Continue setting up your restaurant',
            'message' => 'Complete and submit the remaining required steps. Each step will show its review status here. Menu details are optional.'];
    }
}
