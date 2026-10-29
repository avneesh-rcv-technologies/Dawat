@extends('backend::layouts.app')

@php($designName = ucfirst($key))
@php($breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index')],
    ['label' => 'Email Templates', 'url' => route('notification.email.templates.index')],
    ['label' => 'Edit ' . $designName],
])

@section('title', 'Edit Email ' . $designName)

@section('breadcrumbs')
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Edit Email ' . $designName" />
@endsection

@section('content')
    <div class="email-design-page">
        <div class="email-design-heading">
            <div>
                <p class="email-design-eyebrow">Global email layout</p>
                <h1>Edit {{ $designName }}</h1>
                <p>Update the shared HTML {{ strtolower($designName) }} used in outgoing emails.</p>
            </div>
            <a class="email-design-back" href="{{ route('notification.email.templates.index') }}">← Email templates</a>
        </div>

        @if(session('success'))
            <div class="email-design-alert email-design-alert-success" role="status">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="email-design-alert email-design-alert-error" role="alert">
                <strong>Could not save the email {{ strtolower($designName) }}.</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('notification.email-design.update') }}" class="email-design-form">
            @csrf
            <input type="hidden" name="key" value="{{ $key }}">

            <div class="email-design-toolbar">
                <div>
                    <h2>HTML editor</h2>
                    <p>Changes appear in the preview as you type.</p>
                </div>
                <button type="submit" class="email-design-save"><span aria-hidden="true">✓</span> Save {{ $designName }}</button>
            </div>

            <div class="email-design-workspace">
                <section class="email-design-editor-panel" aria-labelledby="html-editor-label">
                    <div class="email-design-panel-heading">
                        <label id="html-editor-label" for="htmlEditor">HTML source</label>
                        <span>Editable</span>
                    </div>
                    <textarea id="htmlEditor" name="html" spellcheck="false" autocomplete="off" autocapitalize="off" aria-describedby="html-editor-help">{{ old('html', $html) }}</textarea>
                    <p id="html-editor-help" class="email-design-help">Use standard email-safe HTML. Preview scripts and forms are disabled.</p>
                </section>

                <section class="email-design-preview-panel" aria-labelledby="email-preview-label">
                    <div class="email-design-panel-heading">
                        <h2 id="email-preview-label">Live preview</h2>
                        <span class="email-design-preview-state"><i></i> Updates automatically</span>
                    </div>
                    <iframe id="previewFrame" title="Email {{ strtolower($designName) }} preview" sandbox="" loading="lazy"></iframe>
                </section>
            </div>
        </form>
    </div>
@endsection

@section('styles')
<style>
    .email-design-page { --email-ink: #103647; --email-muted: #627785; --email-border: #dce5ea; color: var(--email-ink); }
    .email-design-heading { display: flex; align-items: center; justify-content: space-between; gap: 24px; margin-bottom: 22px; }
    .email-design-heading h1 { margin: 2px 0 6px; color: var(--email-ink); font-size: 25px; font-weight: 700; }
    .email-design-heading p { margin: 0; color: var(--email-muted); }
    .email-design-heading .email-design-eyebrow { color: #087b57; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .email-design-back { flex-shrink: 0; padding: 10px 14px; border: 1px solid var(--email-border); border-radius: 9px; color: var(--email-ink); font-size: 13px; font-weight: 600; text-decoration: none; }
    .email-design-back:hover { background: #f5f9fa; border-color: #b7cbd3; }
    .email-design-form { overflow: hidden; border: 1px solid var(--email-border); border-radius: 14px; background: #fff; box-shadow: 0 4px 16px rgba(16, 54, 71, .06); }
    .email-design-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 20px; border-bottom: 1px solid var(--email-border); background: #fbfcfd; }
    .email-design-toolbar h2 { margin: 0; font-size: 16px; font-weight: 700; }
    .email-design-toolbar p { margin: 4px 0 0; color: var(--email-muted); font-size: 12px; }
    .email-design-save { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 40px; padding: 0 16px; border: 0; border-radius: 8px; background: #101820; color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; transition: background .18s ease, transform .18s ease; }
    .email-design-save:hover { background: #087b57; transform: translateY(-1px); }
    .email-design-workspace { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 18px; padding: 18px; }
    .email-design-editor-panel, .email-design-preview-panel { min-width: 0; overflow: hidden; border: 1px solid var(--email-border); border-radius: 10px; background: #fff; }
    .email-design-panel-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 46px; padding: 0 13px; border-bottom: 1px solid #e7edf0; background: #f8fafb; }
    .email-design-panel-heading label, .email-design-panel-heading h2 { margin: 0; color: #294653; font-size: 12px; font-weight: 700; }
    .email-design-panel-heading > span { color: var(--email-muted); font-size: 11px; }
    .email-design-preview-state { display: inline-flex; align-items: center; gap: 6px; }
    .email-design-preview-state i { width: 7px; height: 7px; border-radius: 50%; background: #00b978; }
    #htmlEditor { display: block; width: 100%; height: min(68vh, 720px); min-height: 420px; resize: vertical; padding: 16px; border: 0; outline: 0; background: #101820; color: #e5edf1; font: 13px/1.65 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; tab-size: 4; }
    #htmlEditor:focus { box-shadow: inset 0 0 0 2px #00b978; }
    .email-design-help { margin: 0; padding: 10px 13px; border-top: 1px solid #e7edf0; color: var(--email-muted); font-size: 11px; }
    #previewFrame { display: block; width: 100%; height: min(68vh, 720px); min-height: 420px; border: 0; background: #fff; }
    .email-design-alert { margin: 0 0 16px; padding: 13px 16px; border: 1px solid; border-radius: 9px; font-size: 13px; }
    .email-design-alert-success { border-color: #a8e6cb; background: #effbf5; color: #087b57; }
    .email-design-alert-error { border-color: #f2c6c4; background: #fff5f4; color: #9f302a; }
    .email-design-alert ul { margin: 7px 0 0; padding-left: 20px; }
    @media (max-width: 900px) { .email-design-workspace { grid-template-columns: 1fr; } #htmlEditor, #previewFrame { height: 440px; min-height: 340px; } }
    @media (max-width: 600px) { .email-design-heading, .email-design-toolbar { align-items: stretch; flex-direction: column; } .email-design-save { width: 100%; } .email-design-workspace { padding: 12px; gap: 12px; } }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editor = document.getElementById('htmlEditor');
        const preview = document.getElementById('previewFrame');
        if (!editor || !preview) return;

        const refreshPreview = () => {
            preview.srcdoc = editor.value || '<!doctype html><html><body style="font:14px Arial,sans-serif;color:#667085;padding:24px">Nothing to preview yet.</body></html>';
        };

        editor.addEventListener('input', refreshPreview);
        refreshPreview();
    });
</script>
@endsection
