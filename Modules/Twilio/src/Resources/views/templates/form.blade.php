
@extends('backend::layouts.app')

@section('title', isset($template) ? 'Edit SMS Template' : 'Create SMS Template')

@section('breadcrumbs')
@php
$title = 'SMS Templates';
$breadcrumbs = [
['label' => 'Dashboard', 'url' => route('dashboard.index')],
['label' => $title],
];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')

<div class="sms-template-page">
<div class="sms-template-heading">
    <div>
        <p class="sms-template-eyebrow">Messaging</p>
        <h1>{{ isset($template) ? 'Edit SMS template' : 'Create SMS template' }}</h1>
        <p>Manage the message content and variables used by SMS notifications.</p>
    </div>
    <a href="{{ route('sms-templates.index') }}" class="sms-template-back">← SMS templates</a>
</div>

<form id="templateForm" class="form-ajax sms-template-form" method="POST"
    action="@isset($template) {{ route('sms-templates.update', $template->id) }} @else {{ route('sms-templates.store') }} @endisset">
    @csrf
    @isset($template) @method('PUT') @endisset

    <div class="sms-template-fields">
    <div class="sms-template-field">
        <label class="custom-label" for="name">Template name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $template->name ?? '') }}" class="input-field" placeholder="For example: OTP Verification SMS">
        <p class="sms-template-help">A recognizable name for admins. It is not shown to recipients.</p>
    </div>

    <div class="sms-template-field">
        <label class="custom-label" for="subject">Subject</label>
        <input type="text" name="subject" id="subject" value="{{ old('subject', $template->subject ?? '') }}" class="input-field" placeholder="Optional message subject">
        <p class="sms-template-help">Used by integrations that support a message subject.</p>
    </div>

    <div class="sms-template-field sms-template-field-wide">
        <label class="custom-label" for="body">Message body</label>
        <textarea name="body" id="body" editor="false" data-height="400" class="input-field" rows="5" placeholder="Write the SMS message sent to recipients">{{ old('body', $template->content ?? '') }}</textarea>
        <p class="sms-template-help">Use the variables supported by your notification, wrapped in double curly braces.</p>
    </div>

    <div class="sms-template-field">
        <label class="custom-label" for="variables">Variables (JSON)</label>
        <textarea name="variables" id="variables" class="input-field sms-template-json" rows="6" spellcheck="false" placeholder='{"otp_code": "123456", "expiry_minutes": 10}'>{{ old('variables', isset($template) && $template->variables !== null ? json_encode($template->variables, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
        <p class="sms-template-help">Optional JSON data for the template. Leave blank if no extra variables are needed.</p>
    </div>

    <div class="sms-template-field">
        <label class="custom-label" for="is_active">Status</label>
        <select name="is_active" id="is_active" class="input-field">
            <option value="1" @selected(old('is_active', $template->is_active ?? true) == true)>Active</option>
            <option value="0" @selected(old('is_active', $template->is_active ?? true) == false)>Inactive</option>
        </select>
        <p class="sms-template-help">Inactive templates are not used for sending messages.</p>
    </div>
    </div>

    <div class="sms-template-actions">
        <x-button type="submit"  class="btn-primary"  label="Save" icon='' name='button' />
    </div>
</form>
</div>

@endsection

@section('styles')
<style>
    .sms-template-page { --sms-ink: #103647; --sms-muted: #667987; --sms-border: #dce5ea; }
    .sms-template-heading { display: flex; align-items: center; justify-content: space-between; gap: 22px; margin-bottom: 20px; }
    .sms-template-heading h1 { margin: 2px 0 6px; color: var(--sms-ink); font-size: 25px; font-weight: 700; }
    .sms-template-heading p { margin: 0; color: var(--sms-muted); font-size: 13px; }
    .sms-template-heading .sms-template-eyebrow { color: #087b57; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .sms-template-back { flex-shrink: 0; padding: 10px 14px; border: 1px solid var(--sms-border); border-radius: 9px; color: var(--sms-ink); font-size: 13px; font-weight: 600; text-decoration: none; }
    .sms-template-back:hover { background: #f5f9fa; border-color: #b7cbd3; }
    .sms-template-form { padding: 24px; border: 1px solid var(--sms-border); border-radius: 14px; background: #fff; box-shadow: 0 4px 16px rgba(16, 54, 71, .06); }
    .sms-template-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
    .sms-template-field { min-width: 0; }
    .sms-template-field-wide { grid-column: 1 / -1; }
    .sms-template-field .custom-label { display: block; margin: 0 0 7px; color: #294653; font-size: 13px; font-weight: 700; }
    .sms-template-field .input-field { width: 100%; min-height: 42px; border: 1px solid #cbd7de; border-radius: 8px; background: #fff; color: var(--sms-ink); box-shadow: 0 1px 2px rgba(16, 54, 71, .03); }
    .sms-template-field .input-field:focus { border-color: #00a86b; outline: 3px solid rgba(0, 168, 107, .12); }
    .sms-template-field textarea.input-field { min-height: 132px; padding: 12px; line-height: 1.55; resize: vertical; }
    .sms-template-field .sms-template-json { min-height: 174px; background: #f8fafb; font: 13px/1.6 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
    .sms-template-help { margin: 6px 0 0; color: var(--sms-muted); font-size: 11px; line-height: 1.5; }
    .sms-template-actions { display: flex; justify-content: flex-end; margin-top: 24px; padding-top: 18px; border-top: 1px solid #e8eef1; }
    .sms-template-actions .btn-primary { min-width: 112px; min-height: 42px; border-radius: 8px; font-weight: 650; }
    @media (max-width: 700px) {
        .sms-template-heading { align-items: stretch; flex-direction: column; }
        .sms-template-back { align-self: flex-start; }
        .sms-template-form { padding: 16px; }
        .sms-template-fields { grid-template-columns: 1fr; gap: 16px; }
        .sms-template-field-wide { grid-column: auto; }
        .sms-template-actions .btn-primary { width: 100%; }
    }
</style>
@endsection
