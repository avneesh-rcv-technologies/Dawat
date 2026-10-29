<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Facades\Log;
use Modules\Acl\Models\Admin;
use Modules\KYC\Models\KycSubmission;
use Modules\Notifications\Services\NotificationService;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;

class OnboardingRefillNotificationService
{
    public function __construct(private NotificationService $notifications) {}

    public function sendForRestaurant(Restaurant $restaurant, OnboardingStep $step, string $reason): void
    {
        try {
            $this->send($restaurant->admin, $restaurant, $step, $reason);
        } catch (\Throwable $exception) {
            Log::warning('Unable to prepare onboarding refill request notification.', ['restaurant_id' => $restaurant->id, 'exception_class' => get_class($exception)]);
        }
    }

    public function sendForDocument(KycSubmission $submission, string $reason): void
    {
        try {
            $restaurant = Restaurant::where('admin_id', $submission->user_id)->first();
            $this->send($submission->user, $restaurant, OnboardingStep::KYC, $reason, $submission->kycType?->name);
        } catch (\Throwable $exception) {
            Log::warning('Unable to prepare KYC refill request notification.', ['submission_id' => $submission->id, 'exception_class' => get_class($exception)]);
        }
    }

    private function send(?Admin $owner, ?Restaurant $restaurant, OnboardingStep $step, string $reason, ?string $document = null): void
    {
        if (!$owner) return;
        try {
            $sent = $this->notifications->trigger('Restaurant', 'OnboardingRefillRequested', $owner, [
                'restaurant_name' => $restaurant?->name ?? $owner->name,
                'restaurant_id' => $restaurant?->id ?? 0, 'owner_name' => $owner->name,
                'step_name' => $step->label(), 'document_name' => $document ?? 'All details in this step',
                'reason' => $reason, 'requested_at' => now()->format('M d, Y h:i A'),
                'correction_url' => $restaurant ? route('restaurant.onboarding.kyc', ['step' => $step->value]) : route('admin.login.form'),
            ]);
            if (!$sent) Log::warning('Unable to dispatch onboarding refill request notification.', ['owner_id' => $owner->id, 'step' => $step->value]);
        } catch (\Throwable $exception) {
            Log::warning('Unable to send onboarding refill request notification.', ['owner_id' => $owner->id, 'step' => $step->value, 'exception_class' => get_class($exception)]);
        }
    }
}
