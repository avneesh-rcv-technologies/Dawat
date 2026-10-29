<form method="POST" action="{{ route('restaurant.onboarding.corrections.registration') }}" data-location-selects="correction"
    data-states-url="{{ route('api.countries.states-by-country', ['countryId' => '__ID__']) }}"
    data-cities-url="{{ route('api.countries.cities-by-state', ['stateId' => '__ID__']) }}">
    @csrf
    <input type="hidden" name="step_key" value="registration">
    <div class="details-grid">
        @foreach(['name' => 'Restaurant name', 'phone' => 'Phone', 'business_license' => 'Business license', 'address_line1' => 'Address line 1', 'address_line2' => 'Address line 2', 'postal_code' => 'Postal code'] as $field => $label)
            <label class="detail-field">{{ $label }}
                <input name="{{ $field }}" value="{{ old($field, $correctionRestaurant->{$field}) }}" @required(in_array($field, ['name', 'phone'], true)) maxlength="{{ $field === 'name' || str_starts_with($field, 'address') ? 100 : ($field === 'phone' ? 20 : ($field === 'postal_code' ? 80 : 255)) }}" style="display:block;width:100%;padding:10px;margin-top:8px;border:1px solid #cbd5e1;border-radius:8px">
            </label>
        @endforeach
        @foreach(['country' => 'Country', 'state' => 'State', 'city' => 'City'] as $field => $label)
            <label class="detail-field" for="correction-{{ $field }}_id">{{ $label }}
                <select name="{{ $field }}_id" id="correction-{{ $field }}_id" required style="display:block;width:100%;padding:10px;margin-top:8px;border:1px solid #cbd5e1;border-radius:8px">
                    <option value="">Select {{ strtolower($label) }}</option>
                    @foreach($correctionData[$field === 'country' ? 'countries' : ($field === 'city' ? 'cities' : 'states')] as $location)
                        <option value="{{ $location->id }}" @selected((string) $correctionData[$field.'Id'] === (string) $location->id)>{{ $location->name }}</option>
                    @endforeach
                </select>
            </label>
        @endforeach
        <label class="detail-field">Description
            <textarea name="description" maxlength="150" rows="3" style="display:block;width:100%;padding:10px;margin-top:8px;border:1px solid #cbd5e1;border-radius:8px">{{ old('description', $correctionRestaurant->getRawOriginal('description')) }}</textarea>
        </label>
    </div>
    <p id="correction-location-error" class="onboarding-form-message onboarding-form-message-error hidden" role="alert"></p>
    <button type="submit">Submit corrections for review</button>
</form>
<script src="{{ asset('js/onboarding-location-selects.js') }}" defer></script>
