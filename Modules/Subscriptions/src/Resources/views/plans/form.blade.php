@extends('backend::layouts.app')

@section('title', isset($plan) ? 'Edit Plan' : 'Create Plan')

@section('breadcrumbs')
    @php
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Subscriptions', 'url' => null],
            ['label' => 'Plans', 'url' => route('subscriptions.plans.index')],
            ['label' => isset($plan) ? 'Edit Plan' : 'Create Plan', 'url' => null],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="isset($plan) ? 'Edit Plan' : 'Create Plan'" />
@endsection

@section('content')

    <form class="form-ajax"
        action="{{ isset($plan) ? route('subscriptions.plans.update', $plan->id) : route('subscriptions.plans.store') }}"
        method="POST">
        @csrf
        @if (isset($plan))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Plan Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Plan Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $plan->name ?? '') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
            </div>

            <!-- Price -->
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Price <span class="text-red-500">*</span>
                </label>
                <input type="number" id="price" name="price" step="0.01"
                    value="{{ old('price', $plan->price ?? '0.00') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
            </div>

            <!-- Billing Cycle -->
            <div>
                <label for="billing_cycle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Billing Cycle <span class="text-red-500">*</span>
                </label>
                <select id="billing_cycle" name="billing_cycle"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                    <option value="daily"
                        {{ old('billing_cycle', $plan->billing_cycle ?? '') == 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly"
                        {{ old('billing_cycle', $plan->billing_cycle ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly"
                        {{ old('billing_cycle', $plan->billing_cycle ?? 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly
                    </option>
                    <option value="quarterly"
                        {{ old('billing_cycle', $plan->billing_cycle ?? '') == 'quarterly' ? 'selected' : '' }}>Quarterly
                    </option>
                    <option value="yearly"
                        {{ old('billing_cycle', $plan->billing_cycle ?? '') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                    <option value="one-time"
                        {{ old('billing_cycle', $plan->billing_cycle ?? '') == 'one-time' ? 'selected' : '' }}>One-Time
                    </option>
                </select>
            </div>

            <!-- Trial Days -->
            <div>
                <label for="trial_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Trial Days
                </label>
                <input type="number" id="trial_days" name="trial_days"
                    value="{{ old('trial_days', $plan->trial_days ?? '0') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <!-- Max Users -->
            <div>
                <label for="max_users" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Max Users (leave empty for unlimited)
                </label>
                <input type="number" id="max_users" name="max_users"
                    value="{{ old('max_users', $plan->max_users ?? '') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <!-- Sort Order -->
            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Sort Order
                </label>
                <input type="number" id="sort_order" name="sort_order"
                    value="{{ old('sort_order', $plan->sort_order ?? '0') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <!-- Description -->
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description
                </label>
                <textarea id="description" name="description" rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('description', $plan->description ?? '') }}</textarea>
            </div>

            <!-- Features -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Features
                </label>
                <div id="features-container" class="space-y-2">
                    @php
                        $features = old('features', isset($plan) ? $plan->features : []);
                        if (empty($features)) {
                            $features = [''];
                        }
                    @endphp
                    @foreach($features as $index => $feature)
                    <div class="feature-item flex gap-2">
                        <input type="text" name="features[]" value="{{ $feature }}"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Enter feature">
                        <button type="button" onclick="removeFeature(this)"
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    @endforeach
                </div>
                <button type="button" onclick="addFeature()"
                    class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Feature
                </button>
            </div>

            <!-- Is Active -->
            <div class="flex items-center">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                    {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="is_active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Active
                </label>
            </div>

            <!-- Is Popular -->
            <div class="flex items-center">
                <input type="checkbox" id="is_popular" name="is_popular" value="1"
                    {{ old('is_popular', $plan->is_popular ?? false) ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="is_popular" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Mark as Popular
                </label>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="mt-6 flex justify-end gap-4">
            <x-button type="submit" class="btn-primary" label="Save" icon='' name="button" />
        </div>
    </form>
@endsection

@section('scripts')
<script>
    function addFeature() {
        const container = document.getElementById('features-container');
        const featureItem = document.createElement('div');
        featureItem.className = 'feature-item flex gap-2';
        featureItem.innerHTML = `
            <input type="text" name="features[]" value=""
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="Enter feature">
            <button type="button" onclick="removeFeature(this)"
                class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        container.appendChild(featureItem);
    }

    function removeFeature(button) {
        const container = document.getElementById('features-container');
        const items = container.querySelectorAll('.feature-item');
        
        // Keep at least one feature input
        if (items.length > 1) {
            button.closest('.feature-item').remove();
        } else {
            // Clear the input instead of removing
            const input = button.closest('.feature-item').querySelector('input');
            input.value = '';
        }
    }
</script>
@endsection
