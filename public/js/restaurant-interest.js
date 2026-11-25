(() => {
    const form = document.getElementById('restaurant-interest-form');
    if (!form) return;
    form.noValidate = true;
    const field = name => form.elements.namedItem(name);
    const country = field('country_id');
    const state = field('state_id');
    const city = field('city_id');
    const phoneCountry = field('phone_country_id');
    let phoneCountries = [];
    const syncPhoneCountry = () => {
        phoneCountry.value = phoneCountries.some(item => String(item.id) === country.value) ? country.value : '';
        if (errors.has(phoneCountry)) validate(phoneCountry);
    };
    const status = document.getElementById('restaurant-interest-location-status');
    const retry = document.getElementById('restaurant-interest-location-retry');
    const draftKey = 'dawat.restaurant-interest';
    const errors = new Map();
    let draft = {};
    let pending = null;
    let retryLoad = null;
    let submitting = false;
    try {
        const saved = JSON.parse(sessionStorage.getItem(draftKey) || 'null');
        if (saved && typeof saved === 'object') draft = saved;
    } catch { /* Storage is optional. */ }

    const showError = (input, message) => {
        if (!errors.has(input)) {
            const error = document.createElement('span');
            error.id = `restaurant-interest-${input.name}-error`;
            error.className = 'partner-field-error';
            error.setAttribute('aria-live', 'polite');
            const errorContainer = ['phone', 'phone_country_id'].includes(input.name)
                ? form.querySelector('[data-phone-errors]') : input.closest('label');
            errorContainer.appendChild(error);
            input.setAttribute('aria-describedby', error.id);
            errors.set(input, error);
        }
        errors.get(input).textContent = message;
        input.setAttribute('aria-invalid', String(Boolean(message)));
        return !message;
    };
    const validate = input => {
        const value = input.value.trim();
        let message = '';
        if (input.disabled || !value) {
            message = {
                restaurant_name: 'Enter your restaurant name.', phone: 'Enter your phone number.',
                phone_country_id: 'Select a phone country code.',
                country_id: 'Select a country.', state_id: 'Select a state / province.',
                city_id: 'Select a city.', address: 'Enter your full address.',
                postal_code: 'Enter your ZIP / postal code.',
            }[input.name];
        } else if (input.name === 'restaurant_name' &&
            (value.length < 2 || value.length > 150 || !/\p{L}/u.test(value))) {
            message = 'Enter a restaurant name with 2 to 150 characters, including at least one letter.';
        } else if (input.name === 'phone' &&
            !/^[0-9]{7,16}$/.test(value)) {
            message = 'Enter a phone number with 7 to 16 digits.';
        } else if (input.name === 'phone' && phoneCountry.value &&
            value.length + (phoneCountries.find(item => String(item.id) === phoneCountry.value)?.dialCode.length || 0) + 1 > 20) {
            message = 'Keep the phone number and country code to 20 characters or fewer.';
        } else if (input.name === 'address' && value.length > 100) {
            message = 'Please keep your full address to 100 characters or fewer.';
        } else if (input.name === 'postal_code' && (value.length < 5 || value.length > 9)) {
            message = 'Enter a ZIP / postal code with 5 to 9 characters.';
        }
        return showError(input, message);
    };
    const required = [...form.querySelectorAll('[required]')];
    required.forEach(input => {
        input.addEventListener('blur', () => validate(input));
        input.addEventListener(input.tagName === 'SELECT' ? 'change' : 'input', () => {
            if (input.name === 'phone') input.value = input.value.replace(/[^0-9]/g, '');
            if (errors.has(input)) validate(input);
        });
    });
    for (const name of ['restaurant_name', 'phone', 'address', 'postal_code']) {
        if (typeof draft[name] === 'string') field(name).value = draft[name];
    }

    const reset = (select, placeholder) => {
        select.replaceChildren(new Option(placeholder, ''));
        select.disabled = true;
        select.removeAttribute('aria-busy');
        if (errors.has(select)) showError(select, '');
    };
    const cancel = () => {
        pending?.abort();
        pending = null;
        retry.hidden = true;
        status.textContent = '';
    };
    const load = async (select, url, key, label, selected = '', next = null) => {
        cancel();
        const controller = new AbortController();
        pending = controller;
        retryLoad = () => load(select, url, key, label, selected, next);
        reset(select, `Loading ${label}…`);
        select.setAttribute('aria-busy', 'true');
        status.textContent = `Loading ${label}…`;
        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' }, signal: controller.signal,
            });
            if (!response.ok) throw new Error('Request failed');
            const data = await response.json();
            if (pending !== controller) return;
            if (!data.success || !Array.isArray(data[key])) throw new Error('Invalid response');
            const items = data[key];
            if (select === country) {
                phoneCountries = items.map(item => ({ ...item, dialCode: String(item.phonecode || '').replace(/[^0-9]/g, '') }))
                    .filter(item => item.dialCode);
                phoneCountry.replaceChildren(new Option('Code', ''));
                for (const item of phoneCountries) {
                    const option = new Option(`${item.code || item.name} +${item.dialCode}`, String(item.id));
                    option.title = `${item.name} (+${item.dialCode})`;
                    phoneCountry.add(option);
                }
                phoneCountry.disabled = !phoneCountries.length;
            }
            if (!items.length) {
                reset(select, `No ${label} available`);
                status.textContent = select === city ? 'No cities available. You can continue without selecting a city.' : `No ${label} are available. Please choose another location.`;
                return;
            }
            select.replaceChildren(new Option(`Select ${label}`, ''));
            items.forEach(item => select.add(new Option(item.name, String(item.id))));
            select.disabled = false;
            select.removeAttribute('aria-busy');
            pending = null;
            status.textContent = '';
            if (selected && items.some(item => String(item.id) === String(selected))) {
                select.value = String(selected);
                next?.();
            } else if (select === country && items.length === 1) {
                select.value = String(items[0].id);
                next?.();
            }
            if (select === country) {
                syncPhoneCountry();
                if (phoneCountries.some(item => String(item.id) === String(draft.phone_country_id))) {
                    phoneCountry.value = String(draft.phone_country_id);
                }
            }
        } catch (error) {
            if (error.name === 'AbortError' || pending !== controller) return;
            reset(select, `Unable to load ${label}`);
            status.textContent = `Could not load ${label}. Check your connection and try again.`;
            retry.hidden = false;
        } finally {
            if (pending === controller) {
                pending = null;
                select.removeAttribute('aria-busy');
            }
        }
    };
    const locationUrl = (url, id) => url.replace('__id__', encodeURIComponent(id));
    const loadCities = (selected = '') => load(city,
        locationUrl(form.dataset.citiesUrl, state.value), 'cities', 'city', selected);
    const loadStates = (selected = '', selectedCity = '') => load(state,
        locationUrl(form.dataset.statesUrl, country.value), 'states', 'state / province', selected,
        () => loadCities(selectedCity));
    country.addEventListener('change', () => {
        syncPhoneCountry();
        cancel();
        reset(state, 'Choose country first');
        reset(city, 'Choose state first');
        if (country.value) loadStates();
    });
    state.addEventListener('change', () => {
        cancel();
        reset(city, 'Choose state first');
        if (state.value) loadCities();
    });
    retry.addEventListener('click', () => retryLoad?.());
    load(country, form.dataset.countriesUrl, 'data', 'country', draft.country_id,
        () => loadStates(draft.state_id, draft.city_id));

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (submitting) return;
        const invalid = required.filter(input => !validate(input));
        if (invalid.length) {
            (invalid.find(input => !input.disabled) || country).focus();
            return;
        }
        for (const name of ['restaurant_name', 'phone', 'address', 'postal_code']) field(name).value = field(name).value.trim();
        // Check the submission limit even when CAPTCHA is disabled.
        {
            const output = document.getElementById('restaurant-interest-captcha-error');
            const token = field('g-recaptcha-response')?.value;
            output.textContent = '';
            if (form.dataset.captchaEnabled === 'true' && !token) {
                output.textContent = 'Please complete the reCAPTCHA challenge.';
                return;
            }
            const button = form.querySelector('[type="submit"]');
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 15000);
            submitting = true;
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            try {
                const response = await fetch(form.dataset.captchaUrl, {
                    method: 'POST', credentials: 'same-origin', signal: controller.signal,
                    headers: {
                        Accept: 'application/json', 'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': field('_token').value,
                    },
                    body: JSON.stringify({ 'g-recaptcha-response': token }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok || data.success !== true) {
                    const fallback = response.status === 419 ? 'Your session expired. Refresh the page and try again.'
                        : response.status === 429 ? 'Too many attempts. Please wait a minute and try again.'
                        : 'Unable to submit restaurant information. Please try again.';
                    throw new Error(data.errors?.['g-recaptcha-response']?.[0] || data.message || fallback);
                }
            } catch (error) {
                output.textContent = error.name === 'AbortError' || error instanceof TypeError
                    ? 'Unable to submit restaurant information. Check your connection and try again.' : error.message;
                window.grecaptcha?.reset();
                return;
            } finally {
                clearTimeout(timeout);
                submitting = false;
                button.disabled = false;
                button.removeAttribute('aria-busy');
            }
        }
        try {
            const draft = {};
            for (const name of ['restaurant_name', 'phone', 'phone_country_id', 'address', 'postal_code', 'country_id', 'state_id', 'city_id']) {
                draft[name] = field(name).value;
            }
            sessionStorage.setItem(draftKey, JSON.stringify(draft));
        } catch { /* Continue when browser storage is unavailable. */ }
        window.location.assign(form.dataset.signupUrl);
    });

    if (form.dataset.informationRequired === 'true' || window.location.hash === '#restaurant-interest-form') {
        const revealForm = () => {
            form.scrollIntoView({ block: 'start', behavior: 'instant' });
            form.focus({ preventScroll: true });
        };
        requestAnimationFrame(revealForm);
        // Reposition after images and fonts finish loading above the form.
        if (document.readyState !== 'complete') window.addEventListener('load', revealForm, { once: true });
    }
})();
