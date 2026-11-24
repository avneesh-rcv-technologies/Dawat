@extends('backend::layouts.app')

@section('title', 'Email Templates')

@section('breadcrumbs')
@php
  $title = isset($template) ? 'Update Template' : 'Create Template';
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index') ],
    ['label' => 'Mail Templates', 'url' => route('notification.email.templates.index') ],
    ['label' => $title, 'url' => null ],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')

    <form id="templateForm" class="form-ajax" method="POST"
        action="@isset($template) {{ route('notification.email.templates.update', $template->id) }} @else {{ route('notification.email.templates.store') }} @endisset">
        @csrf
        @isset($template) @method('PUT') @endisset

        <!-- Name Field -->
        <div class="mb-2">
            <label for="name" class="custom-label">Name </label>
            <input type="text" name="name" id="name" value="{{ $template->name ?? old('name') }}" class="input-field"
                required>

            <input type="hidden" name="id" value="{{$template->id ?? 0}}">
        </div>

        <!-- subject Field -->
        <div class="mb-2">
            <label for="subject" class="custom-label">Subject </label>
            <input type="text" name="subject" id="subject" value="{{ $template->subject ?? old('subject') }}"
                class="input-field" required>

        </div>

        <!-- Content Field -->
        <div class="mb-2">
            <label for="content" class="custom-label">Content</label>
            <textarea name="content" id="content" editor="true" data-editor-mode="email" data-height="800" class="input-field"
                rows="24">{{ old('content', $template->content ?? '') }}</textarea>

        </div>

        <!-- Status Field -->
        <div class="mb-2">
            <label class="custom-label">Status</label>
            <select name="status" id="status" class="input-field">
                <option value="1" @selected(($template->status ?? old('status', true)) == true)>Active</option>
                <option value="0" @selected(($template->status ?? old('status', true)) == false)>Inactive</option>
            </select>

        </div>

        <div class="mt-8 flex justify-end space-x-3">
            <x-button type="submit" class="btn-primary" label="Save" icon='' name='button' />
        </div>
    </form>
@endsection
