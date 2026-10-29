@extends('backend::layouts.app')

@section('title', isset($feature) ? 'Edit Feature' : 'Create Feature')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'Features', 'url' => route('subscriptions.features.index')],
    ['label' => isset($feature) ? 'Edit' : 'Create', 'url' => null],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="isset($feature) ? 'Edit Feature' : 'Create Feature'" />
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Side - Form (2 columns) -->
    <div class="lg:col-span-2">
        <form class="form-ajax"
            action="{{ isset($feature) ? route('subscriptions.features.update', $feature->id) : route('subscriptions.features.store') }}"
            method="POST">
            @csrf
            @if (isset($feature))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Feature Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Feature Name <span class="text-red-500">*</span>
            </label>
            <input type="text" id="name" name="name" value="{{ old('name', $feature->name ?? '') }}"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="e.g., storage_gb, api_calls_monthly"
                required>
            <p class="mt-1 text-xs text-gray-500">Lowercase with underscores (used in code)</p>
        </div>

        <!-- Display Name -->
        <div>
            <label for="display_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Display Name <span class="text-red-500">*</span>
            </label>
            <input type="text" id="display_name" name="display_name" value="{{ old('display_name', $feature->display_name ?? '') }}"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="e.g., Storage Space, API Calls"
                required>
            <p class="mt-1 text-xs text-gray-500">User-friendly name shown in UI</p>
        </div>

        <!-- Feature Type -->
        <div>
            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Feature Type <span class="text-red-500">*</span>
            </label>
            <select id="type" name="type"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                onchange="updateTypeFields()"
                required>
                <option value="">Select Type</option>
                <option value="boolean" {{ old('type', $feature->type ?? '') == 'boolean' ? 'selected' : '' }}>
                    Boolean (Yes/No)
                </option>
                <option value="limit" {{ old('type', $feature->type ?? '') == 'limit' ? 'selected' : '' }}>
                    Limit (Numeric)
                </option>
                <option value="package" {{ old('type', $feature->type ?? '') == 'package' ? 'selected' : '' }}>
                    Package (Tiers)
                </option>
            </select>
        </div>

        <!-- Group -->
        <div>
            <label for="group" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Group
            </label>
            <input type="text" id="group" name="group" value="{{ old('group', $feature->group ?? '') }}"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="e.g., Storage, API, Support"
                list="groups-list">
            <datalist id="groups-list">
                <option value="Storage">
                <option value="API">
                <option value="Users">
                <option value="Projects">
                <option value="Support">
                <option value="Security">
            </datalist>
            <p class="mt-1 text-xs text-gray-500">Organize related features</p>
        </div>

        <!-- Default Value -->
        <div>
            <label for="default_value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Default Value
            </label>
            <input type="text" id="default_value" name="default_value" value="{{ old('default_value', $feature->default_value ?? '') }}"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="e.g., 10, true, basic">
            <p class="mt-1 text-xs text-gray-500">Use -1 or "unlimited" for unlimited limits</p>
        </div>

        <!-- Unit (for limit type) -->
        <div id="unit-field" style="display: none;">
            <label for="unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Unit of Measurement
            </label>
            <input type="text" id="unit" name="unit" value="{{ old('unit', $feature->unit ?? '') }}"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="e.g., GB, Calls/Month, Users">
            <p class="mt-1 text-xs text-gray-500">Unit for display (e.g., "50 GB")</p>
        </div>

        <!-- Sort Order -->
        <div>
            <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Sort Order
            </label>
            <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $feature->sort_order ?? 0) }}"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                min="0">
            <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
        </div>

        <!-- Is Active -->
        <div class="flex items-center">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                {{ old('is_active', $feature->is_active ?? true) ? 'checked' : '' }}
                class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
            <label for="is_active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                Active
            </label>
        </div>

        <!-- Description -->
        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Description
            </label>
            <textarea id="description" name="description" rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="Describe what this feature provides">{{ old('description', $feature->description ?? '') }}</textarea>
        </div>

        <!-- Limit Configuration -->
        <div id="limit-metadata" class="md:col-span-2" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Limit Configuration
            </label>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="metadata_min" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Minimum
                    </label>
                    <input type="number" id="metadata_min" name="metadata[min]" 
                        value="{{ old('metadata.min', $feature->metadata['min'] ?? '') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        step="any">
                </div>
                <div>
                    <label for="metadata_max" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Maximum
                    </label>
                    <input type="number" id="metadata_max" name="metadata[max]" 
                        value="{{ old('metadata.max', $feature->metadata['max'] ?? '') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        step="any">
                </div>
                <div>
                    <label for="metadata_step" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Step
                    </label>
                    <input type="number" id="metadata_step" name="metadata[step]" 
                        value="{{ old('metadata.step', $feature->metadata['step'] ?? '') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        step="any">
                </div>
            </div>
        </div>

        <!-- Package Options -->
        <div id="package-metadata" class="md:col-span-2" style="display: none;">
            <label for="metadata_options" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Package Options
            </label>
            <textarea id="metadata_options" name="metadata_options_text" rows="4"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="basic&#10;standard&#10;premium&#10;enterprise">{{ old('metadata_options_text', isset($feature->metadata['options']) ? implode("\n", $feature->metadata['options']) : '') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Enter one option per line</p>
        </div>

        <!-- Buttons -->
        <div class="md:col-span-2 flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('subscriptions.features.index') }}"
                class="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-700 transition">
                Cancel
            </a>
            <button type="submit"
                name="button"
                class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 transition">
                {{ isset($feature) ? 'Update Feature' : 'Create Feature' }}
            </button>
        </div>
            </div>
        </form>
    </div>

    <!-- Right Side - Guide (1 column) -->
    <div class="lg:col-span-1">
        <!-- Feature Types Guide -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-icons-outlined text-blue-600 text-lg">info</span>
                Feature Types
            </h3>
            
            <div class="space-y-3">
                <!-- Boolean -->
                <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded p-3">
                    <div class="flex items-start gap-2 mb-1">
                        <span class="material-icons-outlined text-blue-600 dark:text-blue-400 text-sm mt-0.5">toggle_on</span>
                        <div>
                            <h4 class="font-medium text-blue-900 dark:text-blue-100 text-sm">Boolean</h4>
                        </div>
                    </div>
                    <p class="text-xs text-blue-800 dark:text-blue-200 ml-6">Yes/No features. Enable or disable.</p>
                    <p class="text-xs text-blue-700 dark:text-blue-300 ml-6 mt-1">
                        <strong>Examples:</strong> API Access, Custom Branding
                    </p>
                </div>

                <!-- Limit -->
                <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded p-3">
                    <div class="flex items-start gap-2 mb-1">
                        <span class="material-icons-outlined text-green-600 dark:text-green-400 text-sm mt-0.5">speed</span>
                        <div>
                            <h4 class="font-medium text-green-900 dark:text-green-100 text-sm">Limit</h4>
                        </div>
                    </div>
                    <p class="text-xs text-green-800 dark:text-green-200 ml-6">Numeric quotas with usage tracking.</p>
                    <p class="text-xs text-green-700 dark:text-green-300 ml-6 mt-1">
                        <strong>Examples:</strong> 50 GB, 1000 Calls/Month
                    </p>
                </div>

                <!-- Package -->
                <div class="bg-purple-50 dark:bg-purple-900 border border-purple-200 dark:border-purple-700 rounded p-3">
                    <div class="flex items-start gap-2 mb-1">
                        <span class="material-icons-outlined text-purple-600 dark:text-purple-400 text-sm mt-0.5">inventory_2</span>
                        <div>
                            <h4 class="font-medium text-purple-900 dark:text-purple-100 text-sm">Package</h4>
                        </div>
                    </div>
                    <p class="text-xs text-purple-800 dark:text-purple-200 ml-6">Tiered options (Basic/Pro/Enterprise).</p>
                    <p class="text-xs text-purple-700 dark:text-purple-300 ml-6 mt-1">
                        <strong>Examples:</strong> Support Tier, SLA Level
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Tips -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-icons-outlined text-amber-600 text-lg">lightbulb</span>
                Quick Tips
            </h3>
            
            <ul class="space-y-2 text-xs">
                <li class="flex gap-2">
                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">•</span>
                    <span class="text-gray-700 dark:text-gray-300">
                        <strong>Name:</strong> Use lowercase with underscores (api_calls, storage_gb)
                    </span>
                </li>
                <li class="flex gap-2">
                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">•</span>
                    <span class="text-gray-700 dark:text-gray-300">
                        <strong>Display Name:</strong> User-friendly name shown in UI
                    </span>
                </li>
                <li class="flex gap-2">
                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">•</span>
                    <span class="text-gray-700 dark:text-gray-300">
                        <strong>Group:</strong> Organize features (Storage, API, Support)
                    </span>
                </li>
                <li class="flex gap-2">
                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">•</span>
                    <span class="text-gray-700 dark:text-gray-300">
                        <strong>Unit:</strong> Add units for limits (GB, Calls/Month)
                    </span>
                </li>
                <li class="flex gap-2">
                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">•</span>
                    <span class="text-gray-700 dark:text-gray-300">
                        <strong>Unlimited:</strong> Use "-1" for unlimited limits
                    </span>
                </li>
                <li class="flex gap-2">
                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">•</span>
                    <span class="text-gray-700 dark:text-gray-300">
                        <strong>Sort Order:</strong> Lower numbers appear first
                    </span>
                </li>
            </ul>
        </div>

        <!-- Example Features -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-icons-outlined text-green-600 text-lg">done_all</span>
                Example Features
            </h3>
            
            <div class="space-y-2 text-xs">
                <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded border border-gray-200 dark:border-gray-600">
                    <p class="font-medium text-gray-900 dark:text-white">Storage Quota</p>
                    <p class="text-gray-600 dark:text-gray-400">Type: <span class="text-green-600 dark:text-green-400">Limit</span> | Unit: GB</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded border border-gray-200 dark:border-gray-600">
                    <p class="font-medium text-gray-900 dark:text-white">API Access</p>
                    <p class="text-gray-600 dark:text-gray-400">Type: <span class="text-blue-600 dark:text-blue-400">Boolean</span> | Yes/No</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded border border-gray-200 dark:border-gray-600">
                    <p class="font-medium text-gray-900 dark:text-white">Support Tier</p>
                    <p class="text-gray-600 dark:text-gray-400">Type: <span class="text-purple-600 dark:text-purple-400">Package</span> | Basic/Pro/Enterprise</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded border border-gray-200 dark:border-gray-600">
                    <p class="font-medium text-gray-900 dark:text-white">Team Members</p>
                    <p class="text-gray-600 dark:text-gray-400">Type: <span class="text-green-600 dark:text-green-400">Limit</span> | Unit: Users</p>
                </div>
            </div>
        </div>
    </div>
</div>
</form>

@endsection

@section('scripts')
<script>
function updateTypeFields() {
    const type = document.getElementById('type').value;
    const unitField = document.getElementById('unit-field');
    const limitMetadata = document.getElementById('limit-metadata');
    const packageMetadata = document.getElementById('package-metadata');
    
    // Hide all type-specific fields
    unitField.style.display = 'none';
    limitMetadata.style.display = 'none';
    packageMetadata.style.display = 'none';
    
    // Show relevant fields based on type
    if (type === 'limit') {
        unitField.style.display = 'block';
        limitMetadata.style.display = 'block';
    } else if (type === 'package') {
        packageMetadata.style.display = 'block';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTypeFields();
});

// Handle form submission to convert package options
document.querySelector('form').addEventListener('submit', function(e) {
    const type = document.getElementById('type').value;
    if (type === 'package') {
        const optionsText = document.getElementById('metadata_options').value;
        const options = optionsText.split('\n').map(o => o.trim()).filter(o => o);
        
        // Create hidden inputs for array
        const form = this;
        options.forEach((option, index) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `metadata[options][${index}]`;
            input.value = option;
            form.appendChild(input);
        });
    }
});
</script>
@endsection
