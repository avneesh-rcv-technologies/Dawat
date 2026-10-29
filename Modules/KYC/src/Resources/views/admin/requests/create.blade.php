@extends('backend::layouts.app')

@section('title', 'Create KYC Request')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'KYC Requests', 'url' => route('admin.kyc.requests.index')],
        ['label' => 'Create'],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Create KYC request'" />
@endsection

@section('content')
@include('kyc::admin.partials.kyc-styles')

<div class="kyc-panel">
        {{-- <div class="px-6 py-4 border-b">
            <h3 class="text-xl font-semibold">Create KYC Request</h3>
        </div> --}}
        
        <form action="{{ route('admin.kyc.requests.store') }}" method="POST" id="request-form">
            @csrf
            <div class="kyc-panel-body">
                @include('kyc::admin.partials.form-errors')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="kyc_type_id" class="custom-label">KYC type <span class="text-red-500">*</span></label>
                        <select id="kyc_type_id" name="kyc_type_id" required class="input-field mt-1">
                            <option value="">Select KYC Type</option>
                            @foreach($kycTypes as $type)
                                <option value="{{ $type->id }}" {{ old('kyc_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="title" class="custom-label">Request title <span class="text-red-500">*</span></label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" required class="input-field mt-1">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="due_date" class="custom-label">Due date</label>
                        <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="input-field mt-1">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="message" class="custom-label">Message</label>
                    <textarea id="message" name="message" rows="4"
                              placeholder="Optional message for the restaurant owner…"
                              class="input-field mt-1">{{ old('message') }}</textarea>
                </div>

                <div class="border-t pt-6 mb-6"></div>

                <h4 class="text-lg font-semibold mb-4">Notification Target</h4>
                
                <input type="hidden" name="target[type]" value="single">

                <div id="target-type-selector" class="mb-6 hidden" aria-hidden="true">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Target Type <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="target[type]" value="single" checked class="peer hidden">
                            <div class="px-4 py-2 border-2 border-gray-300 rounded-md text-center peer-checked:border-[#03502B] peer-checked:bg-[#ffffff] peer-checked:text-[#072B12] hover:border-gray-400 transition">
                                Single Restaurant Owner
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer hidden">
                            <input type="radio" name="target[type]" value="multiple" class="peer hidden">
                            <div class="px-4 py-2 border-2 border-gray-300 rounded-md text-center peer-checked:border-[#03502B] peer-checked:bg-[#ffffff] peer-checked:text-[#072B12] hover:border-gray-400 transition">
                                Multiple Restaurant Owners
                            </div>
                        </label>

                        @if(!empty($restaurantCount) && $restaurantCount > 0)
                        <label class="flex-1 cursor-pointer hidden">
                            <input type="radio" name="target[type]" value="all" class="peer hidden">
                            <div class="px-4 py-2 border-2 border-gray-300 rounded-md text-center peer-checked:border-[#03502B] peer-checked:bg-[#ffffff] peer-checked:text-[#072B12] hover:border-gray-400 transition">
                                All Restaurant Owners
                            </div>
                        </label>
                        @endif
                    </div>
                </div>

                <!-- Single User Selection -->
                <div id="single-user-section" class="target-section mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Restaurant Owner <span class="text-red-500">*</span></label>
                    <select id="single-user-select" class="select2 input-field mt-1 w-full">
                        <option value="">Search for a user...</option>
                    </select>
                </div>

                <!-- Multiple Users Selection -->
                <div id="multiple-users-section" class="target-section mb-6 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Restaurant Owners <span class="text-red-500">*</span></label>
                    <select id="multiple-users-select" multiple class="select2 input-field mt-1 w-full">
                    </select>
                </div>

                <!-- User Group Selection -->
                <div id="group-section" class="target-section mb-6 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Restaurant Owner Group <span class="text-red-500">*</span></label>
                    <select id="group-select" class="input-field mt-1 w-full">
                        <option value="">Select a group</option>
                    </select>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-3">
                <a href="{{ route('admin.kyc.requests.index') }}" class="px-5 py-2.5 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">Send request</button>
                
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
/* Custom Select2 Styling */
.select2-container--default .select2-selection--single,
.select2-container--default .select2-selection--multiple {
    border: 1px solid #d1d5db !important;
    border-radius: 0.375rem !important;
    min-height: 42px !important;
    padding: 0.375rem !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 32px !important;
    padding-left: 8px !important;
    color: #374151 !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px !important;
}

.select2-dropdown {
    border: 1px solid #d1d5db !important;
    border-radius: 0.5rem !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
}

.select2-search--dropdown .select2-search__field {
    border: 1px solid #d1d5db !important;
    border-radius: 0.375rem !important;
    padding: 0.5rem !important;
    font-size: 0.875rem !important;
}

.select2-results__option {
    padding: 0.875rem 1rem !important;
    font-size: 0.875rem !important;
    line-height: 1.5 !important;
}

/* Highlight (hover/focused) option uses project emerald theme */
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #ecfdf5 !important; /* emerald-50 */
    color: #065f46 !important; /* emerald-700 */
}

/* Selected option (aria-selected=true) uses a light emerald tone with dark emerald text */
.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #d1fae5 !important; /* emerald-100 */
    color: #065f46 !important; /* emerald-700 */
}

/* Custom user item styling */
.user-item {
    padding: 4px 0;
}

.user-name {
    font-weight: 600;
    color: #111827;
    font-size: 14px;
    display: block;
    margin-bottom: 4px;
}

.user-email {
    font-size: 13px;
    color: #6b7280;
    display: block;
    margin-bottom: 4px;
}

.user-role {
    font-size: 12px;
    color: #065f46; /* emerald-700 */
    font-weight: 500;
    display: inline-block;
    padding: 2px 8px;
    background-color: #d1fae5; /* emerald-100 */
    border-radius: 4px;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #058A3F !important;
    border: none !important;
    color: white !important;
    border-radius: 0.375rem !important;
    padding: 0.25rem 0.5rem !important;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: white !important;
    margin-right: 0.25rem !important;
}

/* Improve multi-select chips layout and remove icon visibility */
.select2-container--default .select2-selection--multiple .select2-selection__rendered {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 4px 6px !important;
    align-items: center;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    background-color: #058A3F !important;
    border: none !important;
    color: #fff !important;
    border-radius: 0.375rem !important;
    padding: 0.2rem 0.5rem !important;
    font-size: 0.875rem !important;
    line-height: 1 !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #ffffff !important;
    margin-right: 0 !important;
    margin-left: 4px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    display: inline-block !important;
    line-height: 1 !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:before {
    /* ensure a visible × is shown */
    content: '\00d7' !important;
    font-size: 13px !important;
    display: inline-block !important;
}

/* Clear (x) for single select */
.select2-container .select2-selection__clear {
    color: #6b7280 !important;
    font-size: 14px !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Custom template for displaying users
    function formatUser(user) {
        if (!user.id || user.loading) {
            return user.text;
        }
        
        // Parse the user text: "Name (email) - Type"
        var parts = user.text.split('(');
        var name = parts[0].trim();
        var emailPart = parts[1] ? parts[1].split(')')[0].trim() : '';
        var type = user.type || 'Restaurant Owner';
        
        var html = 
            '<div class="user-item">' +
                '<div class="user-name">' + name + '</div>' +
                '<div class="user-email">' + emailPart + '</div>' +
                '<span class="user-role">' + type + '</span>' +
            '</div>';
        
        return $(html);
    }
    
    // Template for selected user
    function formatUserSelection(user) {
        if (!user.id) {
            return user.text;
        }
        var name = user.text.split('(')[0].trim();
        return name;
    }
    
    // Initialize Select2 with AJAX
    function initSelect2(selector) {
        $(selector).select2({
            placeholder: 'Type to search restaurant owners...',
            allowClear: true,
            width: '100%',
            escapeMarkup: function(markup) { return markup; },
            templateResult: formatUser,
            templateSelection: formatUserSelection,
            ajax: {
                url: '{{ route("admin.kyc.requests.get-users") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term || '',
                        type: 'all'
                    };
                },
                processResults: function (data) {
                    console.log('Users loaded:', data);
                    if (!data || data.length === 0) {
                        return {
                            results: [{
                                id: '',
                                text: 'No users found',
                                disabled: true
                            }]
                        };
                    }
                    return {
                        results: $.map(data, function(user) {
                            return {
                                id: user.id,
                                text: user.name + ' (' + user.email + ') - ' + user.type
                            };
                        })
                    };
                },
                cache: false
            },
            minimumInputLength: 0
        });
        
        // Trigger search on open to load initial results
        $(selector).on('select2:open', function() {
            $('.select2-search__field').trigger('input');
        });
    }
    
    initSelect2('#single-user-select');
    initSelect2('#multiple-users-select');

    // Force single-user target only for this KYC request flow.
    $('input[name="target[type]"]').filter('[value="single"]').prop('checked', true);
    $('input[name="target[type]"]').not('[value="single"]').prop('checked', false).closest('label').addClass('hidden');
    $('#target-type-selector').addClass('hidden');
    $('#single-user-section').removeClass('hidden');

    // Form validation and cleanup
    $('#request-form').submit(function(e) {
        var targetType = $('input[name="target[type]"]').first().val();
        var isValid = true;
        
        // Remove all target fields first
        $('input[name="target[user_id]"]').remove();
        $('input[name="target[user_ids][]"]').remove();
        $('input[name="target[group]"]').remove();
        
        switch(targetType) {
            case 'single':
                var userId = $('#single-user-select').val();
                if (!userId) {
                    alert('Please select a restaurant owner');
                    isValid = false;
                } else {
                    // Extract numeric ID from "owner_1" or "user_1" format
                    var numericId = userId.split('_')[1];
                    $('#single-user-select').prop('disabled', true);
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'target[user_id]',
                        value: numericId
                    }).appendTo('#request-form');
                    // Also send the type
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'target[user_type]',
                        value: userId.split('_')[0]
                    }).appendTo('#request-form');
                }
                break;
            case 'multiple':
                var userIds = $('#multiple-users-select').val();
                if (!userIds || userIds.length === 0) {
                    alert('Please select at least one restaurant owner');
                    isValid = false;
                } else {
                    $('#multiple-users-select').prop('disabled', true);
                    $.each(userIds, function(i, id) {
                        var numericId = id.split('_')[1];
                        var userType = id.split('_')[0];
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'target[user_ids][]',
                            value: numericId
                        }).appendTo('#request-form');
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'target[user_types][]',
                            value: userType
                        }).appendTo('#request-form');
                    });
                }
                break;
            case 'group':
                var group = $('#group-select').val();
                if (!group) {
                    alert('Please select a restaurant group');
                    isValid = false;
                } else {
                    $('#group-select').prop('disabled', true);
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'target[group]',
                        value: group
                    }).appendTo('#request-form');
                }
                break;
        }
        
        if (!isValid) {
            e.preventDefault();
            // Re-enable selects if validation fails
            $('#single-user-select, #multiple-users-select, #group-select').prop('disabled', false);
        }
    });
});
</script>
@endpush
