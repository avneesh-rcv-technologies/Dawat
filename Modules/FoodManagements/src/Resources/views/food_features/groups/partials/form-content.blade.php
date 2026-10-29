<form id="featureGroupForm"
    class="space-y-6 form-ajax"
    method="POST"
    action="{{ isset($group)
        ? route('admin.foodmanagements.features.group_update', $group->feature_id)
        : route('admin.foodmanagements.features.group_store') }}">
    @csrf
    @isset($group) @method('PUT') @endisset

    <input type="hidden" name="feature_type" value="G">
    <input type="hidden" name="parent_id" value="0">

    @php
        $groupDescription = $group?->description;
        $selectedCategories = old('category_ids', isset($group) ? $group->categories->pluck('id')->toArray() : []);
    @endphp
        <div class="border-b border-gray-200 sticky top-0 z-10 px-4 py-2 bg-white">
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="px-4 py-2 text-sm border-b-2 border-blue-600 text-blue-700 font-medium"
                    data-group-tab-button="general"
                >General</button>
                <button
                    type="button"
                    class="px-4 py-2 text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700"
                    data-group-tab-button="categories"
                >Categories</button>
            </div>
        </div>

        <div class="p-4 md:p-6">
            <div data-group-tab-panel="general">
                    <div class="space-y-4">
                        <div>
                            <label class="custom-label">Name <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', $groupDescription->name ?? '') }}"
                                class="input-field"
                                required
                            >
                        </div>

                        <div>
                            <label class="custom-label">Description</label>
                            <textarea
                                id="message-editor"
                                name="description"
                                editor="true"
                                rows="5"
                                class="input-field"
                            >{{ old('description', $groupDescription->description ?? '') }}</textarea>
                        </div>

                        <div>
                            <label class="custom-label">Feature Code</label>
                            <input
                                type="text"
                                name="feature_code"
                                value="{{ old('feature_code', $group?->feature_code ?? '') }}"
                                class="input-field"
                            >
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="custom-label">Position</label>
                                <input
                                    type="number"
                                    name="position"
                                    value="{{ old('position', $group?->position ?? 0) }}"
                                    class="input-field"
                                >
                            </div>
                            <div>
                                <label class="custom-label">Status</label>
                                <select name="status" class="input-field">
                                    @foreach ($statusOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('status', $group?->status ?? 'A') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 mt-6">
                        <div class="space-y-3 rounded border border-gray-200 bg-gray-50 p-3">
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="display_on_product" value="N">
                                <input
                                    id="groupDisplayOnProduct"
                                    type="checkbox"
                                    name="display_on_product"
                                    value="Y"
                                    {{ old('display_on_product', $group?->display_on_product ?? 'Y') === 'Y' ? 'checked' : '' }}
                                >
                                <label for="groupDisplayOnProduct" class="text-sm text-gray-700">Display on product page</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="display_on_catalog" value="N">
                                <input
                                    id="groupDisplayOnCatalog"
                                    type="checkbox"
                                    name="display_on_catalog"
                                    value="Y"
                                    {{ old('display_on_catalog', $group?->display_on_catalog ?? 'Y') === 'Y' ? 'checked' : '' }}
                                >
                                <label for="groupDisplayOnCatalog" class="text-sm text-gray-700">Display on catalog pages</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="display_on_header" value="N">
                                <input
                                    id="groupDisplayOnHeader"
                                    type="checkbox"
                                    name="display_on_header"
                                    value="Y"
                                    {{ old('display_on_header', $group?->display_on_header ?? 'N') === 'Y' ? 'checked' : '' }}
                                >
                                <label for="groupDisplayOnHeader" class="text-sm text-gray-700">Display in product header</label>
                            </div>
                        </div>
                    </div>
            </div>

            <div data-group-tab-panel="categories" class="hidden">
                <x-advanced-selector
                    name="category_ids"
                    table="food_categories"
                    search-column="name"
                    value-column="id"
                    display-column="name"
                    :multiple="true"
                    :required="false"
                    :selected="$selectedCategories"
                    placeholder="Select categories..."
                    label="Categories"
                    :additional-columns="[]"
                    :filters="[]"
                    order-by="name"
                    order-direction="asc" />
                <p class="mt-2 text-xs text-gray-500">
                    Leave empty to apply this group to all categories.
                </p>
            </div>
        </div>

    <div class="flex justify-end mt-6">
        <x-button
            type="submit"
            name="button"
            class="btn-primary"
            label="{{ isset($group) ? 'Update Group' : 'Save Group' }}" />
    </div>
</form>

<script>
(function () {
    const form = document.getElementById('featureGroupForm');
    if (!form) {
        return;
    }

    const tabButtons = {
        general: form.querySelector('[data-group-tab-button="general"]'),
        categories: form.querySelector('[data-group-tab-button="categories"]'),
    };
    const tabPanels = {
        general: form.querySelector('[data-group-tab-panel="general"]'),
        categories: form.querySelector('[data-group-tab-panel="categories"]'),
    };

    function setActiveTab(tab) {
        if (!tabButtons[tab]) {
            tab = 'general';
        }

        Object.keys(tabButtons).forEach(function (key) {
            const isActive = key === tab;
            tabButtons[key].classList.toggle('border-blue-600', isActive);
            tabButtons[key].classList.toggle('text-blue-700', isActive);
            tabButtons[key].classList.toggle('font-medium', isActive);
            tabButtons[key].classList.toggle('border-transparent', !isActive);
            tabButtons[key].classList.toggle('text-gray-500', !isActive);
            tabPanels[key].classList.toggle('hidden', !isActive);
        });
    }

    Object.keys(tabButtons).forEach(function (tabKey) {
        tabButtons[tabKey].addEventListener('click', function () {
            setActiveTab(tabKey);
        });
    });

    setActiveTab('general');
})();
</script>
