<section id="tab-activity-log" class="tab-panel">
    <div class="bg-white overflow-hidden">
        <div class="px-6 py-5 ">
            <h2 class="text-lg font-medium text-gray-800">
                Activity Log Configuration
            </h2>
            <p class="mt-1 text-sm text-gray-500">Configure your activity log settings</p>
        </div>
        <div class="px-6 py-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-2">
                <div class="md:col-span-1">
                    <label class="custom-label">Enable Activity Log</label>

                </div>
                <div class="md:col-span-2">
                    <select name="settings[activitylog.enabled]" class="input-field">
                        <option value="Y" {{ old('activitylog.enabled', fn_get_setting('activitylog.enabled', 'Y')) == 'Y' ? 'selected' : '' }}>
                            Enabled</option>
                        <option value="N" {{ old('activitylog.enabled', fn_get_setting('activitylog.enabled', 'N')) == 'N' ? 'selected' : '' }}>
                            Disabled</option>
                    </select>
                </div>
            </div>


            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-2">
                <div class="md:col-span-1">
                    <label class="custom-label">Enable Threat Protection</label>
                    <p class="mt-1 text-sm text-gray-500">Block suspicious traffic and enforce admin IP rules without full request logging.</p>
                </div>
                <div class="md:col-span-2">
                    <select name="settings[activitylog.threat_protection_enabled]" class="input-field">
                        <option value="Y" {{ old('activitylog.threat_protection_enabled', fn_get_setting('activitylog.threat_protection_enabled', 'Y')) == 'Y' ? 'selected' : '' }}>Enabled</option>
                        <option value="N" {{ old('activitylog.threat_protection_enabled', fn_get_setting('activitylog.threat_protection_enabled', 'N')) == 'N' ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-2">
                <div class="md:col-span-1">
                    <label class="custom-label">Enable Request Logging</label>
                    <p class="mt-1 text-sm text-gray-500">Persist HTTP request metadata to the activity log table.</p>
                </div>
                <div class="md:col-span-2">
                    <select name="settings[activitylog.request_logging_enabled]" class="input-field">
                        <option value="Y" {{ old('activitylog.request_logging_enabled', fn_get_setting('activitylog.request_logging_enabled', 'Y')) == 'Y' ? 'selected' : '' }}>Enabled</option>
                        <option value="N" {{ old('activitylog.request_logging_enabled', fn_get_setting('activitylog.request_logging_enabled', 'N')) == 'N' ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-2">
                <div class="md:col-span-1">
                    <label class="custom-label">Store Response Body (errors only)</label>
                </div>
                <div class="md:col-span-2">
                    <select name="settings[activitylog.store_response_body_errors_only]" class="input-field">
                        <option value="Y" {{ old('activitylog.store_response_body_errors_only', fn_get_setting('activitylog.store_response_body_errors_only', 'Y')) == 'Y' ? 'selected' : '' }}>Only 4xx/5xx</option>
                        <option value="N" {{ old('activitylog.store_response_body_errors_only', fn_get_setting('activitylog.store_response_body_errors_only', 'N')) == 'N' ? 'selected' : '' }}>All responses</option>
                    </select>
                </div>
            </div>


            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-2">
                <div class="md:col-span-1">
                    <label class="custom-label">Critical Error Role </label>

                    </p>
                </div>
                <div class="md:col-span-2">
                    <select name="settings[error.default_role]" class="input-field">
                        <option value="">Select Role</option>
                        @foreach (\Illuminate\Support\Facades\DB::table('roles')->get() as $role)
                            <option value="{{ $role->id }}" {{ old('settings.error.default_role', fn_get_setting('error.default_role', '')) == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-2">
                <div class="md:col-span-1">
                    <label class="custom-label">Otp Access Request Role </label>

                </div>
                <div class="md:col-span-2">
                    <select name="settings[accessotp.default_role]" class="input-field">
                        <option value="">Select Role</option>
                        @foreach (\Illuminate\Support\Facades\DB::table('roles')->get() as $role)
                            <option value="{{ $role->id }}" {{ old('settings.accessotp.default_role', fn_get_setting('accessotp.default_role', '')) == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label for="allowed_ips" class="block text-sm font-medium text-gray-700">Request Log Cleanup</label>
                    <p class="mt-1 text-sm text-gray-500">Configure how long to keep request logs. (e.g. 90 days)
                    </p>
                </div>
                <div class="md:col-span-2">
                    {{-- textarea --}}
                    <input type="number" id="request_log_cleanup_days" name="settings[general.request_log_cleanup_days]"
                        value="{{ old('general.request_log_cleanup_days', fn_get_setting('general.request_log_cleanup_days', 90)) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            </div>

            {{-- allowed IPS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label for="allowed_ips" class="block text-sm font-medium text-gray-700">Allowed IPs</label>
                    <p class="mt-1 text-sm text-gray-500">Comma separated IPs that are allowed to access the system.
                    </p>
                </div>
                <div class="md:col-span-2">
                    {{-- textarea --}}
                    <textarea id="allowed_ips" name="settings[general.allowed_ips]" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ old('general.allowed_ips', fn_get_setting('general.allowed_ips')) }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label for="allowed_routes" class="block text-sm font-medium text-gray-700">Allowed
                        Routes</label>
                    <p class="mt-1 text-sm text-gray-500">Comma separated routes that are allowed to access the
                        system.</p>
                </div>
                <div class="md:col-span-2">
                    {{-- textarea --}}
                    @php
                        $defaultAllowedRoutes = implode("\n", [
                            'admin/activity-log/advance-logging',
                            'admin/activity-log/advance-logging/*',
                            'settings',
                            'admin/activity-log/access-requests/*',
                        ]);
                    @endphp
                    <textarea id="allowed_routes" name="settings[general.allowed_routes]" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ old('general.allowed_routes', fn_get_setting('general.allowed_routes', $defaultAllowedRoutes) ?? $defaultAllowedRoutes) }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label for="ignore_routes" class="block text-sm font-medium text-gray-700">Ignore Routes</label>
                    <p class="mt-1 text-sm text-gray-500">Comma separated routes that are ignored by the system.</p>
                </div>
                <div class="md:col-span-2">
                    {{-- textarea --}}
                    {{-- set default routes admin/notifications/unread-count,api/firebase/subscribe --}}
                    @php
                        $defaultIgnoreRoutes = implode("\n", [
                            'up',
                            'broadcasting/auth',
                            'app/*',
                            'build/*',
                            'css/*',
                            'js/*',
                            'images/*',
                            'fonts/*',
                            'favicon.ico',
                            'livewire/*',
                            'admin/notifications/unread-count',
                            'api/firebase/subscribe',
                            'book/*',
                            'google-meet/callback',
                            'google-calendar/callback',
                            'hubspot/callback',
                            'pipedrive/callback',
                            'zoom/callback',
                            'auth/email/callback',
                        ]);
                    @endphp
                    <textarea id="ignore_routes" name="settings[general.ignore_routes]" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ old('general.ignore_routes', fn_get_setting('general.ignore_routes', $defaultIgnoreRoutes) ?? $defaultIgnoreRoutes) }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label for="sampling_routes" class="block text-sm font-medium text-gray-700">Sampling Routes</label>
                    <p class="mt-1 text-sm text-gray-500">Routes where only a fraction of successful requests are logged (see sampling rate).</p>
                </div>
                <div class="md:col-span-2">
                    @php
                        $defaultSamplingRoutes = implode("\n", [
                            'book/*',
                        ]);
                    @endphp
                    <textarea id="sampling_routes" name="settings[general.sampling_routes]" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ old('general.sampling_routes', fn_get_setting('general.sampling_routes', $defaultSamplingRoutes) ?? $defaultSamplingRoutes) }}</textarea>
                    <label for="request_sampling_rate" class="mt-3 block text-sm font-medium text-gray-700">Log 1 in N requests on sampling routes</label>
                    <input type="number" min="1" id="request_sampling_rate" name="settings[activitylog.request_sampling_rate]"
                        value="{{ old('activitylog.request_sampling_rate', fn_get_setting('activitylog.request_sampling_rate', 10)) }}"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label for="rate_limit" class="block text-sm font-medium text-gray-700">Rate Limiting</label>
                    <p class="mt-1 text-sm text-gray-500">Configure rate limiting to protect against abuse. (e.g.
                        180
                        requests per minute, block for 60 minutes)</p>
                </div>
                <div class="md:col-span-2">
                    <div class="flex space-x-4">
                        <div>
                            <label for="max_requests" class="block text-sm font-medium text-gray-700">Max
                                Requests/Minute</label>
                            <input type="number" id="max_requests" name="settings[general.max_requests_per_minute]"
                                value="{{ old('general.max_requests_per_minute', fn_get_setting('general.max_requests_per_minute', 180)) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="block_minutes" class="block text-sm font-medium text-gray-700">Block Minutes
                                on Rate Limit</label>
                            <input type="number" id="block_minutes" name="settings[general.block_minutes_on_rate_limit]"
                                value="{{ old('general.block_minutes_on_rate_limit', fn_get_setting('general.block_minutes_on_rate_limit', 60)) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label for="brute_force" class="block text-sm font-medium text-gray-700">Brute Force
                        Protection</label>
                    <p class="mt-1 text-sm text-gray-500">Configure brute force protection for authentication. (e.g.
                        12
                        failed attempts, block for 240 minutes)</p>
                </div>
                <div class="md:col-span-2">
                    <div class="flex space-x-4">
                        <div>
                            <label for="max_failed_attempts" class="block text-sm font-medium text-gray-700">Max
                                Failed Auth Attempts</label>
                            <input type="number" id="max_failed_attempts"
                                name="settings[general.max_failed_auth_attempts]"
                                value="{{ old('general.max_failed_auth_attempts', fn_get_setting('general.max_failed_auth_attempts', 12)) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="block_minutes_brute_force" class="block text-sm font-medium text-gray-700">Block
                                Minutes on Brute
                                Force</label>
                            <input type="number" id="block_minutes_brute_force"
                                name="settings[general.block_minutes_on_brute_force]"
                                value="{{ old('general.block_minutes_on_brute_force', fn_get_setting('general.block_minutes_on_brute_force', 240)) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



</section>