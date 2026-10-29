<form id="taxCategoryForm" class="form-ajax" method="POST"
    action="@isset($taxCategory) {{ route('admin.tax-category.update', $taxCategory->id) }} @else {{ route('admin.tax-category.store') }} @endisset">
    @csrf
    @isset($taxCategory) @method('PUT') @endisset

    <!-- Name Field -->
    <div class="mb-2">
        <label class="custom-label">Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="name"
            value="{{ $taxCategory->name ?? old('name') }}"
            class="input-field @error('name') border-red-500 @enderror">
        <div class="error-message text-red-500 text-sm mt-1" id="name-error">
            @error('name') {{ $message }} @enderror
        </div>
    </div>

    <!-- Description Field -->
    <div class="mb-2">
        <label class="custom-label">Description</label>
        <textarea name="description" id="description"
            class="input-field @error('description') border-red-500 @enderror">{{ $taxCategory->description ?? old('description') }}</textarea>
        <div class="error-message text-red-500 text-sm mt-1" id="description-error">
            @error('description') {{ $message }} @enderror
        </div>
    </div>

    <!-- Priority Field -->
    <div class="mb-2">
        <label class="custom-label">Priority</label>
        <input type="number" name="priority" id="priority" min="0"
            value="{{ $taxCategory->priority ?? old('priority', 0) }}"
            class="input-field @error('priority') border-red-500 @enderror">
        <div class="error-message text-red-500 text-sm mt-1" id="priority-error">
            @error('priority') {{ $message }} @enderror
        </div>
    </div>

    <!-- Status Field -->
    <div class="mb-2">
        <label class="custom-label">Status</label>
        <select name="status" id="status" 
            class="input-field @error('status') border-red-500 @enderror">
            <option value="1" @selected(($taxCategory->status ?? old('status', true)) == true)>Active</option>
            <option value="0" @selected(($taxCategory->status ?? old('status', true)) == false)>Inactive</option>
        </select>
        <div class="error-message text-red-500 text-sm mt-1" id="status-error">
            @error('status') {{ $message }} @enderror
        </div>
    </div>

    <div class="mt-8 flex justify-end space-x-3">
        <x-button type="submit" class="primary" label="Save" icon='' name='button' />
    </div>
</form>