<?php

namespace Modules\Restaurant\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;
use Throwable;

class FinalOnboardingApprovedService
{
    public function __construct(private RestaurantOnboardingReviewService $review, private RestaurantTaxNotificationService $sender) {}

    public function send(Restaurant $restaurant, string $approvedAt): void
    {
        try {
            $restaurant->loadMissing('admin');
            if (!$restaurant->admin) return;
            $reviewSummary = $this->review->build($restaurant, OnboardingStep::ordered());
            $pdf = Pdf::loadView('restaurant::admin.approvals.review-pdf', compact('reviewSummary'))
                ->setPaper('a4')->setOption('isRemoteEnabled', false)->setOption('defaultFont', 'DejaVu Sans')->output();
            $this->sender->send($restaurant, 'FinalOnboardingApproved', [
                'approved_at' => $approvedAt,
                'attachments' => [[
                    'data' => base64_encode($pdf), 'encoding' => 'base64',
                    'name' => 'restaurant-' . $restaurant->id . '-onboarding-approved.pdf',
                    'mime' => 'application/pdf',
                ]],
            ]);
        } catch (Throwable $e) {
            try {
                ApiExceptionHandler::handle($e);
            } catch (Throwable $reportingError) {
                Log::warning('Unable to report final onboarding notification failure.', ['exception_class' => get_class($reportingError)]);
            }
        }
    }
}
