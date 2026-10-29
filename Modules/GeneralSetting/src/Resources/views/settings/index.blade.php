@extends('backend::layouts.app')

@section('title', 'Settings | Configuration')

@section('breadcrumbs')
@php
$title = 'Configuration';
$breadcrumbs = [
['label' => 'Dashboard', 'url' => route('dashboard.index')],
['label' => 'System Settings', 'url' => route('settings.index')],
['label' => $title],
];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@push('styles')
<style>
    .configuration-settings { --config-tab-ink: #294653; --config-tab-muted: #667987; --config-tab-accent: #087b57; --config-tab-border: #dce5ea; }
    .configuration-settings .settings-tab-nav {
        display: flex;
        align-items: stretch;
        gap: 6px;
        overflow-x: auto;
        padding: 10px 14px 0;
        border-bottom: 1px solid var(--config-tab-border);
        background: linear-gradient(180deg, #fbfcfd 0%, #f6f9fa 100%);
        scrollbar-width: thin;
        scrollbar-color: #c4d1d8 transparent;
    }
    .configuration-settings .settings-tab-nav .tab-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex: 0 0 auto;
        min-height: 46px;
        margin: 0;
        padding: 0 15px;
        border: 1px solid transparent;
        border-bottom: 0;
        border-radius: 9px 9px 0 0;
        background: transparent;
        color: var(--config-tab-muted);
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        transition: background .16s ease, color .16s ease, border-color .16s ease;
    }
    .configuration-settings .settings-tab-nav .tab-btn::before {
        display: inline-block;
        font-family: 'Material Icons Outlined';
        font-size: 18px;
        font-weight: normal;
        line-height: 1;
        -webkit-font-feature-settings: 'liga';
        -webkit-font-smoothing: antialiased;
    }
    .configuration-settings .settings-tab-nav .tab-btn[data-tab="general"]::before { content: 'settings'; }
    .configuration-settings .settings-tab-nav .tab-btn[data-tab="editor"]::before { content: 'edit_note'; }
    .configuration-settings .settings-tab-nav .tab-btn[data-tab="company"]::before { content: 'store'; }
    .configuration-settings .settings-tab-nav .tab-btn[data-tab="policy-terms"]::before { content: 'policy'; }
    .configuration-settings .settings-tab-nav .tab-btn[data-tab="social-auth"]::before { content: 'hub'; }
    .configuration-settings .settings-tab-nav .tab-btn[data-tab="mail"]::before { content: 'mail'; }
    .configuration-settings .settings-tab-nav .tab-btn:hover { background: #eef4f5; color: var(--config-tab-ink); }
    .configuration-settings .settings-tab-nav .tab-btn.border-indigo-500.text-indigo-600 {
        border-color: var(--config-tab-border);
        background: #fff;
        color: var(--config-tab-accent);
        box-shadow: 0 -2px 7px rgba(16, 54, 71, .035);
    }
    .configuration-settings .settings-tab-nav .tab-btn.border-indigo-500.text-indigo-600::after {
        position: absolute;
        right: 10px;
        bottom: -1px;
        left: 10px;
        height: 2px;
        background: #fff;
        content: '';
    }
    .configuration-settings .settings-tab-nav .tab-btn:focus-visible { z-index: 1; outline: 3px solid rgba(0, 168, 107, .22); outline-offset: 2px; }
    @media (max-width: 640px) {
        .configuration-settings .settings-tab-nav { gap: 3px; padding: 8px 8px 0; }
        .configuration-settings .settings-tab-nav .tab-btn { min-height: 42px; padding-inline: 11px; font-size: 12px; }
    }
</style>
@endpush

@section('content')


<div class="configuration-settings bg-white shadow rounded-lg">
    <!-- Tabs navigation -->
    <div class="border-b">
        <nav class="settings-tab-nav flex -mb-px" aria-label="Tabs">
            <button data-tab="general"
                class="tab-btn inline-flex items-center px-4 py-3 border-b-2 -mb-px border-indigo-500 text-indigo-600 focus:outline-none"
                aria-current="page">General</button>
            <button data-tab="editor"
                class="tab-btn inline-flex items-center px-4 py-3 border-b-2 -mb-px border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">Editor</button>
            <button data-tab="company"
                class="tab-btn inline-flex items-center px-4 py-3 border-b-2 -mb-px border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">Company</button>
            <button data-tab="policy-terms"
                class="tab-btn inline-flex items-center px-4 py-3 border-b-2 -mb-px border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">Policy &amp; Terms</button>
            <button data-tab="social-auth"
                class="tab-btn inline-flex items-center px-4 py-3 border-b-2 -mb-px border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">Social
                Auth</button>
            @hook('generalsetting:tabs.post')
        </nav>
    </div>

    <div class="p-6">
        <form action="{{ route('settings.store') }}" method="POST" class="form-ajax">
            @csrf
            <!-- General Tab Panel -->
            <section id="tab-general" class="tab-panel">
                <div class="header py-2">
                    <h2 class="text-gray-800 text-2xl font-bold">General settings</h2>
                    <p class="text-gray-600">Configure store name, contact information, and basic preferences.</p>
                </div>

                <div class="mt-6">
                    <div class="px-6 py-6 space-y-6">
                        @hook('generalsetting:general.pre')
                        <!-- Timezone -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-1">
                                <label for="timezone" class="custom-label">Timezone</label>
                                <p class="mt-1 text-sm text-gray-500">Set the timezone for your organization.</p>
                            </div>
                            <div class="md:col-span-2">
                                <select id="timezone" name="settings[general.timezone]" class="input-field w-full">
                                    @php
                                    use Modules\Core\Enums\Timezone;
                                    $timezones = Timezone::grouped();
                                    $currentTimezone = old('settings[general.timezone]',
                                    fn_get_setting('general.timezone') ?? 'Asia/Kolkata');
                                    @endphp
                                    @foreach($timezones as $region => $regionTimezones)
                                    <optgroup label="{{ $region }}">
                                        @foreach($regionTimezones as $tzValue => $tzLabel)
                                        <option value="{{ $tzValue }}" {{ $currentTimezone==$tzValue ? 'selected' : ''
                                            }}>
                                            {{ $tzLabel }}
                                        </option>
                                        @endforeach
                                    </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                            <div class="md:col-span-1">
                                <label for="image_driver" class="block text-sm font-medium text-gray-700">Files
                                    Store</label>
                                <p class="mt-1 text-sm text-gray-500">Store Files (s3, local, etc)</p>
                            </div>
                            <div class="md:col-span-2">
                                <select id="image_driver" name="settings[general.image_driver]"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    @foreach (fn_get_upload_driver() as $fileDriver)
                                    <option value="{{ $fileDriver }}" {{ old('general.image_driver',
                                        fn_get_setting('general.image_driver'))==$fileDriver ? 'selected' : '' }}>
                                        {{ $fileDriver }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- Date Format -->
                        <!-- <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t border-gray-200 pt-6">
                            <div class="md:col-span-1">
                                <label class="custom-label">Date Format</label>
                                <p class="mt-1 text-sm text-gray-500">How dates should be displayed throughout the
                                    system.</p>
                            </div>
                            <div class="md:col-span-2">
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input id="date-format-1" name="settings[general.timestamp]" type="radio"
                                            value="MM/DD/YYYY"
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" {{
                                            (old('settings.general.timestamp', fn_get_setting('general.timestamp') ?? ''
                                            )=='MM/DD/YYYY' ) ? 'checked' : '' }}>
                                        <span class="ml-3">MM/DD/YYYY</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input id="date-format-2" name="settings[general.timestamp]" type="radio"
                                            value="DD/MM/YYYY"
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" {{
                                            (old('settings.general.timestamp', fn_get_setting('general.timestamp') ?? ''
                                            )=='DD/MM/YYYY' ) ? 'checked' : '' }}>
                                        <span class="ml-3">DD/MM/YYYY</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input id="date-format-3" name="settings[general.timestamp]" type="radio"
                                            value="YYYY-MM-DD"
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" {{
                                            (old('settings.general.timestamp', fn_get_setting('general.timestamp') ?? ''
                                            )=='YYYY-MM-DD' ) ? 'checked' : '' }}>
                                        <span class="ml-3">YYYY-MM-DD</span>
                                    </label>
                                </div>
                            </div>
                        </div> -->

                        <!-- Language -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t border-gray-200 pt-6">
                            <div class="md:col-span-1">
                                <label for="language" class="block text-sm font-medium text-gray-700">Language</label>
                            </div>
                            <div class="md:col-span-2">
                                <select id="language" name="settings[general.locale]" class="w-full px-3 py-2">
                                    @php
                                    $currentLocale = old('general.locale', fn_get_setting('general.locale'));
                                    @endphp
                                    @foreach($languages as $lang)
                                    <option value="{{ $lang->value }}" {{ $currentLocale==$lang->value ? 'selected' : ''
                                        }}>
                                        {{ $lang->label() }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- Restaurant Search Radius -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t border-gray-200 pt-6">
                            <div class="md:col-span-1">
                                <label for="restaurant_radius" class="custom-label">Restaurant Search Radius</label>
                                <p class="mt-1 text-sm text-gray-500">
                                    Maximum distance (in miles) to show restaurants to users. Leave 0 to disable radius
                                    filtering.
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <div class="flex items-center gap-2">
                                    <input type="number" id="restaurant_radius" min="0" step="1"
                                        name="settings[general.restaurant_radius_miles]"
                                        value="{{ old('general.restaurant_radius_miles', fn_get_setting('general.restaurant_radius_miles', 0)) }}"
                                        class="input-field w-40">
                                    <span class="text-sm text-gray-500">miles</span>
                                </div>
                            </div>
                        </div>

                        {{-- get roles in loop --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-1">
                                <label for="timezone" class="custom-label">Default Restorant</label>
                                
                            </div>
                            <div class="md:col-span-2">
                                <select id="timezone" name="settings[general.default_role]" class="input-field w-full">
                                    @php
                                    use Modules\Acl\Models\Role;
                                    $roles = Role::get();
                                    $currentTimezone = old('settings[general.default_role]',fn_get_setting('general.default_role'));
                                    @endphp
                                    @foreach($roles as $role)                                    
                                        <option value="{{ $role->id }}" {{ $role->id==fn_get_setting('general.default_role') ? 'selected' : ''
                                            }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        

                        <!-- Google Maps API Key -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                            <div class="md:col-span-1">
                                <label for="google_maps_api_key" class="block text-sm font-medium text-gray-700">
                                    Google Maps API Key
                                </label>
                                <p class="mt-1 text-sm text-gray-500">
                                    API key for Google Maps services
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <input type="text" id="google_maps_api_key" name="settings[general.google_maps_api_key]"
                                    value="{{ old('general.google_maps_api_key', fn_get_setting('general.google_maps_api_key', '')) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md font-mono text-sm"
                                    placeholder="AIzaSy...">
                                <p class="mt-2 text-xs text-blue-600">
                                    <strong>Note:</strong> Required for address geocoding and distance calculations.
                                    <a href="https://console.cloud.google.com/google/maps-apis" target="_blank"
                                        class="underline">Get your API key</a>
                                </p>
                            </div>
                        </div>



                        @hook('generalsetting:general.post')
                    </div>

                </div>
            </section>

            <!-- Editor Tab Panel -->
            <section id="tab-editor" class="tab-panel hidden">
                <div class="header py-2">
                    <h2 class="text-gray-800 text-2xl font-bold">Editor Settings</h2>
                    <p class="text-gray-600">Configure your default editor preferences.</p>
                </div>

                <div class="px-6 py-6 space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <label for="editor_type" class="custom-label">Editor</label>
                            <p class="mt-1 text-sm text-gray-500">Set the editor.</p>
                        </div>
                        <div class="md:col-span-2">
                            <select id="editor_type" name="settings[general.editor.type]" class="input-field w-full">
                                @foreach (['ckeditor', 'tinymce'] as $tzLabel)
                                <option value="{{ $tzLabel }}" {{ old('settings[general.editor.type]',
                                    fn_get_setting('general.editor.type'))==$tzLabel ? 'selected' : '' }}>
                                    {{ ucfirst($tzLabel) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t border-gray-200 pt-6">
                        <div class="md:col-span-1">
                            <label for="tiny_api_key" class="custom-label">TinyMCE API Key</label>
                            <p class="mt-1 text-sm text-gray-500">Only needed when editor is set to TinyMCE. CKEditor works without a paid key.</p>
                        </div>
                        <div class="md:col-span-2">
                            <input type="text" id="tiny_api_key" name="settings[general.editor.api_key]"
                                value="{{ old('general.editor.api_key', fn_get_setting('general.editor.api_key')) }}"
                                class="input-field w-full">
                        </div>
                    </div>
                </div>
            </section>

            <!-- Company Tab Panel -->
            <section id="tab-company" class="tab-panel hidden">
                <div class="header py-2">
                    <h2 class="text-gray-800 text-2xl font-bold">Company settings</h2>
                    <p class="text-gray-600">Configure store name, contact information, and basic preferences.</p>
                </div>

                <div class="px-6 py-6 space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                        <div class="md:col-span-1">
                            <label for="company_name" class="custom-label">Company name</label>
                        </div>
                        <div class="md:col-span-2">
                            <input type="text" id="company_name" name="settings[general.company.name]"
                                value="{{ old('general.company.name', fn_get_setting('general.company.name')) }}"
                                class="input-field w-full">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                        <div class="md:col-span-1">
                            <label for="company_mail" class="custom-label">Company Default Mail</label>
                            <p class="mt-1 text-sm text-gray-500">Add mail to send notification to this mail.</p>
                        </div>
                        <div class="md:col-span-2">
                            <input type="email" id="company_mail" name="settings[general.company.mail]"
                                value="{{ old('general.company.mail', fn_get_setting('general.company.mail')) }}"
                                class="input-field w-full">
                        </div>
                    </div>
                    @hook('generalsetting:company_address.pre')

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                        <div class="md:col-span-1">
                            <label class="custom-label">Company Logo</label>
                            <p class="mt-1 text-sm text-gray-500">Click the image to change logo.</p>
                        </div>
                        <div class="md:col-span-2">
                            @include('filemanager::components.file-uploader', ['object_type' => 'company_logo',
                            'object_id' => 0, 'name' => 'company_logo' , 'is_multiple' => false, 'accept' => 'image/jpeg,image/png', 'span_text'=>'Drag and drop an image here or click to select a file. (JPEG, PNG)'])
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                        <div class="md:col-span-1">
                            <label class="custom-label">Company Favicon</label>
                            <p class="mt-1 text-sm text-gray-500">Click the image to change favicon.</p>
                        </div>
                        <div class="md:col-span-2">
                            @include('filemanager::components.file-uploader', ['object_type' => 'company_favicon',
                            'object_id' => 0, 'name' => 'company_favicon' , 'is_multiple' => false, 'accept' => 'image/jpeg,image/png', 'span_text'=>'Drag and drop an image here or click to select a file. (JPEG, PNG)'])
                        </div>
                    </div>
                </div>
            </section>

            <!-- Policy & Terms Tab Panel -->
            <section id="tab-policy-terms" class="tab-panel hidden">
                <div class="header py-2">
                    <h2 class="text-gray-800 text-2xl font-bold">Policy &amp; Terms</h2>
                    <p class="text-gray-600">Manage your terms, conditions, and privacy policy with the configured rich text editor.</p>
                </div>

                <div class="px-6 py-6 space-y-8">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Terms &amp; Conditions</h3>
                        <textarea id="terms_conditions" name="settings[general.company.terms_and_conditions]" editor="true" data-height="260"
                            class="input-field w-full min-h-[260px]"
                            placeholder="Write your terms and conditions here...">{{ old('settings[general.company.terms_and_conditions]', fn_get_setting('general.company.terms_and_conditions', '')) }}</textarea>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Privacy Policy</h3>
                        <textarea id="privacy_policy" name="settings[general.company.privacy_policy]" editor="true" data-height="260"
                            class="input-field w-full min-h-[260px]"
                            placeholder="Write your privacy policy here...">{{ old('settings[general.company.privacy_policy]', fn_get_setting('general.company.privacy_policy', '')) }}</textarea>
                    </div>
                </div>
            </section>

            <!-- Social Authentication Tab Panel -->
            <section id="tab-social-auth" class="tab-panel hidden">
                <div class="header py-2">
                    <h2 class="text-gray-800 text-2xl font-bold">Social Authentication Settings</h2>
                    <p class="text-gray-600">Configure Google and Apple Sign-In credentials for customer authentication.</p>
                </div>

                <div class="px-6 py-6 space-y-6">
                    <!-- Google OAuth Section -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Google OAuth Configuration</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                            <div class="md:col-span-1">
                                <label for="google_android_client_id" class="custom-label">Google Android Client ID</label>
                                <p class="mt-1 text-sm text-gray-500">OAuth 2.0 Client ID for Android app</p>
                            </div>
                            <div class="md:col-span-2">
                                <input type="text" id="google_android_client_id" name="settings[social_auth.google.android_client_id]"
                                    value="{{ old('social_auth.google.android_client_id', fn_get_setting('social_auth.google.android_client_id', '')) }}"
                                    class="input-field w-full font-mono text-sm"
                                    placeholder="469594206078-xxxxxxxxxxxxxxxxxxxxxxxx.apps.googleusercontent.com">
                                <p class="mt-2 text-xs text-blue-600">
                                    <strong>For Android:</strong> Get from google-services.json file
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200 mt-6">
                            <div class="md:col-span-1">
                                <label for="google_ios_client_id" class="custom-label">Google iOS Client ID</label>
                                <p class="mt-1 text-sm text-gray-500">OAuth 2.0 Client ID for iOS app</p>
                            </div>
                            <div class="md:col-span-2">
                                <input type="text" id="google_ios_client_id" name="settings[social_auth.google.ios_client_id]"
                                    value="{{ old('social_auth.google.ios_client_id', fn_get_setting('social_auth.google.ios_client_id', '')) }}"
                                    class="input-field w-full font-mono text-sm"
                                    placeholder="469594206078-xxxxxxxxxxxxxxxxxxxxxxxx.apps.googleusercontent.com">
                                <p class="mt-2 text-xs text-blue-600">
                                    <strong>For iOS:</strong> Get from GoogleService-Info.plist file
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200 mt-6">
                            <div class="md:col-span-1">
                                <label for="google_web_client_id" class="custom-label">Google Web Client ID</label>
                                <p class="mt-1 text-sm text-gray-500">OAuth 2.0 Client ID for Web app (optional)</p>
                            </div>
                            <div class="md:col-span-2">
                                <input type="text" id="google_web_client_id" name="settings[social_auth.google.web_client_id]"
                                    value="{{ old('social_auth.google.web_client_id', fn_get_setting('social_auth.google.web_client_id', '')) }}"
                                    class="input-field w-full font-mono text-sm"
                                    placeholder="469594206078-xxxxxxxxxxxxxxxxxxxxxxxx.apps.googleusercontent.com">
                                <p class="mt-2 text-xs text-gray-600">
                                    Optional: For web-based Google Sign-In
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200 mt-6">
                            <div class="md:col-span-1">
                                <label for="google_client_secret" class="custom-label">Google Client Secret</label>
                                <p class="mt-1 text-sm text-gray-500">OAuth 2.0 Client Secret (optional for mobile apps)</p>
                            </div>
                            <div class="md:col-span-2">
                                <input type="password" id="google_client_secret" name="settings[social_auth.google.client_secret]"
                                    value="{{ old('social_auth.google.client_secret', fn_get_setting('social_auth.google.client_secret', '')) }}"
                                    class="input-field w-full font-mono text-sm"
                                    placeholder="GOCSPX-xxxxxxxxxxxxxxxxxxxxxxxx">
                            </div>
                        </div>
                    </div>

                    <!-- Apple Sign In Section -->
                    <div class="bg-gray-50 p-4 rounded-lg mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Apple Sign In Configuration</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                            <div class="md:col-span-1">
                                <label for="apple_client_id" class="custom-label">Apple Client ID (Service ID)</label>
                                <p class="mt-1 text-sm text-gray-500">Service ID from Apple Developer Console</p>
                            </div>
                            <div class="md:col-span-2">
                                <input type="text" id="apple_client_id" name="settings[social_auth.apple.client_id]"
                                    value="{{ old('social_auth.apple.client_id', fn_get_setting('social_auth.apple.client_id', '')) }}"
                                    class="input-field w-full font-mono text-sm"
                                    placeholder="com.yourcompany.yourapp">
                                <p class="mt-2 text-xs text-blue-600">
                                    <strong>Required:</strong> Get your Service ID from 
                                    <a href="https://developer.apple.com/account/resources/identifiers/list/serviceId" target="_blank" class="underline">Apple Developer Console</a>
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200 mt-6">
                            <div class="md:col-span-1">
                                <label for="apple_team_id" class="custom-label">Apple Team ID</label>
                                <p class="mt-1 text-sm text-gray-500">Your Apple Developer Team ID</p>
                            </div>
                            <div class="md:col-span-2">
                                <input type="text" id="apple_team_id" name="settings[social_auth.apple.team_id]"
                                    value="{{ old('social_auth.apple.team_id', fn_get_setting('social_auth.apple.team_id', '')) }}"
                                    class="input-field w-full font-mono text-sm"
                                    placeholder="XXXXXXXXXX">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200 mt-6">
                            <div class="md:col-span-1">
                                <label for="apple_key_id" class="custom-label">Apple Key ID</label>
                                <p class="mt-1 text-sm text-gray-500">Key ID for your Sign in with Apple private key</p>
                            </div>
                            <div class="md:col-span-2">
                                <input type="text" id="apple_key_id" name="settings[social_auth.apple.key_id]"
                                    value="{{ old('social_auth.apple.key_id', fn_get_setting('social_auth.apple.key_id', '')) }}"
                                    class="input-field w-full font-mono text-sm"
                                    placeholder="XXXXXXXXXX">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200 mt-6">
                            <div class="md:col-span-1">
                                <label for="apple_private_key" class="custom-label">Apple Private Key</label>
                                <p class="mt-1 text-sm text-gray-500">Private key content (.p8 file)</p>
                            </div>
                            <div class="md:col-span-2">
                                <textarea id="apple_private_key" name="settings[social_auth.apple.private_key]"
                                    rows="6"
                                    class="input-field w-full font-mono text-xs"
                                    placeholder="-----BEGIN PRIVATE KEY-----&#10;MIGTAgEAMBMGByqGSM49AgEGCCqGSM49AwEHBHkwdwIBAQQg...&#10;-----END PRIVATE KEY-----">{{ old('social_auth.apple.private_key', fn_get_setting('social_auth.apple.private_key', '')) }}</textarea>
                                <p class="mt-2 text-xs text-amber-600">
                                    <strong>Note:</strong> This is optional for mobile apps using native Apple Sign In. Required only for web-based authentication.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Help Section -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
                        <h4 class="text-sm font-semibold text-blue-800 mb-2">📘 Setup Instructions</h4>
                        <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                            <li><strong>Google:</strong> Only Client ID is required for mobile app authentication</li>
                            <li><strong>Apple:</strong> Only Client ID is required for mobile app authentication</li>
                            <li>Team ID, Key ID, and Private Key are needed only for server-side token verification</li>
                            <li>Make sure to add your app's bundle ID/package name to the OAuth console</li>
                        </ul>
                    </div>
                </div>
            </section>

            @hook('generalsetting:tab_content.post')

            <div class="flex justify-end border-t border-gray-200 pt-6">
                <button type="submit" name="button" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>


<!-- Minimal tab switching script -->
@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        (function() {
            const buttons = document.querySelectorAll('.tab-btn');
            const panels = document.querySelectorAll('.tab-panel');

            function activate(tab) {
                buttons.forEach(b => {
                    if (b.dataset.tab === tab) {
                        b.classList.add('border-indigo-500', 'text-indigo-600');
                        b.classList.remove('border-transparent', 'text-gray-500');
                    } else {
                        b.classList.remove('border-indigo-500', 'text-indigo-600');
                        b.classList.add('border-transparent', 'text-gray-500');
                    }
                });
                
                panels.forEach(p => {
                    if (p.id === 'tab-' + tab) p.classList.remove('hidden');
                    else p.classList.add('hidden');
                });
            }

            buttons.forEach(b => b.addEventListener('click', (e) => {
                e.preventDefault();
                activate(b.dataset.tab);
            }));

            // default to first tab
            if (buttons.length) activate(buttons[0].dataset.tab);
        })();
    });
</script>
@endsection

@endsection
