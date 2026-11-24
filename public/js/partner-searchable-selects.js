(() => {
    'use strict';
    const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase().trim();
    ['partner-state', 'partner-city', 'restaurant-interest-state_id', 'restaurant-interest-city_id'].forEach(id => {
        const select = document.getElementById(id);
        if (!select || select.dataset.searchEnhanced) return;
        select.dataset.searchEnhanced = 'true';
        const isState = id === 'partner-state' || select.name === 'state_id';
        const wrapper = document.createElement('div');
        wrapper.className = 'partner-search-select';
        const input = document.createElement('input');
        input.type = 'text';
        input.id = `${id}-search`;
        input.className = 'partner-search-input';
        input.autocomplete = 'off';
        input.spellcheck = false;
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-controls', `${id}-options`);
        if (select.getAttribute('aria-describedby')) input.setAttribute('aria-describedby', select.getAttribute('aria-describedby'));
        input.setAttribute('aria-required', String(select.required));
        const list = document.createElement('div');
        list.id = `${id}-options`;
        list.className = 'partner-search-options';
        list.setAttribute('role', 'listbox');
        list.setAttribute('aria-label', isState ? 'States and provinces' : 'Cities');
        list.hidden = true;
        const arrow = document.createElement('span');
        arrow.className = 'partner-search-arrow';
        arrow.textContent = '⌄';
        arrow.setAttribute('aria-hidden', 'true');
        select.before(wrapper);
        wrapper.append(select, input, arrow, list);
        const label = document.querySelector(`label[for="${id}"]`);
        if (label) label.htmlFor = input.id;
        select.classList.add('partner-search-native');
        select.tabIndex = -1;
        select.setAttribute('aria-hidden', 'true');
        let matches = [];
        let active = -1;
        let open = false;
        const restore = () => {
            input.value = select.value ? select.selectedOptions[0]?.textContent || '' : '';
        };
        const highlight = index => {
            active = index;
            Array.from(list.children).forEach((option, position) => {
                option.classList.toggle('is-highlighted', position === active);
                if (option.getAttribute('role') === 'option') option.setAttribute('aria-selected', String(matches[position]?.value === select.value));
            });
            if (active >= 0 && matches[active]) {
                input.setAttribute('aria-activedescendant', `${id}-option-${active}`);
                list.children[active]?.scrollIntoView({block: 'nearest'});
            } else input.removeAttribute('aria-activedescendant');
        };
        const close = () => {
            open = false;
            list.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
            restore();
        };
        const choose = option => {
            select.value = option.value;
            select.dispatchEvent(new Event('change', {bubbles: true}));
            close();
            input.focus();
        };
        const render = (query = '') => {
            matches = Array.from(select.options).filter(option => option.value && !option.disabled && normalize(option.textContent).includes(normalize(query)));
            list.replaceChildren();
            matches.forEach((option, index) => {
                const row = document.createElement('div');
                row.id = `${id}-option-${index}`;
                row.className = 'partner-search-option';
                row.setAttribute('role', 'option');
                row.setAttribute('aria-selected', String(option.value === select.value));
                row.textContent = option.textContent;
                // Keep focus on the combobox until the option click commits its value.
                row.addEventListener('pointerdown', event => event.preventDefault());
                row.addEventListener('mousedown', event => event.preventDefault());
                row.addEventListener('click', event => {
                    // The list sits inside a label; its default click would reopen the input.
                    event.preventDefault();
                    event.stopPropagation();
                    choose(option);
                });
                list.append(row);
            });
            if (!matches.length) {
                const empty = document.createElement('p');
                empty.className = 'partner-search-empty';
                empty.textContent = 'No matching locations';
                empty.setAttribute('role', 'status');
                list.append(empty);
            }
            highlight(-1);
        };
        const show = () => {
            if (select.disabled) return;
            open = true;
            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            render();
            input.select();
        };
        const sync = () => {
            input.disabled = select.disabled;
            if (select.getAttribute('aria-describedby')) input.setAttribute('aria-describedby', select.getAttribute('aria-describedby'));
            input.placeholder = select.disabled ? select.options[0]?.textContent || 'Choose location first' : (isState ? 'Search state / province…' : 'Search city…');
            input.setAttribute('aria-invalid', select.getAttribute('aria-invalid') || 'false');
            input.setAttribute('aria-busy', select.getAttribute('aria-busy') || 'false');
            if (select.disabled) close();
            else if (!open) restore();
            else render(input.value);
        };
        input.addEventListener('click', () => { if (!open) show(); });
        input.addEventListener('input', () => {
            if (!open) { open = true; list.hidden = false; input.setAttribute('aria-expanded', 'true'); }
            render(input.value);
        });
        input.addEventListener('keydown', event => {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                if (!open) show();
                if (matches.length) highlight(event.key === 'ArrowDown' ? (active + 1) % matches.length : (active <= 0 ? matches.length - 1 : active - 1));
            } else if (event.key === 'Enter' && open) {
                event.preventDefault();
                if (matches[active]) choose(matches[active]);
                else if (matches.length === 1) choose(matches[0]);
            } else if (event.key === 'Escape' && open) { event.preventDefault(); close(); }
            else if (event.key === 'Tab') close();
        });
        input.addEventListener('blur', close);
        select.addEventListener('change', () => { close(); sync(); });
        select.addEventListener('focus', () => input.focus());
        select.addEventListener('invalid', event => {
            event.preventDefault();
            input.setAttribute('aria-invalid', 'true');
            const error = document.getElementById(id.startsWith('restaurant-interest-') ? `${id}-error` : `${id.replace('partner-', '')}-error`);
            if (error) error.textContent = isState ? 'Please select a state or province.' : 'Please select a city.';
            if (!select.form?.querySelector('.partner-search-input[aria-invalid="true"]:focus')) input.focus();
        });
        new MutationObserver(sync).observe(select, {childList: true, subtree: true, attributes: true, attributeFilter: ['disabled', 'selected', 'aria-invalid', 'aria-busy', 'aria-describedby']});
        select.form?.addEventListener('reset', () => setTimeout(() => { close(); sync(); }, 0));
        sync();
    });
})();
