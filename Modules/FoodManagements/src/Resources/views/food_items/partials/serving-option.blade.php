@php
    $servingConfig = $servingConfig ?? app(\Modules\FoodManagements\Services\ServingOptionConfiguration::class)->all();
    $serveType = $option['serve_type'] ?? $servingConfig['default_serve_type'];
    $typeConfig = $servingConfig['serve_types'][$serveType] ?? [];
@endphp
<div class="serving-option rounded-lg border border-gray-200 bg-gray-50 p-5" data-serving-option>
    <div class="mb-5 flex items-center justify-between gap-4">
        <h4 class="font-semibold text-gray-900" data-serving-heading>Serving option {{ is_numeric($index) ? $index + 1 : '' }}</h4>
        <button type="button" class="text-sm text-red-600" data-remove-serving>Remove</button>
    </div>
    @if (!empty($option['id']))
        <input type="hidden" name="serving_options[{{ $index }}][id]" value="{{ $option['id'] }}">
    @endif
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
            <label class="form-label" for="serving_{{ $index }}_serve_type">{{ $servingConfig['fields']['serve_type']['label'] }} <span class="text-red-500">*</span></label>
            <select id="serving_{{ $index }}_serve_type" name="serving_options[{{ $index }}][serve_type]" class="input-field" data-serve-type aria-describedby="serving_{{ $index }}_type_help" required>
                @if (!isset($servingConfig['serve_types'][$serveType]))
                    <option value="" selected>Select a serve type</option>
                @endif
                @foreach ($servingConfig['serve_types'] as $value => $definition)
                    <option value="{{ $value }}" @selected($serveType === $value)>{{ $definition['label'] }}</option>
                @endforeach
            </select>
            <p id="serving_{{ $index }}_type_help" class="mt-1 text-xs text-gray-500">{{ $servingConfig['fields']['serve_type']['help'] }}</p>
            @error('serving_options.'.$index.'.serve_type')<p class="error-message">{{ $message }}</p>@enderror
        </div>
        @foreach ($servingConfig['field_order'] as $field)
            @php
                $definition = $servingConfig['fields'][$field];
                $visible = in_array($field, $typeConfig['enabled_fields'] ?? [], true);
                $isRequired = $visible && in_array($field, $typeConfig['required_fields'] ?? [], true);
            @endphp
            <div data-serving-field="{{ $field }}" @if (!$visible) hidden @endif>
                <label class="form-label" for="serving_{{ $index }}_{{ $field }}">
                    <span data-serving-caption="{{ $field }}">{{ $typeConfig['field_labels'][$field] ?? $definition['label'] }}</span>
                    <span class="text-red-500" data-required-marker @if (!$isRequired) hidden @endif>*</span>
                </label>
                @if ($definition['input'] === 'select')
                    @php
                        $units = $typeConfig['allowed_units'] ?? [];
                        $selectedUnit = in_array($option['unit'] ?? '', $units, true) ? $option['unit'] : ($typeConfig['defaults']['unit'] ?? '');
                    @endphp
                    <select id="serving_{{ $index }}_{{ $field }}" name="serving_options[{{ $index }}][{{ $field }}]" class="input-field" data-serving-input="{{ $field }}" aria-describedby="serving_{{ $index }}_{{ $field }}_help" @required($isRequired) @disabled(!$visible)>
                        @foreach ($units as $unit)
                            <option value="{{ $unit }}" @selected($selectedUnit === $unit)>{{ $unit }}</option>
                        @endforeach
                    </select>
                @else
                    <input id="serving_{{ $index }}_{{ $field }}" name="serving_options[{{ $index }}][{{ $field }}]"
                        type="{{ $definition['input'] }}" value="{{ $option[$field] ?? '' }}" placeholder="{{ $definition['placeholder'] ?? '' }}" class="input-field"
                        data-serving-input="{{ $field }}" aria-describedby="serving_{{ $index }}_{{ $field }}_help" @required($isRequired) @disabled(!$visible)
                        @foreach (['min', 'max', 'step'] as $attribute)
                            @if (isset($definition[$attribute])) {{ $attribute }}="{{ $definition[$attribute] }}" @endif
                        @endforeach
                        @foreach ($definition['validation'] as $rule)
                            @if ($definition['input'] === 'text' && str_starts_with($rule, 'max:')) maxlength="{{ substr($rule, 4) }}" @endif
                        @endforeach>
                @endif
                <p id="serving_{{ $index }}_{{ $field }}_help" class="mt-1 text-xs text-gray-500" data-field-help="{{ $field }}">{{ $typeConfig['field_help'][$field] ?? $definition['help'] }}</p>
                @error('serving_options.'.$index.'.'.$field)<p class="error-message" data-validation-error>{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>
    <p class="mt-4 rounded bg-gray-100 px-3 py-2 text-xs text-gray-600" data-serve-help>{{ $typeConfig['help'] ?? '' }}</p>
    <label class="mt-4 inline-flex items-center gap-2 text-sm" data-serving-field="is_available">
        <input type="hidden" name="serving_options[{{ $index }}][is_available]" value="0">
        <input type="checkbox" name="serving_options[{{ $index }}][is_available]" value="1" data-serving-input="is_available" aria-describedby="serving_{{ $index }}_available_help" @checked($option['is_available'] ?? $servingConfig['fields']['is_available']['default'])>
        {{ $servingConfig['fields']['is_available']['label'] }}
    </label>
    <p id="serving_{{ $index }}_available_help" class="mt-1 text-xs text-gray-500">{{ $servingConfig['fields']['is_available']['help'] }}</p>
    <p class="mt-3 text-xs text-gray-600" data-serving-total aria-live="polite"></p>
</div>
