@extends('backend::layouts.app')

@section('title', isset($subscription) ? 'Edit Subscription' : 'Create Subscription')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'Subscriptions', 'url' => route('subscriptions.index')],
    ['label' => isset($subscription) ? 'Edit Subscription' : 'Create Subscription', 'url' => null],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="isset($subscription) ? 'Edit Subscription' : 'Create Subscription'" />
@endsection

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form class="form-ajax" 
              action="{{ isset($subscription) ? route('subscriptions.update', $subscription->id) : route('subscriptions.store') }}" 
              method="POST">
            @csrf
            @if(isset($subscription))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Plan Selection -->
                <div class="md:col-span-2">
                    <label for="plan_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Plan <span class="text-red-500">*</span>
                    </label>
                    <select id="plan_id" 
                            name="plan_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            required>
                        <option value="">Select a Plan</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" 
                                    {{ old('plan_id', $subscription->plan_id ?? '') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} - {{ $plan->formatted_price }} / {{ $plan->billing_cycle }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Direct plan purchase - subscription_product entry will be auto-created
                    </p>
                </div>

                @if(!isset($subscription))
                <!-- Subscribable Type -->
                <div>
                    <label for="subscribable_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Subscriber Type <span class="text-red-500">*</span>
                    </label>
                    <select id="subscribable_type" 
                            name="subscribable_type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            required>
                        <option value="">Select Type</option>
                        <option value="App\Models\User" {{ old('subscribable_type') == 'App\Models\User' ? 'selected' : '' }}>User</option>
                        <option value="App\Models\Admin" {{ old('subscribable_type') == 'App\Models\Admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <!-- Subscribable ID -->
                <div>
                    <label for="subscribable_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Subscriber ID <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="subscribable_id" 
                           name="subscribable_id" 
                           value="{{ old('subscribable_id') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           required>
                </div>
                @endif

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select id="status" 
                            name="status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            required>
                        <option value="active" {{ old('status', $subscription->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="trialing" {{ old('status', $subscription->status ?? '') == 'trialing' ? 'selected' : '' }}>Trialing</option>
                        <option value="past_due" {{ old('status', $subscription->status ?? '') == 'past_due' ? 'selected' : '' }}>Past Due</option>
                        <option value="paused" {{ old('status', $subscription->status ?? '') == 'paused' ? 'selected' : '' }}>Paused</option>
                        <option value="canceled" {{ old('status', $subscription->status ?? '') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                        <option value="expired" {{ old('status', $subscription->status ?? '') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>

                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Start Date
                    </label>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           value="{{ old('start_date', isset($subscription) ? $subscription->start_date?->format('Y-m-d') : now()->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <!-- Trial Ends At -->
                <div>
                    <label for="trial_ends_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Trial Ends At
                    </label>
                    <input type="date" 
                           id="trial_ends_at" 
                           name="trial_ends_at" 
                           value="{{ old('trial_ends_at', isset($subscription) ? $subscription->trial_ends_at?->format('Y-m-d') : '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <!-- Current Period Start -->
                <div>
                    <label for="current_period_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Current Period Start
                    </label>
                    <input type="date" 
                           id="current_period_start" 
                           name="current_period_start" 
                           value="{{ old('current_period_start', isset($subscription) ? $subscription->current_period_start?->format('Y-m-d') : '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <!-- Current Period End -->
                <div>
                    <label for="current_period_end" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Current Period End
                    </label>
                    <input type="date" 
                           id="current_period_end" 
                           name="current_period_end" 
                           value="{{ old('current_period_end', isset($subscription) ? $subscription->current_period_end?->format('Y-m-d') : '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Notes
                    </label>
                    <textarea id="notes" 
                              name="notes" 
                              rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('notes', $subscription->notes ?? '') }}</textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-6 flex justify-end gap-4">
                 <x-button type="submit"                     
                        class="btn-primary" 
                        label="Save" 
                        icon=''
                        name="button" 
                    />
            </div>
        </form>
    </div>
</div>
@endsection
