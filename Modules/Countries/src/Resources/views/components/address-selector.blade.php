@props([
    'modelClass' => '',
    'modelId' => null,
    'addressId' => null,
    'address' => null,
    'showAddressType' => true,
    'required' => true,
    'fieldPrefix' => 'address',
])

@php
    use Modules\Countries\Helpers\AddressHelper;

    $addressData = AddressHelper::formatAddressForForm($address);
    $componentId = 'address_' . uniqid();
    $countries = fn_get_countries();
@endphp

<div class="space-y-4 w-full" data-address-component id="{{ $componentId }}">

    <input type="hidden" name="{{ $fieldPrefix }}[model_class]" value="{{ $modelClass }}">
    <input type="hidden" name="{{ $fieldPrefix }}[model_id]" value="{{ $modelId }}">
    @if($addressId || !empty($addressData['id']))
        <input type="hidden" name="{{ $fieldPrefix }}[address_id]" value="{{ $addressId ?? $addressData['id'] }}">
    @endif

    {{-- Address Type --}}
    @if($showAddressType)
        <div class="flex flex-col gap-1">
            <label class="custom-label" for="{{ $fieldPrefix }}_type">Address Type</label>
            <select name="{{ $fieldPrefix }}[address_type]" id="{{ $fieldPrefix }}_type" class="input-field">
                <option value="">Select Type</option>
                @foreach(['home','office','billing','shipping','other'] as $type)
                    <option value="{{ $type }}" {{ $addressData['address_type'] === $type ? 'selected' : '' }}>
                        {{ ucfirst($type) }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    {{-- Address Line 1 --}}
    <div class="flex flex-col gap-1">
        <label class="custom-label" for="{{ $fieldPrefix }}_line_1">
            Address Line 1 @if($required)<span class="text-red-500">*</span>@endif
        </label>
        <input
            type="text"
            id="{{ $fieldPrefix }}_line_1"
            name="{{ $fieldPrefix }}[address_line_1]"
            class="input-field"
            value="{{ old($fieldPrefix.'.address_line_1', $addressData['address_line_1']) }}"
            {{ $required ? 'required' : '' }}
        >
    </div>

    {{-- Address Line 2 --}}
    <div class="flex flex-col gap-1">
        <label class="custom-label" for="{{ $fieldPrefix }}_line_2">Address Line 2</label>
        <input
            type="text"
            id="{{ $fieldPrefix }}_line_2"
            name="{{ $fieldPrefix }}[address_line_2]"
            class="input-field"
            value="{{ old($fieldPrefix.'.address_line_2', $addressData['address_line_2']) }}"
        >
    </div>

    {{-- Country --}}
    <div class="flex flex-col gap-1">
        <label class="custom-label" for="{{ $fieldPrefix }}_country">
            Country @if($required)<span class="text-red-500">*</span>@endif
        </label>
        <select 
            name="{{ $fieldPrefix }}[country_id]" 
            id="{{ $fieldPrefix }}_country" 
            class="input-field"
            {{ $required ? 'required' : '' }}
        >
            <option value="">Select Country</option>
            @foreach($countries as $country)
                <option value="{{ $country->id }}" {{ $addressData['country_id'] == $country->id ? 'selected' : '' }}>
                    {{ $country->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- State --}}
    <div class="flex flex-col gap-1">
        <label class="custom-label" for="{{ $fieldPrefix }}_state">State / Province</label>
        <select 
            name="{{ $fieldPrefix }}[state_id]" 
            id="{{ $fieldPrefix }}_state" 
            class="input-field"
        >
            <option value="">Select State</option>
        </select>
    </div>

    {{-- City --}}
    <div class="flex flex-col gap-1">
        <label class="custom-label" for="{{ $fieldPrefix }}_city">City</label>
        <select 
            name="{{ $fieldPrefix }}[city_id]" 
            id="{{ $fieldPrefix }}_city" 
            class="input-field"
        >
            <option value="">Select City</option>
        </select>
    </div>

    {{-- Zip Code --}}
    <div class="flex flex-col gap-1">
        <label class="custom-label" for="{{ $fieldPrefix }}_zip">Zip Code</label>
        <input
            type="text"
            id="{{ $fieldPrefix }}_zip"
            class="input-field"
            name="{{ $fieldPrefix }}[zip_code]"
            value="{{ old($fieldPrefix.'.zip_code', $addressData['zip_code']) }}"
        >
    </div>

    {{-- Hidden Lat / Lng --}}
    <div class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="flex flex-col gap-1">
            <label class="custom-label" for="{{ $fieldPrefix }}_latitude">Latitude</label>
            <input type="text" id="{{ $fieldPrefix }}_latitude" name="{{ $fieldPrefix }}[latitude]"
                value="{{ old($fieldPrefix.'.latitude', $addressData['latitude']) }}" class="input-field">
        </div>

        <div class="flex flex-col gap-1">
            <label class="custom-label" for="{{ $fieldPrefix }}_longitude">Longitude</label>
            <input type="text" id="{{ $fieldPrefix }}_longitude" name="{{ $fieldPrefix }}[longitude]"
                value="{{ old($fieldPrefix.'.longitude', $addressData['longitude']) }}" class="input-field">
        </div>
    </div>
</div>

<script>
(function () {
    const componentId = '{{ $componentId }}';
    const prefix = '{{ $fieldPrefix }}';
    const countrySelect = document.getElementById(prefix + '_country');
    const stateSelect = document.getElementById(prefix + '_state');
    const citySelect = document.getElementById(prefix + '_city');
    
    const selectedCountryId = '{{ $addressData['country_id'] ?? '' }}';
    const selectedStateId = '{{ $addressData['state_id'] ?? '' }}';
    const selectedCityId = '{{ $addressData['city_id'] ?? '' }}';

    // Load states when country changes
    countrySelect.addEventListener('change', function() {
        const countryId = this.value;
        stateSelect.innerHTML = '<option value="">Select State</option>';
        citySelect.innerHTML = '<option value="">Select City</option>';
        
        if (countryId) {
            fetch(`/api/countries/states-by-country/${countryId}`)
                .then(response => response.json())
                .then(result => {
                    if (result.success && result.states) {
                        result.states.forEach(state => {
                            const option = document.createElement('option');
                            option.value = state.id;
                            option.textContent = state.name;
                            stateSelect.appendChild(option);
                        });
                        
                        if (selectedStateId) {
                            stateSelect.value = selectedStateId;
                            stateSelect.dispatchEvent(new Event('change'));
                        }
                    }
                })
                .catch(error => console.error('Error loading states:', error));
        }
    });

    // Load cities when state changes
    stateSelect.addEventListener('change', function() {
        const stateId = this.value;
        citySelect.innerHTML = '<option value="">Select City</option>';
        
        if (stateId) {
            fetch(`/api/countries/cities-by-state/${stateId}`)
                .then(response => response.json())
                .then(result => {
                    if (result.success && result.cities) {
                        result.cities.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.id;
                            option.textContent = city.name;
                            citySelect.appendChild(option);
                        });
                        
                        if (selectedCityId) {
                            citySelect.value = selectedCityId;
                        }
                    }
                })
                .catch(error => console.error('Error loading cities:', error));
        }
    });

    // Initialize on page load if editing
    if (selectedCountryId) {
        countrySelect.dispatchEvent(new Event('change'));
    }
})();
</script>
