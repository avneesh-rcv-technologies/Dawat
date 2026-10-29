(() => {
    document.querySelectorAll('form[data-location-selects]').forEach(form => {
        if (form.dataset.locationInitialized) return;
        form.dataset.locationInitialized = 'true';
        const prefix = form.dataset.locationSelects;
        const country = form.querySelector(`#${prefix}-country_id`);
        const state = form.querySelector(`#${prefix}-state_id`);
        const city = form.querySelector(`#${prefix}-city_id`);
        const error = form.querySelector(`#${prefix}-location-error`);
        let statesRequest, citiesRequest;
        const message = text => {
            error.textContent = text;
            error.classList.toggle('hidden', !text);
            if (text) window.onboardingToast?.(text, 'error');
        };
        const reset = (select, label) => {
            select.replaceChildren(new Option(label, ''));
            select.disabled = true;
            select.removeAttribute('aria-busy');
        };
        const load = async (select, url, key, controller) => {
            reset(select, `Loading ${key}…`);
            select.setAttribute('aria-busy', 'true');
            message('');
            try {
                const response = await fetch(url, { signal: controller.signal, headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Request failed');
                const result = await response.json();
                if (controller.signal.aborted) return;
                if (!result.success || !Array.isArray(result[key])) throw new Error('Invalid response');
                if (!result[key].length) {
                    reset(select, `No ${key} available`);
                    message(`No ${key} are available for this selection. Choose another location.`);
                    return;
                }
                select.replaceChildren(new Option(key === 'states' ? 'Select state' : 'Select city', ''));
                result[key].forEach(item => select.add(new Option(item.name, item.id)));
                select.disabled = false;
            } catch (failure) {
                if (controller.signal.aborted) return;
                reset(select, `Unable to load ${key}`);
                message(`Could not load ${key}. Select the ${key === 'states' ? 'country' : 'state'} again to retry.`);
            } finally {
                if (!controller.signal.aborted) select.removeAttribute('aria-busy');
            }
        };
        if (!country.value) reset(state, 'Choose country first');
        if (!state.value) reset(city, 'Choose state first');
        country.addEventListener('change', () => {
            statesRequest?.abort(); citiesRequest?.abort();
            reset(state, 'Choose country first'); reset(city, 'Choose state first');
            message('');
            if (country.value) {
                statesRequest = new AbortController();
                load(state, form.dataset.statesUrl.replace('__ID__', encodeURIComponent(country.value)), 'states', statesRequest);
            }
        });
        state.addEventListener('change', () => {
            citiesRequest?.abort(); reset(city, 'Choose state first');
            message('');
            if (state.value) {
                citiesRequest = new AbortController();
                load(city, form.dataset.citiesUrl.replace('__ID__', encodeURIComponent(state.value)), 'cities', citiesRequest);
            }
        });
        city.addEventListener('change', () => message(''));
        form.addEventListener('submit', event => {
            if (state.disabled || city.disabled || !country.value || !state.value || !city.value) {
                event.preventDefault();
                event.stopImmediatePropagation();
                message('Select a country, state and city, and wait for the lists to finish loading.');
                (!country.value ? country : !state.disabled && !state.value ? state : !city.disabled ? city : country).focus();
            }
        }, true);
    });
})();
