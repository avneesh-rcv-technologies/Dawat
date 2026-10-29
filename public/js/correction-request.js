(() => {
    'use strict';
    const dialog = document.getElementById('correction-request-dialog');
    if (!dialog || dialog.dataset.initialized) return;
    dialog.dataset.initialized = 'true';
    const menu = document.getElementById('correction-actions-menu');
    const requestButton = menu.querySelector('[data-correction-request]');
    const viewLink = menu.querySelector('[data-correction-view-link]');
    const form = document.getElementById('correction-request-form');
    const reason = document.getElementById('correction-reason');
    const submit = document.getElementById('correction-request-submit');
    const error = document.getElementById('correction-request-error');
    let activeTrigger = null, returnFocus = null, confirming = false, submitting = false, mode = 'form';

    const closeMenu = (restoreFocus = false) => {
        menu.hidden = true;
        activeTrigger?.setAttribute('aria-expanded', 'false');
        if (restoreFocus) activeTrigger?.focus();
    };
    const menuItems = () => [...menu.querySelectorAll('a, button')].filter(item => !item.hidden && !item.disabled);
    const setConfirmation = value => {
        confirming = value;
        document.getElementById('correction-edit-panel').hidden = value;
        document.getElementById('correction-confirm-panel').hidden = !value;
        document.getElementById('correction-edit-back').hidden = !value;
        submit.textContent = value ? 'Confirm and send request' : 'Continue';
        document.getElementById('correction-dialog-title').textContent = value ? 'Confirm change request' : 'Request changes';
        if (value) document.getElementById('correction-confirm-reason').textContent = reason.value;
    };
    const showError = message => { error.textContent = message; error.hidden = !message; };
    const countReason = () => {
        reason.setCustomValidity('');
        document.getElementById('correction-reason-count').textContent = `${reason.value.length} / 500`;
    };
    const openDialog = (trigger, previousReason = '') => {
        const data = trigger.dataset;
        closeMenu();
        returnFocus = trigger;
        form.setAttribute('action', data.correctionUrl);
        mode = data.correctionMode || 'form';
        const step = document.getElementById('correction-step');
        step.value = data.correctionStep || '';
        step.disabled = mode === 'ajax';
        document.getElementById('correction-action').disabled = mode !== 'ajax';
        document.getElementById('correction-target').textContent = data.correctionTitle;
        document.getElementById('correction-kyc-note').hidden = step.value !== 'kyc';
        reason.value = previousReason;
        countReason(); showError(''); setConfirmation(false);
        dialog.showModal();
        reason.focus();
    };

    document.querySelectorAll('[data-correction-menu]').forEach(trigger => {
        trigger.addEventListener('click', () => {
            const wasOpen = activeTrigger === trigger && !menu.hidden;
            closeMenu();
            if (wasOpen) return;
            activeTrigger = trigger;
            const data = trigger.dataset;
            viewLink.hidden = !data.correctionView;
            viewLink.href = data.correctionView || '#';
            viewLink.textContent = data.correctionViewLabel || 'View submission';
            requestButton.hidden = data.correctionAllowed !== 'true';
            requestButton.disabled = data.correctionDisabled === 'true';
            menu.querySelector('[data-correction-menu-note]').hidden = requestButton.hidden || !requestButton.disabled;
            menu.hidden = false;
            trigger.setAttribute('aria-expanded', 'true');
            const bounds = trigger.getBoundingClientRect();
            menu.style.left = `${Math.max(8, Math.min(bounds.right - menu.offsetWidth, window.innerWidth - menu.offsetWidth - 8))}px`;
            menu.style.top = `${bounds.bottom + menu.offsetHeight + 8 <= window.innerHeight ? bounds.bottom + 6 : Math.max(8, bounds.top - menu.offsetHeight - 6)}px`;
            (menuItems()[0] || trigger).focus();
        });
    });
    requestButton.addEventListener('click', () => openDialog(activeTrigger));
    viewLink.addEventListener('click', () => closeMenu());
    document.addEventListener('click', event => {
        if (!menu.contains(event.target) && !event.target.closest('[data-correction-menu]')) closeMenu();
    });
    document.addEventListener('keydown', event => {
        if (menu.hidden) return;
        if (event.key === 'Escape') { event.preventDefault(); closeMenu(true); }
        if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
            event.preventDefault();
            const items = menuItems();
            if (!items.length) return;
            const index = items.indexOf(document.activeElement);
            const next = event.key === 'Home' ? 0 : event.key === 'End' ? items.length - 1 : (index + (event.key === 'ArrowUp' ? -1 : 1) + items.length) % items.length;
            items[next].focus();
        }
        if (event.key === 'Tab') closeMenu();
    });
    window.addEventListener('resize', () => closeMenu());
    document.addEventListener('scroll', () => closeMenu(), true);
    dialog.querySelectorAll('[data-correction-close]').forEach(button => button.addEventListener('click', () => { if (!submitting) dialog.close(); }));
    dialog.addEventListener('cancel', event => { if (submitting) event.preventDefault(); });
    dialog.addEventListener('close', () => returnFocus?.focus());
    dialog.addEventListener('click', event => {
        if (event.target !== dialog || submitting) return;
        const bounds = dialog.getBoundingClientRect();
        if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close();
    });
    reason.addEventListener('input', countReason);
    submit.addEventListener('click', event => { if (event.detail > 1) event.preventDefault(); });
    document.getElementById('correction-edit-back').addEventListener('click', () => { setConfirmation(false); reason.focus(); });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (submitting) return;
        reason.value = reason.value.trim();
        countReason();
        if (!reason.value) reason.setCustomValidity('Enter the reason and required corrections.');
        if (!form.reportValidity()) return;
        showError('');
        if (!confirming) { setConfirmation(true); submit.focus(); return; }
        submitting = true;
        dialog.querySelectorAll('button').forEach(button => { button.disabled = true; });
        submit.textContent = 'Sending request…';
        if (mode !== 'ajax') { HTMLFormElement.prototype.submit.call(form); return; }
        try {
            const response = await fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const result = await response.json();
            if (!response.ok || !result.success) {
                const messages = Object.values(result.errors || {}).flat();
                throw new Error(messages.join(' ') || result.message || 'Unable to send the request. Please try again.');
            }
            window.location.reload();
        } catch (failure) {
            submitting = false;
            dialog.querySelectorAll('button').forEach(button => { button.disabled = false; });
            setConfirmation(false); showError(failure.message); reason.focus();
        }
    });
    const previous = dialog.dataset.previousStep;
    if (dialog.dataset.hasErrors === 'true' && previous) {
        const trigger = [...document.querySelectorAll('[data-correction-menu]')].find(button => button.dataset.correctionStep === previous && button.dataset.correctionAllowed === 'true');
        if (trigger) { openDialog(trigger, dialog.dataset.previousReason || ''); showError(dialog.dataset.previousError || 'Please check your request.'); }
    }
})();
