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
<div class="space-y-6 p-6" id="banking-panel">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Banking Information</h3>
            <p class="review-help">Bank account and company information submitted by the restaurant owner.</p>
        </div>
        @if($canEditBanking)
            <button type="button" id="edit-banking" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold" aria-expanded="false" aria-controls="banking-edit">Edit information</button>
        @endif
    </div>
    @if($banking)
        <div id="banking-details" class="space-y-6">
            <dl class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-xl bg-gray-50 p-4"><dt class="text-xs font-semibold text-gray-500">Name of Account Holder</dt><dd class="mt-2 break-words text-sm font-medium">{{ $banking->account_holder_name }}</dd></div>
                <div class="rounded-xl bg-gray-50 p-4"><dt class="text-xs font-semibold text-gray-500">Routing Number</dt><dd class="mt-2 text-sm font-medium">{{ $banking->routing_number }}</dd></div>
                <div class="rounded-xl bg-gray-50 p-4 md:col-span-2"><dt class="text-xs font-semibold text-gray-500">Bank Account Number</dt><dd class="mt-2 text-sm font-medium">{{ $banking->maskedAccountNumber() }}</dd></div>
            </dl>
            <div class="border-t border-gray-100 pt-5">
                <h4 class="font-semibold">Company Information</h4>
                <dl class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach([
                        'business_legal_name' => 'Business Legal Entity Name',
                        'doing_business_as' => 'DBA / Doing Business As',
                        'address' => 'Address', 'city' => 'City', 'state' => 'State', 'country' => 'Country', 'postal_code' => 'Zip / Postal Code',
                    ] as $field => $label)
                        <div class="rounded-xl bg-gray-50 p-4"><dt class="text-xs font-semibold text-gray-500">{{ $label }}</dt><dd class="mt-2 break-words text-sm">{{ in_array($field, ['city', 'state', 'country'], true) ? ($banking->locationName($field) ?: 'Not provided') : ($banking->{$field} ?: 'Not provided') }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </div>
        @if($canEditBanking)
            <form id="banking-edit" data-location-selects="banking"
                data-states-url="{{ route('api.countries.states-by-country', ['countryId' => '__ID__']) }}"
                data-cities-url="{{ route('api.countries.cities-by-state', ['stateId' => '__ID__']) }}"
                class="hidden space-y-4" action="{{ route('admin.restaurant.approvals.banking.update', $restaurant->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_banking_edit" value="1">
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
                <div class="flex gap-3"><button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white">Save changes</button><button id="cancel-banking-edit" type="button" class="rounded-lg border border-gray-300 px-4 py-2">Cancel</button></div>
            </form>
        @endif
        <dl class="grid grid-cols-1 gap-4 border-t border-gray-100 pt-5 text-sm md:grid-cols-2">
            <div><dt class="text-gray-500">Submitted</dt><dd class="mt-1">{{ $bankingStep?->submitted_at?->format('M d, Y h:i A') ?? 'N/A' }}</dd></div>
            <div><dt class="text-gray-500">Last updated</dt><dd class="mt-1">{{ $banking->updated_at?->format('M d, Y h:i A') ?? 'N/A' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-gray-500">Reviewed</dt><dd class="mt-1">{{ $bankingStep?->reviewed_at?->format('M d, Y h:i A') ?? 'N/A' }}@if($bankingStep?->reviewer) by {{ $bankingStep->reviewer->name }}@endif</dd></div>
        </dl>
        @if($bankingStep?->rejection_reason && $bankingStep->status !== 'action_required')
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800"><h4 class="font-semibold">Rejection reason</h4><p class="mt-2 whitespace-pre-line text-sm">{{ $bankingStep->rejection_reason }}</p></div>
        @endif
    @else
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 text-center">
            <h4 class="font-semibold text-gray-900">No banking details yet</h4>
            <p class="review-help">Banking and company details will appear here when the owner submits them.</p>
        </div>
    @endif
</div>

@push('scripts')
    <script src="{{ asset('js/onboarding-location-selects.js') }}" defer></script>
@endpush
