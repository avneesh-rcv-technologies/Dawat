<?php

namespace Modules\Restaurant\Services;

use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;

class RestaurantOnboardingReviewService
{
    public function __construct(private RestaurantOnboardingSummaryService $summary, private RestaurantOnboardingDetailsService $details) {}

    public function build(Restaurant $restaurant, array $visibleSteps): array
    {
        $sections = $this->summary->build($restaurant, [], $visibleSteps);
        $details = $this->details->build($restaurant);
        $states = collect($details['steps'])->keyBy('key');
        foreach ($sections as &$section) {
            $state = $states->get($section['key']);
            $section['can_skip'] = $state['can_skip'];
            $section['resubmitted_at'] = $state['resubmitted_at'];
            if ($section['key'] === OnboardingStep::TAX->value) {
                $section['fields'] = $this->taxFields($restaurant);
            }
            if ($section['key'] === OnboardingStep::REGISTRATION->value) {
                $restaurant->loadMissing(['addresses.city', 'addresses.state', 'addresses.country']);
                $address = $restaurant->addresses->sortByDesc('is_default')->first();
                if ($address) {
                    $fields = collect($section['fields'])->pluck('value', 'label')->all();
                    foreach ([
                        'Address line 1' => $address->address_line_1, 'Address line 2' => $address->address_line_2,
                        'City' => $address->city?->name ?: $address->city_name,
                        'State' => $address->state?->name, 'Country' => $address->country?->name,
                        'Postal code' => $address->zip_code,
                    ] as $label => $value) {
                        if (filled($value)) $fields[$label] = $value;
                    }
                    $section['fields'] = collect($fields)->map(fn ($value, $label) => compact('label', 'value'))->values()->all();
                }
            }
        }
        unset($section);

        return [
            'restaurant' => $details['restaurant'], 'progress' => $details['progress'],
            'owner' => ['name' => $restaurant->admin?->name, 'email' => $restaurant->admin?->email],
            'generated_at' => now()->format('M d, Y h:i A T'),
            'sections' => $sections,
        ];
    }

    private function taxFields(Restaurant $restaurant): array
    {
        $fields = [];
        foreach ($restaurant->taxInformation()->with(['city', 'state', 'country'])->orderBy('tax_type')->get() as $tax) {
            $type = ucfirst($tax->tax_type);
            $values = $tax->tax_type === 'individual'
                ? ['Full name' => $tax->full_name, 'SSN' => $tax->ssn ? '**** ' . substr($tax->ssn, -4) : null]
                : ['Company legal name' => $tax->company_legal_name, 'Doing business as' => $tax->doing_business_as, 'EIN' => $tax->ein ? '**** ' . substr($tax->ein, -4) : null];
            $values += [
                'Selected type' => $restaurant->tax_type === $tax->tax_type ? 'Yes' : 'No',
                'Address' => $tax->address, 'City' => $tax->city?->name, 'State' => $tax->state?->name,
                'Country' => $tax->country?->name, 'ZIP code' => $tax->zip_code,
                'Same mailing address' => $tax->same_mailing_address ? 'Yes' : 'No',
                'Receive 1099 electronically' => $tax->receive_1099_electronically ? 'Yes' : 'No',
                'Backup tax withholding' => $tax->backup_tax_withholding ? 'Yes' : 'No',
                'Certification' => $tax->tax_certification ? 'Certified' : 'Not certified',
                'Certified at' => $tax->tax_certified_at?->format('M d, Y h:i A'),
            ];
            if (!$tax->same_mailing_address) {
                $values += ['Mailing address' => $tax->mailing_address, 'Mailing city' => $tax->mailing_city, 'Mailing state' => $tax->mailing_state, 'Mailing ZIP code' => $tax->mailing_zip_code];
            }
            foreach ($values as $label => $value) {
                if (filled($value)) $fields[] = ['label' => $type . ' - ' . $label, 'value' => $value];
            }
        }

        return $fields;
    }
}
