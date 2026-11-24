@extends('backend::layouts.app')

@section('title', $label->exists ? 'Edit Dietary Label' : 'Create Dietary Label')

@section('breadcrumbs')
    @php
        $title = $label->exists ? 'Edit Dietary Label' : 'Create Dietary Label';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Dietary Labels', 'url' => route('admin.dietarymanagment.labels.index')],
            ['label' => $title],
        ];
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
    <form method="POST" class="form-ajax space-y-6 rounded-lg border border-gray-200 bg-white p-6"
        action="{{ $label->exists ? route('admin.dietarymanagment.labels.update', $label->id) : route('admin.dietarymanagment.labels.store') }}">
        @csrf
        @if ($label->exists)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @foreach ([
                'name' => ['Name', 100, 'e.g. Gluten-Free'],
                'code' => ['Display Code', 10, 'e.g. GF'],
                'slug' => ['Slug', 100, 'Leave blank to generate from the name'],
                'backend_value' => ['System Identifier', 50, 'e.g. GLUTEN_FREE'],
                'icon' => ['Icon Name or Asset Path', 255, 'e.g. leaf or icons/gluten-free.svg'],
            ] as $field => [$caption, $length, $placeholder])
                <div>
                    <label class="custom-label" for="{{ $field }}">{{ $caption }}
                        @if (in_array($field, ['name', 'code', 'backend_value']))<span class="text-red-500">*</span>@endif
                    </label>
                    <input type="text" id="{{ $field }}" name="{{ $field }}" maxlength="{{ $length }}"
                        value="{{ old($field, $label->$field) }}" placeholder="{{ $placeholder }}" class="input-field"
                        @required(in_array($field, ['name', 'code', 'backend_value']))>
                    @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            @endforeach
            <div>
                <label class="custom-label" for="display_order">Display Order <span class="text-red-500">*</span></label>
                <input type="number" id="display_order" name="display_order" min="0" max="2147483647" step="1"
                    value="{{ old('display_order', $label->display_order) }}" class="input-field" required>
                @error('display_order')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="custom-label" for="description">Description</label>
            <textarea id="description" name="description" rows="4" class="input-field" editor="false">{{ old('description', $label->description) }}</textarea>
            @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="custom-label" for="is_active">Status</label>
            <select id="is_active" name="is_active" class="input-field" required>
                <option value="1" @selected(old('is_active', $label->is_active) == 1)>Active</option>
                <option value="0" @selected(old('is_active', $label->is_active) == 0)>Inactive</option>
            </select>
            @error('is_active')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <input type="hidden" name="is_certification_required" value="0">
            <label class="flex items-center gap-2" for="is_certification_required">
                <input type="checkbox" id="is_certification_required" name="is_certification_required" value="1"
                    @checked(old('is_certification_required', $label->is_certification_required))>
                <span>Certification Required</span>
            </label>
            <p class="mt-1 text-xs text-gray-500">Mark this label as requiring certification. Turning this off retains existing restaurant submissions and reviews.</p>
            @error('is_certification_required')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <a href="{{ route('admin.dietarymanagment.labels.index') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-primary">{{ $label->exists ? 'Update Label' : 'Create Label' }}</button>
        </div>
    </form>
@endsection
