@php
    $servingConfig = app(\Modules\FoodManagements\Services\ServingOptionConfiguration::class)->all();
    $savedOptions = $item ? $item->servingOptions->toArray() : [];
    if (!$savedOptions) {
        $savedOptions = [[
            'serve_type' => $servingConfig['default_serve_type'],
            'serving_name' => 'Standard', 'quantity' => 1,
            'unit' => $servingConfig['serve_types'][$servingConfig['default_serve_type']]['defaults']['unit'], 'serves' => 1,
            'price' => $item ? ($item->list_price ?: $item->sale_price) : '',
            'discount_price' => $item && $item->list_price > $item->sale_price ? $item->sale_price : '',
            'is_available' => true,
        ]];
    }
    $servingOptions = old('serving_options', $savedOptions);
@endphp

<form id="food-item-general" method="POST" enctype="multipart/form-data" class="space-y-6"
    action="{{ $foodItemId ? route('admin.foodmanagements.food-items.update.general', $foodItemId) : route('admin.foodmanagements.food-items.store.general') }}">
    @csrf
    @if ($foodItemId) @method('PUT') @endif
    <input type="hidden" name="restaurant_id" value="{{ $certificateRestaurantId ?? '' }}">

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
            <p class="font-semibold">Please correct the following:</p>
            <ul class="mt-2 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-5 text-lg font-semibold text-gray-900">Item Details</h3>
        <div class="space-y-5">
            <div>
                <label for="food-item-name" class="form-label">Item name <span class="text-red-500">*</span></label>
                <input id="food-item-name" name="name" class="input-field" maxlength="255" value="{{ old('name', $item->name ?? '') }}" placeholder="e.g. Butter chicken" aria-describedby="food-name-help" required>
                <p id="food-name-help" class="mt-1 text-xs text-gray-500">Use the dish name customers will see on the menu, e.g. Butter chicken.</p>
                @error('name')<p class="error-message">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="food-item-description" class="form-label">Description <span class="text-red-500">*</span></label>
                <textarea id="food-item-description" name="description" class="input-field" editor="false" rows="4" maxlength="500" aria-describedby="food-description-help" required>{{ old('description', $item->description ?? '') }}</textarea>
                <p id="food-description-help" class="mt-1 text-xs text-gray-500">Describe the main ingredients, preparation, and what is included. Keep it within 500 characters.</p>
                <p class="mt-1 text-right text-xs text-gray-500"><span id="food-description-count">0</span> / 500 characters</p>
                @error('description')<p class="error-message">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="food-item-category" class="form-label">Category <span class="text-red-500">*</span></label>
                    <select id="food-item-category" name="category_id" class="input-field" aria-describedby="food-category-help" required>
                        <option value="">Select category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $item?->categories->first()?->id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <p id="food-category-help" class="mt-1 text-xs text-gray-500">Choose the menu section where this item belongs, e.g. Starters or Main dishes.</p>
                    @error('category_id')<p class="error-message">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="food-item-tax" class="form-label">Tax (%) <span class="text-red-500">*</span></label>
                    <input id="food-item-tax" name="tax_percentage" type="number" class="input-field" min="0" max="100" step="0.01" value="{{ old('tax_percentage', $item->tax_percentage ?? 0) }}" placeholder="e.g. 8.25" aria-describedby="food-tax-help" required>
                    <p id="food-tax-help" class="mt-1 text-xs text-gray-500">Enter 8.25 for 8.25% tax, or 0 for no tax. Use up to 2 decimal places. This rate applies to every serving option, using its discount price when provided.</p>
                    @error('tax_percentage')<p class="error-message">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="food-item-image" class="form-label">Item image</label>
                <div class="rounded-lg border border-dashed border-gray-300 p-5">
                    <input id="food-item-image" type="file" name="food_image[]" accept=".jpg,.jpeg,.png,.webp" multiple data-existing-count="{{ $item ? \Modules\Filemanager\Models\FileLink::where('object_type', 'food_image')->where('object_id', $item->id)->count() : 0 }}" class="block w-full text-sm" aria-describedby="food-image-help">
                    <p id="food-image-help" class="mt-2 text-xs text-gray-500">Select up to 10 JPG, JPEG, PNG, or WebP images, 2 MB each. New uploads are added to the current gallery. Existing and new images together cannot exceed 10.</p>
                </div>
                @if ($item && ($image = $item->image))
                    <img src="{{ $image['url'] }}" alt="Current item image" class="mt-3 h-24 w-24 rounded-lg object-cover">
                @endif
                @error('food_image')<p class="error-message">{{ $message }}</p>@enderror
                @error('food_image.*')<p class="error-message">{{ $message }}</p>@enderror
            </div>
            <x-advanced-selector name="dietary_label_ids" table="dietary_labels" label="Dietary Labels"
                placeholder="Search dietary labels..." :multiple="true" :required="true"
                :selected="old('dietary_label_ids', $item->dietary_label_ids ?? [])"
                :eligibility="$labelEligibility" :eligibility-url="route('profile.edit', ['tab' => 'dietary_certificates', 'restaurant_id' => $certificateRestaurantId])"
                icon-column="icon" :additional-columns="['description']" order-by="display_order" />
            <p class="mt-1 text-xs text-gray-500">Select all dietary labels that accurately describe this item. Each selected label shows its icon.</p>
            @error('dietary_label_ids')<p class="error-message">{{ $message }}</p>@enderror
            @error('dietary_label_ids.*')<p class="error-message">{{ $message }}</p>@enderror
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                @foreach (['is_available' => ['Availability', 'Available', 'Unavailable'], 'is_active' => ['Status', 'Active', 'Inactive']] as $field => [$caption, $yes, $no])
                    <div>
                        <label class="form-label" for="food-item-{{ $field }}">{{ $caption }}</label>
                        <select id="food-item-{{ $field }}" name="{{ $field }}" class="input-field" aria-describedby="food-{{ $field }}-help">
                            <option value="1" @selected(old($field, $item?->$field ?? true) == 1)>{{ $yes }}</option>
                            <option value="0" @selected(old($field, $item?->$field ?? true) == 0)>{{ $no }}</option>
                        </select>
                        <p id="food-{{ $field }}-help" class="mt-1 text-xs text-gray-500">{{ $field === 'is_available' ? 'Set to Unavailable when this item is temporarily not being offered.' : 'Set to Active when this item is ready to appear on the menu.' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900">Serving Options</h3>
        <p class="mb-5 text-sm text-gray-500">Choose a serve type for each option. The fields below change with the type.</p>
        <div id="serving-options" class="space-y-5" data-serving-config="{{ json_encode($servingConfig) }}">
            @foreach ($servingOptions as $index => $option)
                @include('foodmanagements::food_items.partials.serving-option', compact('index', 'option'))
            @endforeach
        </div>
        @error('serving_options')<p class="error-message">{{ $message }}</p>@enderror
        <button type="button" id="add-serving-option" class="mt-5 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium">+ Add serving option</button>
    </section>
    <div class="flex justify-end">
        <button type="submit" class="btn btn-primary">{{ $foodItemId ? 'Update Food Item' : 'Save Food Item' }}</button>
    </div>
</form>

<template id="serving-option-template">
    @include('foodmanagements::food_items.partials.serving-option', ['index' => '__INDEX__', 'option' => ['is_available' => true]])
</template>

@include('foodmanagements::food_items.partials.serving-script')
