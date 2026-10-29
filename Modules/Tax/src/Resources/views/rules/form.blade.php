<form id="taxRuleForm" class="form-ajax" method="POST"
    action="@isset($taxRule) {{ route('admin.tax-rules.update', $taxRule->id) }} @else {{ route('admin.tax-rules.store') }} @endisset">
    @csrf
    @isset($taxRule) @method('PUT') @endisset

    <!-- Category Field -->
    <div class="mb-4">
        <label class="custom-label">Tax Category <span class="text-red-500">*</span></label>
        <select name="tax_category_id" id="tax_category_id" 
            class="input-field @error('category_id') border-red-500 @enderror">
            <option value="">Select Category</option>
            @foreach($taxCategories as $category)
                <option value="{{ $category->id }}" 
                    @selected(($taxRule->tax_category_id ?? old('tax_category_id')) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <div class="error-message text-red-500 text-sm mt-1" id="category_id-error">
            @error('category_id') {{ $message }} @enderror
        </div>
    </div>

    <!-- Tax Rate Field -->
    <div class="mb-4">
        <label class="custom-label">Tax Rate <span class="text-red-500">*</span></label>
        <select name="tax_rate_id" id="tax_rate_id" 
            class="input-field @error('tax_rate_id') border-red-500 @enderror">
            <option value="">Select Rate</option>
            @foreach($taxRates as $taxRate)
                <option value="{{ $taxRate->id }}" 
                    @selected(($taxRule->tax_rate_id ?? old('tax_rate_id')) == $taxRate->id)>
                    {{ $taxRate->name }}
                </option>
            @endforeach
        </select>
        <!-- <div class="error-message text-red-500 text-sm mt-1" id="category_id-error">
            @error('category_id') {{ $message }} @enderror
        </div> -->
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
