<form id="foodOptionGroupForm"
      method="POST" 
      class="form-ajax"
      action="@isset($optionGroup)
          {{ route('admin.foodmanagements.food-option-groups.update', $optionGroup->id) }}
      @else
          {{ route('admin.foodmanagements.food-option-groups.store') }}
      @endisset">

    @csrf
    @isset($optionGroup)
        @method('PUT')
    @endisset

    <input type="hidden" name="id" value="{{ $optionGroup->id ?? 0 }}">

    {{-- Group Name --}}
    <div class="mb-6">
        <label class="custom-label" for="name">
            Option Group Name <span class="text-red-500">*</span>
        </label>

        <input type="text"
               name="name"
               id="name"
               required
               value="{{ old('name', $optionGroup->name ?? '') }}"
               class="input-field @error('name') border-red-500 @enderror"
               placeholder="e.g. Size, Add-ons, Spice Level">

        @error('name')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Is Required --}}
    <div class="mb-6">
        <label class="custom-label" for="is_required">
            Is Selection Required?
        </label>

        <select name="is_required"
                id="is_required"
                class="input-field @error('is_required') border-red-500 @enderror">
            <option value="0" @selected(old('is_required', $optionGroup->is_required ?? 0) == 0)>
                No
            </option>
            <option value="1" @selected(old('is_required', $optionGroup->is_required ?? 0) == 1)>
                Yes
            </option>
        </select>

        @error('is_required')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Min Selection --}}
    <div class="mb-6">
        <label class="custom-label" for="min">
            Minimum Selection
        </label>

        <input type="number"
               name="min"
               id="min"
               min="0"
               value="{{ old('min', $optionGroup->min ?? 0) }}"
               class="input-field @error('min') border-red-500 @enderror">

        @error('min')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Max Selection --}}
    <div class="mb-6">
        <label class="custom-label" for="max">
            Maximum Selection
        </label>

        <input type="number"
               name="max"
               id="max"
               min="1"
               value="{{ old('max', $optionGroup->max ?? 1) }}"
               class="input-field @error('max') border-red-500 @enderror">

        @error('max')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Helper text --}}
    <div class="mb-6 text-sm text-gray-500">
        <p>
            Example:
            <br>• Size → Min: 1, Max: 1 (radio)
            <br>• Add-ons → Min: 0, Max: 3 (checkbox)
        </p>
    </div>

    {{-- Actions --}}
    <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('admin.foodmanagements.food-option-groups.index') }}"
           class="btn-cancel">
            Cancel
        </a>

        <button type="submit" name="button" class="btn-primary-submit">
            @isset($optionGroup)
                Update Option Group
            @else
                Create Option Group
            @endisset
        </button>
    </div>
</form>

<style>
.custom-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.input-field {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: all 0.2s ease-in-out;
    background-color: #ffffff;
}

.input-field:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.input-field.border-red-500 {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.btn-cancel {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    color: #374151;
    background-color: #f9fafb;
    border: 1px solid #d1d5db;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    text-decoration: none;
}

.btn-cancel:hover {
    background-color: #f3f4f6;
    border-color: #9ca3af;
}

.btn-primary-submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    background-color: #621F09;
    color: #ffffff;
    border: none;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
}

.btn-primary:hover {
    background-color: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}
</style>
