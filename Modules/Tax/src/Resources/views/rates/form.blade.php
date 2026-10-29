<form id="taxForm" class="form-ajax" method="POST"
    action="@isset($tax) {{ route('admin.tax.update', $tax->id) }} @else {{ route('admin.tax.store') }} @endisset">
    @csrf
    @isset($tax) @method('PUT') @endisset

    <!-- Name Field -->
    <div class="mb-4">
        <label class="custom-label">Tax Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="name"
            value="{{ $tax->name ?? old('name') }}"
            class="input-field @error('name') border-red-500 @enderror">
        <div class="error-message text-red-500 text-sm mt-1" id="name-error">
            @error('name') {{ $message }} @enderror
        </div>
    </div>

    <!-- Rate Value Field -->
    <div class="mb-4">
        <label class="custom-label">Rate Value <span class="text-red-500">*</span></label>
        <input type="number" step="0.0001" name="rate_value" id="rate_value"
            value="{{ $tax->rate_value ?? old('rate_value') }}"
            class="input-field @error('rate_value') border-red-500 @enderror">
        <div class="error-message text-red-500 text-sm mt-1" id="rate_value-error">
            @error('rate_value') {{ $message }} @enderror
        </div>
    </div>

    <!-- Type Field -->
    <div class="mb-4">
        <label class="custom-label">Type <span class="text-red-500">*</span></label>
        <select name="type" id="type" 
            class="input-field @error('type') border-red-500 @enderror">
            <option value="P" @selected(($tax->type ?? old('type')) == 'P')>Percentage</option>
            <option value="F" @selected(($tax->type ?? old('type')) == 'F')>Fixed Amount</option>
        </select>
        <div class="error-message text-red-500 text-sm mt-1" id="type-error">
            @error('type') {{ $message }} @enderror
        </div>
    </div>

    <!-- Country Field -->
    <div class="mb-4">
        <label class="custom-label">Country <span class="text-red-500">*</span></label>
        <select name="country_id" id="country_id" 
            class="input-field @error('country_id') border-red-500 @enderror">
            <option value="">Select Country</option>
            @foreach($countries as $country)
                <option value="{{ $country->id }}" 
                    @selected(($tax->country_id ?? old('country_id')) == $country->id)>
                    {{ $country->name }}
                </option>
            @endforeach
        </select>
        <div class="error-message text-red-500 text-sm mt-1" id="country_id-error">
            @error('country_id') {{ $message }} @enderror
        </div>
    </div>
    <!-- State Field -->
    <!-- <div class="mb-4">
        <label class="custom-label">State/Region</label>
        <input type="text" name="state" id="state"
            value="{{ $tax->state ?? old('state') }}"
            class="input-field @error('state') border-red-500 @enderror">
        <div class="error-message text-red-500 text-sm mt-1" id="state-error">
            @error('state') {{ $message }} @enderror
        </div>
    </div> -->

    <!-- Postcode Field -->
    <!-- <div class="mb-4">
        <label class="custom-label">Postal/Zip Code</label>
        <input type="text" name="postcode" id="postcode"
            value="{{ $tax->postcode ?? old('postcode') }}"
            class="input-field @error('postcode') border-red-500 @enderror">
        <div class="error-message text-red-500 text-sm mt-1" id="postcode-error">
            @error('postcode') {{ $message }} @enderror
        </div>
    </div> -->

    <!-- City Field -->
    <!-- <div class="mb-4">
        <label class="custom-label">City</label>
        <input type="text" name="city" id="city"
            value="{{ $tax->city ?? old('city') }}"
            class="input-field @error('city') border-red-500 @enderror">
        <div class="error-message text-red-500 text-sm mt-1" id="city-error">
            @error('city') {{ $message }} @enderror
        </div>
    </div> -->

    <!-- Status Field -->
    <div class="mb-4">
        <label class="custom-label">Status</label>
        <select name="is_active" id="is_active" 
            class="input-field @error('is_active') border-red-500 @enderror">
            <option value="1" @selected(($tax->is_active ?? old('is_active', true)) == true)>Active</option>
            <option value="0" @selected(($tax->is_active ?? old('is_active', true)) == false)>Inactive</option>
        </select>
        <div class="error-message text-red-500 text-sm mt-1" id="is_active-error">
            @error('is_active') {{ $message }} @enderror
        </div>
    </div>

    <!-- Priority Field -->
    <div class="mb-4">
        <label class="custom-label">Priority</label>
        <input type="number" name="priority" id="priority" min="0"
            value="{{ $tax->priority ?? old('priority', 0) }}"
            class="input-field @error('priority') border-red-500 @enderror">
        <div class="error-message text-red-500 text-sm mt-1" id="priority-error">
            @error('priority') {{ $message }} @enderror
        </div>
    </div>

    <div class="mt-8 flex justify-end space-x-3">
        <x-button type="submit" class="primary" label="Save" icon='' name='button' />
    </div>
</form>
