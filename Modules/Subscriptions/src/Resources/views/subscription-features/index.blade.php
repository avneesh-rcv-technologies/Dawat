@extends('backend::layouts.app')

@section('title', 'Subscription Features')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'Subscriptions', 'url' => route('subscriptions.index')],
    ['label' => 'Subscription #' . $subscription->id, 'url' => route('subscriptions.show', $subscription->id)],
    ['label' => 'Features', 'url' => null],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Subscription Features'" />
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Subscription #{{ $subscription->id }} - Features</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ $subscription->plan->name }} Plan · 
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $subscription->isActive() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($subscription->status) }}
                        </span>
                    </p>
                </div>
                <a href="{{ route('subscriptions.show', $subscription->id) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <span class="material-icons-outlined text-sm mr-1">arrow_back</span>
                    Back to Subscription
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Total Features</span>
                <span class="material-icons-outlined text-blue-500">dashboard</span>
            </div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['total_features'] }}</div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Active</span>
                <span class="material-icons-outlined text-green-500">check_circle</span>
            </div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['active_features'] }}</div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Limit Features</span>
                <span class="material-icons-outlined text-orange-500">speed</span>
            </div>
            <div class="text-2xl font-bold text-orange-600">{{ $stats['limit_features'] }}</div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Boolean Features</span>
                <span class="material-icons-outlined text-purple-500">toggle_on</span>
            </div>
            <div class="text-2xl font-bold text-purple-600">{{ $stats['boolean_features'] }}</div>
        </div>
    </div>

    <!-- Add Feature -->
    @if($availableFeatures->count() > 0)
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm mb-6">
        <form method="POST" action="{{ route('subscriptions.features.store', $subscription->id) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Feature</label>
                <select name="feature_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" required>
                    <option value="" disabled selected>Select feature</option>
                    @foreach($availableFeatures as $feature)
                        <option value="{{ $feature->id }}">{{ $feature->display_name }} ({{ ucfirst($feature->type) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Value (optional)</label>
                <input type="text" name="value" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Defaults to feature setting" />
            </div>
            <div class="md:col-span-2 flex items-center gap-3 md:justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">
                    <span class="material-icons-outlined text-sm mr-1">add</span>
                    Add Feature to Subscription
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Features by Group -->
    @if($features->count() > 0)
        @foreach($features as $group => $groupFeatures)
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
            <!-- Group Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <span class="material-icons-outlined text-blue-500">folder</span>
                    {{ $group }}
                    <span class="text-xs font-normal text-gray-500">{{ $groupFeatures->count() }} features</span>
                </h3>
            </div>

            <!-- Features Grid -->
            <div class="divide-y divide-gray-200">
                @foreach($groupFeatures as $feature)
                @php
                    $isCustom = $feature->pivot->is_custom;
                    $isActive = $feature->pivot->is_active;
                @endphp
                <div class="px-6 py-4 hover:bg-gray-50 transition">
                    <div class="flex items-start justify-between gap-4">
                        <!-- Feature Info -->
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="text-sm font-medium text-gray-900">{{ $feature->display_name }}</h4>
                                @if($isCustom)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <span class="material-icons-outlined text-xs mr-0.5">edit</span>
                                        Custom Override
                                    </span>
                                @endif
                                @if(!$isActive)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        <span class="material-icons-outlined text-xs mr-0.5">cancel</span>
                                        Inactive
                                    </span>
                                @endif
                            </div>
                            @if($feature->description)
                                <p class="text-xs text-gray-500">{{ $feature->description }}</p>
                            @endif
                        </div>

                        <!-- Feature Value -->
                        <div class="text-right min-w-max">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ $feature->pivot->value }}
                                @if($feature->unit)
                                    <span class="text-gray-500 font-normal">{{ $feature->unit }}</span>
                                @endif
                            </div>
                            @if($feature->pivot->custom_notes)
                                <p class="text-xs text-gray-500 mt-1">{{ $feature->pivot->custom_notes }}</p>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            @if($feature->type === 'limit')
                                <a href="{{ route('subscriptions.features.show', [$subscription->id, $feature->id]) }}" 
                                   class="inline-flex items-center px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded hover:bg-gray-200"
                                   title="View usage">
                                    <span class="material-icons-outlined text-sm">analytics</span>
                                </a>
                            @endif

                                <button type="button" 
                                    onclick="editFeature({{ $subscription->id }}, {{ $feature->id }}, '{{ $feature->pivot->value }}')"
                                    class="inline-flex items-center px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded hover:bg-gray-200"
                                    title="Edit override">
                                <span class="material-icons-outlined text-sm">edit</span>
                            </button>

                                @if($feature->type === 'limit' && $feature->pivot->used > 0)
                                <form method="POST" 
                                      action="{{ route('subscriptions.features.resetUsage', [$subscription->id, $feature->id]) }}" 
                                      style="display: inline;"
                                      onsubmit="return confirm('Reset usage to 0?')">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded hover:bg-gray-200"
                                            title="Reset usage">
                                        <span class="material-icons-outlined text-sm">refresh</span>
                                    </button>
                                </form>
                            @endif

                            <form method="POST" 
                                  action="{{ route('subscriptions.features.toggle', [$subscription->id, $feature->id]) }}" 
                                  style="display: inline;">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-3 py-2 text-xs font-medium rounded {{ $isActive ? 'text-green-700 bg-green-100 border border-green-300 hover:bg-green-200' : 'text-gray-700 bg-gray-100 border border-gray-300 hover:bg-gray-200' }}"
                                        title="{{ $isActive ? 'Disable feature' : 'Enable feature' }}">
                                    <span class="material-icons-outlined text-sm">{{ $isActive ? 'check_circle' : 'cancel' }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    @else
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-12 text-center">
            <span class="material-icons-outlined text-4xl text-gray-300 flex justify-center mb-4">inbox</span>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No Features Assigned</h3>
            <p class="text-sm text-gray-600">This subscription doesn't have any features assigned yet.</p>
        </div>
    @endif
</div>

<!-- Edit Feature Modal -->
<div id="editFeatureModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <form method="POST" id="editFeatureForm">
            @csrf
            @method('PUT')
            
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Override Feature Value</h3>
            </div>

            <div class="px-6 py-4 space-y-4">
                <div>
                    <label for="override_value" class="block text-sm font-medium text-gray-700 mb-1">
                        Feature Value
                    </label>
                    <input type="text" 
                           id="override_value" 
                           name="value" 
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                           required>
                </div>

                <div>
                    <label for="override_notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Notes
                    </label>
                    <textarea id="override_notes" 
                              name="custom_notes" 
                              rows="2"
                              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                              placeholder="Reason for override"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 flex gap-3">
                <button type="button" 
                        onclick="closeFeatureModal()"
                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">
                    Save Override
                </button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
function editFeature(subscriptionId, featureId, currentValue) {
    const modal = document.getElementById('editFeatureModal');
    const form = document.getElementById('editFeatureForm');
    const valueInput = document.getElementById('override_value');
    
    valueInput.value = currentValue;
    form.action = `/subscriptions/${subscriptionId}/features/${featureId}`;
    modal.classList.remove('hidden');
    valueInput.focus();
}

function closeFeatureModal() {
    document.getElementById('editFeatureModal').classList.add('hidden');
}

// Close modal on escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFeatureModal();
    }
});
</script>
@endsection
@endsection
