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
            <textarea name="content" id="content" editor="false" data-height="800" class="input-field email-template-source"
                rows="24" spellcheck="false" required>{{ old('content', $template->content ?? '') }}</textarea>

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

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.21/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.21/theme/material-darker.min.css">
<style>
    .email-template-source { width: 100%; min-height: 420px; padding: 14px; border: 1px solid #cbd5df; border-radius: 9px; background: #111820; color: #edf4f7; font: 13px/1.6 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
    .email-template-source:focus { border-color: #00a86b; outline: 3px solid rgba(0, 168, 107, .12); }
    .email-template-editor { overflow: hidden; border: 1px solid #cbd5df; border-radius: 10px; }
    .email-template-editor .CodeMirror { height: 520px; font: 13px/1.6 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
    .email-template-editor .CodeMirror-focused { box-shadow: inset 0 0 0 2px #00a86b; }
    .email-template-editor .CodeMirror-gutters { border-right: 1px solid #33414a; }
    @media (max-width: 700px) { .email-template-editor .CodeMirror { height: 420px; } }
</style>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.21/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.21/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.21/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.21/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.21/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.21/addon/edit/closetag.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.21/addon/edit/matchbrackets.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const textarea = document.getElementById('content');
        const form = document.getElementById('templateForm');
        if (!textarea || !form || typeof CodeMirror === 'undefined') return;

        const editor = CodeMirror.fromTextArea(textarea, {
            mode: 'htmlmixed',
            theme: 'material-darker',
            lineNumbers: true,
            lineWrapping: true,
            indentUnit: 4,
            tabSize: 4,
            indentWithTabs: false,
            autoCloseTags: true,
            matchBrackets: true,
            viewportMargin: 20,
            extraKeys: { 'Ctrl-S': () => form.requestSubmit(), 'Cmd-S': () => form.requestSubmit() },
        });
        editor.getWrapperElement().classList.add('email-template-editor');
        editor.on('change', () => editor.save());
        form.addEventListener('submit', () => editor.save(), true);
        editor.refresh();
    });
</script>
@endsection
