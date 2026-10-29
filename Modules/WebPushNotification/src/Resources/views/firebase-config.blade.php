@extends('backend::layouts.app')

@section('title', 'Settings | Firebase Configuration')

@section('breadcrumbs')
    @php
        $title = 'Firebase Configuration';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'System Settings', 'url' => route('settings.index')],
            ['label' => $title, 'url' => null],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('styles')
<style>
    .firebase-config-page { --firebase-ink: #103647; --firebase-muted: #667987; --firebase-border: #dce5ea; }
    .firebase-config-card { overflow: hidden; border: 1px solid var(--firebase-border); border-radius: 14px; background: #fff; box-shadow: 0 4px 16px rgba(16, 54, 71, .06); }
    .firebase-config-header { display: flex; align-items: center; gap: 14px; padding: 22px 24px; background: linear-gradient(135deg, #fbfdfd, #f5f9fa); }
    .firebase-config-header-icon { display: grid; place-items: center; width: 44px; height: 44px; flex: 0 0 44px; border-radius: 12px; background: #e8f8f1; color: #008b5d; font-size: 23px; }
    .firebase-config-header h3 { margin: 0; color: var(--firebase-ink); font-size: 17px; font-weight: 700; }
    .firebase-config-header p { margin-top: 4px; color: var(--firebase-muted); font-size: 12px; }
    .firebase-config-tabs { gap: 24px; padding-inline: 24px; background: #fff; }
    .firebase-config-tabs .tab-btn { position: relative; min-height: 48px; padding: 0 3px; border: 0; border-bottom: 2px solid transparent; background: transparent; font-size: 13px; font-weight: 650; cursor: pointer; transition: color .15s ease, border-color .15s ease; }
    .firebase-config-tabs .tab-btn-active { border-bottom-color: #00a86b; color: #087b57; }
    .firebase-config-tabs .tab-btn-inactive { color: #667987; }
    .firebase-config-tabs .tab-btn-inactive:hover { border-bottom-color: #c6d8d1; color: #294653; }
    .firebase-config-tabs .tab-btn:focus-visible { outline: 3px solid rgba(0, 168, 107, .18); outline-offset: 2px; }
    .firebase-config-form { padding: 24px; }
    .firebase-config-form .tab-section { display: flex; flex-direction: column; gap: 0; }
    .firebase-config-form .tab-section.hidden { display: none !important; }
    .firebase-config-form .tab-section > * + * { margin-top: 0; }
    .firebase-config-form .tab-section > h3 { margin: 0 0 10px; color: var(--firebase-ink); font-size: 15px; font-weight: 700; }
    .firebase-config-form .tab-section > .grid { grid-template-columns: minmax(190px, .8fr) minmax(0, 2fr); align-items: start; gap: 12px 24px; padding: 14px 0; border-bottom: 1px solid #edf1f3; }
    .firebase-config-form .tab-section > .grid > div { grid-column: auto; }
    .firebase-config-form .tab-section > .grid:last-child { border-bottom: 0; }
    .firebase-config-form .custom-label { display: block; margin: 2px 0 0; color: #294653; font-size: 13px; font-weight: 650; }
    .firebase-config-form .tab-section > .grid p { margin-top: 5px; color: var(--firebase-muted); font-size: 11px; line-height: 1.5; }
    .firebase-config-form .input-field { width: 100%; min-height: 42px; border: 1px solid #cbd7de; border-radius: 8px; background: #fff; color: var(--firebase-ink); }
    .firebase-config-form .input-field:focus { border-color: #00a86b; outline: 3px solid rgba(0, 168, 107, .12); }
    .firebase-config-form .firebase-json-field { min-height: 190px; padding: 12px; background: #f8fafb; font: 12px/1.6 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; resize: vertical; }
    .firebase-config-form > .mt-8 { margin-top: 22px; padding-top: 18px; border-top: 1px solid #e8eef1; }
    .firebase-config-form > .mt-8 button { min-width: 120px; min-height: 42px; border: 0; border-radius: 8px; background: #101820; font-weight: 650; transition: background .15s ease, transform .15s ease; }
    .firebase-config-form > .mt-8 button:hover { background: #087b57; transform: translateY(-1px); }
    .firebase-test-actions { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 24px; border-top: 1px solid var(--firebase-border); background: #fbfcfd; }
    .firebase-test-actions-copy strong { display: block; color: var(--firebase-ink); font-size: 13px; }
    .firebase-test-actions-copy span { color: var(--firebase-muted); font-size: 11px; }
    #sendTestNotification { flex: 0 0 auto; min-height: 40px; border-radius: 8px; background: #087b57; font-weight: 650; }
    #sendTestNotification:hover { background: #066b4b; }
    #sendTestNotification:disabled { cursor: wait; opacity: .65; }
    #testNotificationStatus { margin: 0 24px 18px; }
    @media (max-width: 700px) {
        .firebase-config-header { align-items: flex-start; padding: 18px; }
        .firebase-config-form { padding: 16px; }
        .firebase-config-form .tab-section > .grid { grid-template-columns: 1fr; gap: 8px; padding: 13px 0; }
        .firebase-config-tabs { padding-inline: 18px; }
        .firebase-test-actions { align-items: stretch; flex-direction: column; padding: 16px 18px; }
        #sendTestNotification { width: 100%; justify-content: center; }
        #testNotificationStatus { margin-inline: 18px; }
    }
</style>
@endsection

@section('content')
<div class="firebase-config-page">
<div class="firebase-config-card">

    <!-- Header -->
    <div class="firebase-config-header px-6 py-5 border-b border-gray-200">
        <div class="firebase-config-header-icon" aria-hidden="true">✦</div>
        <div>
            <h3 class="text-lg font-medium text-gray-900">Firebase Configuration</h3>
            <p class="mt-1 text-sm text-gray-500">Configure the credentials used by web and iOS push notifications.</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="firebase-config-tabs -mb-px flex space-x-8 px-6" role="tablist" aria-label="Firebase platforms">
            <button type="button" class="tab-btn tab-btn-active" data-tab="web" id="tab-button-web" role="tab" aria-controls="tab-web" aria-selected="true">Web</button>
            <button type="button" class="tab-btn tab-btn-inactive" data-tab="ios" id="tab-button-ios" role="tab" aria-controls="tab-ios" aria-selected="false">iOS</button>
        </nav>
    </div>

    <form method="POST" action="{{ route('firebase.update', ['firebase' => 1]) }}" class="firebase-config-form px-6 py-6">

        @csrf
        @method('PUT')

        <div class="space-y-6">

            <!-- ------------------------------------------------------------------ -->
            <!-- 🔵 TAB 1: WEB FIREBASE CONFIG ----------------------------------- -->
            <!-- ------------------------------------------------------------------ -->
                <div id="tab-web" class="tab-section space-y-4" role="tabpanel" aria-labelledby="tab-button-web">

                <!-- API Key -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <label for="api_key" class="custom-label">API Key</label>
                        <p class="mt-1 text-sm text-gray-500">Your Firebase Web API Key</p>
                    </div>
                    <div class="md:col-span-2">
                        <input id="api_key" type="text" class="input-field" name="api_key"
                            value="{{ old('api_key', $config['api_key']) }}">
                    </div>
                </div>

                <!-- Auth Domain -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <label for="auth_domain" class="custom-label">Auth Domain</label>
                        <p class="text-sm text-gray-500">Firebase Auth Domain</p>
                    </div>
                    <div class="md:col-span-2">
                        <input id="auth_domain" type="text" class="input-field" name="auth_domain"
                            value="{{ old('auth_domain', $config['auth_domain']) }}">
                    </div>
                </div>

                <!-- Project ID -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1"><label for="project_id" class="custom-label">Project ID</label></div>
                    <div class="md:col-span-2">
                        <input id="project_id" type="text" class="input-field" name="project_id"
                            value="{{ old('project_id', $config['project_id']) }}">
                    </div>
                </div>

                <!-- Storage Bucket -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1"><label for="storage_bucket" class="custom-label">Storage Bucket</label></div>
                    <div class="md:col-span-2">
                        <input id="storage_bucket" type="text" class="input-field" name="storage_bucket"
                            value="{{ old('storage_bucket', $config['storage_bucket']) }}">
                    </div>
                </div>

                <!-- Messaging Sender ID -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1"><label for="messaging_sender_id" class="custom-label">Messaging Sender ID</label></div>
                    <div class="md:col-span-2">
                        <input id="messaging_sender_id" type="text" class="input-field" name="messaging_sender_id"
                            value="{{ old('messaging_sender_id', $config['messaging_sender_id']) }}">
                    </div>
                </div>

                <!-- App ID -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1"><label for="app_id" class="custom-label">App ID</label></div>
                    <div class="md:col-span-2">
                        <input id="app_id" type="text" class="input-field" name="app_id"
                            value="{{ old('app_id', $config['app_id']) }}">
                    </div>
                </div>

                <!-- VAPID Key -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1"><label for="vapid_key" class="custom-label">VAPID Key</label></div>
                    <div class="md:col-span-2">
                        <input id="vapid_key" type="text" class="input-field" name="vapid_key"
                            value="{{ old('vapid_key', $config['vapid_key']) }}">
                    </div>
                </div>

                <!-- Service Account JSON -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div><label for="service_account_json" class="custom-label">Service Account JSON</label><p class="firebase-field-help">Paste the service account JSON used to authenticate server-side push delivery.</p></div>
                    <div class="md:col-span-2">
                        <textarea id="service_account_json" name="service_account_json" rows="8" spellcheck="false" class="input-field firebase-json-field">{{ old('service_account_json', fn_get_setting('firebase_service_account_json')) }}</textarea>
                    </div>
                </div>

                <!-- Measurement ID -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div><label for="measurement_id" class="custom-label">Measurement ID</label></div>
                    <div class="md:col-span-2">
                        <input id="measurement_id" type="text" class="input-field" name="measurement_id"
                            value="{{ old('measurement_id', $config['measurement_id']) }}">
                    </div>
                </div>

            </div>

            <!-- ------------------------------------------------------------------ -->
            <!-- 🔴 TAB 3: IOS FIREBASE CONFIG ------------------------------------ -->
            <!-- ------------------------------------------------------------------ -->
            <div id="tab-ios" class="tab-section hidden space-y-4" role="tabpanel" aria-labelledby="tab-button-ios" aria-hidden="true">

                <h3 class="text-lg font-medium text-gray-900 mb-4">iOS Firebase Configuration</h3>

                @php
                    $ios = [
                        'app_id' => fn_get_setting('ios_app_id'),
                        'api_key' => fn_get_setting('ios_api_key'),
                        'bundle_id' => fn_get_setting('ios_bundle_id'),
                        'sender_id' => fn_get_setting('ios_messaging_sender_id'),
                    ];
                @endphp

                <!-- iOS App ID -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div><label for="ios_app_id" class="custom-label">iOS App ID</label></div>
                    <div class="md:col-span-2">
                        <input id="ios_app_id" type="text" class="input-field" name="ios_app_id"
                            value="{{ old('ios_app_id', $ios['app_id']) }}">
                    </div>
                </div>

                <!-- iOS API Key -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div><label for="ios_api_key" class="custom-label">iOS API Key</label></div>
                    <div class="md:col-span-2">
                        <input id="ios_api_key" type="text" class="input-field" name="ios_api_key"
                            value="{{ old('ios_api_key', $ios['api_key']) }}">
                    </div>
                </div>

                <!-- iOS Bundle ID -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div><label for="ios_bundle_id" class="custom-label">Bundle ID</label></div>
                    <div class="md:col-span-2">
                        <input id="ios_bundle_id" type="text" class="input-field" name="ios_bundle_id"
                            value="{{ old('ios_bundle_id', $ios['bundle_id']) }}">
                    </div>
                </div>

                <!-- iOS Sender ID -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div><label for="ios_messaging_sender_id" class="custom-label">Messaging Sender ID</label></div>
                    <div class="md:col-span-2">
                        <input id="ios_messaging_sender_id" type="text" class="input-field" name="ios_messaging_sender_id"
                            value="{{ old('ios_messaging_sender_id', $ios['sender_id']) }}">
                    </div>
                </div>

            </div>

        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit"
                class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                Save
            </button>
        </div>

    </form>

    <div id="testNotificationStatus" class="hidden" role="status" aria-live="polite">
        <div class="rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg id="testNotificationIcon" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <!-- Default to hidden checkmark, will be shown on success -->
                        <path id="successIcon" fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                            clip-rule="evenodd" class="hidden text-green-500" />
                        <!-- Default to hidden X mark, will be shown on error -->
                        <path id="errorIcon" fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                            clip-rule="evenodd" class="hidden text-red-500" />
                        <!-- Default to spinner -->
                        <path id="loadingIcon" class="text-blue-500" fill="currentColor"
                            d="M10 3.5a6.5 6.5 0 100 13 6.5 6.5 0 000-13zM2 10a8 8 0 1114.32 4.906.75.75 0 011.06 1.06A9.5 9.5 0 1010 19.5a9.5 9.5 0 007.38-15.44.75.75 0 01-1.06-1.06A8 8 0 012 10z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p id="testNotificationMessage" class="text-sm font-medium"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="firebase-test-actions">
        <div class="firebase-test-actions-copy">
            <strong>Test push delivery</strong>
            <span>Send a test notification using the current Firebase configuration.</span>
        </div>
    <button type="button" id="sendTestNotification"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
            aria-hidden="true">
            <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                clip-rule="evenodd" />
        </svg>
        Send Test Notification
    </button>
    </div>

</div>
</div>


<!-- TAB SCRIPT -->
<script>
    document.querySelectorAll(".tab-btn").forEach(btn => {
        btn.addEventListener("click", function() {

            let tab = this.dataset.tab;

            document.querySelectorAll(".tab-btn").forEach(b => {
                b.classList.remove("tab-btn-active");
                b.classList.add("tab-btn-inactive");
                b.setAttribute('aria-selected', 'false');
            });

            this.classList.remove("tab-btn-inactive");
            this.classList.add("tab-btn-active");
            this.setAttribute('aria-selected', 'true');

            document.querySelectorAll(".tab-section").forEach(s => {
                s.classList.add("hidden");
                s.setAttribute('aria-hidden', 'true');
            });
            const activePanel = document.getElementById("tab-" + tab);
            activePanel.classList.remove("hidden");
            activePanel.setAttribute('aria-hidden', 'false');
        });
    });
</script>

@endsection


@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sendTestBtn = document.getElementById('sendTestNotification');
        const statusDiv = document.getElementById('testNotificationStatus');
        const statusMessage = document.getElementById('testNotificationMessage');
        const successIcon = document.getElementById('successIcon');
        const errorIcon = document.getElementById('errorIcon');
        const loadingIcon = document.getElementById('loadingIcon');

        if (sendTestBtn) {
            sendTestBtn.addEventListener('click', function() {
                // Show loading state
                statusDiv.classList.remove('hidden');
                successIcon.classList.add('hidden');
                errorIcon.classList.add('hidden');
                loadingIcon.classList.remove('hidden');
                statusMessage.textContent = 'Sending test notification...';
                statusDiv.className = 'mt-4';

                // Disable button to prevent multiple clicks
                sendTestBtn.disabled = true;

                // Send AJAX request
                fetch('{{ route('firebase.send-test-notification') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Show success state
                        loadingIcon.classList.add('hidden');
                        if (data.success) {
                            successIcon.classList.remove('hidden');
                            statusMessage.textContent = data.message ||
                                'Test notification sent successfully!';
                            statusDiv.className = 'mt-4 bg-green-50 rounded-md p-4';
                        } else {
                            errorIcon.classList.remove('hidden');
                            statusMessage.textContent = data.message ||
                                'Failed to send test notification.';
                            statusDiv.className = 'mt-4 bg-red-50 rounded-md p-4';
                            console.error('Test notification error:', data);
                        }
                    })
                    .catch(error => {
                        // Show error state
                        console.error('Error:', error);
                        loadingIcon.classList.add('hidden');
                        errorIcon.classList.remove('hidden');
                        statusMessage.textContent =
                            'An error occurred while sending the test notification.';
                        statusDiv.className = 'mt-4 bg-red-50 rounded-md p-4';
                    })
                    .finally(() => {
                        // Re-enable button
                        sendTestBtn.disabled = false;

                        // Hide the status after 5 seconds
                        setTimeout(() => {
                            statusDiv.classList.add('hidden');
                        }, 5000);
                    });
            });
        }
    });
</script>
@endsection
