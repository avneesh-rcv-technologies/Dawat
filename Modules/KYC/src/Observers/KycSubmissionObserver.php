<?php

namespace Modules\KYC\Observers;

use Illuminate\Support\Facades\Log;
use Modules\KYC\Models\KycSubmission;
use Modules\KYC\Services\ComplianceService;

class KycSubmissionObserver
{
    public function updated(KycSubmission $submission): void
    {
        try {
            if (!$submission->wasChanged('status') || $submission->status !== 'approved') {
                return;
            }

            $complianceService = app(ComplianceService::class);
            if ($complianceService->isComplianceType((int) $submission->kyc_type_id)) {
                $complianceService->openRecord($submission);
            }
        } catch (\Throwable $e) {
            Log::error('KycSubmissionObserver compliance handling failed', [
                'error' => $e->getMessage(),
                'submission_id' => $submission->id,
            ]);
        }
    }
}
