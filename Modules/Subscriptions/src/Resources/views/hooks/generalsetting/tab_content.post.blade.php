<section id="tab-subscriptions" class="tab-panel">
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-5">
            <h2 class="text-lg font-medium text-gray-800">
                Subscriptions Configuration
            </h2>
            <p class="mt-1 text-sm text-gray-500">Configure subscription reminders, payment timing, and expiry alerts</p>
        </div>
        <div class="px-6 py-6 space-y-6">
            <!-- REMINDER SETTINGS SECTION -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-md font-semibold text-gray-800 mb-4">Reminder Settings</h3>

                <!-- Number of Reminders -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">How Many Times Remind</label>
                        <p class="mt-1 text-sm text-gray-500">Total number of reminders to send</p>
                    </div>
                    <div class="md:col-span-2">
                        <input name="settings[subscriptions.reminder_count]" type="number" min="1"
                            max="10"
                            value="{{ old('subscriptions.reminder_count', fn_get_setting('subscriptions.reminder_count', 3)) }}"
                            class="input-field">
                    </div>
                </div>

                <!-- Reminder Interval -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Reminder Interval (Days)</label>
                        <p class="mt-1 text-sm text-gray-500">Days between each reminder notification</p>
                    </div>
                    <div class="md:col-span-2">
                        <input name="settings[subscriptions.reminder_interval]" type="number" min="1"
                            max="365"
                            value="{{ old('subscriptions.reminder_interval', fn_get_setting('subscriptions.reminder_interval', 7)) }}"
                            class="input-field">
                    </div>
                </div>

                <!-- Reminder Start Days Before Expiry -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Start Reminders Before Expiry</label>
                        <p class="mt-1 text-sm text-gray-500">Days before expiry to begin reminders</p>
                    </div>
                    <div class="md:col-span-2">
                        <input name="settings[subscriptions.reminder_start_days]" type="number" min="1"
                            max="365"
                            value="{{ old('subscriptions.reminder_start_days', fn_get_setting('subscriptions.reminder_start_days', 30)) }}"
                            class="input-field">
                    </div>
                </div>

                <!-- Send Reminder Emails -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Send Reminder Emails</label>
                        <p class="mt-1 text-sm text-gray-500">Enable email notifications for reminders</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[subscriptions.send_reminder_emails]" value="0">
                            <input type="checkbox" name="settings[subscriptions.send_reminder_emails]" value="1"
                                {{ old('subscriptions.send_reminder_emails', fn_get_setting('subscriptions.send_reminder_emails', true)) ? 'checked' : '' }}
                                class="sr-only peer hidden">
                            <div
                                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                            <span class="ms-3 text-sm font-medium text-gray-700">Send emails</span>
                        </label>
                    </div>
                </div>

                <!-- Send In-App Notifications -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Send In-App Notifications</label>
                        <p class="mt-1 text-sm text-gray-500">Enable in-app notifications for reminders</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[subscriptions.send_reminder_notifications]"
                                value="0">
                            <input type="checkbox" name="settings[subscriptions.send_reminder_notifications]"
                                value="1"
                                {{ old('subscriptions.send_reminder_notifications', fn_get_setting('subscriptions.send_reminder_notifications', true)) ? 'checked' : '' }}
                                class="sr-only peer hidden">
                            <div
                                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                            <span class="ms-3 text-sm font-medium text-gray-700">Send notifications</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- PAYMENT SETTINGS SECTION -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-md font-semibold text-gray-800 mb-4">Payment Reminder Settings</h3>

                <!-- Payment Reminder Days Before Due -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Reminder Before Payment Due</label>
                        <p class="mt-1 text-sm text-gray-500">Days before payment due to send reminder</p>
                    </div>
                    <div class="md:col-span-2">
                        <input name="settings[subscriptions.payment_reminder_days]" type="number" min="1"
                            max="30"
                            value="{{ old('subscriptions.payment_reminder_days', fn_get_setting('subscriptions.payment_reminder_days', 3)) }}"
                            class="input-field">
                    </div>
                </div>

                <!-- Payment Overdue Alert Days -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Overdue Payment Alert</label>
                        <p class="mt-1 text-sm text-gray-500">Days after payment is overdue to send alert</p>
                    </div>
                    <div class="md:col-span-2">
                        <input name="settings[subscriptions.payment_overdue_days]" type="number" min="1"
                            max="30"
                            value="{{ old('subscriptions.payment_overdue_days', fn_get_setting('subscriptions.payment_overdue_days', 5)) }}"
                            class="input-field">
                    </div>
                </div>

                <!-- Max Payment Retries -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Maximum Retry Attempts</label>
                        <p class="mt-1 text-sm text-gray-500">Max attempts to retry failed payments</p>
                    </div>
                    <div class="md:col-span-2">
                        <input name="settings[subscriptions.max_payment_retries]" type="number" min="1"
                            max="10"
                            value="{{ old('subscriptions.max_payment_retries', fn_get_setting('subscriptions.max_payment_retries', 3)) }}"
                            class="input-field">
                    </div>
                </div>

                <!-- Retry Interval Hours -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Retry Interval (Hours)</label>
                        <p class="mt-1 text-sm text-gray-500">Hours to wait between retry attempts</p>
                    </div>
                    <div class="md:col-span-2">
                        <input name="settings[subscriptions.retry_interval_hours]" type="number" min="1"
                            max="168"
                            value="{{ old('subscriptions.retry_interval_hours', fn_get_setting('subscriptions.retry_interval_hours', 24)) }}"
                            class="input-field">
                    </div>
                </div>

                <!-- Send Payment Confirmation -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Send Payment Confirmation</label>
                        <p class="mt-1 text-sm text-gray-500">Send confirmation after successful payment</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[subscriptions.send_payment_confirmation]"
                                value="0">
                            <input type="checkbox" name="settings[subscriptions.send_payment_confirmation]"
                                value="1"
                                {{ old('subscriptions.send_payment_confirmation', fn_get_setting('subscriptions.send_payment_confirmation', true)) ? 'checked' : '' }}
                                class="sr-only peer hidden">
                            <div
                                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                            <span class="ms-3 text-sm font-medium text-gray-700">Enabled</span>
                        </label>
                    </div>
                </div>

                <!-- Send Payment Failure Alert -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Send Payment Failure Alert</label>
                        <p class="mt-1 text-sm text-gray-500">Alert subscribers of payment failures</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[subscriptions.send_payment_failure_alert]"
                                value="0">
                            <input type="checkbox" name="settings[subscriptions.send_payment_failure_alert]"
                                value="1"
                                {{ old('subscriptions.send_payment_failure_alert', fn_get_setting('subscriptions.send_payment_failure_alert', true)) ? 'checked' : '' }}
                                class="sr-only peer hidden">
                            <div
                                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                            <span class="ms-3 text-sm font-medium text-gray-700">Enabled</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- EXPIRY SETTINGS SECTION -->
            <div>
                <h3 class="text-md font-semibold text-gray-800 mb-4">Expiry & Renewal Settings</h3>

                <!-- Expiry Warning Days -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Expiry Warning (Days Before)</label>
                        <p class="mt-1 text-sm text-gray-500">Days before expiry to warn subscribers</p>
                    </div>
                    <div class="md:col-span-2">
                        <input name="settings[subscriptions.expiry_warning_days]" type="number" min="1"
                            max="60"
                            value="{{ old('subscriptions.expiry_warning_days', fn_get_setting('subscriptions.expiry_warning_days', 14)) }}"
                            class="input-field">
                    </div>
                </div>

                <!-- Final Notice Days -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Final Notice (Days Before Expiry)</label>
                        <p class="mt-1 text-sm text-gray-500">Send final notice this many days before expiry</p>
                    </div>
                    <div class="md:col-span-2">
                        <input name="settings[subscriptions.final_notice_days]" type="number" min="0"
                            max="14"
                            value="{{ old('subscriptions.final_notice_days', fn_get_setting('subscriptions.final_notice_days', 1)) }}"
                            class="input-field">
                    </div>
                </div>

                <!-- Auto Renew on Expiry -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Allow Auto Renewal</label>
                        <p class="mt-1 text-sm text-gray-500">Automatically renew subscriptions on expiry</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[subscriptions.auto_renew_expiry]" value="0">
                            <input type="checkbox" name="settings[subscriptions.auto_renew_expiry]" value="1"
                                {{ old('subscriptions.auto_renew_expiry', fn_get_setting('subscriptions.auto_renew_expiry', false)) ? 'checked' : '' }}
                                class="sr-only peer hidden">
                            <div
                                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                            <span class="ms-3 text-sm font-medium text-gray-700">Enable auto-renewal</span>
                        </label>
                    </div>
                </div>

                <!-- Pause on Expiry -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Pause on Expiry</label>
                        <p class="mt-1 text-sm text-gray-500">Pause instead of cancelling when subscription expires</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[subscriptions.pause_on_expiry]" value="0">
                            <input type="checkbox" name="settings[subscriptions.pause_on_expiry]" value="1"
                                {{ old('subscriptions.pause_on_expiry', fn_get_setting('subscriptions.pause_on_expiry', true)) ? 'checked' : '' }}
                                class="sr-only peer hidden">
                            <div
                                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                            <span class="ms-3 text-sm font-medium text-gray-700">Pause subscriptions</span>
                        </label>
                    </div>
                </div>

                <!-- Allow Renewal After Expiry -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Allow Renewal After Expiry</label>
                        <p class="mt-1 text-sm text-gray-500">Allow subscribers to renew after expiry period</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[subscriptions.allow_renewal_after_expiry]"
                                value="0">
                            <input type="checkbox" name="settings[subscriptions.allow_renewal_after_expiry]"
                                value="1"
                                {{ old('subscriptions.allow_renewal_after_expiry', fn_get_setting('subscriptions.allow_renewal_after_expiry', true)) ? 'checked' : '' }}
                                class="sr-only peer hidden">
                            <div
                                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                            <span class="ms-3 text-sm font-medium text-gray-700">Allow post-expiry renewal</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
