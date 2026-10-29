@extends('backend::layouts.app')

@section('title', isset($template) ? 'Edit Push Notification Template' : 'Create Push Notification Template')

@section('breadcrumbs')
@php
  $title = isset($template) ? 'Edit Push Notification Template' : 'Create Push Notification Template';
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index') ],
    ['label' => 'Push Notifications Templates', 'url' => route('push-notification-template.index') ],
    ['label' => $title, 'url' => null ],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
<div class="push-template-page">
<div class="push-template-heading">
    <div>
        <p class="push-template-eyebrow">Messaging</p>
        <h1>{{ isset($template) ? 'Edit push notification template' : 'Create push notification template' }}</h1>
        <p>Set the title, message, and variables used in push notifications.</p>
    </div>
    <a href="{{ route('push-notification-template.index') }}" class="push-template-back">← Push templates</a>
</div>

<form id="templateForm" class="form-ajax push-template-form" method="POST"
    action="@isset($template) {{ route('push-notification-template.update', $template->id) }} @else {{ route('push-notification-template.store') }} @endisset">
    @csrf
    @isset($template) @method('PUT') @endisset

    <div class="push-template-fields">
    <div class="push-template-field">
        <label class="custom-label" for="name">Template name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $template->name ?? '') }}" class="input-field" placeholder="For example: Order update">
        <p class="push-template-help">A recognizable name for admins. It is not shown to recipients.</p>
    </div>

    <div class="push-template-field">
        <label class="custom-label" for="subject">Notification title</label>
        <input type="text" name="subject" id="subject" value="{{ old('subject', $template->subject ?? '') }}" class="input-field" placeholder="Title shown in the notification">
        <p class="push-template-help">Keep the title short so it reads well on mobile devices.</p>
    </div>

    <div class="push-template-field push-template-field-wide">
        <label class="custom-label" for="body">Notification message</label>
        <textarea name="body" id="body" editor="false" data-height="400" class="input-field" rows="5" placeholder="Write the message shown to recipients">{{ old('body', $template->body ?? '') }}</textarea>
        <p class="push-template-help">Use the variables supported by this notification, wrapped in double curly braces.</p>
    </div>

    <div class="push-template-field">
        <label class="custom-label" for="variables">Variables (JSON)</label>
        <textarea name="variables" id="variables" class="input-field push-template-json" rows="6" spellcheck="false" placeholder='{"order_number": "12345", "status": "on the way"}'>{{ old('variables', isset($template) && $template->variables !== null ? json_encode($template->variables, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
        <p class="push-template-help">Optional JSON data for your notification. Leave blank when no extra variables are needed.</p>
    </div>

    <div class="push-template-field">
        <label class="custom-label" for="is_active">Status</label>
        <select name="is_active" id="is_active" class="input-field">
            <option value="1" @selected(old('is_active', $template->is_active ?? true) == true)>Active</option>
            <option value="0" @selected(old('is_active', $template->is_active ?? true) == false)>Inactive</option>
        </select>
        <p class="push-template-help">Inactive templates are not sent to users.</p>
    </div>
    </div>

    <div class="push-template-actions">
        <x-button type="submit"  class="btn-primary"  label="Save" icon='' name='button' />
    </div>
</form>
</div>
@endsection

@section('styles')
<style>
    .push-template-page { --push-ink: #103647; --push-muted: #667987; --push-border: #dce5ea; }
    .push-template-heading { display: flex; align-items: center; justify-content: space-between; gap: 22px; margin-bottom: 20px; }
    .push-template-heading h1 { margin: 2px 0 6px; color: var(--push-ink); font-size: 25px; font-weight: 700; }
    .push-template-heading p { margin: 0; color: var(--push-muted); font-size: 13px; }
    .push-template-heading .push-template-eyebrow { color: #087b57; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .push-template-back { flex-shrink: 0; padding: 10px 14px; border: 1px solid var(--push-border); border-radius: 9px; color: var(--push-ink); font-size: 13px; font-weight: 600; text-decoration: none; }
    .push-template-back:hover { background: #f5f9fa; border-color: #b7cbd3; }
    .push-template-form { padding: 24px; border: 1px solid var(--push-border); border-radius: 14px; background: #fff; box-shadow: 0 4px 16px rgba(16, 54, 71, .06); }
    .push-template-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
    .push-template-field { min-width: 0; }
    .push-template-field-wide { grid-column: 1 / -1; }
    .push-template-field .custom-label { display: block; margin: 0 0 7px; color: #294653; font-size: 13px; font-weight: 700; }
    .push-template-field .input-field { width: 100%; min-height: 42px; border: 1px solid #cbd7de; border-radius: 8px; background: #fff; color: var(--push-ink); box-shadow: 0 1px 2px rgba(16, 54, 71, .03); }
    .push-template-field .input-field:focus { border-color: #00a86b; outline: 3px solid rgba(0, 168, 107, .12); }
    .push-template-field textarea.input-field { min-height: 132px; padding: 12px; line-height: 1.55; resize: vertical; }
    .push-template-field .push-template-json { min-height: 174px; background: #f8fafb; font: 13px/1.6 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
    .push-template-help { margin: 6px 0 0; color: var(--push-muted); font-size: 11px; line-height: 1.5; }
    .push-template-actions { display: flex; justify-content: flex-end; margin-top: 24px; padding-top: 18px; border-top: 1px solid #e8eef1; }
    .push-template-actions .btn-primary { min-width: 112px; min-height: 42px; border-radius: 8px; font-weight: 650; }
    @media (max-width: 700px) {
        .push-template-heading { align-items: stretch; flex-direction: column; }
        .push-template-back { align-self: flex-start; }
        .push-template-form { padding: 16px; }
        .push-template-fields { grid-template-columns: 1fr; gap: 16px; }
        .push-template-field-wide { grid-column: auto; }
        .push-template-actions .btn-primary { width: 100%; }
    }
</style>
@endsection
