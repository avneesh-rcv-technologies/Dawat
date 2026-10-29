@extends('backend::layouts.app')

@section('title', 'Create KYC Type')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'KYC Types', 'url' => route('admin.kyc.types.index')],
        ['label' => 'Create'],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Create KYC type'" />
@endsection

@section('content')
@include('kyc::admin.partials.kyc-styles')

<div class="max-w-7xl mx-auto">
    <form action="{{ route('admin.kyc.types.store') }}" method="POST" id="kyc-type-form" enctype="multipart/form-data">
        @csrf

        @include('kyc::admin.partials.form-errors')

        <div class="kyc-panel mb-6">
            <div class="kyc-panel-header">Basic information</div>
            <div class="kyc-panel-body">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="name" class="custom-label">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name') }}"
                               required
                               class="input-field mt-1"
                               placeholder="e.g. GST certificate">
                    </div>

                    <div>
                        <label for="is_active" class="custom-label">Status</label>
                        <select name="is_active" id="is_active" class="input-field mt-1">
                            <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="type" class="custom-label">Category</label>
                        <select name="type" id="type" class="input-field mt-1">
                            <option value="kyc" {{ old('type', 'kyc') === 'kyc' ? 'selected' : '' }}>KYC (onboarding)</option>
                            <option value="compliance" {{ old('type') === 'compliance' ? 'selected' : '' }}>Compliance</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Compliance types use the validity period from Settings → Compliance.</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="custom-label">Description</label>
                        <textarea name="description"
                                  id="description"
                                  rows="3"
                                  class="input-field mt-1"
                                  placeholder="Optional description">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="kyc-panel mb-6">
            <div class="kyc-panel-header flex justify-between items-center gap-3">
                <span>Fields</span>
                <button type="button" id="add-field-btn" name="button" class="btn-primary text-sm inline-flex items-center gap-1 button">
                    <span class="material-icons-outlined text-base">add</span>
                    Add field
                </button>
            </div>
            <div class="kyc-panel-body">
                <div id="fields-container" class="space-y-4"></div>
                <div id="no-fields-message" class="text-center py-8 text-gray-500">
                    <span class="material-icons-outlined text-4xl text-gray-300">description</span>
                    <p class="mt-2 text-sm">No fields yet. Click “Add field” to define the form.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.kyc.types.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" name="button" class="btn-primary inline-flex items-center gap-1 button">
                <span class="material-icons-outlined text-base">check</span>
                Create type
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var fieldIndex = 0;

    function addField(data = {}) {
        $('#no-fields-message').hide();
        var vr = data.validation_rules || {};
        var initType = data.field_type || 'text';

        var html = `
            <div class="field-card bg-white border border-gray-200 rounded-md p-4 relative" data-index="${fieldIndex}">
                <button type="button" name="button" aria-label="Remove field" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 remove-field button">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                    <div class="sm:col-span-2">
                        <label class="custom-label">
                            Field <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="fields[${fieldIndex}][field_name]"
                               value="${data.field_name || ''}"
                               required
                               class="input-field mt-1"
                               placeholder="e.g., Document Number">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="custom-label">
                            Field Key <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="fields[${fieldIndex}][field_key]"
                               value="${data.field_key || ''}"
                               pattern="[a-z0-9_]+"
                               required
                               class="field-key input-field mt-1"
                               placeholder="e.g., document_number">
                        <p class="mt-1 text-xs text-gray-500">Lowercase letters, numbers, and underscores only</p>
                    </div>

                    <div class="sm:col-span-1">
                        <label class="custom-label">
                            Field Type <span class="text-red-500">*</span>
                        </label>
                        <select name="fields[${fieldIndex}][field_type]"
                                required
                                class="field-type input-field mt-1">
                            <option value="text"   ${initType === 'text'   ? 'selected' : ''}>Text</option>
                            <option value="number" ${initType === 'number' ? 'selected' : ''}>Number</option>
                            <option value="date"   ${initType === 'date'   ? 'selected' : ''}>Date</option>
                            <option value="file"   ${initType === 'file'   ? 'selected' : ''}>File</option>
                            <option value="select" ${initType === 'select' ? 'selected' : ''}>Select</option>
                        </select>
                    </div>

                    <div class="sm:col-span-1">
                        <label class="custom-label">Required?</label>
                        <select name="fields[${fieldIndex}][is_required]"
                                class="input-field mt-1">
                            <option value="1" ${data.is_required !== false ? 'selected' : ''}>Yes</option>
                            <option value="0" ${data.is_required === false ? 'selected' : ''}>No</option>
                        </select>
                    </div>

                    <!-- Options (select type) -->
                    <div class="sm:col-span-6 options-row" style="${initType === 'select' ? '' : 'display:none;'}">
                        <label class="block text-sm font-medium text-gray-700">Options (JSON format)</label>
                        <textarea name="fields[${fieldIndex}][options]"
                                  rows="2"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                  placeholder='{"value1": "Label 1", "value2": "Label 2"}'>${data.options ? JSON.stringify(data.options) : ''}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Example: {"value1": "Label 1", "value2": "Label 2"}</p>
                    </div>

                    <!-- Validation Rules -->
                    <div class="sm:col-span-6 border-t pt-3 mt-1">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Validation Rules</p>

                        <!-- Text rules -->
                        <div class="vr-text space-y-2 ${initType === 'text' ? '' : 'hidden'}">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-xs text-gray-500">Min length</label>
                                    <input type="number" name="fields[${fieldIndex}][validation_rules][min_length]"
                                           min="0" placeholder="e.g. 3" value="${vr.min_length ?? ''}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Max length</label>
                                    <input type="number" name="fields[${fieldIndex}][validation_rules][max_length]"
                                           min="1" placeholder="default 255" value="${vr.max_length ?? ''}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Allowed characters</label>
                                <select name="fields[${fieldIndex}][validation_rules][format]"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                    <option value="none"               ${(vr.format||'none')==='none'               ? 'selected':''}>Any text (no restriction)</option>
                                    <option value="letters_only"       ${vr.format==='letters_only'       ? 'selected':''}>Letters only (a-z, A-Z, spaces)</option>
                                    <option value="numbers_only"       ${vr.format==='numbers_only'       ? 'selected':''}>Numbers only (0-9)</option>
                                    <option value="alphanumeric"       ${vr.format==='alphanumeric'       ? 'selected':''}>Alphanumeric (letters, numbers, spaces)</option>
                                    <option value="alphanumeric_strict"${vr.format==='alphanumeric_strict'? 'selected':''}>Alphanumeric strict (no spaces)</option>
                                    <option value="email"              ${vr.format==='email'              ? 'selected':''}>Email format</option>
                                </select>
                            </div>
                        </div>

                        <!-- Number rules -->
                        <div class="vr-number grid grid-cols-2 gap-2 ${initType === 'number' ? '' : 'hidden'}">
                            <div>
                                <label class="text-xs text-gray-500">Min value</label>
                                <input type="number" name="fields[${fieldIndex}][validation_rules][min]"
                                       placeholder="e.g. 0" value="${vr.min ?? ''}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Max value</label>
                                <input type="number" name="fields[${fieldIndex}][validation_rules][max]"
                                       placeholder="e.g. 100" value="${vr.max ?? ''}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            </div>
                        </div>

                        <!-- File rules -->
                        <div class="vr-file space-y-2 ${initType === 'file' ? '' : 'hidden'}">
                            <div>
                                <label class="text-xs text-gray-500">Max file size (KB)</label>
                                <input type="number" name="fields[${fieldIndex}][validation_rules][max_size_kb]"
                                       min="1" placeholder="default 10240 (10MB)" value="${vr.max_size_kb ?? ''}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Allowed file types (comma separated)</label>
                                <input type="text" name="fields[${fieldIndex}][validation_rules][allowed_types]"
                                       placeholder="pdf,jpg,jpeg,png"
                                       value="${Array.isArray(vr.allowed_types) ? vr.allowed_types.join(',') : (vr.allowed_types || '')}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <p class="text-xs text-gray-400 mt-1">Leave blank for default: pdf, jpg, jpeg, png, doc, docx</p>
                            </div>
                        </div>

                        <!-- Date rules -->
                        <div class="vr-date grid grid-cols-2 gap-2 ${initType === 'date' ? '' : 'hidden'}">
                            <div>
                                <label class="text-xs text-gray-500">Min date</label>
                                <input type="date" name="fields[${fieldIndex}][validation_rules][min_date]"
                                       value="${vr.min_date || ''}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Max date</label>
                                <input type="date" name="fields[${fieldIndex}][validation_rules][max_date]"
                                       value="${vr.max_date || ''}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            </div>
                        </div>

                        <!-- Select: no extra rules -->
                        <p class="vr-select text-xs text-gray-400 ${initType === 'select' ? '' : 'hidden'}">No extra validation rules for "select" type.</p>
                    </div>
                </div>

                <input type="hidden" name="fields[${fieldIndex}][order_index]" value="${fieldIndex}">
            </div>
        `;

        $('#fields-container').append(html);
        fieldIndex++;
    }

    $('#add-field-btn').click(function() {
        addField();
    });

    $(document).on('click', '.remove-field', function() {
        $(this).closest('.field-card').remove();
        if ($('#fields-container').children().length === 0) {
            $('#no-fields-message').show();
        }
    });

    $(document).on('change', '.field-type', function() {
        var $card = $(this).closest('.field-card');
        var type  = $(this).val();

        // Options row
        $card.find('.options-row').toggle(type === 'select');

        // Validation rule panels
        $card.find('.vr-text, .vr-number, .vr-file, .vr-date, .vr-select').addClass('hidden');
        $card.find('.vr-' + type).removeClass('hidden');
    });

    // Auto-generate field key from field name
    $(document).on('input', 'input[name*="[field_name]"]', function() {
        var $card = $(this).closest('.field-card');
        var $keyInput = $card.find('.field-key');
        
        if (!$keyInput.data('manual')) {
            var key = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '');
            $keyInput.val(key);
        }
    });

    $(document).on('input', '.field-key', function() {
        $(this).data('manual', true);
    });

    // Default field removed to make section optional
});
</script>
@endpush
