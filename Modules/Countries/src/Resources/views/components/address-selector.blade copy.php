@props([
    'modelClass' => '',
    'modelId' => null,
    'addressId' => null,
    'address' => null,
    'showAddressType' => true,
    'required' => true,
    'fieldPrefix' => 'address',
    'ajax' => false,
])

@php
    use Modules\Countries\Helpers\AddressHelper;

    $addressData = AddressHelper::formatAddressForForm($address);
    $componentId = 'address_' . uniqid();
   
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

       <x-advanced-selector
        name="{{ $fieldPrefix }}[country_id]"
        table="countries"
        search-column="name"
        value-column="id"
        display-column="name"
        :multiple="false"
        :required="$required"
        :selected="!empty($addressData['country_id']) ? [$addressData['country_id']] : []"
        placeholder="Select country..."
        label="Country"
        :ajax="$ajax"
    />

    {{-- ================= STATE MOUNT ================= --}}
    <div id="{{ $componentId }}_state"></div>

    {{-- ================= CITY MOUNT ================= --}}
    <div id="{{ $componentId }}_city"></div>

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

{{-- ================= STATE TEMPLATE ================= --}}
<template id="{{ $componentId }}_state_tpl">
    <x-advanced-selector
        name="{{ $fieldPrefix }}[state_id]"
        table="country_states"
        search-column="name"
        value-column="id"
        display-column="name"
        :multiple="false"
        placeholder="Select state..."
        label="State / Province"
        :filters="[['column'=>'country_id','operator'=>'=','value'=>'__COUNTRY__']]"
        :ajax="$ajax"
    />
</template>

{{-- ================= CITY TEMPLATE ================= --}}
<template id="{{ $componentId }}_city_tpl">
    <x-advanced-selector
        name="{{ $fieldPrefix }}[city_id]"
        table="cities"
        search-column="name"
        value-column="id"
        display-column="name"
        :multiple="false"
        placeholder="Select city..."
        label="City"
        :filters="[['column'=>'state_id','operator'=>'=','value'=>'__STATE__']]"
        :ajax="$ajax"
    />
</template>

<script>
(function () {
    const root = document.getElementById('{{ $componentId }}');
    if (!root) return;

    const prefix = '{{ $fieldPrefix }}';
    const stateMount = document.getElementById('{{ $componentId }}_state');
    const cityMount  = document.getElementById('{{ $componentId }}_city');

    function getHidden(name) {
        return root.querySelector(`input[type="hidden"][name="${name}"]`);
    }

    function renderState(countryId) {
        console.log('renderState()', countryId);
        cityMount.innerHTML = '';
        const tpl = document.getElementById('{{ $componentId }}_state_tpl').innerHTML;
        stateMount.innerHTML = tpl.replace('__COUNTRY__', countryId);
        console.log(stateMount , countryId );
        
    }

    function renderCity(stateId) {
        console.log('renderCity()', stateId);
        const tpl = document.getElementById('{{ $componentId }}_city_tpl').innerHTML;
        cityMount.innerHTML = tpl.replace('__STATE__', stateId);
    }

    // ---------------- PATCH VALUE SETTER ----------------
    function watchValue(input, callback) {
        if (!input) return;

        let current = input.value;
        console.log(current);
        

        Object.defineProperty(input, 'value', {
            get() {
                return current;
            },
            set(newValue) {
                if (newValue !== current) {
                    current = newValue;
                    callback(newValue);
                }
            }
        });
    }

    function initCountryWatcher() {
        const input = getHidden(`${prefix}[country_id]`);
        if (!input) return;

        watchValue(input, (val) => {
            if (val) renderState(val);
        });

        // EDIT MODE
        if (input.value) renderState(input.value);
    }

    function initStateWatcher() {
        const input = getHidden(`${prefix}[state_id]`);
        if (!input) return;

        watchValue(input, (val) => {
            if (val) renderCity(val);
        });

        // EDIT MODE
        if (input.value) renderCity(input.value);
    }

    // Init once
    initCountryWatcher();
    initStateWatcher();

    // Re-init after dynamic renders
    new MutationObserver(() => {
        initCountryWatcher();
        initStateWatcher();
    }).observe(root, { childList: true, subtree: true });

})();
</script>
