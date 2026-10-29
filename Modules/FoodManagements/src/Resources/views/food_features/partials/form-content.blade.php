<form id="featureForm"
    class="space-y-6 form-ajax"
    method="POST"
    action="{{ isset($feature)
        ? route('admin.foodmanagements.features.update', $feature->feature_id)
        : route('admin.foodmanagements.features.store') }}">
    @csrf
    @isset($feature) @method('PUT') @endisset

    @php
        $featureDescription = $feature?->description;
        $selectedCategories = old('category_ids', isset($feature) ? $feature->categories->pluck('id')->toArray() : []);
        $selectedGroup = (int) old('parent_id', $feature?->parent_id ?? 0);
        $currentType = old('feature_type', $feature?->feature_type ?? 'S');
        $currentPurpose = old('purpose', $feature?->purpose ?? ($defaultPurpose ?? 'find_products'));
        $currentFeatureStyle = old('feature_style', $feature?->feature_style ?? '');
        $currentFilterStyle = old('filter_style', $feature?->filter_style ?? '');

        $purposeOptions = collect($featurePurposes ?? [])->map(function ($purpose, $key) {
            return [
                'value' => $key,
                'label' => $purpose['label'] ?? ucwords(str_replace('_', ' ', $key)),
            ];
        })->values()->all();
        $currentPurposeDescription = $featurePurposes[$currentPurpose]['description'] ?? '';

        $featureStyleOptions = [
            'text' => 'Text',
            'checkbox' => 'Checkbox',
            'multiple_checkbox' => 'Multiple Checkboxes',
            'number' => 'Number',
            'brand' => 'Brand',
            'color' => 'Color',
            'dropdown' => 'Dropdown',
            'dropdown_images' => 'Dropdown Images',
            'dropdown_labels' => 'Dropdown Labels',
        ];
        $fallbackFeatureStyleOptions = ['' => '--'] + $featureStyleOptions;

        $filterStyleOptions = [
            '' => '--',
            'checkbox' => 'Checkbox',
            'slider' => 'Slider',
            'color' => 'Color',
            'date' => 'Date',
        ];

        $variantRows = [];
        $oldVariants = old('variants');

        if (is_array($oldVariants)) {
            foreach ($oldVariants as $variant) {
                if (!is_array($variant)) {
                    continue;
                }

                $variantRows[] = [
                    'variant_id' => $variant['variant_id'] ?? null,
                    'description' => $variant['description'] ?? '',
                    'position' => $variant['position'] ?? 0,
                    'status' => $variant['status'] ?? 'A',
                ];
            }
        } elseif (isset($feature) && $feature->variants->isNotEmpty()) {
            foreach ($feature->variants as $variant) {
                $variantRows[] = [
                    'variant_id' => $variant->variant_id,
                    'description' => $variant->description->description ?? '',
                    'position' => $variant->position ?? 0,
                    'status' => $variant->status ?? 'A',
                ];
            }
        }
    @endphp

        <div class="border-b border-gray-200 sticky top-0 z-10 px-4 py-2 bg-white">
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="px-4 py-2 text-sm border-b-2 border-blue-600 text-blue-700 font-medium"
                    data-feature-tab-button="general"
                >General</button>
                <button
                    type="button"
                    class="px-4 py-2 text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700"
                    data-feature-tab-button="variants"
                >Variants</button>
                <button
                    type="button"
                    class="px-4 py-2 text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700"
                    data-feature-tab-button="categories"
                >Categories</button>
            </div>
        </div>

        <div class="p-4 md:p-6">
            <div data-feature-tab-panel="general">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="custom-label">Name <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', $featureDescription->name ?? '') }}"
                                class="input-field"
                                required
                            >
                        </div>

                        <div>
                            <label class="custom-label">Description</label>
                            <textarea
                                id="message-editor"
                                editor="true"
                                name="description"
                                rows="4"
                                class="input-field"
                            >{{ old('description', $featureDescription->description ?? '') }}</textarea>
                        </div>

                        <div>
                            <label class="custom-label">Purpose</label>
                            <select name="purpose" id="featurePurposeSelect" class="input-field">
                                <option value="">--</option>
                                @foreach ($purposeOptions as $purpose)
                                    <option value="{{ $purpose['value'] }}" {{ $currentPurpose === $purpose['value'] ? 'selected' : '' }}>
                                        {{ $purpose['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <p id="featurePurposeDescription" class="mt-2 text-xs text-gray-500">
                                {{ $currentPurposeDescription }}
                            </p>
                        </div>

                        <input type="hidden" name="feature_type" id="featureTypeInput" value="{{ $currentType }}">

                        <div class="rounded border border-gray-200 bg-gray-50 p-3 space-y-3">
                            <label class="text-sm font-medium text-gray-800">Style Settings</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="custom-label">Feature Style</label>
                                    <select name="feature_style" id="featureStyleSelect" class="input-field">
                                        <option value="">--</option>
                                        @foreach ($featureStyleOptions as $key => $label)
                                            <option value="{{ $key }}" {{ $currentFeatureStyle === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="custom-label">Filter Style</label>
                                    <select name="filter_style" id="filterStyleSelect" class="input-field">
                                        @foreach ($filterStyleOptions as $key => $label)
                                            <option value="{{ $key }}" {{ $currentFilterStyle === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <p id="featureResolvedStyleSummary" class="text-xs text-gray-600">
                                Feature type is detected automatically from style settings.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="custom-label">Group</label>
                            <select name="parent_id" id="featureGroupSelect" class="input-field">
                                <option value="0">- None -</option>
                                @foreach ($groups as $group)
                                    <option
                                        value="{{ $group->feature_id }}"
                                        data-display-on-product="{{ $group->display_on_product }}"
                                        data-display-on-catalog="{{ $group->display_on_catalog }}"
                                        data-display-on-header="{{ $group->display_on_header }}"
                                        {{ $selectedGroup === (int) $group->feature_id ? 'selected' : '' }}
                                    >
                                        {{ $group->description->name ?? $group->description->description ?? ('Group #' . $group->feature_id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="custom-label">Feature Code</label>
                            <input
                                type="text"
                                name="feature_code"
                                value="{{ old('feature_code', $feature?->feature_code ?? '') }}"
                                class="input-field"
                            >
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="custom-label">Position</label>
                                <input
                                    type="number"
                                    name="position"
                                    value="{{ old('position', $feature?->position ?? 0) }}"
                                    class="input-field"
                                >
                            </div>
                            <div>
                                <label class="custom-label">Status</label>
                                <select name="status" class="input-field">
                                    @foreach ($statusOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('status', $feature?->status ?? 'A') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="space-y-3 rounded border border-gray-200 bg-gray-50 p-3">
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="display_on_product" value="N">
                                <input
                                    id="featureDisplayOnProduct"
                                    type="checkbox"
                                    name="display_on_product"
                                    value="Y"
                                    {{ old('display_on_product', $feature?->display_on_product ?? 'Y') === 'Y' ? 'checked' : '' }}
                                >
                                <label for="featureDisplayOnProduct" class="text-sm text-gray-700">Display on product page</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="display_on_catalog" value="N">
                                <input
                                    id="featureDisplayOnCatalog"
                                    type="checkbox"
                                    name="display_on_catalog"
                                    value="Y"
                                    {{ old('display_on_catalog', $feature?->display_on_catalog ?? 'Y') === 'Y' ? 'checked' : '' }}
                                >
                                <label for="featureDisplayOnCatalog" class="text-sm text-gray-700">Display on catalog pages</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="display_on_header" value="N">
                                <input
                                    id="featureDisplayOnHeader"
                                    type="checkbox"
                                    name="display_on_header"
                                    value="Y"
                                    {{ old('display_on_header', $feature?->display_on_header ?? 'N') === 'Y' ? 'checked' : '' }}
                                >
                                <label for="featureDisplayOnHeader" class="text-sm text-gray-700">Display in product header</label>
                            </div>
                            <p id="featureGroupLockNotice" class="hidden text-xs text-amber-700">
                                Some display options are inherited from selected group.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div data-feature-tab-panel="variants" class="hidden">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800">Variants</h3>
                    <button
                        type="button"
                        id="addFeatureVariantRow"
                        class="rounded bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >Add Variant</button>
                </div>

                <div class="overflow-x-auto rounded border border-gray-200">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-700">
                            <tr>
                                <th class="w-32 px-3 py-2 text-left">Position</th>
                                <th class="px-3 py-2 text-left">Variant</th>
                                <th class="w-24 px-3 py-2 text-center">Status</th>
                                <th class="w-24 px-3 py-2 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody id="featureVariantRows">
                            @forelse ($variantRows as $index => $row)
                                <tr class="border-t" data-feature-variant-row>
                                    <td class="px-3 py-2">
                                        @if (!empty($row['variant_id']))
                                            <input type="hidden" name="variants[{{ $index }}][variant_id]" value="{{ $row['variant_id'] }}">
                                        @endif
                                        <input
                                            type="number"
                                            name="variants[{{ $index }}][position]"
                                            value="{{ $row['position'] }}"
                                            class="input-field"
                                        >
                                    </td>
                                    <td class="px-3 py-2">
                                        <input
                                            type="text"
                                            name="variants[{{ $index }}][description]"
                                            value="{{ $row['description'] }}"
                                            class="input-field"
                                            placeholder="Variant name"
                                        >
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <input type="hidden" name="variants[{{ $index }}][status]" value="D">
                                        <input
                                            type="checkbox"
                                            name="variants[{{ $index }}][status]"
                                            value="A"
                                            {{ strtoupper((string) ($row['status'] ?? 'A')) === 'A' ? 'checked' : '' }}
                                        >
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <button type="button" class="text-red-600 hover:text-red-700" data-remove-feature-variant>Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="featureVariantEmpty">
                                    <td colspan="4" class="px-3 py-6 text-center text-sm text-gray-500">No variants added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div data-feature-tab-panel="categories" class="hidden">
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
                    Leave empty to apply this feature to all categories.
                </p>
            </div>
        </div>

    <div class="flex justify-end mt-6">
        <x-button
            type="submit"
            name="button"
            class="btn-primary"
            label="{{ isset($feature) ? 'Update Feature' : 'Save Feature' }}" />
    </div>
</form>

<script>
(function () {
    const form = document.getElementById('featureForm');
    if (!form) {
        return;
    }

    const selectableTypes = ['S', 'N', 'E', 'M'];
    let variantIndex = {{ count($variantRows) }};
    let activeTab = 'general';

    const purposeSchema = @json($featurePurposes ?? []);
    const featureTypeOptions = @json($featureTypes ?? []);
    const fallbackFeatureStyles = @json($fallbackFeatureStyleOptions ?? []);
    const fallbackFilterStyles = @json($filterStyleOptions ?? []);

    const featureTypeInput = form.querySelector('#featureTypeInput');
    const purposeSelect = form.querySelector('#featurePurposeSelect');
    const styleSummary = form.querySelector('#featureResolvedStyleSummary');
    const featureStyleSelect = form.querySelector('#featureStyleSelect');
    const filterStyleSelect = form.querySelector('#filterStyleSelect');
    const purposeDescription = form.querySelector('#featurePurposeDescription');
    const groupSelect = form.querySelector('#featureGroupSelect');
    const variantRows = form.querySelector('#featureVariantRows');
    const variantEmptyRowId = 'featureVariantEmpty';
    const groupLockNotice = form.querySelector('#featureGroupLockNotice');

    const displayProduct = form.querySelector('#featureDisplayOnProduct');
    const displayCatalog = form.querySelector('#featureDisplayOnCatalog');
    const displayHeader = form.querySelector('#featureDisplayOnHeader');

    const tabButtons = {
        general: form.querySelector('[data-feature-tab-button="general"]'),
        variants: form.querySelector('[data-feature-tab-button="variants"]'),
        categories: form.querySelector('[data-feature-tab-button="categories"]'),
    };
    const tabPanels = {
        general: form.querySelector('[data-feature-tab-panel="general"]'),
        variants: form.querySelector('[data-feature-tab-panel="variants"]'),
        categories: form.querySelector('[data-feature-tab-panel="categories"]'),
    };

    function normalizeStyleValue(value) {
        if (value === null || typeof value === 'undefined') {
            return '';
        }
        return String(value);
    }

    function getPurposeData(purposeKey) {
        if (!purposeKey || !purposeSchema[purposeKey]) {
            return null;
        }
        return purposeSchema[purposeKey];
    }

    function getPurposeStyleItems(purposeKey) {
        const purposeData = getPurposeData(purposeKey);
        if (!purposeData || !purposeData.styles_map) {
            return [];
        }
        return Object.values(purposeData.styles_map).filter(Boolean);
    }

    function renderSelectOptions(selectElement, options, selectedValue) {
        const entries = Object.entries(options || {});
        selectElement.innerHTML = '';

        if (!entries.length) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = '--';
            selectElement.appendChild(option);
            selectElement.value = '';
            selectElement.disabled = true;
            return '';
        }

        entries.forEach(function ([value, label]) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            selectElement.appendChild(option);
        });

        selectElement.disabled = false;

        const selected = String(selectedValue ?? '');
        const hasSelection = entries.some(function ([value]) {
            return String(value) === selected;
        });

        selectElement.value = hasSelection ? selected : String(entries[0][0]);
        return String(selectElement.value || '');
    }

    function getFeatureStyleChoices(purposeKey) {
        const styles = {};
        getPurposeStyleItems(purposeKey).forEach(function (item) {
            const styleValue = normalizeStyleValue(item.feature_style);
            if (!styleValue || styles[styleValue]) return;
            styles[styleValue] = item.feature_style_label || styleValue;
        });
        return styles;
    }

    function getFilterStyleChoices(purposeKey, featureStyle) {
        const styles = {};
        getPurposeStyleItems(purposeKey).forEach(function (item) {
            const styleValue = normalizeStyleValue(item.feature_style);
            const filterValue = normalizeStyleValue(item.filter_style);
            if (styleValue !== String(featureStyle || '') || !filterValue || styles[filterValue]) return;
            styles[filterValue] = item.filter_style_label || filterValue;
        });
        return styles;
    }

    function updatePurposeDescription() {
        const purposeData = getPurposeData(purposeSelect.value);
        purposeDescription.textContent = purposeData ? (purposeData.description || '') : '';
    }

    function ensureStyleSelectionForPurpose(purposeKey) {
        const purposeItems = getPurposeStyleItems(purposeKey);
        if (!purposeItems.length) {
            renderSelectOptions(featureStyleSelect, fallbackFeatureStyles, featureStyleSelect.value);
            renderSelectOptions(filterStyleSelect, fallbackFilterStyles, filterStyleSelect.value);
            return;
        }

        const selectedFeatureStyle = renderSelectOptions(
            featureStyleSelect,
            getFeatureStyleChoices(purposeKey),
            featureStyleSelect.value
        );

        let selectedFilterStyle = renderSelectOptions(
            filterStyleSelect,
            getFilterStyleChoices(purposeKey, selectedFeatureStyle),
            filterStyleSelect.value
        );

        const exactItem = purposeItems.find(function (item) {
            return normalizeStyleValue(item.feature_style) === selectedFeatureStyle
                && normalizeStyleValue(item.filter_style) === selectedFilterStyle;
        });

        if (exactItem) return;

        const firstForStyle = purposeItems.find(function (item) {
            return normalizeStyleValue(item.feature_style) === selectedFeatureStyle;
        });

        if (firstForStyle) {
            selectedFilterStyle = normalizeStyleValue(firstForStyle.filter_style);
            renderSelectOptions(
                filterStyleSelect,
                getFilterStyleChoices(purposeKey, selectedFeatureStyle),
                selectedFilterStyle
            );
            return;
        }

        const fallbackItem = purposeItems[0];
        const fallbackStyle = normalizeStyleValue(fallbackItem.feature_style);
        renderSelectOptions(featureStyleSelect, getFeatureStyleChoices(purposeKey), fallbackStyle);
        renderSelectOptions(
            filterStyleSelect,
            getFilterStyleChoices(purposeKey, fallbackStyle),
            normalizeStyleValue(fallbackItem.filter_style)
        );
    }

    function findPurposeStyleMatch(purposeKey, featureStyle, filterStyle) {
        const normalizedStyle = normalizeStyleValue(featureStyle);
        const normalizedFilter = normalizeStyleValue(filterStyle);
        return getPurposeStyleItems(purposeKey).find(function (item) {
            return normalizeStyleValue(item.feature_style) === normalizedStyle
                && normalizeStyleValue(item.filter_style) === normalizedFilter;
        }) || null;
    }

    function resolveFeatureType(purposeKey, featureStyle, filterStyle) {
        const purposeMatch = findPurposeStyleMatch(purposeKey, featureStyle, filterStyle);
        if (purposeMatch && purposeMatch.feature_type) {
            return normalizeStyleValue(purposeMatch.feature_type);
        }

        const style = normalizeStyleValue(featureStyle);
        const filter = normalizeStyleValue(filterStyle);

        switch (style) {
            case 'multiple_checkbox': return 'M';
            case 'checkbox': return 'C';
            case 'brand': return 'E';
            case 'number': return 'O';
            case 'dropdown':
            case 'dropdown_images':
            case 'dropdown_labels':
                return filter === 'slider' ? 'N' : 'S';
            case 'text':
                if (filter === 'date') return 'D';
                if (filter === 'slider') return 'N';
                return 'S';
            case 'color': return 'S';
            default: return 'T';
        }
    }

    function getFeatureTypeLabel(type) {
        return featureTypeOptions[type] || type;
    }

    function syncFeatureSetup() {
        const purposeKey = String(purposeSelect.value || '');
        updatePurposeDescription();
        ensureStyleSelectionForPurpose(purposeKey);

        const resolvedType = resolveFeatureType(
            purposeKey,
            featureStyleSelect.value,
            filterStyleSelect.value
        );
        featureTypeInput.value = resolvedType;
        styleSummary.textContent = `Detected feature type: ${getFeatureTypeLabel(resolvedType)}.`;

        syncVisibility(resolvedType);
    }

    function setActiveTab(tab) {
        if (!tabButtons[tab] || tabButtons[tab].classList.contains('hidden')) {
            tab = 'general';
        }

        activeTab = tab;

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

    function syncVariantEmptyState() {
        const rowCount = variantRows.querySelectorAll('[data-feature-variant-row]').length;
        const empty = variantRows.querySelector('#' + variantEmptyRowId);

        if (rowCount === 0 && !empty) {
            const row = document.createElement('tr');
            row.id = variantEmptyRowId;
            row.innerHTML = '<td colspan="4" class="px-3 py-6 text-center text-sm text-gray-500">No variants added yet.</td>';
            variantRows.appendChild(row);
        }

        if (rowCount > 0 && empty) {
            empty.remove();
        }
    }

    function syncGroupDisplayLocks() {
        const selectedOption = groupSelect.options[groupSelect.selectedIndex];
        const selectedGroup = Number(groupSelect.value || 0);
        const locks = {
            product: selectedGroup > 0 && selectedOption && String(selectedOption.getAttribute('data-display-on-product') || '').toUpperCase() === 'Y',
            catalog: selectedGroup > 0 && selectedOption && String(selectedOption.getAttribute('data-display-on-catalog') || '').toUpperCase() === 'Y',
            header: selectedGroup > 0 && selectedOption && String(selectedOption.getAttribute('data-display-on-header') || '').toUpperCase() === 'Y',
        };

        if (locks.product) displayProduct.checked = true;
        if (locks.catalog) displayCatalog.checked = true;
        if (locks.header) displayHeader.checked = true;

        displayProduct.disabled = locks.product;
        displayCatalog.disabled = locks.catalog;
        displayHeader.disabled = locks.header;

        groupLockNotice.classList.toggle('hidden', !(locks.product || locks.catalog || locks.header));
    }

    function syncVisibility(resolvedType = null) {
        const currentType = String(resolvedType || featureTypeInput.value || '');
        const isSelectable = selectableTypes.includes(currentType);
        const isGrouped = Number(groupSelect.value || 0) > 0;

        tabButtons.variants.classList.toggle('hidden', !isSelectable);
        tabButtons.categories.classList.toggle('hidden', isGrouped);

        if (activeTab === 'variants' && !isSelectable) setActiveTab('general');
        if (activeTab === 'categories' && isGrouped) setActiveTab('general');

        groupSelect.disabled = false;

        syncGroupDisplayLocks();
        syncVariantEmptyState();
    }

    function createVariantRow(index) {
        return `
            <tr class="border-t" data-feature-variant-row>
                <td class="px-3 py-2">
                    <input type="number" name="variants[${index}][position]" value="0" class="input-field">
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="variants[${index}][description]" value="" class="input-field" placeholder="Variant name">
                </td>
                <td class="px-3 py-2 text-center">
                    <input type="hidden" name="variants[${index}][status]" value="D">
                    <input type="checkbox" name="variants[${index}][status]" value="A" checked>
                </td>
                <td class="px-3 py-2 text-right">
                    <button type="button" class="text-red-600 hover:text-red-700" data-remove-feature-variant>Delete</button>
                </td>
            </tr>
        `;
    }

    form.querySelector('#addFeatureVariantRow').addEventListener('click', function () {
        variantRows.insertAdjacentHTML('beforeend', createVariantRow(variantIndex));
        variantIndex += 1;
        syncVariantEmptyState();
    });

    variantRows.addEventListener('click', function (event) {
        const removeBtn = event.target.closest('[data-remove-feature-variant]');
        if (!removeBtn) return;
        const row = removeBtn.closest('[data-feature-variant-row]');
        if (row) row.remove();
        syncVariantEmptyState();
    });

    purposeSelect.addEventListener('change', function () { syncFeatureSetup(); });
    featureStyleSelect.addEventListener('change', function () { syncFeatureSetup(); });
    filterStyleSelect.addEventListener('change', function () { syncFeatureSetup(); });
    groupSelect.addEventListener('change', function () { syncVisibility(); });

    Object.keys(tabButtons).forEach(function (tabKey) {
        tabButtons[tabKey].addEventListener('click', function () {
            setActiveTab(tabKey);
        });
    });

    setActiveTab('general');
    syncFeatureSetup();
})();
</script>
