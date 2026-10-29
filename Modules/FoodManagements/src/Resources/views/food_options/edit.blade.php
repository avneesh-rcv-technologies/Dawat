@extends('backend::layouts.app')

@section('title', 'Edit Option Group')

@section('breadcrumbs')
    @php
        $title = 'Edit Option Group';
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
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Edit Option Group: {{ $optionGroup->name }}</h3>
            <p class="text-sm text-gray-600">Modify option group details and manage individual options</p>
        </div>

        @include('foodmanagements::food_options.form')
    </div>

    {{-- Options Management Section --}}
    @if (isset($optionGroup) && $optionGroup->id)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
            <div class="mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Manage Options</h3>
                        <p class="text-sm text-gray-600">Add, edit, or delete individual options for this group</p>
                    </div>
                </div>
            </div>

            <div id="existing-options" class="space-y-4">
                @forelse($optionGroup->options as $option)
                    <div class="option-item bg-gray-50 p-4 rounded-lg border">
                        <form class="form-ajax" method="POST"
                            action="{{ route('admin.foodmanagements.food-options.update', $option->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Option Name</label>
                                    <input type="text" name="name" value="{{ $option->name }}" class="input-field"
                                        required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Status</label>
                                    <select name="is_active" class="input-field">
                                        <option value="1" @selected($option->is_active)>Active</option>
                                        <option value="0" @selected(!$option->is_active)>Inactive</option>
                                    </select>
                                </div>

                                <div class="flex gap-2">
                                    <button type="submit" name="button" class="btn btn-success">Update</button>
                                </div>
                            </div>
                        </form>

                        <form class="form-ajax mt-2" method="POST"
                            action="{{ route('admin.foodmanagements.food-options.destroy', $option->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" name="button" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8l-4 4m0 0l-4-4m4 4V3">
                            </path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Options Added</h3>
                        <p class="text-gray-500 mb-4">Start by adding your first option to this group.</p>
                    </div>
                @endforelse
            </div>

            {{-- Add New Option Form --}}
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 mt-6">
                <h4 class="font-medium mb-4">Add New Option</h4>

                <form class="form-ajax" method="POST" action="{{ route('admin.foodmanagements.food-options.store') }}">
                    @csrf
                    <input type="hidden" name="food_option_group_id" value="{{ $optionGroup->id }}">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium mb-1">Option Name</label>
                            <input type="text" name="name" class="input-field" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Status</label>
                            <select name="is_active" class="input-field">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div>
                            <button type="submit" name="button" class="btn btn-primary">Add Option</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    @endif

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

        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
        }

        .btn-primary {
            background-color: #621F09;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #621F09;
        }

        .btn-secondary {
            color: #374151;
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #f3f4f6;
        }

        .btn-success {
            background-color: #10b981;
            color: #ffffff;
        }

        .btn-success:hover {
            background-color: #059669;
        }

        .btn-danger {
            background-color: #ef4444;
            color: #ffffff;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .input-field {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: all 0.2s ease-in-out;
        }

        .input-field:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
    </style>
@endsection
