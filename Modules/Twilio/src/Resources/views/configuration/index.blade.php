@extends('backend::layouts.app')

@section('title', 'Twillo Configuration')

@section('breadcrumbs')
@php
$title = 'Twilio Configuration';
$breadcrumbs = [
['label' => 'Dashboard', 'url' => route('dashboard.index')],
['label' => $title],
];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
{{-- @include('generalsetting::components.top-tabs') --}}
<form action="{{ route('settings.store') }}" class="form-ajax mt-4 space-y-4" method="POST">
    @csrf
    {{-- ============================================ --}}
    {{-- SECTION 1: Twilio Account Credentials --}}
    {{-- ============================================ --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-800">
                Twilio Account Credentials
            </h2>
            <p class="mt-1 text-sm text-gray-500">Configure your Twilio account details for SMS and phone masking</p>
        </div>
        <div class="px-6 py-6 space-y-6">
            {{-- Account SID --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1">
                    <label class="custom-label">Account SID</label>
                    <p class="mt-1 text-sm text-gray-500">Your Twilio Account SID (starts with AC...)</p>
                </div>
                <div class="md:col-span-2">
                    <input name="settings[twilio.sid]" type="text"
                        value="{{ old('twilio.sid', fn_get_setting('twilio.sid')) }}"
                        placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" class="input-field">
                </div>
            </div>
            {{-- Auth Token --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label class="custom-label">Auth Token</label>
                    <p class="mt-1 text-sm text-gray-500">Your Twilio Auth Token (keep this secret)</p>
                </div>
                <div class="md:col-span-2">
                    <input name="settings[twilio.token]" type="password"
                        value="{{ old('twilio.token', fn_get_setting('twilio.token')) }}"
                        placeholder="••••••••••••••••••••••••••••••••" class="input-field">
                </div>
            </div>
            {{-- From Phone Number --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label class="custom-label">SMS From Number</label>
                    <p class="mt-1 text-sm text-gray-500">Twilio phone number used to send SMS (E.164 format)</p>
                </div>
                <div class="md:col-span-2">
                    <input name="settings[twilio.phone]" type="text"
                        value="{{ old('twilio.phone', fn_get_setting('twilio.phone')) }}" placeholder="+1234567890"
                        class="input-field">
                </div>
            </div>
        </div>
    </div>
    {{-- ============================================ --}}
    {{-- SECTION 2: Phone Masking (Proxy) Settings --}}
    {{-- ============================================ --}}
    <!--
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-800">
                Phone Number Masking (Twilio Proxy)
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Protect driver & member privacy by masking real phone numbers during trips
            </p>
        </div>
        <div class="px-6 py-6 space-y-6">
            {{-- Enable/Disable Proxy --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1">
                    <label class="custom-label">Enable Phone Masking</label>
                    <p class="mt-1 text-sm text-gray-500">When enabled, drivers and members see proxy numbers instead of
                        real phone numbers during trips</p>
                </div>
                <div class="md:col-span-2">
                    <select name="settings[twilio.proxy_enabled]" class="input-field">
                        @php
                        $proxyEnabled = old(
                        'twilio.proxy_enabled',
                        fn_get_setting('twilio.proxy_enabled', 'false'),
                        );
                        @endphp
                        <option value="false" {{ $proxyEnabled=='false' ? 'selected' : '' }}>Disabled</option>
                        <option value="true" {{ $proxyEnabled=='true' ? 'selected' : '' }}>Enabled</option>
                    </select>
                </div>
            </div>
            {{-- Proxy Service SID --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label class="custom-label">Proxy Service SID</label>
                    <p class="mt-1 text-sm text-gray-500">Your Twilio Proxy Service SID (starts with KS...). Create one
                        in Twilio Console → Proxy → Services</p>
                </div>
                <div class="md:col-span-2">
                    <input name="settings[twilio.proxy_service_sid]" type="text"
                        value="{{ old('twilio.proxy_service_sid', fn_get_setting('twilio.proxy_service_sid')) }}"
                        placeholder="KSxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" class="input-field">
                </div>
            </div>
            {{-- Session TTL --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label class="custom-label">Session Duration (seconds)</label>
                    <p class="mt-1 text-sm text-gray-500">How long proxy numbers stay active after trip assignment.
                        Default: 28800 (8 hours)</p>
                </div>
                <div class="md:col-span-2">
                    <input name="settings[twilio.proxy_session_ttl]" type="number"
                        value="{{ old('twilio.proxy_session_ttl', fn_get_setting('twilio.proxy_session_ttl', '28800')) }}"
                        placeholder="28800" min="3600" max="86400" class="input-field">
                </div>
            </div>
        </div>
    </div>
   -->

    {{-- ============================================ --}}


    <div class="flex justify-end pt-6">
        <button type="submit" name="button" class="btn btn-primary">
            Save Changes
        </button>
    </div>
</form>
@endsection