@extends('backend::layouts.app')

@section('title', 'Option Group Details')

@section('breadcrumbs')
    @php
        $title = 'Option Group Details';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Food Options', 'url' => route('admin.foodmanagements.food-option-groups.index')],
            ['label' => $title],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $optionGroup->name }}</h1>
                <div class="flex items-center space-x-4 mt-2">
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs {{ $optionGroup->is_required ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $optionGroup->is_required ? 'Required' : 'Optional' }}
                    </span>
                    <span class="text-sm text-gray-500">Min: {{ $optionGroup->min }} | Max: {{ $optionGroup->max }}</span>
                </div>
            </div>
          
        </div>
    </div>

    @if($optionGroup->options->count() > 0)
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Options ({{ $optionGroup->options->count() }})</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($optionGroup->options as $option)
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-medium text-gray-900">{{ $option->name }}</h3>
                            <span class="inline-flex px-2 py-1 text-xs rounded-full {{ $option->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $option->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="text-lg font-semibold text-blue-600">
                            @if($option->price > 0)
                                + {{ fn_format_default_currency() }}{{ number_format($option->price, 2) }}
                            @else
                                Free
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="text-center py-8">
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8l-4 4m0 0l-4-4m4 4V3"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Options Added</h3>
            <p class="text-gray-500 mb-4">This option group doesn't have any options yet.</p>
            <a href="{{ route('admin.foodmanagements.food-option-groups.edit', $optionGroup->id) }}" 
               class="btn btn-primary">
                Add Options
            </a>
        </div>
    @endif
</div>

<style>
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    font-size: 0.875rem;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    border: none;
    text-decoration: none;
}

.btn-primary {
    background-color: #3b82f6;
    color: #ffffff;
}

.btn-primary:hover {
    background-color: #2563eb;
}

.btn-secondary {
    color: #374151;
    background-color: #f9fafb;
    border: 1px solid #d1d5db;
}

.btn-secondary:hover {
    background-color: #f3f4f6;
}
</style>
@endsection