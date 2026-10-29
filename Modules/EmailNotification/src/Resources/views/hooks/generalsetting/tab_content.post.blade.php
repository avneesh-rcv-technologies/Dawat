<section id="tab-mail" class="tab-panel">
<div class="bg-white overflow-hidden">
            <div class="px-6 py-5 ">
                <h2 class="text-lg font-medium text-gray-800">
                    Mail Server Configuration
                </h2>
                <p class="mt-1 text-sm text-gray-500">Set up your email server settings</p>
            </div>
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Mail Driver</label>
                        <p class="mt-1 text-sm text-gray-500">Select your email delivery method</p>
                    </div>
                    <div class="md:col-span-2">
                        <select name="settings[general.mail.driver]" class="input-field">
                            @php
                                $selectedDriver = old('general.mail.driver', fn_get_setting('general.mail.driver'));
                            @endphp
                            <option value="smtp" {{ $selectedDriver == 'smtp' ? 'selected' : '' }}>SMTP</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- SMTP Settings Section -->
        <div class="bg-white overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800">
                    SMTP Settings
                </h2>
                <p class="mt-1 text-sm text-gray-500">Configure your SMTP server details</p>
            </div>
            <div class="px-6 py-6 space-y-6">
                <!-- Mail Host -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Mail Host</label>
                        <p class="mt-1 text-sm text-gray-500">Your email server address</p>
                    </div>
                    <div class="md:col-span-2">
                        <input name="settings[general.mail.host]" type="text"
                            value="{{ old('general.mail.host', fn_get_setting('general.mail.host')) }}" class="input-field">
                    </div>
                    
                </div>

                <!-- Mail Port -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                    <div class="md:col-span-1">
                        <label class="custom-label">Mail Port</label>
                        <p class="mt-1 text-sm text-gray-500">Port number for your email server</p>
                    </div>
                    <div class="md:col-span-2">
                        <input name="settings[general.mail.port]" type="text"
                            value="{{ old('general.mail.port', fn_get_setting('general.mail.port')) }}" class="input-field">
                    </div>
                </div>

                <!-- Mail Username -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                    <div class="md:col-span-1">
                        <label class="custom-label">Mail Username</label>
                        <p class="mt-1 text-sm text-gray-500">Your email account username</p>
                    </div>
                    <div class="md:col-span-2">
                        <input name="settings[general.mail.username]" type="text"
                            value="{{ old('general.mail.username', fn_get_setting('general.mail.username')) }}"
                            class="input-field">
                    </div>
                </div>

                <!-- Mail Password -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                    <div class="md:col-span-1">
                        <label class="custom-label">Mail Password</label>
                        <p class="mt-1 text-sm text-gray-500">Your email account password</p>
                    </div>
                    <div class="md:col-span-2">
                        <div class="relative">
                            <input name="settings[general.mail.password]" type="text"
                                value="{{ old('general.mail.password', fn_get_setting('general.mail.password')) }}"
                                class="input-field">
                        </div>
                    </div>
                </div>

                <!-- Mail Encryption -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                    <div class="md:col-span-1">
                        <label class="custom-label">Mail Encryption</label>
                        <p class="mt-1 text-sm text-gray-500">Encryption method for email transmission</p>
                    </div>
                    <div class="md:col-span-2">
                        <select name="settings[general.mail.encryption]" class="input-field">
                            @php
                                $selectedDriver = old('general.mail.encryption', fn_get_setting('general.mail.encryption'));
                            @endphp

                            <option value="tls" {{ $selectedDriver == 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ $selectedDriver == 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="none" {{ $selectedDriver == 'none' ? 'selected' : '' }}>None</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Address Configuration -->
        <div class="bg-white  overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800">
                    Email Address Configuration
                </h2>
                <p class="mt-1 text-sm text-gray-500">Set up your email addresses</p>
            </div>
            <div class="px-6 py-6 space-y-6">
                <!-- From Address -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Mail From Address</label>
                        <p class="mt-1 text-sm text-gray-500">The email address that will appear as sender</p>
                    </div>
                    <div class="md:col-span-2">
                        <input name="settings[general.mail.mail_from]" type="text"
                            value="{{ old('general.mail.mail_from', fn_get_setting('general.mail.mail_from')) }}"
                            class="input-field">
                    </div>
                </div>

                <!-- From Name -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                    <div class="md:col-span-1">
                        <label class="custom-label">Mail From Name</label>
                        <p class="mt-1 text-sm text-gray-500">The name that will appear as sender</p>
                    </div>
                    <div class="md:col-span-2">
                        <input name="settings[general.mail.mail_from_name]" type="text"
                            value="{{ old('general.mail.mail_from_name', fn_get_setting('general.mail.mail_from_name')) }}"
                            class="input-field">
                    </div>
                </div>

                <!-- CC Emails -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                    <div class="md:col-span-1">
                        <label class="custom-label">Add CC</label>
                        <p class="mt-1 text-sm text-gray-500">Use comma separated values to add multiple CC addresses</p>
                    </div>
                    <div class="md:col-span-2">
                        <input name="settings[general.mail.cc]" type="text"
                            placeholder="admin@example.com, manager@example.com" class="input-field"
                            value="{{ old('general.mail.cc', fn_get_setting('general.mail.cc')) }}">
                    </div>
                </div>
            </div>
        </div>
    </section>