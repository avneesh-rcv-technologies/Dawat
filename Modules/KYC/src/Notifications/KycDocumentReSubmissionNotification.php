<?php

namespace Modules\KYC\Notifications;

class KycDocumentReSubmissionNotification extends KycDocumentSubmissionNotification
{
    public function __construct($data)
    {
        parent::__construct(array_replace([
            'title' => 'KYC Documents Resubmitted',
            'request_message' => 'Your corrected KYC documents have been resubmitted successfully and are awaiting a new review.',
        ], $data));
    }

    public function getEventCode(): string
    {
        return 'kyc_document_re_submission';
    }

    public function getVariables(): array
    {
        return parent::getVariables() + [
            'resubmitted_at' => e($this->data['resubmitted_at'] ?? $this->data['submitted_at'] ?? 'Not recorded'),
        ];
    }

    public function getAvailableVariables(): array
    {
        return parent::getAvailableVariables() + ['resubmitted_at' => 'Recorded KYC document resubmission time'];
    }
}
