@php
    $tax = $taxRecords->get($taxType);
    $taxStep = $stepRecords->get(\Modules\Restaurant\Enums\OnboardingStep::TAX->value);
    $canEditTax = $tax && bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.update');
@endphp
<div id="tax-panel">
    <div class="ar-panel-head">
        <div>
            <p class="ar-eyebrow">Step {{ \Modules\Restaurant\Enums\OnboardingStep::TAX->order() }}</p>
            <h3>Tax Information</h3>
            <p class="review-help">Review the owner's individual and business tax information.</p>
        </div>
        <form id="tax-type-selection" method="POST" action="{{ route('admin.restaurant.approvals.tax.type', $restaurant->id) }}">
            @csrf
            @method('PATCH')
            <fieldset @disabled(!bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.type'))>
                <legend>Tax type</legend>
                <div class="ar-segmented">
                @foreach(['individual' => 'Individual', 'business' => 'Business'] as $value => $label)
                    <label @class(['is-unavailable' => !$taxRecords->has($value)]) @if(!$taxRecords->has($value)) title="No {{ strtolower($label) }} tax information has been submitted" @endif>
                        <input type="radio" name="tax_type" value="{{ $value }}" @checked($taxType === $value) @disabled(!$taxRecords->has($value)) required>
                        {{ $label }}
                    </label>
                @endforeach
                </div>
            </fieldset>
            @error('tax_type')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            @if($taxRecords->count() < 2)<p class="mt-2 text-xs text-gray-500">A tax type can be selected after its information has been submitted.</p>@endif
        </form>
    </div>
    <div class="ar-callout ar-callout--info" role="note">
        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/></svg>
        <p>Only the selected tax type is used for review. Changing the tax type submits its saved information for review again.</p>
    </div>
    @if($tax)
        @if($canEditTax)
            <div class="flex justify-end"><button type="button" id="edit-tax" class="ar-btn" aria-expanded="false" aria-controls="tax-edit">Edit information</button></div>
            @include('restaurant::admin.approvals.partials.tax-edit')
        @endif
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
            <div class="ar-field"><dt>{{ $taxType === 'individual' ? 'SSN' : 'EIN' }}</dt><dd class="tabular-nums">{{ filled($taxType === 'individual' ? $tax->ssn : $tax->ein) ? '•••• ' . substr($taxType === 'individual' ? $tax->ssn : $tax->ein, -4) : 'Not provided' }}</dd></div>
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
