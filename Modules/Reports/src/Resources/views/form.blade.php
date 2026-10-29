@extends('backend::layouts.app')

@section('title', isset($report) ? 'Edit Report' : 'Create Report')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index')],
    ['label' => 'Reports', 'url' => route('reports.index')],
    ['label' => isset($report) ? 'Edit Report' : 'Create Report', 'url' => null],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="isset($report) ? 'Edit Report' : 'Create Report'" />
@endsection

@section('content')
<div class="space-y-6">
    <!-- Form -->
    <form action="{{ isset($report) ? route('reports.update', $report->id) : route('reports.store') }}" method="POST" class="space-y-6 report-form">
        @csrf
        @if(isset($report))
            @method('PUT')
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
            
            <div class="grid grid-cols-1 gap-6">
                <!-- Report Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Report Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $report->name ?? '') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description', $report->description ?? '') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Visualization</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Chart Type -->
                <div>
                    <label for="chart_type" class="block text-sm font-medium text-gray-700 mb-2">Chart Type *</label>
                    <select name="chart_type" id="chart_type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('chart_type') border-red-500 @enderror">
                        <option value="table" {{ old('chart_type', $report->chart_type ?? 'table') === 'table' ? 'selected' : '' }}>Table</option>
                        <option value="comparison" {{ old('chart_type', $report->chart_type ?? '') === 'comparison' ? 'selected' : '' }}>Comparison</option>
                        <option value="bar" {{ old('chart_type', $report->chart_type ?? '') === 'bar' ? 'selected' : '' }}>Bar Chart</option>
                        <option value="stat" {{ old('chart_type', $report->chart_type ?? '') === 'stat' ? 'selected' : '' }}>Stat</option>
                        <option value="line" {{ old('chart_type', $report->chart_type ?? '') === 'line' ? 'selected' : '' }}>Line Chart</option>
                        <option value="pie" {{ old('chart_type', $report->chart_type ?? '') === 'pie' ? 'selected' : '' }}>Pie Chart</option>
                        <option value="area" {{ old('chart_type', $report->chart_type ?? '') === 'area' ? 'selected' : '' }}>Area Chart</option>
                        <option value="donut" {{ old('chart_type', $report->chart_type ?? '') === 'donut' ? 'selected' : '' }}>Donut Chart</option>
                        <option value="scatter" {{ old('chart_type', $report->chart_type ?? '') === 'scatter' ? 'selected' : '' }}>Scatter Plot</option>
                    </select>
                    @error('chart_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Result Limit -->
                <div>
                    <label for="limit" class="block text-sm font-medium text-gray-700 mb-2">Result Limit</label>
                    <input type="number" name="limit" id="limit" min="1" max="10000" value="{{ old('limit', $report->limit ?? 100) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('limit') border-red-500 @enderror">
                    @error('limit')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Access Control</h2>
            
            <div class="space-y-6">
                <!-- Guard Selection (Multiple) -->
                <div>
                    <label for="guards" class="block text-sm font-medium text-gray-700 mb-2">Guards *</label>
                    <select name="guards[]" id="guards" required multiple size="5"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('guards') border-red-500 @enderror">
                        @php
                            $selectedGuards = old('guards', $report->guards ?? [$guard ?? 'admin']);
                            if (!is_array($selectedGuards)) {
                                $selectedGuards = [$selectedGuards];
                            }
                        @endphp
                        @foreach(config('acl::guards.guards') as $guardName => $guardConfig)
                            @if(isset($guardConfig['provider']))
                                <option value="{{ $guardName }}" {{ in_array($guardName, $selectedGuards) ? 'selected' : '' }}>
                                    {{ ucfirst($guardName) }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('guards')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple guards. This report will be accessible to selected guards.</p>
                </div>

                <!-- Is Public -->
                <div class="flex items-center">
                    <input type="hidden" name="is_public" value="0">
                    <input type="checkbox" name="is_public" id="is_public" value="1" 
                           {{ old('is_public', $report->is_public ?? false) ? 'checked' : '' }}
                           onchange="toggleAccessControls()"
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="is_public" class="ml-2 text-sm text-gray-700">Make this report public (accessible to all users in selected guards)</label>
                </div>

                <!-- Allowed Roles (Multiple Guards) -->
                <div id="allowed_roles_section" style="{{ old('is_public', $report->is_public ?? false) ? 'display: none;' : '' }}">
                    @php
                        // Get roles for all selected guards
                        $allRoles = collect();
                        foreach ($selectedGuards as $guardName) {
                            $guardRoles = \Modules\Acl\Models\Role::where('guard', $guardName)->get();
                            foreach ($guardRoles as $role) {
                                $role->display_name = $role->name . ' (' . $guardName . ')';
                            }
                            $allRoles = $allRoles->merge($guardRoles);
                        }
                        
                        $selectedRoleIds = old('allowed_roles', $report->allowed_roles ?? []);
                    @endphp
                    @if($allRoles->isNotEmpty())
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Allowed Roles</label>
                            <div class="max-h-60 overflow-y-auto border border-gray-300 rounded-lg p-3">
                                @foreach($allRoles->groupBy('guard') as $guardName => $roles)
                                    <div class="mb-3">
                                        <p class="text-xs font-semibold text-gray-600 uppercase mb-2">{{ ucfirst($guardName) }} Guard</p>
                                        @foreach($roles as $role)
                                            <label class="flex items-center py-1 hover:bg-gray-50 px-2 rounded">
                                                <input type="checkbox" name="allowed_roles[]" value="{{ $role->id }}"
                                                    {{ in_array($role->id, $selectedRoleIds) ? 'checked' : '' }}
                                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <span class="ml-2 text-sm text-gray-700">{{ $role->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Select roles that can access this report. Leave empty to allow all roles.</p>
                        </div>
                    @else
                        <p class="text-sm text-yellow-600">No roles found for selected guards. Please create roles first.</p>
                    @endif
                </div>

                <!-- Allowed Users (Multiple Guards with Model Path) -->
                <div id="allowed_users_section" style="{{ old('is_public', $report->is_public ?? false) ? 'display: none;' : '' }}">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Allowed Users</label>
                        <div class="space-y-4">
                            @php
                                // Get selected users with their model info
                                $selectedUsersData = old('allowed_users_data', $report->allowed_users ?? []);
                                if (!is_array($selectedUsersData)) {
                                    $selectedUsersData = [];
                                }
                            @endphp
                            
                            @foreach($selectedGuards as $guardName)
                                @php
                                    $guardConfig = config("acl::guards.guards.{$guardName}");
                                    $provider = $guardConfig['provider'] ?? $guardName;
                                    $providerConfig = config("acl::guards.providers.{$provider}");
                                    $modelClass = $providerConfig['model'] ?? null;
                                    
                                    // Try to get table name
                                    $usersTable = null;
                                    if ($modelClass && class_exists($modelClass)) {
                                        try {
                                            $instance = new $modelClass();
                                            $usersTable = $instance->getTable();
                                        } catch (\Exception $e) {
                                            $usersTable = $provider . 's';
                                        }
                                    } else {
                                        $usersTable = $provider . 's';
                                        $modelClass = 'App\\Models\\' . ucfirst($provider);
                                    }
                                    
                                    // Get users for this guard
                                    $users = \DB::table($usersTable)->select('id', 'name', 'email')->get();
                                    
                                    // Get currently selected users for this guard
                                    $guardSelectedUsers = collect($selectedUsersData)->filter(function($user) use ($guardName) {
                                        return ($user['guard'] ?? null) === $guardName;
                                    })->pluck('id')->toArray();
                                @endphp
                                
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <p class="text-sm font-semibold text-gray-700 mb-3">
                                        {{ ucfirst($guardName) }} Guard Users 
                                        <span class="text-xs text-gray-500 font-normal">({{ $usersTable }} table)</span>
                                    </p>
                                    
                                    @if($users->isNotEmpty())
                                        <div class="max-h-48 overflow-y-auto space-y-1">
                                            @foreach($users as $user)
                                                <label class="flex items-center py-1 px-2 hover:bg-gray-50 rounded">
                                                    <input type="checkbox" 
                                                        name="allowed_users_{{$guardName}}" 
                                                        value="{{ $user->id }}"
                                                        data-guard="{{ $guardName }}"
                                                        data-model="{{ $modelClass }}"
                                                        data-user-name="{{ $user->name }}"
                                                        {{ in_array($user->id, $guardSelectedUsers) ? 'checked' : '' }}
                                                        class="user-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                    <span class="ml-2 text-sm text-gray-700">
                                                        {{ $user->name }}
                                                        @if(isset($user->email))
                                                            <span class="text-xs text-gray-500">({{ $user->email }})</span>
                                                        @endif
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500">No users found for this guard</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Select specific users who can access this report. Leave empty to allow all users (based on roles).</p>
                    </div>
                    
                    <input type="hidden" name="allowed_users_data" id="allowed_users_data" value="">
                    
                </div>

                @if(isset($report))
                <!-- Is Active -->
                <div class="flex items-center pt-4 border-t border-gray-200">
                    <input type="checkbox" name="is_active" id="is_active" value="1" 
                           {{ old('is_active', $report->is_active ?? true) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Active (report is enabled)</label>
                </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                {{ isset($report) ? 'Update Report' : 'Create Report' }}
            </button>
        </div>
    </form>
</div>

@section('scripts')
<script>
function toggleDataSourceOptions() {
    const type = document.getElementById('data_source_type').value;
    const selectDiv = document.getElementById('data_source_select');
    const customDiv = document.getElementById('custom_query_input');
    
    if (type === 'custom_query') {
        selectDiv.style.display = 'none';
        customDiv.style.display = 'block';
        document.getElementById('data_source').required = false;
        document.getElementById('custom_query').required = true;
    } else {
        selectDiv.style.display = 'block';
        customDiv.style.display = 'none';
        document.getElementById('data_source').required = true;
        document.getElementById('custom_query').required = false;
    }
}

function toggleAccessControls() {
    const isPublic = document.getElementById('is_public').checked;
    const rolesSection = document.getElementById('allowed_roles_section');
    const usersSection = document.getElementById('allowed_users_section');
    
    if (isPublic) {
        rolesSection.style.display = 'none';
        usersSection.style.display = 'none';
    } else {
        rolesSection.style.display = 'block';
        usersSection.style.display = 'block';
    }
}

// Collect structured user data before form submission
function collectAllowedUsers() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    const allowedUsersData = [];
    
    checkboxes.forEach(checkbox => {
        const userData = {
            model: checkbox.dataset.model || '',
            id: parseInt(checkbox.value) || 0,
            guard: checkbox.dataset.guard || '',
            name: checkbox.dataset.userName || ''
        };
        
        allowedUsersData.push(userData);
    });
    
    // Always set valid JSON, even if empty array
    const jsonData = JSON.stringify(allowedUsersData);
    
    
    const hiddenField = document.getElementById('allowed_users_data');
    if (hiddenField) {
        hiddenField.value = jsonData;
        
    } else {
        
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleDataSourceOptions();
    toggleAccessControls();
    
    // Set initial value
    collectAllowedUsers();
    
    // Attach form submission handler
    const form = document.querySelector('form.report-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            collectAllowedUsers();
            
            // Debug: show what will be submitted
            const hiddenField = document.getElementById('allowed_users_data');
        });
    }
});
</script>
@endsection
@endsection
