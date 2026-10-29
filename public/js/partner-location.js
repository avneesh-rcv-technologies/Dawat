(() => {
    const root = document.getElementById('partner-location');
    if (!root) return;
    const country = document.getElementById('partner-country');
    const state = document.getElementById('partner-state');
    const city = document.getElementById('partner-city');
    const status = document.getElementById('location-status');
    const retry = document.getElementById('location-retry');
    const form = root.closest('form');
    let pending = null;
    let retryLoad = null;

    const message = (text, error = false) => {
        status.textContent = text;
        status.dataset.error = String(error);
    };
    const reset = (select, placeholder) => {
        select.replaceChildren(new Option(placeholder, ''));
        select.disabled = true;
        select.removeAttribute('aria-busy');
    };
    const clearError = (select) => {
        select.setAttribute('aria-invalid', 'false');
        document.getElementById(`${select.id.replace('partner-', '')}-error`).textContent = '';
    };
    const load = async (select, url, key, parent) => {
        pending?.abort();
        const controller = new AbortController();
        pending = controller;
        retry.hidden = true;
        retryLoad = () => load(select, url, key, parent);
        reset(select, `Loading ${key}…`);
        select.setAttribute('aria-busy', 'true');
        message(`Loading ${key}…`);
        try {
            const response = await fetch(url.replace('__id__', encodeURIComponent(parent)), {
                headers: { Accept: 'application/json' }, signal: controller.signal,
            });
            if (!response.ok) throw new Error('Request failed');
            const data = await response.json();
            if (!data.success || !Array.isArray(data[key])) throw new Error('Invalid response');
            if (pending !== controller) return;
            const items = data[key];
            if (!items.length) {
                reset(select, `No ${key} available`);
                message(`No ${key} are available for this selection. Please choose another ${key === 'states' ? 'country' : 'state'}.`, true);
                return;
            }
            select.replaceChildren(new Option(`Select ${key === 'states' ? 'state / province' : 'city'}`, ''));
            items.forEach(item => select.add(new Option(item.name, item.id)));
            select.disabled = false;
            message(key === 'states' ? 'Now choose your state or province.' : 'Now choose your city.');
        } catch (error) {
            if (error.name === 'AbortError' || pending !== controller) return;
            reset(select, `Unable to load ${key}`);
            message(`We couldn't load ${key}. Check your connection and try again.`, true);
            retry.hidden = false;
        } finally {
            if (pending === controller) {
                select.removeAttribute('aria-busy');
                pending = null;
            }
        }
    };

    country.addEventListener('change', () => {
        pending?.abort();
        pending = null;
        retry.hidden = true;
        [country, state, city].forEach(clearError);
        reset(state, 'Choose country first');
        reset(city, 'Choose state first');
        message('');
        if (country.value) load(state, root.dataset.statesUrl, 'states', country.value);
    });
    state.addEventListener('change', () => {
        pending?.abort();
        pending = null;
        retry.hidden = true;
        [state, city].forEach(clearError);
        reset(city, 'Choose state first');
        message('');
        if (state.value) load(city, root.dataset.citiesUrl, 'cities', state.value);
    });
    city.addEventListener('change', () => {
        clearError(city);
        message(city.value ? 'Restaurant location selected.' : 'Please choose your city.');
    });
    retry.addEventListener('click', () => retryLoad?.());
    // Disabled dependent fields are skipped by browser validation; validate before OTP.
    form.addEventListener('submit', (event) => {
        if (!country.value || !state.value || !city.value || state.disabled || city.disabled) {
            event.preventDefault();
            event.stopImmediatePropagation();
            message('Please select a country, state and city before continuing.', true);
            const target = !country.value ? country : !state.value && !state.disabled ? state : !city.disabled ? city : country;
            target.focus();
        }
    }, true);
})();
