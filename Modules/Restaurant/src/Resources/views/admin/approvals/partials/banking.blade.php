@php
    $banking = $restaurant->bankingDetail;
    $bankingStep = $stepRecords->get(\Modules\Restaurant\Enums\OnboardingStep::BANKING->value);
@endphp
<div id="banking-panel">
    <div class="ar-panel-head">
        <div>
            <p class="ar-eyebrow">Step {{ \Modules\Restaurant\Enums\OnboardingStep::BANKING->order() }}</p>
            <h3>Banking Information</h3>
            <p class="review-help">Bank account and company information submitted by the restaurant owner.</p>
        </div>
    </div>
    @if($banking)
        <div id="banking-details" class="space-y-6">
            <section class="ar-section" aria-labelledby="banking-account-heading">
                <h4 id="banking-account-heading" class="ar-section-title">Bank Account</h4>
                <dl class="ar-fields">
                    <div class="ar-field"><dt>Name of Account Holder</dt><dd>{{ $banking->account_holder_name }}</dd></div>
                    <div class="ar-field"><dt>Routing Number</dt><dd class="tabular-nums">{{ $banking->routing_number }}</dd></div>
                    <div class="ar-field ar-field--wide"><dt>Bank Account Number</dt><dd class="ar-sensitive-value tabular-nums"><span data-sensitive-display>{{ $banking->maskedAccountNumber() }}</span>@if(filled($banking->account_number))<button type="button" class="ar-sensitive-toggle" data-sensitive-toggle data-sensitive-label="bank account number" data-sensitive-value="{{ $banking->account_number }}" aria-label="Show bank account number" aria-pressed="false" title="Show bank account number"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/><path data-sensitive-slash d="m3 3 18 18" style="display:none"/></svg></button>@endif</dd></div>
                </dl>
            </section>
            @php $commission = \Modules\Restaurant\Support\PlatformCommission::forRestaurant($restaurant); @endphp
            <div class="ar-callout {{ $commission['accepted'] ? 'ar-callout--success' : 'ar-callout--warning' }} flex-wrap justify-between">
                <div>
                    <h4>Platform Commission</h4>
                    <p>
                        @if($commission['accepted'])
                            Agreed to {{ \Modules\Restaurant\Support\PlatformCommission::format($commission['accepted_percentage'] ?? 0) }}% on every order amount
                            <span class="opacity-75">&middot; {{ $restaurant->platform_commission_accepted_at?->format('M d, Y h:i A') }}</span>
                        @else
                            Not agreed yet
                        @endif
                    </p>
                </div>
                @if($commission['needs_reacceptance'])
                    <span class="ar-pill ar-pill--warning">Current rate is {{ \Modules\Restaurant\Support\PlatformCommission::format($commission['percentage'] ?? 0) }}% &mdash; needs re-acceptance</span>
                @endif
            </div>
            <section class="ar-section" aria-labelledby="banking-company-heading">
                <h4 id="banking-company-heading" class="ar-section-title">Company Information</h4>
                <dl class="ar-fields">
                    @foreach([
                        'business_legal_name' => 'Business Legal Entity Name',
                        'doing_business_as' => 'DBA / Doing Business As',
                        'address' => 'Address', 'city' => 'City', 'state' => 'State', 'country' => 'Country', 'postal_code' => 'Zip / Postal Code',
                    ] as $field => $label)
                        @php $bankingValue = in_array($field, ['city', 'state', 'country'], true) ? $banking->locationName($field) : $banking->{$field}; @endphp
                        <div class="ar-field"><dt>{{ $label }}</dt><dd @class(['is-empty' => blank($bankingValue)])>{{ $bankingValue ?: 'Not provided' }}</dd></div>
                    @endforeach
                </dl>
            </section>
        </div>
        @if($bankingStep?->rejection_reason && $bankingStep->status !== 'action_required')
            <div class="ar-callout ar-callout--danger" role="note">
                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></svg>
                <div><h4>Rejection reason</h4><p>{{ $bankingStep->rejection_reason }}</p></div>
            </div>
        @endif
    @else
        <div class="ar-empty">
            <span class="ar-empty-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10h18M5 10V20M19 10v10M9 14v3M15 14v3M3 20h18M12 3l9 5H3Z"/></svg></span>
            <h4>No banking details yet</h4>
            <p>Banking and company details will appear here when the owner submits them.</p>
        </div>
    @endif
</div>
