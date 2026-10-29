@extends('backend::layouts.app')

@section('title', 'Manage KYC Fields')

@section('content')
@include('admin::components.common.back-button', ['route' => route('admin.kyc.types.index'), 'name' => 'KYC Fields'])
<div class="container mx-auto px-4">
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-xl font-semibold">{{ $kycType->name }} - Fields</h3>
            <button type="button" id="addFieldBtn" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-600">
                <i class="fas fa-plus mr-2"></i>Add Field
            </button>
        </div>
        <div class="p-6">
            @if($kycType->fields->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Field Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Field Key</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Required</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Validation Rules
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($kycType->fields as $field)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $field->order_index }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $field->field_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <code class="bg-gray-100 px-2 py-1 rounded">{{ $field->field_key }}</code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-primary-100 text-primary">
                                {{ ucfirst($field->field_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($field->is_required)
                            <span
                                class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Yes</span>
                            @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">No</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-500 max-w-xs">
                            @if($field->validation_rules)
                            @foreach($field->validation_rules as $ruleKey => $ruleVal)
                            @if($ruleVal !== null && $ruleVal !== '')
                            <span class="inline-block bg-gray-100 rounded px-1 py-0.5 mr-1 mb-1">
                                {{ $ruleKey }}: {{ is_array($ruleVal) ? implode(',', $ruleVal) : $ruleVal }}
                            </span>
                            @endif
                            @endforeach
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button onclick='editField(@json($field))'
                                class="text-primary hover:text-primary-600 mr-3" title="Manage Fields"><span class="material-icons-outlined">edit</span></button>
                            <button onclick="deleteField({{ $field->id }}, '{{ $field->field_name }}')"
                                class="text-red-600 hover:text-red-900"><span class="material-icons-outlined">delete</span></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-center text-gray-500 py-8">No fields yet. Click "Add Field" to create one.</p>
            @endif
        </div>
    </div>
</div>

<!-- Field Modal -->
<div id="fieldModal" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Add Field</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-4 max-h-[70vh] overflow-y-auto space-y-4">
            <input type="hidden" id="fieldId">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Field Name *</label>
                <input type="text" id="fieldName" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-primary focus:border-primary">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Field Key *</label>
                <input type="text" id="fieldKey" pattern="[a-z0-9_]+" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-primary focus:border-primary">
                <p class="text-xs text-gray-400 mt-1">Lowercase, numbers, underscores only</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                <select id="fieldType" required onchange="onTypeChange(this.value)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-primary focus:border-primary">
                    <option value="text">Text</option>
                   <option value="number">Number</option>
                    <option value="date">Date</option>
                    <option value="file">File</option>
                    <option value="select">Select</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Required?</label>
                <select id="fieldRequired"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-primary focus:border-primary">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>

            <!-- Select options -->
            <div id="optionsGroup" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Options (JSON)</label>
                <textarea id="fieldOptions" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-primary focus:border-primary"></textarea>
                <p class="text-xs text-gray-400 mt-1">Example: {"val1": "Label 1", "val2": "Label 2"}</p>
            </div>

            <!-- ── Validation Rules ── -->
            <div class="border-t pt-4">
                <p class="text-sm font-semibold text-gray-700 mb-3">Validation Rules</p>

                <!-- Text rules -->
                <div id="textRules" class="hidden space-y-2">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-xs text-gray-500">Min length</label>
                            <input type="number" id="vMinLength" min="0" placeholder="e.g. 3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Max length</label>
                            <input type="number" id="vMaxLength" min="1" placeholder="default 255"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Allowed characters</label>
                        <select id="vFormat" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            <option value="none">Any text (no restriction)</option>
                            <option value="letters_only">Letters only (a-z, A-Z, spaces)</option>
                            <option value="numbers_only">Numbers only (0-9)</option>
                            <option value="alphanumeric">Alphanumeric (letters, numbers, spaces)</option>
                            <option value="alphanumeric_strict">Alphanumeric strict (no spaces)</option>
                            <option value="email">Email format</option>
                        </select>
                    </div>
                </div>

                <!-- Number rules -->
                <div id="numberRules" class="hidden space-y-2">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-xs text-gray-500">Min value</label>
                            <input type="number" id="vMin" placeholder="e.g. 0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Max value</label>
                            <input type="number" id="vMax" placeholder="e.g. 100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        </div>
                    </div>
                </div>

                <!-- File rules -->
                <div id="fileRules" class="hidden space-y-2">
                    <div>
                        <label class="text-xs text-gray-500">Max file size (KB)</label>
                        <input type="number" id="vMaxSizeKb" min="1" placeholder="default 10240 (10MB)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Allowed file types (comma separated)</label>
                        <input type="text" id="vAllowedTypes" placeholder="pdf,jpg,jpeg,png"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <p class="text-xs text-gray-400 mt-1">Leave blank for default: pdf, jpg, jpeg, png, doc, docx
                        </p>
                    </div>
                </div>

                <!-- Date rules -->
                <div id="dateRules" class="hidden space-y-2">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-xs text-gray-500">Min date</label>
                            <input type="date" id="vMinDate"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Max date</label>
                            <input type="date" id="vMaxDate"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            <p class="text-xs text-gray-400 mt-1">Leave blank for no limit</p>
                        </div>
                    </div>
                </div>

                <p id="noRulesMsg" class="text-xs text-gray-400">No extra rules for "select" type.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-3">
            <button type="button" onclick="closeModal()"
                class="px-5 py-2.5 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 text-sm font-medium">
                Cancel
            </button>
            <button type="button" name="button" class="btn-primary" onclick="saveField()"
                class="px-5 py-2.5 bg-primary text-white rounded-lg hover:bg-primary-600 text-sm font-medium">
                Save
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const kycTypeId = {{ $kycType->id }};

const rulesPanels = ['textRules', 'numberRules', 'fileRules', 'dateRules', 'noRulesMsg'];

function onTypeChange(type) {
    rulesPanels.forEach(id => document.getElementById(id).classList.add('hidden'));
    document.getElementById('optionsGroup').classList.add('hidden');

    if (type === 'text')   document.getElementById('textRules').classList.remove('hidden');
    if (type === 'number') document.getElementById('numberRules').classList.remove('hidden');
    if (type === 'file')   document.getElementById('fileRules').classList.remove('hidden');
    if (type === 'date')   document.getElementById('dateRules').classList.remove('hidden');
    if (type === 'select') {
        document.getElementById('optionsGroup').classList.remove('hidden');
        document.getElementById('noRulesMsg').classList.remove('hidden');
    }
}

function resetRuleInputs() {
    ['vMinLength','vMaxLength','vFormat',
     'vMin','vMax','vMaxSizeKb','vAllowedTypes','vMinDate','vMaxDate'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.value = el.tagName === 'SELECT' ? el.options[0].value : '';
    });
}

function fillRuleInputs(rules) {
    if (!rules) return;
    if (rules.min_length    !== undefined) document.getElementById('vMinLength').value  = rules.min_length;
    if (rules.max_length    !== undefined) document.getElementById('vMaxLength').value  = rules.max_length;
    if (rules.format        !== undefined) document.getElementById('vFormat').value     = rules.format;
    if (rules.min           !== undefined) document.getElementById('vMin').value        = rules.min;
    if (rules.max           !== undefined) document.getElementById('vMax').value        = rules.max;
    if (rules.max_size_kb   !== undefined) document.getElementById('vMaxSizeKb').value  = rules.max_size_kb;
    if (rules.allowed_types !== undefined) {
        const t = Array.isArray(rules.allowed_types) ? rules.allowed_types.join(',') : rules.allowed_types;
        document.getElementById('vAllowedTypes').value = t;
    }
    if (rules.min_date !== undefined) document.getElementById('vMinDate').value = rules.min_date;
    if (rules.max_date !== undefined) document.getElementById('vMaxDate').value = rules.max_date;
}

function buildValidationRules(type) {
    const rules = {};
    if (type === 'text') {
        const minLen = document.getElementById('vMinLength').value;
        const maxLen = document.getElementById('vMaxLength').value;
        const format = document.getElementById('vFormat').value;
        if (minLen !== '')          rules.min_length = parseInt(minLen);
        if (maxLen !== '')          rules.max_length = parseInt(maxLen);
        if (format && format !== 'none') rules.format = format;
    }
    if (type === 'number') {
        const min = document.getElementById('vMin').value;
        const max = document.getElementById('vMax').value;
        if (min !== '') rules.min = parseFloat(min);
        if (max !== '') rules.max = parseFloat(max);
    }
    if (type === 'file') {
        const kb    = document.getElementById('vMaxSizeKb').value;
        const types = document.getElementById('vAllowedTypes').value.trim();
        if (kb    !== '') rules.max_size_kb   = parseInt(kb);
        if (types !== '') rules.allowed_types = types.split(',').map(t => t.trim()).filter(Boolean);
    }
    if (type === 'date') {
        const minD = document.getElementById('vMinDate').value;
        const maxD = document.getElementById('vMaxDate').value;
        if (minD !== '') rules.min_date = minD;
        if (maxD !== '') rules.max_date = maxD;
    }
    return Object.keys(rules).length ? rules : null;
}

// Open add modal
document.getElementById('addFieldBtn').addEventListener('click', function () {
    document.getElementById('modalTitle').textContent = 'Add Field';
    document.getElementById('fieldId').value = '';
    document.getElementById('fieldName').value = '';
    document.getElementById('fieldKey').value = '';
    document.getElementById('fieldType').value = 'text';
    document.getElementById('fieldRequired').value = '1';
    document.getElementById('fieldOptions').value = '';
    resetRuleInputs();
    onTypeChange('text');
    document.getElementById('fieldModal').classList.remove('hidden');
});

function closeModal() {
    document.getElementById('fieldModal').classList.add('hidden');
}

function editField(field) {
    document.getElementById('modalTitle').textContent = 'Edit Field';
    document.getElementById('fieldId').value          = field.id;
    document.getElementById('fieldName').value        = field.field_name;
    document.getElementById('fieldKey').value         = field.field_key;
    document.getElementById('fieldType').value        = field.field_type;
    document.getElementById('fieldRequired').value    = field.is_required ? 1 : 0;

    if (field.options) {
        document.getElementById('fieldOptions').value = JSON.stringify(field.options);
    }

    resetRuleInputs();
    onTypeChange(field.field_type);
    fillRuleInputs(field.validation_rules);

    document.getElementById('fieldModal').classList.remove('hidden');
}

function saveField() {
    const fieldId  = document.getElementById('fieldId').value;
    const fieldType = document.getElementById('fieldType').value;
    const url    = fieldId
        ? `/admin/kyc/types/${kycTypeId}/fields/${fieldId}`
        : `/admin/kyc/types/${kycTypeId}/fields`;
    const method = fieldId ? 'PUT' : 'POST';

    const optionsRaw = document.getElementById('fieldOptions').value.trim();
    let parsedOptions = null;
    if (optionsRaw) {
        try { parsedOptions = JSON.parse(optionsRaw); }
        catch (e) { alert('Options JSON is invalid'); return; }
    }

    const data = {
        field_name:       document.getElementById('fieldName').value,
        field_key:        document.getElementById('fieldKey').value,
        field_type:       fieldType,
        is_required:      document.getElementById('fieldRequired').value,
        options:          parsedOptions,
        validation_rules: buildValidationRules(fieldType),
    };

    fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) location.reload();
        else alert(res.message || 'Error saving field');
    })
    .catch(err => alert('Error: ' + err.message));
}

function deleteField(id, name) {
    if (confirm(`Delete field "${name}"?`)) {
        fetch(`/admin/kyc/types/${kycTypeId}/fields/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) location.reload();
            else alert(res.message || 'Error deleting field');
        });
    }
}

// Init on page load
onTypeChange(document.getElementById('fieldType').value);
</script>
@endpush