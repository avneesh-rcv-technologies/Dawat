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
    .configuration-settings { --config-tab-ink: #1F1F1F; --config-tab-muted: #6B6B6B; --config-tab-accent: #C2470A; --config-tab-border: #ECE7E3; }
    .configuration-settings .settings-tab-nav {
        display: flex;
        align-items: stretch;
        gap: 6px;
        overflow-x: auto;
        overflow-y: hidden;
        padding: 10px 14px 0;
        border-bottom: 1px solid var(--config-tab-border);
        background: linear-gradient(180deg, #FFFCFA 0%, #FBF8F5 100%);
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
    .configuration-settings .settings-tab-nav .tab-btn[data-tab="company"]::before { content: 'store'; }
    .configuration-settings .settings-tab-nav .tab-btn[data-tab="mail"]::before { content: 'mail'; }
    .configuration-settings .settings-tab-nav .tab-btn[data-tab="captcha"]::before { content: 'verified_user'; }
    .configuration-settings .settings-tab-nav .tab-btn:hover { background: #FFF4EE; color: var(--config-tab-ink); }
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
        background: #FF6210;
        border-radius: 2px;
        content: '';
    }
    .configuration-settings .settings-tab-nav .tab-btn:focus-visible { z-index: 1; outline: 3px solid rgba(255, 98, 16, .22); outline-offset: 2px; }
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
            <button data-tab="company"
                class="tab-btn inline-flex items-center px-4 py-3 border-b-2 -mb-px border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">Company</button>
            @hook('generalsetting:tabs.post')
            <button type="button" data-tab="captcha"
                class="tab-btn inline-flex items-center px-4 py-3 border-b-2 -mb-px border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">CAPTCHA</button>
        </nav>
    </div>

    <div class="p-6">
        <form action="{{ route('settings.store') }}" method="POST" enctype="multipart/form-data" class="form-ajax">
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

                        <!-- Platform Commission -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t border-gray-200 pt-6">
                            <div class="md:col-span-1">
                                <label for="platform_commission_percentage" class="custom-label">Platform Commission</label>
                                <p class="mt-1 text-sm text-gray-500">
                                    Percentage applied to every order amount. Restaurants must agree to it with a checkbox when they add banking information. Defaults to 10% when unset. Set 0% for no commission.
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <div class="flex items-center gap-2">
                                    <input type="number" id="platform_commission_percentage" min="0" max="100" step="0.01"
                                        name="settings[general.platform_commission_percentage]"
                                        value="{{ old('general.platform_commission_percentage', fn_get_setting('general.platform_commission_percentage', 10)) }}"
                                        class="input-field w-40">
                                    <span class="text-sm text-gray-500">%</span>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Changing this asks restaurants that agreed to an older rate to agree again on their next banking update.</p>
                            </div>
                        </div>

                        {{-- get roles in loop --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-1">
                                <label for="timezone" class="custom-label">Default Restorant Role</label>
                                
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
                                <label for="google_maps_api_key" class="custom-label">
                                    Google Maps API Key
                                </label>
                                <p class="mt-1 text-sm text-gray-500">
                                    API key for Google Maps services
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <input type="text" id="google_maps_api_key" name="settings[general.google_maps_api_key]"
                                    value="{{ old('general.google_maps_api_key', fn_get_setting('general.google_maps_api_key', '')) }}"
                                    class="input-field w-full font-mono text-sm"
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

            <section id="tab-captcha" class="tab-panel hidden">
                @php($captcha = app(\Modules\GeneralSetting\Services\CaptchaSettings::class))
                <div class="header py-2">
                    <h2 class="text-gray-800 text-2xl font-bold">CAPTCHA configuration</h2>
                    <p class="text-gray-600">Protect admin login and password recovery, plus the contact and partner forms, with Google reCAPTCHA v2 (I'm not a robot checkbox).</p>
                </div>
                <div class="px-6 py-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <label for="captcha_enabled" class="custom-label">Enable reCAPTCHA</label>
                            <p class="mt-1 text-sm text-gray-500">Require visitors to complete the challenge on admin login and password recovery, contact, partner information, signup, login, and forgot password forms.</p>
                        </div>
                        <div class="md:col-span-2">
                            <select id="captcha_enabled" name="settings[captcha.enabled]" class="input-field w-full">
                                <option value="0" @selected(! $captcha->enabled())>Disabled</option>
                                <option value="1" @selected($captcha->enabled())>Enabled</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                        <div class="md:col-span-1">
                            <label for="captcha_site_key" class="custom-label">Site key</label>
                            <p class="mt-1 text-sm text-gray-500">Enter the site key for your website's domains.</p>
                        </div>
                        <div class="md:col-span-2">
                            <input type="text" id="captcha_site_key" name="settings[captcha.site_key]"
                                value="{{ $captcha->siteKey() }}" maxlength="255" autocomplete="off"
                                class="input-field w-full" placeholder="Enter reCAPTCHA site key">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                        <div class="md:col-span-1">
                            <label for="captcha_secret_key" class="custom-label">Secret key</label>
                            <p class="mt-1 text-sm text-gray-500">Leave blank to keep the current secret key.</p>
                        </div>
                        <div class="md:col-span-2">
                            <input type="password" id="captcha_secret_key" name="settings[captcha.secret_key]"
                                maxlength="255" autocomplete="new-password" class="input-field w-full"
                                placeholder="{{ filled($captcha->secretKey()) ? 'Secret key configured' : 'Enter reCAPTCHA secret key' }}">
                            <p class="mt-2 text-xs text-gray-500">The secret key is stored encrypted. Both keys are required when reCAPTCHA is enabled.</p>
                        </div>
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
