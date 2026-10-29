<style>
    #onboarding-tax-form [hidden], #tax-message[hidden], #tax-retry[hidden] { display: none !important; }
    .tax-intro { font-size: 20px; margin: 20px 0 28px; }
    .tax-fields, .tax-entity-fields { border: 0; padding: 0; margin: 0; min-width: 0; }
    .tax-entity { display: grid; grid-template-columns: minmax(0, 1fr) minmax(180px, 260px); align-items: center; gap: 24px; border: 1px solid #d4dfe3; border-radius: 16px; background: #f8fafb; padding: 24px; margin-bottom: 28px; }
    .tax-entity .tax-entity-copy label { font-size: 20px; font-weight: 700; margin-bottom: 6px; }
    .tax-entity-help { margin: 0; font-size: 13px; color: #61727a; }
    .tax-entity-value { display: flex; justify-content: flex-end; }
    .tax-entity .tax-entity-select { width: 100%; border-radius: 8px; background: #fff; }
    .tax-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 24px; }
    .tax-field { margin-bottom: 22px; min-width: 0; }
    .tax-field label { display: block; font-weight: 700; margin-bottom: 8px; }
    .tax-field input, .tax-field select { width: 100%; border: 1px solid #bdc8cc; border-radius: 4px; background: #fafbfb; color: #103647; padding: 12px; font: inherit; }
    .tax-field input:focus, .tax-field select:focus { outline: 2px solid #31be91; outline-offset: 2px; }
    .tax-check { margin: 0 0 20px; }
    .tax-check label { display: grid; grid-template-columns: 18px minmax(0, 1fr); align-items: start; column-gap: 12px; font-weight: 600; line-height: 24px; cursor: pointer; }
    .tax-check input[type="checkbox"] { display: block; margin: 3px 0 0; padding: 0; width: 18px; height: 18px; accent-color: #103647; }
    #onboarding-tax-form [aria-invalid="true"] { border-color: #a52e25; outline-color: #a52e25; }
    .tax-help { color: #61727a; font-size: 13px; margin-top: 8px; }
    .tax-error { color: #a52e25; font-size: 13px; margin-top: 6px; }
    .tax-message { padding: 14px 16px; margin: 16px 0; border-radius: 8px; background: #edf5f5; }
    .tax-message[data-error="true"] { background: #fff0ed; color: #973b21; }
    .tax-message[data-success="true"] { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .tax-save { display: block; width: min(100%, 540px); margin: 28px auto 16px; background: #111; color: #fff; }
    .tax-save:hover { background: #264b59; }
    .tax-save:disabled { opacity: .5; cursor: wait; }
    @media (max-width: 600px) { .tax-grid, .tax-entity { grid-template-columns: 1fr; } .tax-entity { gap: 16px; padding: 20px; } .tax-entity-value { justify-content: flex-start; } }
</style>
<p class="tax-intro">Enter your tax information here.</p>
<p class="tax-message" id="tax-message" role="status" aria-live="polite">Loading tax information…</p>
<button id="tax-retry" type="button" hidden>Retry loading</button>
<form id="onboarding-tax-form" novalidate>
    @csrf
    <fieldset id="tax-fields" class="tax-fields" disabled>
        <div class="tax-entity tax-field">
            <div class="tax-entity-copy">
                <label for="tax-tax_type">Preferred entity type</label>
                <p class="tax-entity-help" id="tax-entity-help">Choose Individual or Business. You can change this selection when updating your tax information.</p>
            </div>
            <div class="tax-entity-value">
                <select id="tax-tax_type" name="tax_type" class="tax-control tax-entity-select" aria-describedby="tax-entity-help" required>
                    <option value="individual">Individual</option>
                    <option value="business">Business</option>
                </select>
            </div>
        </div>
        @foreach(['individual' => [['full_name', 'Full Name', 'text', 100], ['ssn', 'Social Security Number (SSN)', 'password', 11]], 'business' => [['doing_business_as', 'DBA / Doing Business As', 'text', 150], ['ein', 'Employer Identification Number (EIN)', 'password', 10]]] as $entity => $entityFields)
            @if($entity === 'business')
        <div class="tax-field">
            <label for="tax-company_legal_name">Business Legal Entity Name *</label>
            <input class="tax-control" id="tax-company_legal_name" name="company_legal_name" maxlength="150" autocomplete="organization" required>
        </div>
            @endif
            <fieldset class="tax-entity-fields" data-tax-entity="{{ $entity }}" @if($entity === 'business') hidden disabled @endif>
                <div class="tax-grid">
                    @foreach($entityFields as [$name, $label, $kind, $length])
                        <div class="tax-field">
                            <label for="tax-{{ $name }}">{{ $label }}{{ $name !== 'doing_business_as' ? ' *' : '' }}</label>
                            <input class="tax-control" id="tax-{{ $name }}" name="{{ $name }}" type="{{ $kind }}" maxlength="{{ $length }}"
                                @if($kind === 'password') autocomplete="off" inputmode="numeric" @endif
                                @if($name !== 'doing_business_as') required @endif>
                            @if($kind === 'password')<p class="tax-help" id="tax-{{ $name }}-help">Enter 9 digits, with or without hyphens.</p>@endif
                        </div>
                    @endforeach
                </div>
            </fieldset>
        @endforeach
        <div class="tax-field">
            <label for="tax-address">Address *</label>
            <input class="tax-control" id="tax-address" name="address" maxlength="150" autocomplete="street-address" required>
        </div>
        <div class="tax-grid">
            @foreach(['country_id' => ['Country', 'country'], 'state_id' => ['State', 'address-level1'], 'city_id' => ['City', 'address-level2']] as $name => [$label, $autocomplete])
                <div class="tax-field">
                    <label for="tax-{{ $name }}">{{ $label }} *</label>
                    <select class="tax-control" id="tax-{{ $name }}" name="{{ $name }}" autocomplete="{{ $autocomplete }}" required @if($name !== 'country_id') disabled @endif><option value="">Select {{ strtolower($label) }}</option></select>
                </div>
            @endforeach
            <div class="tax-field">
                <label for="tax-zip_code">Zip / Postal Code *</label>
                <input class="tax-control" id="tax-zip_code" name="zip_code" maxlength="20" autocomplete="postal-code" required>
            </div>
        </div>
        <div class="tax-check">
            <label for="tax-same_mailing_address"><input class="tax-control" type="checkbox" id="tax-same_mailing_address" name="same_mailing_address" checked><span>My mailing address is the same as the one above</span></label>
        </div>
        <fieldset class="tax-entity-fields" id="tax-mailing" hidden disabled>
            <h3>Mailing address</h3>
            <div class="tax-grid">
                @foreach(['mailing_address' => ['Mailing Address', 150], 'mailing_city' => ['Mailing City', 100], 'mailing_state' => ['Mailing State', 100], 'mailing_zip_code' => ['Mailing Zip / Postal Code', 20]] as $name => [$label, $length])
                    <div class="tax-field">
                        <label for="tax-{{ $name }}">{{ $label }} *</label>
                        <input class="tax-control" id="tax-{{ $name }}" name="{{ $name }}" maxlength="{{ $length }}" required>
                    </div>
                @endforeach
            </div>
        </fieldset>
        <div class="tax-check"><label for="tax-receive_1099_electronically"><input class="tax-control" type="checkbox" id="tax-receive_1099_electronically" name="receive_1099_electronically"><span>I want to receive my 1099s electronically</span></label></div>
        <fieldset class="tax-entity-fields" data-tax-entity="business" hidden disabled>
        <div class="tax-check"><label for="tax-backup_tax_withholding"><input class="tax-control" type="checkbox" id="tax-backup_tax_withholding" name="backup_tax_withholding"><span>I am subject to backup tax withholding</span></label></div>
        <div class="tax-check"><label for="tax-tax_certification"><input class="tax-control" type="checkbox" id="tax-tax_certification" name="tax_certification" required><span>I certify that the tax information provided is correct *</span></label></div>
        </fieldset>
        <button class="tax-save" id="tax-save" type="submit">Submit Tax Information</button>
    </fieldset>
</form>
<noscript><p class="reason">Enable JavaScript to load and submit your tax information.</p></noscript>
<script>
(() => {
    const urls = {
        get: @json(route('api.restaurant.me.tax-information.show')),
        store: @json(route('api.restaurant.me.tax-information.store')),
        update: @json(route('api.restaurant.me.tax-information.update')),
        countries: @json(route('api.countries.country')),
        states: @json(route('api.countries.states-by-country', ['countryId' => '__ID__'])),
        cities: @json(route('api.countries.cities-by-state', ['stateId' => '__ID__'])),
    };
    const form = document.getElementById('onboarding-tax-form');
    const fields = document.getElementById('tax-fields'), message = document.getElementById('tax-message');
    const retry = document.getElementById('tax-retry'), save = document.getElementById('tax-save');
    const input = name => form.elements.namedItem(name);
    const controls = Array.from(form.querySelectorAll('.tax-control'));
    const country = input('country_id'), state = input('state_id'), city = input('city_id');
    let records = {}, drafts = {}, selectedType = 'individual', approved = false, locationVersion = 0;
    let successMessage = @json(session('tax_success'));
    function notify(text, error = false, success = false) {
        message.textContent = text; message.hidden = !text;
        message.dataset.error = String(error); message.dataset.success = String(success);
        if (error && text) window.onboardingToast(text, 'error');
    }
    async function api(url, options = {}) {
        let response;
        try { response = await fetch(url, {credentials: 'same-origin', ...options,
            headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': input('_token').value, ...options.headers}}); }
        catch { throw new Error('Unable to connect. Please check your internet connection and try again.'); }
        const result = await response.json().catch(() => ({}));
        if (!response.ok || result.success !== true) {
            const error = new Error([401, 419].includes(response.status) ? 'Your session expired. Refresh the page and sign in again.'
                : (response.status >= 500 ? 'Tax information is temporarily unavailable. Please try again.' : result.message || 'Unable to load or save tax information. Please try again.'));
            error.errors = result.errors || {}; throw error;
        }
        return result;
    }
    function options(select, rows, label, selected = '') {
        select.replaceChildren(new Option(label, ''));
        rows.forEach(row => select.add(new Option(row.name, String(row.id))));
        select.value = String(selected ?? ''); select.disabled = !rows.length;
    }
    function activeControls() {
        return controls.filter(control => {
            const entity = control.closest('[data-tax-entity]');
            return (!entity || entity.dataset.taxEntity === selectedType)
                && (!control.closest('#tax-mailing') || !input('same_mailing_address').checked);
        });
    }
    let savedType = '', baselines = {};
    function snapshot() {
        return Object.fromEntries(activeControls().map(control => [control.name, control.type === 'checkbox' ? control.checked : control.value.trim()]));
    }
    function clearError(control) {
        const id = `tax-error-${control.name}`;
        document.getElementById(id)?.remove(); control.removeAttribute('aria-invalid');
        const description = (control.getAttribute('aria-describedby') || '').split(' ').filter(part => part && part !== id).join(' ');
        if (description) control.setAttribute('aria-describedby', description); else control.removeAttribute('aria-describedby');
    }
    function fieldError(control, text) {
        clearError(control);
        const node = document.createElement('p'); node.id = `tax-error-${control.name}`; node.className = 'tax-error'; node.textContent = text;
        control.setAttribute('aria-invalid', 'true');
        control.setAttribute('aria-describedby', [control.getAttribute('aria-describedby'), node.id].filter(Boolean).join(' '));
        control.closest('.tax-field, .tax-check').append(node);
    }
    function identifierRequirements() {
        for (const name of ['ssn', 'ein']) {
            const relevant = (name === 'ssn' ? 'individual' : 'business') === selectedType;
            input(name).required = relevant && !records[selectedType]?.tax_identifier_masked;
            document.getElementById(`tax-${name}-help`).textContent = relevant && records[selectedType]?.tax_identifier_masked
                ? `Saved ${name.toUpperCase()}: ${records[selectedType].tax_identifier_masked}. Leave blank to keep it.`
                : 'Enter 9 digits, with or without hyphens.';
        }
    }
    function mailing() {
        const hidden = input('same_mailing_address').checked;
        const section = document.getElementById('tax-mailing'); section.hidden = hidden; section.disabled = hidden;
        if (hidden) section.querySelectorAll('.tax-control').forEach(clearError);
    }
    async function populate(type) {
        ++locationVersion; save.disabled = false;
        selectedType = type; fields.disabled = true; controls.forEach(clearError);
        const data = drafts[type] || records[type] || {};
        input('tax_type').value = type;
        input('tax_type').disabled = false;
        input('tax_type').hidden = false;
        document.getElementById('tax-entity-help').textContent = 'Choose Individual or Business. You can change this selection when updating your tax information.';
        document.querySelectorAll('[data-tax-entity]').forEach(group => {
            group.hidden = group.dataset.taxEntity !== type; group.disabled = group.hidden;
        });
        for (const control of controls) {
            if (control.name === 'tax_type' || control.tagName === 'SELECT') continue;
            if (control.type === 'checkbox') control.checked = !!(data[control.name] ?? (control.name === 'same_mailing_address'));
            else control.value = data[control.name] ?? '';
        }
        country.value = String(data.country_id ?? '');
        options(state, [], 'Select state'); options(city, [], 'Select city');
        mailing(); identifierRequirements();
        save.textContent = Object.values(records).some(Boolean) ? 'Update Tax Information' : 'Submit Tax Information';
        save.hidden = false;
        try {
            if (country.value) {
                const result = await api(urls.states.replace('__ID__', encodeURIComponent(country.value)));
                options(state, result.states, 'Select state', data.state_id);
                if (state.value) {
                    const result = await api(urls.cities.replace('__ID__', encodeURIComponent(state.value)));
                    options(city, result.cities, 'Select city', data.city_id);
                }
            }
        } finally { fields.disabled = false; }
        if (!(type in baselines) && !drafts[type]) baselines[type] = JSON.stringify(snapshot());
    }
    async function initialize() {
        fields.disabled = true; retry.hidden = true; notify('Loading tax information…');
        try {
            const [tax, countries] = await Promise.all([api(urls.get), api(urls.countries)]);
            records = tax.data; drafts = {}; baselines = {}; savedType = tax.tax_type || 'individual'; approved = tax.onboarding_step?.status === 'approved';
            options(country, countries.data, 'Select country');
            await populate(['individual', 'business'].includes(tax.tax_type) ? tax.tax_type : 'individual');
            notify([successMessage, approved ? 'Your tax information is approved. Any changes will be submitted for review again.' : ''].filter(Boolean).join(' '), false, !!successMessage);
            if (successMessage) { message.scrollIntoView({block: 'center'}); successMessage = null; }
        } catch (error) { fields.disabled = true; notify(error.message, true); retry.hidden = false; }
    }
    retry.addEventListener('click', initialize);
    input('tax_type').addEventListener('change', async () => {
        const type = input('tax_type').value;
        drafts[selectedType] = snapshot(); notify('');
        try { await populate(type); } catch (error) { notify(error.message + ' Select the location again to retry.', true); }
    });
    input('same_mailing_address').addEventListener('change', mailing);
    async function changeLocation(parent, target, url, key, label) {
        const version = ++locationVersion; options(target, [], label);
        if (parent === country) options(city, [], 'Select city');
        save.disabled = true; notify('');
        try {
            if (parent.value) {
                const result = await api(url.replace('__ID__', encodeURIComponent(parent.value)));
                if (version === locationVersion) options(target, result[key], label);
            }
        } catch (error) { if (version === locationVersion) notify(error.message + ' Select the location again to retry.', true); }
        finally { if (version === locationVersion) save.disabled = false; }
    }
    country.addEventListener('change', () => changeLocation(country, state, urls.states, 'states', 'Select state'));
    state.addEventListener('change', () => changeLocation(state, city, urls.cities, 'cities', 'Select city'));
    function validationError(control) {
        const label = control.closest('.tax-field, .tax-check').querySelector('label').textContent.replace('*', '').trim();
        const value = control.value.trim();
        if (control.type === 'checkbox') return control.required && !control.checked ? 'Please certify that your tax information is correct before submitting.' : '';
        if (control.required && !value) return `${control.tagName === 'SELECT' ? 'Please select' : 'Please enter'} ${label.toLowerCase()}.`;
        if (control.name === 'ssn' && value && !/^(?:[0-9]{9}|[0-9]{3}-[0-9]{2}-[0-9]{4})$/.test(value)) return 'Enter a 9-digit SSN, with or without hyphens (123-45-6789).';
        if (control.name === 'ein' && value && !/^(?:[0-9]{9}|[0-9]{2}-[0-9]{7})$/.test(value)) return 'Enter a 9-digit EIN, with or without a hyphen (12-3456789).';
        if (control.maxLength > 0 && value.length > control.maxLength) return `Please enter no more than ${control.maxLength} characters.`;
        return '';
    }
    for (const control of controls) for (const event of ['input', 'change']) control.addEventListener(event, () => {
        if (!control.hasAttribute('aria-invalid')) return;
        const error = validationError(control); if (error) fieldError(control, error); else clearError(control);
    });
    function focusError() {
        const control = form.querySelector('[aria-invalid="true"]'); control?.focus(); control?.scrollIntoView({block: 'center'});
    }
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (fields.disabled || save.disabled) return;
        if (records[selectedType] && selectedType === savedType && JSON.stringify(snapshot()) === baselines[selectedType]) {
            notify('No changes were made. Please update at least one field before submitting again.', true);
            message.scrollIntoView({block: 'center'});
            return;
        }
        controls.forEach(clearError);
        for (const control of activeControls()) { const error = validationError(control); if (error) fieldError(control, error); }
        if (form.querySelector('[aria-invalid="true"]')) { notify('Please correct the highlighted fields and submit again.', true); focusError(); return; }
        const payload = snapshot(), existing = Object.values(records).some(Boolean);
        if (!await window.confirmOnboardingSubmission(existing
            ? 'Update your tax information and submit it for review?'
            : 'Submit your tax information for review?')) return;
        if (records[selectedType] && !payload.ssn) delete payload.ssn;
        if (records[selectedType] && !payload.ein) delete payload.ein;
        fields.disabled = true; notify('Saving tax information…');
        try {
            const result = await api(existing ? urls.update : urls.store, {method: existing ? 'PATCH' : 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload)});
            window.onboardingComplete(result.message || 'Tax information submitted successfully for review.');
        } catch (error) {
            fields.disabled = false;
            const general = [];
            for (const [name, errors] of Object.entries(error.errors || {})) {
                const control = input(name), text = Array.isArray(errors) ? errors.join(' ') : String(errors);
                if (control?.closest('.tax-field, .tax-check')) fieldError(control, text); else general.push(text);
            }
            notify(general.length ? general.join(' ') : error.message, true); focusError();
            if (!form.querySelector('[aria-invalid="true"]')) message.scrollIntoView({block: 'center'});
        }
    });
    initialize();
})();
</script>
