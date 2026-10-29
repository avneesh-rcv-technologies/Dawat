@extends('backend::layouts.app')

@section('title', 'Edit KYC Type')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'KYC Types', 'url' => route('admin.kyc.types.index')],
        ['label' => 'Edit'],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Edit KYC type'" />
@endsection

@section('content')
@include('kyc::admin.partials.kyc-styles')

<div class="max-w-7xl mx-auto">
    <form action="{{ route('admin.kyc.types.update', $kycType->id) }}" method="POST">
        @csrf
        @method('PUT')

        @include('kyc::admin.partials.form-errors')

        @if($kycType->isInUse())
            <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Warning</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>This KYC type is currently in use. Some changes may affect existing submissions.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

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
                               value="{{ old('name', $kycType->name) }}"
                               required
                               class="input-field mt-1">
                    </div>

                    <div>
                        <label for="is_active" class="block text-sm font-medium text-gray-700">
                            Status
                        </label>
                        <select name="is_active" 
                                id="is_active" 
                                class="mt-1 p-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="1" {{ old('is_active', $kycType->is_active) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active', $kycType->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="type" class="block text-sm font-medium text-gray-700">
                            Type
                        </label>
                        <select name="type" 
                                id="type" 
                                class="mt-1 p-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="kyc"        {{ old('type', $kycType->type ?? 'kyc') === 'kyc' ? 'selected' : '' }}>KYC (Onboarding)</option>
                            <option value="compliance" {{ old('type', $kycType->type ?? '') === 'compliance' ? 'selected' : '' }}>Compliance</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">"Compliance" types are tracked for expiry (validity period is configured in Settings &raquo; Compliance).</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Description
                        </label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="3" 
                                  class="mt-1 p-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('description', $kycType->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fields Information -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Fields Configuration</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $kycType->fields->count() }} field(s) configured</p>
                </div>
                <a href="{{ route('admin.kyc.types.fields', $kycType->id) }}" 
                   class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="mr-2 -ml-0.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Manage Fields
                </a>
            </div>
            <div class="p-6">
                @if($kycType->fields->count() > 0)
                    <div class="space-y-2">
                        @foreach($kycType->fields as $field)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst($field->field_type) }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-900">{{ $field->field_name }}</span>
                                    <code class="text-xs text-gray-500">{{ $field->field_key }}</code>
                                </div>
                                <div>
                                    @if($field->is_required)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-700">
                                            Required
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                            Optional
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">No fields configured yet.</p>
                @endif
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.kyc.types.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary inline-flex items-center gap-1">
                <span class="material-icons-outlined text-base">save</span>
                Update type
            </button>
        </div>
    </form>
</div>
@endsection
