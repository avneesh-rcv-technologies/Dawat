<form id="tax-edit" class="hidden space-y-4" action="{{ route('admin.restaurant.approvals.tax.update', $restaurant->id) }}" method="POST">
    @csrf
    @method('PATCH')
    <input type="hidden" name="_tax_edit" value="1">
    <input type="hidden" name="tax_type" value="{{ $taxType }}">
    <p class="review-help">Edit {{ $taxType }} tax information. The current review status will be preserved.</p>
    @if($errors->any() && old('_tax_edit'))
        <div role="alert" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @php
            $editFields = $taxType === 'individual'
                ? ['full_name' => ['Full name', 100], 'company_legal_name' => ['Business legal entity name', 150]]
                : ['company_legal_name' => ['Company legal name', 150], 'doing_business_as' => ['Doing business as', 150]];
            $editFields += ['address' => ['Address', 150], 'zip_code' => ['ZIP code', 20]];
            $identifier = $taxType === 'individual' ? 'ssn' : 'ein';
        @endphp
        @foreach($editFields as $field => [$label, $maxLength])
            <div>
                <label for="tax-{{ $field }}" class="mb-1 block text-sm font-medium">{{ $label }}{{ $field !== 'doing_business_as' ? ' *' : '' }}</label>
                <input id="tax-{{ $field }}" name="{{ $field }}" type="text" value="{{ old($field, $tax->{$field}) }}" maxlength="{{ $maxLength }}" @required($field !== 'doing_business_as') class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
        @endforeach
        <div>
            <label for="tax-identifier" class="mb-1 block text-sm font-medium">New {{ strtoupper($identifier) }}</label>
            <input id="tax-identifier" name="{{ $identifier }}" type="password" inputmode="numeric" autocomplete="new-password" maxlength="11" pattern="{{ $identifier === 'ssn' ? '([0-9]{9}|[0-9]{3}-[0-9]{2}-[0-9]{4})' : '([0-9]{9}|[0-9]{2}-[0-9]{7})' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            <p class="review-help">Leave blank to keep the current {{ strtoupper($identifier) }}.</p>
        </div>
        @foreach(['country_id' => ['Country', $taxCountries], 'state_id' => ['State', $taxStates], 'city_id' => ['City', $taxCities]] as $field => [$label, $options])
            <div>
                <label for="tax-{{ $field }}" class="mb-1 block text-sm font-medium">{{ $label }} *</label>
                <select id="tax-{{ $field }}" name="{{ $field }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="">Select {{ strtolower($label) }}</option>
                    @foreach($options as $option)
                        <option value="{{ $option->id }}" @selected((string) old($field, $tax->{$field}) === (string) $option->id)>{{ $option->name }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
        @foreach(array_merge(['same_mailing_address' => 'Same mailing address', 'receive_1099_electronically' => 'Receive 1099 electronically'], $taxType === 'business' ? ['backup_tax_withholding' => 'Backup tax withholding'] : []) as $field => $label)
            <div>
                <label for="tax-{{ $field }}" class="mb-1 block text-sm font-medium">{{ $label }} *</label>
                <select id="tax-{{ $field }}" name="{{ $field }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="1" @selected((bool) old($field, $tax->{$field}))>Yes</option>
                    <option value="0" @selected(!(bool) old($field, $tax->{$field}))>No</option>
                </select>
            </div>
        @endforeach
    </div>
    <fieldset id="tax-mailing-fields" class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <legend class="mb-3 font-semibold">Mailing address</legend>
        @foreach(['mailing_address' => ['Address', 150], 'mailing_city' => ['City', 100], 'mailing_state' => ['State', 100], 'mailing_zip_code' => ['ZIP code', 20]] as $field => [$label, $maxLength])
            <div>
                <label for="tax-{{ $field }}" class="mb-1 block text-sm font-medium">{{ $label }} *</label>
                <input id="tax-{{ $field }}" name="{{ $field }}" value="{{ old($field, $tax->{$field}) }}" maxlength="{{ $maxLength }}" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
        @endforeach
    </fieldset>
    <p id="tax-location-error" role="alert" class="hidden text-sm text-red-600"></p>
    <div class="flex gap-3">
        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white">Save changes</button>
        <button id="cancel-tax-edit" type="button" class="rounded-lg border border-gray-300 px-4 py-2">Cancel</button>
    </div>
</form>
