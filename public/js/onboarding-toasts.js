(() => {
    'use strict';
    const storageKey = `dawat.onboarding.toast:${window.location.pathname}${window.location.search}`;
    let lastToast = '', lastToastTime = 0, validationTimer = null;

    window.onboardingToast = (message, type = 'error') => {
        const text = String(message || '').trim();
        if (!text) return;
        clearTimeout(validationTimer);
        validationTimer = null;
        const signature = `${type}:${text}`;
        if (signature === lastToast && Date.now() - lastToastTime < 750) return;
        lastToast = signature; lastToastTime = Date.now();
        // The project's toast renderer accepts HTML; onboarding messages are plain text.
        const encoded = document.createElement('span');
        encoded.textContent = text;
        const toast = window.showToast(encoded.innerHTML, type, type === 'success' ? 'Success' : 'Please check your details', type === 'success' ? 4500 : 6500);
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
        toast.setAttribute('aria-atomic', 'true');
        const close = toast.querySelector('.toast-close');
        close.type = 'button';
        close.textContent = '×';
        close.setAttribute('aria-label', 'Dismiss notification');
    };

    window.onboardingComplete = message => {
        const text = String(message || 'Your information was submitted successfully for review.');
        try {
            sessionStorage.setItem(storageKey, JSON.stringify({ message: text, createdAt: Date.now() }));
        } catch (_) {
            // Server flash messages remain available when browser storage is disabled.
        }
        window.location.reload();
    };

    // Native registration/menu validation happens before the submit event.
    document.addEventListener('invalid', event => {
        if (!event.target.closest('.step-panel form') || validationTimer) return;
        const control = event.target;
        validationTimer = setTimeout(() => {
            validationTimer = null;
            const label = control.labels?.[0]?.textContent.trim().replace(/\s+/g, ' ');
            window.onboardingToast(`${label ? label + ': ' : ''}${control.validationMessage}`, 'error');
        }, 0);
    }, true);

    document.addEventListener('DOMContentLoaded', () => {
        let pending = null;
        try {
            pending = JSON.parse(sessionStorage.getItem(storageKey) || 'null');
            sessionStorage.removeItem(storageKey);
        } catch (_) { /* Server flash messages also cover normal redirects. */ }
        let flash = { success: [], errors: [] };
        try { flash = JSON.parse(document.getElementById('onboarding-toast-data').textContent); } catch (_) { /* No flash data. */ }
        if (pending?.message && Date.now() - pending.createdAt < 60000) {
            window.onboardingToast(pending.message, 'success');
        } else {
            [...new Set(flash.success || [])].forEach(message => window.onboardingToast(message, 'success'));
        }
        if (flash.errors?.length) window.onboardingToast([...new Set(flash.errors)].join('\n'), 'error');
    });
})();
