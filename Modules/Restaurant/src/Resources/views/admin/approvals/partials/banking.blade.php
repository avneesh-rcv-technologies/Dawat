@php
    $banking = $restaurant->bankingDetail;
    $bankingStep = $stepRecords->get(\Modules\Restaurant\Enums\OnboardingStep::BANKING->value);
    $canEditBanking = $banking && bouncer('admin')->hasPermission('admin.restaurant.approvals.banking.update');
    $bankingFields = [
        'account_holder_name' => ['Name of Account Holder', 150],
        'routing_number' => ['Routing Number', 9],
        'business_legal_name' => ['Business Legal Entity Name', 150],
        'doing_business_as' => ['DBA / Doing Business As', 150],
        'address' => ['Address', 255], 'postal_code' => ['Zip / Postal Code', 20],
    ];
@endphp
<div id="banking-panel">
    <div class="ar-panel-head">
        <div>
            <p class="ar-eyebrow">Step {{ \Modules\Restaurant\Enums\OnboardingStep::BANKING->order() }}</p>
            <h3>Banking Information</h3>
            <p class="review-help">Bank account and company information submitted by the restaurant owner.</p>
        </div>
        @if($canEditBanking)
            <button type="button" id="edit-banking" class="ar-btn" aria-expanded="false" aria-controls="banking-edit">Edit information</button>
        @endif
    </div>
    @if($banking)
        <div id="banking-details" class="space-y-6">
            <section class="ar-section" aria-labelledby="banking-account-heading">
                <h4 id="banking-account-heading" class="ar-section-title">Bank Account</h4>
                <dl class="ar-fields">
                    <div class="ar-field"><dt>Name of Account Holder</dt><dd>{{ $banking->account_holder_name }}</dd></div>
                    <div class="ar-field"><dt>Routing Number</dt><dd class="tabular-nums">{{ $banking->routing_number }}</dd></div>
                    <div class="ar-field ar-field--wide"><dt>Bank Account Number</dt><dd class="tabular-nums">{{ $banking->maskedAccountNumber() }}</dd></div>
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
        @if($canEditBanking)
            <form id="banking-edit" data-location-selects="banking"
                data-states-url="{{ route('api.countries.states-by-country', ['countryId' => '__ID__']) }}"
                data-cities-url="{{ route('api.countries.cities-by-state', ['stateId' => '__ID__']) }}"
                class="hidden space-y-4" action="{{ route('admin.restaurant.approvals.banking.update', $restaurant->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_banking_edit" value="1">
                <h4 class="review-edit-title">Edit banking information</h4>
                <p class="review-help">Fields marked * are required. The current review status will be preserved.</p>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach($bankingFields as $field => [$label, $maxLength])
                        <div>
                            <label for="banking-{{ $field }}" class="mb-1 block text-sm font-medium">{{ $label }}@if($field !== 'doing_business_as') <span aria-hidden="true">*</span>@endif</label>
                            <input id="banking-{{ $field }}" name="{{ $field }}" type="text" value="{{ old($field, $banking->{$field}) }}" maxlength="{{ $maxLength }}" @if($field !== 'doing_business_as') required @endif @if($field === 'routing_number') inputmode="numeric" pattern="[0-9]{9}" @endif aria-invalid="{{ $errors->has($field) ? 'true' : 'false' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                            @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                    @foreach(['country_id' => ['Country', $bankingCountries], 'state_id' => ['State', $bankingStates], 'city_id' => ['City', $bankingCities]] as $field => [$label, $locations])
                        <div>
                            <label for="banking-{{ $field }}" class="mb-1 block text-sm font-medium">{{ $label }} <span aria-hidden="true">*</span></label>
                            <select id="banking-{{ $field }}" name="{{ $field }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" aria-invalid="{{ $errors->has($field) ? 'true' : 'false' }}">
                                <option value="">Select {{ strtolower($label) }}</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" @selected(is_scalar(old($field, $banking->{$field})) && (string) old($field, $banking->{$field}) === (string) $location->id)>{{ $location->name }}</option>
                                @endforeach
                            </select>
                            @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                    <p id="banking-location-error" class="hidden text-sm text-red-600 md:col-span-2" role="status"></p>
                    <div>
                        <label for="banking-account-number" class="mb-1 block text-sm font-medium">New Bank Account Number</label>
                        <input id="banking-account-number" name="account_number" type="password" inputmode="numeric" autocomplete="new-password" pattern="[0-9]{4,17}" minlength="4" maxlength="17" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        <p class="review-help">Leave blank to keep the current account number ({{ $banking->maskedAccountNumber() }}).</p>
                        @error('account_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="banking-account-confirmation" class="mb-1 block text-sm font-medium">Confirm New Bank Account Number</label>
                        <input id="banking-account-confirmation" name="account_number_confirmation" type="password" inputmode="numeric" autocomplete="new-password" maxlength="17" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('account_number_confirmation')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="ar-form-actions"><button type="submit" class="ar-btn ar-btn--primary">Save changes</button><button id="cancel-banking-edit" type="button" class="ar-btn">Cancel</button></div>
            </form>
        @endif
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

@push('scripts')
    <script src="{{ asset('js/onboarding-location-selects.js') }}" defer></script>
@endpush
