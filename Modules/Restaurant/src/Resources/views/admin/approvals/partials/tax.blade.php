@php
    $tax = $taxRecords->get($taxType);
    $taxStep = $stepRecords->get(\Modules\Restaurant\Enums\OnboardingStep::TAX->value);
@endphp
<div id="tax-panel">
    <div class="ar-panel-head">
        <div>
            <p class="ar-eyebrow">Step {{ \Modules\Restaurant\Enums\OnboardingStep::TAX->order() }}</p>
            <h3>Tax Information</h3>
            <p class="review-help">Review the owner's individual and business tax information.</p>
        </div>
        <div class="ar-tax-type"><span>Tax type</span><strong>{{ ucfirst($taxType) }}</strong></div>
    </div>
    <div class="ar-callout ar-callout--info" role="note">
        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/></svg>
        <p>The selected tax type is used for this review.</p>
    </div>
    @if($tax)
        <div id="tax-details" class="space-y-6">
        <section class="ar-section" aria-labelledby="tax-details-heading">
        <h4 id="tax-details-heading" class="ar-section-title">{{ $taxType === 'individual' ? 'Individual' : 'Business' }} tax details</h4>
        <dl class="ar-fields">
            @php
                $taxFields = $taxType === 'individual'
                    ? ['full_name' => 'Full name', 'company_legal_name' => 'Business legal entity name']
                    : ['company_legal_name' => 'Company legal name', 'doing_business_as' => 'Doing business as'];
            @endphp
            @foreach($taxFields as $field => $label)
                <div class="ar-field"><dt>{{ $label }}</dt><dd @class(['is-empty' => blank($tax->{$field})])>{{ $tax->{$field} ?: 'Not provided' }}</dd></div>
            @endforeach
            @php $taxIdentifierLabel = $taxType === 'individual' ? 'SSN' : 'EIN'; $taxIdentifier = $taxType === 'individual' ? $tax->ssn : $tax->ein; @endphp
            <div class="ar-field"><dt>{{ $taxIdentifierLabel }}</dt><dd class="ar-sensitive-value tabular-nums"><span data-sensitive-display>{{ filled($taxIdentifier) ? '•••• ' . substr($taxIdentifier, -4) : 'Not provided' }}</span>@if(filled($taxIdentifier))<button type="button" class="ar-sensitive-toggle" data-sensitive-toggle data-sensitive-label="{{ $taxIdentifierLabel }}" data-sensitive-value="{{ $taxIdentifier }}" aria-label="Show {{ $taxIdentifierLabel }}" aria-pressed="false" title="Show {{ $taxIdentifierLabel }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/><path data-sensitive-slash d="m3 3 18 18" style="display:none"/></svg></button>@endif</dd></div>
            @foreach([
                'Address' => $tax->address,
                'City' => $tax->city?->name,
                'State' => $tax->state?->name,
                'Country' => $tax->country?->name,
                'ZIP code' => $tax->zip_code,
                'Same mailing address' => $tax->same_mailing_address ? 'Yes' : 'No',
                'Receive 1099 electronically' => $tax->receive_1099_electronically ? 'Yes' : 'No',
                'Backup tax withholding' => $taxType === 'business' ? ($tax->backup_tax_withholding ? 'Yes' : 'No') : null,
                'Tax certification' => $taxType === 'business' ? ($tax->tax_certification ? 'Certified' : 'Not certified') : null,
                'Certified at' => $taxType === 'business' ? $tax->tax_certified_at?->format('M d, Y h:i A') : null,
            ] as $label => $value)
                @if($value !== null)
                <div class="ar-field"><dt>{{ $label }}</dt><dd @class(['is-empty' => blank($value)])>{{ $value ?: 'Not provided' }}</dd></div>
                @endif
            @endforeach
        </dl>
        </section>
        @unless($tax->same_mailing_address)
            <section class="ar-section" aria-labelledby="tax-mailing-heading">
                <h4 id="tax-mailing-heading" class="ar-section-title">Mailing address</h4>
                <dl class="ar-fields">
                    @foreach(['mailing_address' => 'Address', 'mailing_city' => 'City', 'mailing_state' => 'State', 'mailing_zip_code' => 'ZIP code'] as $field => $label)
                        <div class="ar-field"><dt>{{ $label }}</dt><dd @class(['is-empty' => blank($tax->{$field})])>{{ $tax->{$field} ?: 'Not provided' }}</dd></div>
                    @endforeach
                </dl>
            </section>
        @endunless
        </div>
    @else
        <div class="ar-empty">
            <span class="ar-empty-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg></span>
            <h4>No {{ $taxType }} tax information yet</h4>
            <p>Information will appear here when the owner submits this tax type.</p>
        </div>
    @endif
    @if($taxStep?->rejection_reason && $taxStep->status !== 'action_required')
        <div class="ar-callout ar-callout--danger" role="note">
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></svg>
            <div><h4>Rejection reason</h4><p>{{ $taxStep->rejection_reason }}</p></div>
        </div>
    @endif
</div>
