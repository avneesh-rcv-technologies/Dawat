@extends($menu->layout)

@section('title', $item ? 'Edit food item' : 'Add food item')
@section('back_url', $menu->route('index', ['tab' => 'items']))
@section('back_label', $menu->panel ? 'Back to menu management' : 'Back to menu details')
@section('heading', $item ? 'Edit food item' : 'Add food item')
@section('subheading', $item ? $item->name : 'Describe the dish, choose its category and set how it is served and priced.')

@section('menu_content')
    @php
        // Same serving option defaults as the admin food item form.
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
        $selectedLabels = array_map('strval', (array) old('dietary_label_ids', $item->dietary_label_ids ?? []));
        $existingImages = $item ? \Modules\Filemanager\Models\FileLink::where('object_type', 'food_image')->where('object_id', $item->id)->count() : 0;
    @endphp

    @if ($errors->any())
        <div class="menu-alert" role="alert">
            <strong>Please correct the following:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if ($item)
        <div class="menu-toolbar"><a href="{{ $menu->route('items.show', ['id' => $item->id]) }}" class="menu-btn menu-btn-ghost">View food item</a></div>
    @endif

    <form id="food-item-general" method="POST" enctype="multipart/form-data" class="menu-form"
          action="{{ $item ? $menu->route('items.update', ['id' => $item->id]) : $menu->route('items.store') }}">
        @csrf
        @if ($item) @method('PUT') @endif

        <section class="menu-section">
            <h3>Item details</h3>
            <p class="menu-section-sub">Information customers will see on your menu.</p>
            <div class="menu-fields">
                <div>
                    <label for="food-item-name" class="form-label">Item name <span class="text-red-500">*</span></label>
                    <input id="food-item-name" name="name" class="input-field" maxlength="255" value="{{ old('name', $item->name ?? '') }}" placeholder="e.g. Butter chicken" required>
                    <p class="menu-help">Use the dish name customers will see on the menu.</p>
                    @error('name')<p class="error-message">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="food-item-description" class="form-label">Description <span class="text-red-500">*</span></label>
                    <textarea id="food-item-description" name="description" class="input-field" rows="4" maxlength="500" required>{{ old('description', $item->description ?? '') }}</textarea>
                    <p class="menu-help">Describe the main ingredients, preparation, and what is included. <span style="float: right;"><span id="food-description-count">0</span> / 500</span></p>
                    @error('description')<p class="error-message">{{ $message }}</p>@enderror
                </div>
                <div class="menu-grid-2">
                    <div>
                        <label for="food-item-category" class="form-label">Category <span class="text-red-500">*</span></label>
                        <select id="food-item-category" name="category_id" class="input-field" required>
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $item?->categories->first()?->id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <p class="menu-help">Missing one? <a href="{{ $menu->route('categories.create', $item ? [] : ['return_to' => 'item_create']) }}" class="menu-link">Add a category</a></p>
                        @error('category_id')<p class="error-message">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="food-item-tax" class="form-label">Tax (%) <span class="text-red-500">*</span></label>
                        <input id="food-item-tax" name="tax_percentage" type="number" class="input-field" min="0" max="100" step="0.01" value="{{ old('tax_percentage', $item->tax_percentage ?? 0) }}" placeholder="e.g. 8.25" required>
                        <p class="menu-help">Enter 8.25 for 8.25% tax, or 0 for no tax.</p>
                        @error('tax_percentage')<p class="error-message">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label for="menu-item-images" class="form-label">Item images</label>
                    @php
                        $galleryImages = $item ? collect($item->images)->filter(fn ($image) => !empty($image['url'])
                            && !in_array($image['url'], ['/dawat.png', asset('dawat.png')], true))->values() : collect();
                    @endphp
                    {{-- Files are collected across selections and previewed before saving. --}}
                    <div class="menu-image-picker" data-image-picker data-existing-count="{{ $existingImages }}" data-max="{{ \Modules\FoodManagements\Services\FoodImageUploadPolicy::MAX_IMAGES }}">
                        <div class="menu-image-grid" data-image-grid>
                            @foreach ($galleryImages as $galleryImage)
                                @php $linkId = (int) ($galleryImage['file_link_id'] ?? $galleryImage['id'] ?? 0); @endphp
                                <figure class="menu-image-tile is-saved" data-saved-image>
                                    <img src="{{ $galleryImage['url'] }}" alt="Saved image {{ $loop->iteration }}">
                                    @if ($linkId)
                                        {{-- Checked = remove this image when the item is saved. --}}
                                        <input type="checkbox" name="remove_images[]" value="{{ $linkId }}" hidden @checked(in_array($linkId, array_map('intval', (array) old('remove_images', [])), true))>
                                        <button type="button" class="menu-image-remove" data-toggle-saved title="Remove image" aria-label="Remove saved image {{ $loop->iteration }}">×</button>
                                    @endif
                                    <figcaption>Saved</figcaption>
                                </figure>
                            @endforeach
                            <label class="menu-image-add" for="menu-item-images" data-image-add>
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                                <span>Add images</span>
                            </label>
                        </div>
                        <input id="menu-item-images" type="file" name="food_image[]" accept=".jpg,.jpeg,.png,.webp" multiple class="menu-visually-hidden">
                    </div>
                    <p class="menu-help" data-image-help>Up to 10 images in total (JPG, JPEG, PNG or WebP, 2 MB each). You can add several at once or one by one; new images are added to the gallery when you save.</p>
                    @error('food_image')<p class="error-message">{{ $message }}</p>@enderror
                    @error('food_image.*')<p class="error-message">{{ $message }}</p>@enderror
                </div>
                <div>
                    <span class="form-label">Dietary labels <span class="text-red-500">*</span></span>
                    <div class="menu-check-grid">
                        @forelse ($labelEligibility as $labelId => $label)
                            @php $checked = in_array((string) $labelId, $selectedLabels, true); @endphp
                            <label class="menu-check {{ !$label['allowed'] && !$checked ? 'is-locked' : '' }} {{ !$label['allowed'] && $checked ? 'is-blocked' : '' }}" title="{{ $label['message'] }}">
                                <input type="checkbox" name="dietary_label_ids[]" value="{{ $labelId }}" @checked($checked) @disabled(!$label['allowed'] && !$checked)>
                                <span>{{ $label['name'] }}</span>
                                @if ($label['required'])
                                    <span class="menu-cert-tag {{ $label['allowed'] ? 'is-ok' : '' }}">{{ $label['allowed'] ? 'Certified' : 'Certificate ' . str_replace('_', ' ', $label['status']) }}</span>
                                @endif
                            </label>
                        @empty
                            <p class="menu-help">No dietary labels are available yet.</p>
                        @endforelse
                    </div>
                    <p class="menu-help">Select every label that accurately describes this item. Halal and Kosher need a certificate: they unlock once your certificate is approved in <a href="{{ $menu->certificatesUrl() }}" class="menu-link">Dietary certificates</a>.</p>
                    @php $blockedLabels = collect($labelEligibility)->filter(fn ($label, $id) => !$label['allowed'] && in_array((string) $id, $selectedLabels, true)); @endphp
                    @foreach ($blockedLabels as $label)
                        <p class="error-message">{{ $label['name'] }}: {{ $label['message'] }} Untick it to save this item.</p>
                    @endforeach
                    @error('dietary_label_ids')<p class="error-message">{{ $message }}</p>@enderror
                    @error('dietary_label_ids.*')<p class="error-message">{{ $message }}</p>@enderror
                </div>
                <div class="menu-grid-2">
                    @foreach (['is_available' => ['Availability', 'Available', 'Unavailable', 'Set to Unavailable when this item is temporarily not offered.'], 'is_active' => ['Status', 'Active', 'Inactive', 'Set to Active when this item is ready to appear on the menu.']] as $field => [$caption, $yes, $no, $help])
                        <div>
                            <label class="form-label" for="food-item-{{ $field }}">{{ $caption }}</label>
                            <select id="food-item-{{ $field }}" name="{{ $field }}" class="input-field">
                                <option value="1" @selected(old($field, $item?->$field ?? true) == 1)>{{ $yes }}</option>
                                <option value="0" @selected(old($field, $item?->$field ?? true) == 0)>{{ $no }}</option>
                            </select>
                            <p class="menu-help">{{ $help }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="menu-section">
            <h3>Serving options</h3>
            <p class="menu-section-sub">Choose a serve type for each option. The fields change with the type.</p>
            <div id="serving-options" data-serving-config="{{ json_encode($servingConfig) }}">
                @foreach ($servingOptions as $index => $option)
                    @include('foodmanagements::food_items.partials.serving-option', compact('index', 'option', 'servingConfig'))
                @endforeach
            </div>
            @error('serving_options')<p class="error-message">{{ $message }}</p>@enderror
            <button type="button" id="add-serving-option" class="menu-btn menu-btn-ghost menu-btn-sm">+ Add serving option</button>
        </section>

        {{-- ===================== FEATURES (saved with the item) ===================== --}}
        <section class="menu-section" id="features">
            <h3>Features</h3>
            <p class="menu-section-sub">Optional. Describe this item with your features, e.g. spice level.</p>

            @php $allFeatures = $featureGroups->flatMap(fn ($group) => $group->children)->values(); @endphp
            @if ($allFeatures->isEmpty())
                <p class="menu-help">You haven't created any features yet. <a href="{{ $menu->route('features.create') }}" class="menu-link">Add a feature</a>, e.g. Spice level.</p>
            @else
                @php
                    // After a failed save, show what was submitted; otherwise the saved values.
                    $submitted = session()->hasOldInput() ? (array) old('features', []) : null;
                    $savedValues = $item ? $item->featureValues->groupBy('feature_id') : collect();
                    $selectedFeatureIds = session()->hasOldInput()
                        ? array_map('intval', (array) old('feature_ids', []))
                        : $savedValues->keys()->map(fn ($id) => (int) $id)->all();
                    $selectedFeatureIds = array_values(array_intersect($selectedFeatureIds, $allFeatures->pluck('feature_id')->map(fn ($id) => (int) $id)->all()));
                    $typeLabels = \Modules\Restaurant\Http\Controllers\OnboardingMenuController::FEATURE_TYPES;
                @endphp
                <input type="hidden" name="features_submitted" value="1">

                <div class="menu-feature-picker" data-feature-picker>
                    <x-advanced-selector name="feature_ids" label="Features" placeholder="Search your features, e.g. Spice level"
                        :api-route="$menu->route('features.lookup')" value-column="feature_id" display-column="name" search-column="name"
                        :additional-columns="['type_label']" :selected="$selectedFeatureIds" :multiple="true" />
                    <p class="menu-help">Each selected feature requires a value. Remove a feature if it does not apply.</p>

                    <div class="menu-feature-panels" data-feature-panels>
                        @foreach ($allFeatures as $feature)
                            @php
                                $fid = (int) $feature->feature_id;
                                $ftype = $feature->feature_type;
                                $fname = $feature->description->name ?? 'Feature #' . $fid;
                                $isSelected = in_array($fid, $selectedFeatureIds, true);
                                if ($submitted !== null) {
                                    $raw = $submitted[$fid] ?? null;
                                    $savedVariantIds = array_map('strval', array_filter((array) $raw, fn ($v) => $v !== null && $v !== ''));
                                    $savedValue = is_array($raw) ? '' : (string) $raw;
                                    $isChecked = $raw === 'Y';
                                } else {
                                    $fSaved = $savedValues->get($fid, collect());
                                    $savedVariantIds = $fSaved->pluck('variant_id')->filter()->map(fn ($v) => (string) $v)->toArray();
                                    $savedValue = $fSaved->first()?->value ?? '';
                                    $isChecked = $fSaved->isNotEmpty();
                                }
                            @endphp
                            <div class="feature-field menu-feature-panel" data-feature-panel="{{ $fid }}" data-feature-type="{{ $ftype }}" @if (!$isSelected) hidden @endif>
                                <div class="menu-feature-panel-head">
                                    <span class="form-label" style="margin: 0;">{{ $fname }} <span class="menu-chip">{{ $typeLabels[$ftype]['label'] ?? $ftype }}</span></span>
                                    <button type="button" class="menu-icon-btn is-danger" data-remove-feature="{{ $fid }}" title="Remove" aria-label="Remove {{ $fname }}"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg></button>
                                </div>
                                <fieldset class="menu-feature-inputs" @disabled(!$isSelected)>
                                    <legend class="menu-visually-hidden">{{ $fname }}</legend>
                                    @if ($ftype === 'C')
                                        <label class="menu-check">
                                            <input type="hidden" name="features[{{ $fid }}]" value="">
                                            <input type="checkbox" name="features[{{ $fid }}]" value="Y" @checked($isChecked)>
                                            <span>Yes</span>
                                        </label>
                                    @elseif (in_array($ftype, ['S', 'M'], true) && $feature->variants->isEmpty())
                                        <p class="menu-help">No options defined for this feature yet.</p>
                                    @elseif ($ftype === 'S' && in_array($feature->feature_style, ['dropdown', 'dropdown_labels'], true))
                                        <select name="features[{{ $fid }}]" class="input-field" aria-label="{{ $fname }}">
                                            <option value="">— Select —</option>
                                            @foreach ($feature->variants as $variant)
                                                <option value="{{ $variant->variant_id }}" @selected(in_array((string) $variant->variant_id, $savedVariantIds, true))>{{ $variant->description->description ?? 'Variant #' . $variant->variant_id }}</option>
                                            @endforeach
                                        </select>
                                    @elseif (in_array($ftype, ['S', 'M'], true))
                                        <div class="menu-check-grid">
                                            @foreach ($feature->variants as $variant)
                                                <label class="menu-check">
                                                    <input type="{{ $ftype === 'S' ? 'radio' : 'checkbox' }}" name="features[{{ $fid }}]{{ $ftype === 'M' ? '[]' : '' }}" value="{{ $variant->variant_id }}" @checked(in_array((string) $variant->variant_id, $savedVariantIds, true))>
                                                    <span>{{ $variant->description->description ?? 'Variant #' . $variant->variant_id }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif (in_array($ftype, ['N', 'O'], true))
                                        <input type="number" step="any" name="features[{{ $fid }}]" value="{{ $savedValue }}" class="input-field" placeholder="Enter value" aria-label="{{ $fname }}">
                                    @elseif ($ftype === 'D')
                                        <input type="date" name="features[{{ $fid }}]" value="{{ $savedValue }}" class="input-field" aria-label="{{ $fname }}">
                                    @else
                                        <input type="text" name="features[{{ $fid }}]" value="{{ $savedValue }}" class="input-field" placeholder="Enter value" aria-label="{{ $fname }}">
                                    @endif
                                </fieldset>
                                @error('features.'.$fid)<p class="error-message">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        <div class="menu-actions">
            <a href="{{ $menu->route('index', ['tab' => 'items']) }}" class="menu-btn menu-btn-ghost">Cancel</a>
            <button type="submit" class="menu-btn">{{ $item ? 'Update food item' : 'Save food item' }}</button>
        </div>
    </form>

    <template id="serving-option-template">
        @include('foodmanagements::food_items.partials.serving-option', ['index' => '__INDEX__', 'option' => ['is_available' => true], 'servingConfig' => $servingConfig])
    </template>

@endsection

@push('scripts')
    @include('foodmanagements::food_items.partials.serving-script')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        /* ---------- Features: chosen with the advanced selector ---------- */
        const picker = document.querySelector('[data-feature-picker]');
        if (picker) {
            const panelsBox = picker.querySelector('[data-feature-panels]');
            const panels = new Map([...panelsBox.querySelectorAll('[data-feature-panel]')].map(panel => [panel.dataset.featurePanel, panel]));
            const componentId = picker.querySelector('.advanced-selector-wrapper')?.dataset.componentId;

            const requireValue = panel => {
                const controls = [...panel.querySelectorAll('fieldset input:not([type="hidden"]), fieldset select')];
                const multiple = panel.dataset.featureType === 'M';
                const hasSelection = controls.some(control => control.checked);
                controls.forEach((control, index) => {
                    control.required = !panel.hidden && (!multiple || (index === 0 && !hasSelection));
                    control.setCustomValidity(!panel.hidden && control.type === 'text' && !control.value.trim()
                        ? 'Enter a value for this feature, or remove it from the item.' : '');
                });
            };
            panels.forEach(requireValue);
            panelsBox.addEventListener('input', event => {
                const panel = event.target.closest('[data-feature-panel]');
                if (panel) requireValue(panel);
            });
            panelsBox.addEventListener('change', event => {
                const panel = event.target.closest('[data-feature-panel]');
                if (panel) requireValue(panel);
            });

            const show = values => {
                panels.forEach((panel, id) => {
                    const on = values.includes(id);
                    const wasOn = !panel.hidden;
                    panel.hidden = !on;
                    panel.querySelector('fieldset').disabled = !on;
                    if (on && !wasOn) {
                        // A newly picked Yes/No feature starts as "Yes".
                        const yes = panel.querySelector('input[type="checkbox"][value="Y"]');
                        if (yes) yes.checked = true;
                    }
                    requireValue(panel);
                });
                // Keep panels in the order the features were picked.
                values.forEach(id => panels.has(id) && panelsBox.appendChild(panels.get(id)));
            };

            picker.addEventListener('advanced-selector:change', event => show(event.detail.values.map(String)));
            panelsBox.addEventListener('click', event => {
                const button = event.target.closest('[data-remove-feature]');
                if (!button) return;
                if (componentId && typeof removeSelectedItem === 'function') {
                    removeSelectedItem(componentId, button.dataset.removeFeature);
                } else {
                    show([...panels.keys()].filter(id => id !== button.dataset.removeFeature && !panels.get(id).hidden));
                }
            });
        }

        /* ---------- Images: add across selections, preview, remove ---------- */
        const imagePicker = document.querySelector('[data-image-picker]');
        if (imagePicker) {
            const input = imagePicker.querySelector('input[type="file"]');
            const grid = imagePicker.querySelector('[data-image-grid]');
            const addTile = imagePicker.querySelector('[data-image-add]');
            const existingTotal = Number(imagePicker.dataset.existingCount || 0);
            const markedForRemoval = () => imagePicker.querySelectorAll('[data-saved-image] input[name="remove_images[]"]:checked').length;
            let existing = existingTotal;
            const max = Number(imagePicker.dataset.max || 10);
            let files = [];

            const notify = message => (typeof showToast === 'function')
                ? (window.onboardingToast ? window.onboardingToast(message, 'error') : showToast(message, 'error', 'Images', 5000))
                : appAlert(message, { tone: 'error', title: 'Images' });

            const sync = () => {
                const transfer = new DataTransfer();
                files.forEach(file => transfer.items.add(file));
                input.files = transfer.files;
                grid.querySelectorAll('[data-new-image]').forEach(tile => {
                    URL.revokeObjectURL(tile.querySelector('img').src);
                    tile.remove();
                });
                files.forEach((file, index) => {
                    const tile = document.createElement('figure');
                    tile.className = 'menu-image-tile';
                    tile.dataset.newImage = '';
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.alt = file.name;
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'menu-image-remove';
                    remove.setAttribute('aria-label', `Remove ${file.name}`);
                    remove.textContent = '×';
                    remove.addEventListener('click', () => { files.splice(index, 1); sync(); });
                    const caption = document.createElement('figcaption');
                    caption.textContent = 'New';
                    tile.append(img, remove, caption);
                    grid.insertBefore(tile, addTile);
                });
                addTile.hidden = existing + files.length >= max;
            };

            // Saved images: × marks for removal on save; click again to keep it.
            const paintSaved = tile => {
                const box = tile.querySelector('input[name="remove_images[]"]');
                const removed = box.checked;
                tile.classList.toggle('is-removed', removed);
                tile.querySelector('figcaption').textContent = removed ? 'Removed' : 'Saved';
                const button = tile.querySelector('[data-toggle-saved]');
                button.textContent = removed ? '↺' : '×';
                button.title = removed ? 'Keep image' : 'Remove image';
                button.setAttribute('aria-label', removed ? 'Keep this image' : 'Remove this image');
            };
            imagePicker.querySelectorAll('[data-saved-image]').forEach(tile => {
                const button = tile.querySelector('[data-toggle-saved]');
                if (!button) return;
                paintSaved(tile);
                button.addEventListener('click', () => {
                    const box = tile.querySelector('input[name="remove_images[]"]');
                    if (box.checked && existingTotal - markedForRemoval() + 1 + files.length > max) {
                        notify(`You can have at most ${max} images in total. Remove a new image first.`);
                        return;
                    }
                    box.checked = !box.checked;
                    paintSaved(tile);
                    existing = existingTotal - markedForRemoval();
                    sync();
                });
            });
            existing = existingTotal - markedForRemoval();

            input.addEventListener('change', () => {
                const picked = [...input.files];
                const errors = [];
                for (const file of picked) {
                    if (!/\.(jpe?g|png|webp)$/i.test(file.name)) { errors.push(`${file.name}: only JPG, JPEG, PNG or WebP images are allowed.`); continue; }
                    if (file.size > 2 * 1024 * 1024) { errors.push(`${file.name}: each image must be 2 MB or smaller.`); continue; }
                    if (files.some(f => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified)) continue;
                    if (existing + files.length >= max) { errors.push(`You can have at most ${max} images in total.`); break; }
                    files.push(file);
                }
                errors.forEach(notify);
                sync();
            });
            sync();
        }
    });
    </script>
    @if (request('section') === 'features')
        <script>document.getElementById('features')?.scrollIntoView({ behavior: 'smooth', block: 'start' });</script>
    @endif
@endpush
