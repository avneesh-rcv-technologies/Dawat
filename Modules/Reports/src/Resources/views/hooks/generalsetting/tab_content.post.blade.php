<section id="tab-reports" class="tab-panel">
    <div class="overflow-hidden">
        <div class="px-6 py-5">
            <h2 class="text-lg font-medium text-gray-800">Reports Configuration</h2>
            <p class="mt-1 text-sm text-gray-500">Configure scheduled report email period, report type, export format, and chart attachment.</p>
        </div>

        <div class="px-6 py-6 space-y-6">
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-md font-semibold text-gray-800 mb-4">Email Schedule</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <label for="reports_mail_report_period" class="custom-label">Mail Report Period</label>
                        <p class="mt-1 text-sm text-gray-500">How often reports are sent by email.</p>
                    </div>
                    <div class="md:col-span-2">
                        <select id="reports_mail_report_period" name="settings[reports.mail_report_period]" class="input-field">
                            <option value="daily" {{ old('reports.mail_report_period', fn_get_setting('reports.mail_report_period', 'weekly')) === 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ old('reports.mail_report_period', fn_get_setting('reports.mail_report_period', 'weekly')) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('reports.mail_report_period', fn_get_setting('reports.mail_report_period', 'weekly')) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ old('reports.mail_report_period', fn_get_setting('reports.mail_report_period', 'weekly')) === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="yearly" {{ old('reports.mail_report_period', fn_get_setting('reports.mail_report_period', 'weekly')) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                        </select>
                    </div>
                </div>
            </div>
            {{--  --}}

            

            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-md font-semibold text-gray-800 mb-4">Report Output</h3>

                <!-- Client Role -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Client Role</label>
                        <p class="mt-1 text-sm text-gray-500">Select default role for clients</p>
                    </div>
                    <div class="md:col-span-2">
                        <select name="settings[reports.default_role]" class="input-field">
                            <option value="">Select Role</option>
                            @foreach(\Illuminate\Support\Facades\DB::table('roles')->where('guard', 'admin')->get() as $role)
                                <option value="{{ $role->id }}" {{ old('reports.default_role', fn_get_setting('reports.default_role', fn_get_setting('settings.reports.default_role', ''))) == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="md:col-span-1">
                        <label for="reports_report_type" class="custom-label">Report Type</label>
                        <p class="mt-1 text-sm text-gray-500">Select data detail level in emailed report.</p>
                    </div>
                    <div class="md:col-span-2">
                        <select id="reports_report_type" name="settings[reports.report_type]" class="input-field">
                            <option value="summary" {{ old('reports.report_type', fn_get_setting('reports.report_type', 'summary')) === 'summary' ? 'selected' : '' }}>Summary</option>
                            <option value="detailed" {{ old('reports.report_type', fn_get_setting('reports.report_type', 'summary')) === 'detailed' ? 'selected' : '' }}>Detailed</option>
                            <option value="kpi" {{ old('reports.report_type', fn_get_setting('reports.report_type', 'summary')) === 'kpi' ? 'selected' : '' }}>KPI Snapshot</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="md:col-span-1">
                        <label for="reports_export_format" class="custom-label">Export Format</label>
                        <p class="mt-1 text-sm text-gray-500">Choose attachment file type for report export.</p>
                    </div>
                    <div class="md:col-span-2">
                        <select id="reports_export_format" name="settings[reports.export_format]" class="input-field">
                            <option value="xlsx" {{ old('reports.export_format', fn_get_setting('reports.export_format', 'xlsx')) === 'xlsx' ? 'selected' : '' }}>Excel (.xlsx)</option>
                            <option value="csv" {{ old('reports.export_format', fn_get_setting('reports.export_format', 'xlsx')) === 'csv' ? 'selected' : '' }}>CSV (.csv)</option>
                            <option value="pdf" {{ old('reports.export_format', fn_get_setting('reports.export_format', 'xlsx')) === 'pdf' ? 'selected' : '' }}>PDF (.pdf)</option>
                            <option value="docx" {{ old('reports.export_format', fn_get_setting('reports.export_format', 'xlsx')) === 'docx' ? 'selected' : '' }}>Word (.docx)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="md:col-span-1">
                        <label class="custom-label">Attach Chart</label>
                        <p class="mt-1 text-sm text-gray-500">Attach report visualization chart in email.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[reports.attach_chart]" value="0">
                            <input type="checkbox" name="settings[reports.attach_chart]" value="1"
                                {{ old('reports.attach_chart', fn_get_setting('reports.attach_chart', 1)) ? 'checked' : '' }}
                                class="hidden peer">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ms-3 text-sm font-medium text-gray-700">Enable chart attachment</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
