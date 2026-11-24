<?php

namespace Modules\KYC\Notifications\Concerns;

trait BuildsKycEmailVariables
{
    protected function kycEmailVariables(): array
    {
        return [
            'login_url' => route('website.partner-login'),
            'submission_url' => e($this->data['submission_url'] ?? route('restaurant.onboarding.kyc', ['step' => 'kyc'])),
            'support_url' => route('website.contact'),
            'current_year' => now()->year,
        ];
    }

    protected function kycEmailAvailableVariables(): array
    {
        return [
            'login_url' => 'Restaurant partner login URL',
            'submission_url' => 'Link to KYC documents',
            'support_url' => 'Contact support URL',
            'current_year' => 'Current year',
        ];
    }
}
