<style>
    #onboarding-bank-form [hidden], #bank-message[hidden], #bank-retry[hidden] { display: none !important; }
    .bank-intro { font-size: 20px; margin: 16px 0 28px; }
    .bank-fields { border: 0; padding: 0; margin: 0; min-width: 0; }
    .bank-field { margin-bottom: 22px; min-width: 0; }
    .bank-field label { display: block; font-weight: 700; margin-bottom: 8px; }
    .bank-field input, .bank-field select { width: 100%; border: 1px solid #bdc8cc; border-radius: 4px; background: #fafbfb; padding: 12px; color: #103647; font: inherit; }
    .bank-field input:focus, .bank-field select:focus { outline: 2px solid #31be91; outline-offset: 2px; }
    .bank-field [aria-invalid="true"] { border-color: #a52e25; outline-color: #a52e25; }
    .bank-account-fields { max-width: 600px; }
    .bank-help { font-size: 13px; color: #61727a; margin-top: 8px; }
    .bank-company-title { text-align: center; font-size: 22px; margin: 32px 0 24px; }
    .bank-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 24px; }
    .bank-wide { grid-column: 1 / -1; }
    .bank-save { display: block; width: min(100%, 540px); margin: 24px auto 0; background: #111; color: white; border-color: #111; }
    .bank-save:hover { background: #263c45; }
    .bank-save:disabled { opacity: .5; cursor: wait; }
    .bank-error { color: #a52e25; font-size: 13px; margin: 6px 0 0; }
    .bank-message { padding: 14px 16px; margin: 16px 0; border-radius: 8px; background: #edf5f5; }
    .bank-message[data-error="true"] { background: #fff0ed; color: #973b21; }
    .bank-message[data-success="true"] { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    @media (max-width: 600px) { .bank-grid { grid-template-columns: 1fr; } }
</style>
<p class="bank-intro">Earnings will be deposited into the bank account you add below.</p>
<p id="bank-message" class="bank-message" role="status" aria-live="polite">Loading banking information…</p>
<button type="button" id="bank-retry" hidden>Retry loading</button>
<form id="onboarding-bank-form" novalidate>
    @csrf
    <fieldset class="bank-fields" id="bank-fields" disabled>
        <div class="bank-account-fields">
            <div class="bank-field">
                <label for="bank-account_holder_name">Name of Account Holder *</label>
                <input id="bank-account_holder_name" name="account_holder_name" maxlength="150" autocomplete="name" required>
                <p class="bank-help">Enter the legal business name, or your first and last name for an individual sole proprietorship.</p>
            </div>
            <div class="bank-field">
                <label for="bank-routing_number">Routing Number *</label>
                <input id="bank-routing_number" name="routing_number" inputmode="numeric" pattern="[0-9]{9}" maxlength="9" required>
                <p class="bank-help">Enter your 9-digit business account routing number.</p>
            </div>
            <div class="bank-field">
                <label for="bank-account_number">Bank Account Number <span id="bank-account-required">*</span></label>
                <input id="bank-account_number" name="account_number" type="password" inputmode="numeric" pattern="[0-9]{4,17}" maxlength="17" autocomplete="off" required>
                <p class="bank-help" id="bank-account-help">Enter an account number containing 4 to 17 digits.</p>
            </div>
            <div class="bank-field">
                <label for="bank-account_number_confirmation">Re-enter Account Number <span id="bank-confirm-required">*</span></label>
                <input id="bank-account_number_confirmation" name="account_number_confirmation" type="password" inputmode="numeric" maxlength="17" autocomplete="off" required>
            </div>
        </div>
        <h3 class="bank-company-title">Company Information</h3>
        <div class="bank-grid">
            <div class="bank-field">
                <label for="bank-business_legal_name">Business Legal Entity Name *</label>
                <input id="bank-business_legal_name" name="business_legal_name" maxlength="150" autocomplete="organization" required>
            </div>
            <div class="bank-field">
                <label for="bank-doing_business_as">DBA / Doing Business As</label>
                <input id="bank-doing_business_as" name="doing_business_as" maxlength="150">
            </div>
            <div class="bank-field bank-wide">
                <label for="bank-address">Address *</label>
                <input id="bank-address" name="address" maxlength="255" autocomplete="street-address" required>
            </div>
            <div class="bank-field">
                <label for="bank-country_id">Country *</label>
                <select id="bank-country_id" name="country_id" autocomplete="country" required><option value="">Select country</option></select>
            </div>
            <div class="bank-field">
                <label for="bank-state_id">State *</label>
                <select id="bank-state_id" name="state_id" autocomplete="address-level1" required disabled><option value="">Select state</option></select>
            </div>
            <div class="bank-field">
                <label for="bank-city_id">City *</label>
                <select id="bank-city_id" name="city_id" autocomplete="address-level2" required disabled><option value="">Select city</option></select>
            </div>
            <div class="bank-field">
                <label for="bank-postal_code">Zip / Postal Code *</label>
                <input id="bank-postal_code" name="postal_code" maxlength="20" autocomplete="postal-code" required>
            </div>
        </div>
        <button class="bank-save" type="submit" id="bank-save">Submit Banking Information</button>
    </fieldset>
</form>
<noscript><p class="reason">Enable JavaScript to load and submit your banking information.</p></noscript>
<script>
(() => {
    const urls = {
        show: @json(route('api.restaurant.me.banking.show')),
        store: @json(route('api.restaurant.me.banking.store')),
        update: @json(route('api.restaurant.me.banking.update')),
        countries: @json(route('api.countries.country')),
        states: @json(route('api.countries.states-by-country', ['countryId' => '__ID__'])),
        cities: @json(route('api.countries.cities-by-state', ['stateId' => '__ID__'])),
    };
    const form = document.getElementById('onboarding-bank-form');
    const fields = document.getElementById('bank-fields');
    const message = document.getElementById('bank-message');
    const retry = document.getElementById('bank-retry');
    const save = document.getElementById('bank-save');
    const input = name => form.elements.namedItem(name);
    const country = input('country_id'), state = input('state_id'), city = input('city_id');
    let existing = false, approved = false, locationVersion = 0;
    let savedValues = '';
    const snapshot = () => JSON.stringify(controls.map(control => [control.name, control.value.trim()]));
    let successMessage = @json(session('banking_success'));

    function notify(text, error = false, success = false) {
        message.textContent = text;
        message.hidden = !text;
        message.dataset.error = String(error);
        message.dataset.success = String(success);
        if (error && text) window.onboardingToast(text, 'error');
    }

    async function api(url, options = {}) {
        let response;
        try { response = await fetch(url, {
            credentials: 'same-origin', ...options,
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': input('_token').value, ...options.headers },
        }); } catch {
            throw new Error('Unable to connect. Please check your internet connection and try again.');
        }
        const data = await response.json().catch(() => ({}));
        if (!response.ok || data.success === false) {
            const error = new Error(response.status === 419 || response.status === 401
                ? 'Your session expired. Refresh the page and sign in again.'
                : (response.status >= 500
                    ? 'Banking information is temporarily unavailable. Please try again.'
                    : (data.message || 'Unable to load or save banking information. Please try again.')));
            error.errors = data.errors || {};
            throw error;
        }
        return data;
    }

    function options(select, rows, label, selected = '') {
        select.replaceChildren(new Option(label, ''));
        for (const row of rows) select.add(new Option(row.name, String(row.id)));
        select.value = String(selected ?? '');
        select.disabled = rows.length === 0;
    }

    function accountRequirements() {
        const required = !existing || input('account_number').value.length > 0 || input('account_number_confirmation').value.length > 0;
        input('account_number').required = required;
        input('account_number_confirmation').required = required;
        document.getElementById('bank-account-required').textContent = required ? '*' : '';
        document.getElementById('bank-confirm-required').textContent = required ? '*' : '';
        input('account_number_confirmation').setCustomValidity(
            input('account_number_confirmation').value && input('account_number_confirmation').value !== input('account_number').value
                ? 'The account numbers do not match.' : ''
        );
    }
    input('account_number').addEventListener('input', accountRequirements);
    input('account_number_confirmation').addEventListener('input', accountRequirements);

    function clearFieldError(control) {
        const id = `bank-error-${control.name}`;
        document.getElementById(id)?.remove();
        control.removeAttribute('aria-invalid');
        const description = (control.getAttribute('aria-describedby') || '').split(' ').filter(value => value && value !== id).join(' ');
        if (description) control.setAttribute('aria-describedby', description);
        else control.removeAttribute('aria-describedby');
    }

    function fieldError(control, text) {
        clearFieldError(control);
        const node = document.createElement('p');
        node.className = 'bank-error'; node.id = `bank-error-${control.name}`;
        node.textContent = text;
        control.setAttribute('aria-invalid', 'true');
        control.setAttribute('aria-describedby', [control.getAttribute('aria-describedby'), node.id].filter(Boolean).join(' '));
        control.closest('.bank-field').append(node);
    }

    function validationError(control) {
        const value = control.value.trim();
        const requiredMessages = {
            account_holder_name: 'Please enter the account holder’s name.',
            routing_number: 'Please enter your 9-digit routing number.',
            account_number: 'Please enter your bank account number.',
            account_number_confirmation: 'Please re-enter your bank account number.',
            business_legal_name: 'Please enter the business legal entity name.',
            address: 'Please enter your business address.',
            country_id: 'Please select a country.', state_id: 'Please select a state.', city_id: 'Please select a city.',
            postal_code: 'Please enter your zip or postal code.',
        };
        if (control.required && !value) return requiredMessages[control.name] || 'Please complete this field.';
        if (control.name === 'routing_number' && value && !/^[0-9]{9}$/.test(value)) return 'The routing number must contain exactly 9 digits.';
        if (control.name === 'account_number' && value && !/^[0-9]{4,17}$/.test(value)) return 'The account number must contain 4 to 17 digits.';
        if (control.name === 'account_number_confirmation' && value && value !== input('account_number').value.trim()) return 'The account numbers do not match. Please re-enter the same account number.';
        if (control.maxLength > 0 && value.length > control.maxLength) return `Please enter no more than ${control.maxLength} characters.`;
        return '';
    }

    const controls = Array.from(form.querySelectorAll('.bank-field input, .bank-field select'));
    for (const control of controls) {
        const updateError = () => {
            if (control.hasAttribute('aria-invalid')) {
                const error = validationError(control);
                if (error) fieldError(control, error);
                else clearFieldError(control);
            }
            if (control.name === 'account_number' && input('account_number_confirmation').hasAttribute('aria-invalid')) {
                const confirmation = input('account_number_confirmation');
                const error = validationError(confirmation);
                if (error) fieldError(confirmation, error);
                else clearFieldError(confirmation);
            }
        };
        control.addEventListener('input', updateError);
        control.addEventListener('change', updateError);
    }

    function focusFirstError() {
        const control = form.querySelector('[aria-invalid="true"]');
        control?.focus();
        control?.scrollIntoView({ block: 'center' });
    }

    async function initialize() {
        fields.disabled = true;
        retry.hidden = true;
        notify('Loading banking information…');
        try {
            const [bank, countries] = await Promise.all([api(urls.show), api(urls.countries)]);
            const data = bank.data;
            existing = !!data;
            approved = bank.onboarding_step?.status === 'approved';
            for (const name of ['account_holder_name', 'routing_number', 'business_legal_name', 'doing_business_as', 'address', 'postal_code']) {
                input(name).value = data?.[name] ?? '';
            }
            input('account_number').value = '';
            input('account_number_confirmation').value = '';
            document.getElementById('bank-account-help').textContent = existing
                ? `Saved account: ${data.account_number_masked}. Leave both account number fields blank to keep it.`
                : 'Enter an account number containing 4 to 17 digits.';
            options(country, countries.data, 'Select country', data?.country_id);
            options(state, [], 'Select state');
            options(city, [], 'Select city');
            if (country.value) {
                const result = await api(urls.states.replace('__ID__', encodeURIComponent(country.value)));
                options(state, result.states, 'Select state', data?.state_id);
                if (state.value) {
                    const result = await api(urls.cities.replace('__ID__', encodeURIComponent(state.value)));
                    options(city, result.cities, 'Select city', data?.city_id);
                }
            }
            accountRequirements();
            savedValues = snapshot();
            save.textContent = existing ? 'Update Banking Information' : 'Submit Banking Information';
            fields.disabled = approved;
            save.hidden = approved;
            notify([successMessage, approved ? 'Your banking information is approved and cannot be updated.' : ''].filter(Boolean).join(' '), false, !!successMessage);
            if (successMessage) {
                message.scrollIntoView({ block: 'center' });
                successMessage = null;
            }
        } catch (error) {
            notify(error.message, true);
            retry.hidden = false;
        }
    }
    retry.addEventListener('click', initialize);

    async function changeLocation(parent, target, url, key, label) {
        const version = ++locationVersion;
        options(target, [], label);
        if (parent === country) options(city, [], 'Select city');
        save.disabled = true;
        notify('');
        try {
            if (parent.value) {
                const result = await api(url.replace('__ID__', encodeURIComponent(parent.value)));
                if (version === locationVersion) options(target, result[key], label);
            }
        } catch (error) {
            if (version === locationVersion) notify(error.message + ' Select the location again to retry.', true);
        } finally {
            if (version === locationVersion) save.disabled = false;
        }
    }
    country.addEventListener('change', () => changeLocation(country, state, urls.states, 'states', 'Select state'));
    state.addEventListener('change', () => changeLocation(state, city, urls.cities, 'cities', 'Select city'));

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (approved || fields.disabled || save.disabled) return;
        if (existing && snapshot() === savedValues) {
            notify('No changes were made. Please update at least one field before submitting again.', true);
            message.scrollIntoView({ block: 'center' });
            return;
        }
        accountRequirements();
        controls.forEach(clearFieldError);
        for (const control of controls) {
            const error = validationError(control);
            if (error) fieldError(control, error);
        }
        if (form.querySelector('[aria-invalid="true"]')) {
            notify('Please correct the highlighted fields and submit again.', true);
            focusFirstError();
            return;
        }
        if (!await window.confirmOnboardingSubmission(existing
            ? 'Update your banking information and submit it for review?'
            : 'Submit your banking information for review?')) return;
        const payload = Object.fromEntries(new FormData(form));
        delete payload._token;
        if (existing && !payload.account_number && !payload.account_number_confirmation) {
            delete payload.account_number;
            delete payload.account_number_confirmation;
        }
        fields.disabled = true;
        notify('Saving banking information…');
        try {
            const result = await api(existing ? urls.update : urls.store, {
                method: existing ? 'PATCH' : 'POST',
                headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
            });
            // Reload the GET data and saved review status after a successful submission.
            window.onboardingComplete(result.message || 'Banking information submitted successfully for review.');
        } catch (error) {
            fields.disabled = false;
            const generalErrors = [];
            for (const [name, errors] of Object.entries(error.errors || {})) {
                const control = input(name);
                const text = Array.isArray(errors) ? errors.join(' ') : String(errors);
                if (control?.closest('.bank-field')) fieldError(control, text);
                else generalErrors.push(text);
            }
            notify(generalErrors.length ? generalErrors.join(' ') : error.message, true);
            focusFirstError();
            if (!form.querySelector('[aria-invalid="true"]')) message.scrollIntoView({ block: 'center' });
        }
    });
    initialize();
})();
</script>
