<?php

namespace Modules\Restaurant\Services;

use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;

class RestaurantOnboardingStepDetailsService
{
    public function __construct(private RestaurantOnboardingSummaryService $summary)
    {
    }

    public function build(Restaurant $restaurant, OnboardingStep $step): array
    {
        $details = $this->summary->build($restaurant, visibleSteps: [$step])[0];

        if ($step === OnboardingStep::TAX) {
            $details['fields'] = $this->taxFields($restaurant);
        }

        return $details;
    }

    private function taxFields(Restaurant $restaurant): array
    {
        $tax = $restaurant->taxInformation()->with(['city', 'state', 'country'])
            ->where('tax_type', $restaurant->tax_type)->first();
        if (!$tax) {
            return [];
        }

        $identifier = $tax->tax_type === 'individual' ? $tax->ssn : $tax->ein;
        $fields = [
            'Tax type' => ucfirst($tax->tax_type),
            'Full name' => $tax->tax_type === 'individual' ? $tax->full_name : null,
            'Business legal entity name' => $tax->company_legal_name,
            'Doing business as' => $tax->tax_type === 'business' ? $tax->doing_business_as : null,
            $tax->tax_type === 'individual' ? 'SSN' : 'EIN' => filled($identifier) ? '•••• ' . substr($identifier, -4) : null,
            'Address' => $tax->address,
            'City' => $tax->city?->name,
            'State' => $tax->state?->name,
            'Country' => $tax->country?->name,
            'ZIP code' => $tax->zip_code,
            'Same mailing address' => $tax->same_mailing_address ? 'Yes' : 'No',
            'Receive 1099 electronically' => $tax->receive_1099_electronically ? 'Yes' : 'No',

        ];
        if ($tax->tax_type === 'business') {
            $fields += [
                'Backup tax withholding' => $tax->backup_tax_withholding ? 'Yes' : 'No',
                'Tax certification' => $tax->tax_certification ? 'Certified' : 'Not certified',
                'Certified at' => $tax->tax_certified_at?->format('M d, Y h:i A'),
            ];
        }
        if (!$tax->same_mailing_address) {
            $fields += [
                'Mailing address' => $tax->mailing_address,
                'Mailing city' => $tax->mailing_city,
                'Mailing state' => $tax->mailing_state,
                'Mailing ZIP code' => $tax->mailing_zip_code,
            ];
        }

        return collect($fields)->filter(fn ($value) => filled($value))
            ->map(fn ($value, $label) => ['label' => $label, 'value' => (string) $value])->values()->all();
    }
}
