
// Modern Toast System
class ModernToast {
    constructor() {
        this.container = document.getElementById('toast-container');
        this.toasts = [];
        this.maxToasts = 3;
        this.recentToasts = new Map(); // signature -> timestamp for deduplication
    }

    show(message, type = 'info', title = '', duration = 5000) {
        if (!this.container) {
            this.container = document.getElementById('toast-container');
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.id = 'toast-container';
                this.container.className = 'toast-container';
                document.body.appendChild(this.container);
            }
        }

        // Deduplication: prevent identical messages appearing within 2.5s
        const plainMsg = String(message || '').trim();
        if (!plainMsg) return null;
        const signature = `${type}:${title}:${plainMsg}`;
        const now = Date.now();
        if (this.recentToasts.has(signature) && (now - this.recentToasts.get(signature) < 2500)) {
            return null;
        }
        this.recentToasts.set(signature, now);

        // Clean up old entries from recentToasts map
        for (const [key, time] of this.recentToasts.entries()) {
            if (now - time > 10000) this.recentToasts.delete(key);
        }

        // Limit concurrent visible toasts to maxToasts (3)
        while (this.toasts.length >= this.maxToasts) {
            this.remove(this.toasts[0]);
        }

        const toast = this.create(message, type, title, duration);
        this.container.appendChild(toast);
        this.toasts.push(toast);

        // Trigger animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                this.remove(toast);
            }, duration);
        }

        return toast;
    }

    create(message, type, title, duration) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        const icons = {
            success: 'check_circle',
            error: 'error',
            warning: 'warning',
            info: 'info'
        };

        const titles = {
            success: title || 'Success',
            error: title || 'Error',
            warning: title || 'Warning',
            info: title || 'Information'
        };

        toast.innerHTML = `
    <div class="toast-left">
        <div class="toast-inner">
        <span class="material-icons-outlined toast-left-icon">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.81087 0.477295V2.46063C2.81087 2.78733 2.81087 2.95067 2.87445 3.07546C2.93038 3.18522 3.01962 3.27446 3.12938 3.33038C3.25416 3.39396 3.41751 3.39396 3.74421 3.39396H7.71087C8.03757 3.39396 8.20092 3.39396 8.3257 3.33038C8.43546 3.27446 8.5247 3.18522 8.58063 3.07546C8.64421 2.95067 8.64421 2.78733 8.64421 2.46063V1.06063M8.64421 10.9773V7.24396C8.64421 6.91726 8.64421 6.75392 8.58063 6.62913C8.5247 6.51937 8.43546 6.43013 8.3257 6.37421C8.20092 6.31063 8.03757 6.31063 7.71087 6.31063H3.74421C3.41751 6.31063 3.25416 6.31063 3.12938 6.37421C3.01962 6.43013 2.93038 6.51937 2.87445 6.62913C2.81087 6.75392 2.81087 6.91726 2.81087 7.24396V10.9773M10.9775 4.16716V8.17729C10.9775 9.15739 10.9775 9.64743 10.7868 10.0218C10.619 10.3511 10.3513 10.6188 10.022 10.7866C9.64768 10.9773 9.15763 10.9773 8.17754 10.9773H3.27754C2.29745 10.9773 1.8074 10.9773 1.43306 10.7866C1.10377 10.6188 0.836056 10.3511 0.668278 10.0218C0.477539 9.64743 0.477539 9.15739 0.477539 8.17729V3.27729C0.477539 2.2972 0.477539 1.80716 0.668278 1.43281C0.836056 1.10353 1.10377 0.835812 1.43306 0.668034C1.8074 0.477295 2.29745 0.477295 3.27754 0.477295H7.28767C7.57303 0.477295 7.71571 0.477295 7.84998 0.50953C7.96902 0.53811 8.08282 0.585248 8.1872 0.649215C8.30494 0.721363 8.40583 0.822252 8.60761 1.02403L10.4308 2.84723C10.6326 3.049 10.7335 3.14989 10.8056 3.26763C10.8696 3.37201 10.9167 3.48582 10.9453 3.60486C10.9775 3.73913 10.9775 3.8818 10.9775 4.16716Z" stroke="white" stroke-width="0.954545" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        </div>
    </div>

    <div class="toast-body">
        <div class="toast-title">${titles[type]}</div>
        <div class="toast-message">${message}</div>
    </div>

    <button type="button" class="toast-close" aria-label="Dismiss notification">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false">
            <path d="m6 6 12 12M18 6 6 18" />
        </svg>
    </button>

    ${duration > 0 ? `<div class="toast-progress" style="animation-duration:${duration}ms;"></div>` : ''}
`;


        toast.querySelector('.toast-close').addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.remove(toast);
        });

        // Click to dismiss
        toast.addEventListener('click', (e) => {
            if (!e.target.closest('.toast-close')) {
                this.remove(toast);
            }
        });

        return toast;
    }

    remove(toast) {
        // Release the slot immediately; waiting for the animation blocks show()'s loop.
        this.toasts = this.toasts.filter(t => t !== toast);
        if (!toast || !toast.parentElement) return;

        toast.classList.add('hide');

        setTimeout(() => {
            if (toast.parentElement) {
                toast.parentElement.removeChild(toast);
            }
        }, 300);
    }

    clear() {
        this.toasts.forEach(toast => this.remove(toast));
    }
}



// Initialize toast system
const toastSystem = new ModernToast();

// Global function for easy use
function showToast(message, type = 'info', title = '', duration = 5000) {

    return toastSystem.show(message, type, title, duration);
}

// Additional utility functions
function showSuccess(message, title = 'Success', duration = 4000) {
    return showToast(message, 'success', title, duration);
}

function showError(message, title = 'Error', duration = 6000) {
    return showToast(message, 'error', title, duration);
}

function showWarning(message, title = 'Warning', duration = 5000) {
    return showToast(message, 'warning', title, duration);
}

function showInfo(message, title = 'Information', duration = 4000) {
    return showToast(message, 'info', title, duration);
}

// Clear all toasts
function clearAllToasts() {
    toastSystem.clear();
}

// Onboarding notifications use the same renderer as the rest of the application.
(() => {
    'use strict';
    const onboardingData = document.getElementById('onboarding-toast-data');
    if (!onboardingData) return;
    const overviewUrl = new URL(onboardingData.dataset.overviewUrl, window.location.origin);
    const storageKey = `dawat.onboarding.toast:${overviewUrl.pathname}${overviewUrl.search}`;
    let lastToast = '', lastToastTime = 0, validationTimer = null;

    // Show only the first field error using the shared toast.js renderer.
    window.onboardingToasts = (messages, type = 'error') => {
        const list = [...new Set((messages || []).flatMap(m => String(m || '').split('\n')).map(m => m.trim()).filter(Boolean))];
        if (list.length) showOne(type === 'error' ? list[0] : list.join('\n'), type);
    };

    window.onboardingToast = (message, type = 'error') => {
        const text = String(message || '').trim();
        if (!text) return;
        showOne(type === 'error' ? text.split('\n').find(line => line.trim()).trim() : text, type);
    };

    function showOne(text, type) {
        clearTimeout(validationTimer);
        validationTimer = null;
        const signature = `${type}:${text}`;
        if (signature === lastToast && Date.now() - lastToastTime < 750) return;
        lastToast = signature; lastToastTime = Date.now();
        // The project's toast renderer accepts HTML; onboarding messages are plain text.
        const encoded = document.createElement('span');
        encoded.textContent = text;
        const toast = window.showToast(encoded.innerHTML, type, type === 'success' ? 'Success' : 'Please check your details', type === 'success' ? 4500 : 6500);
        if (toast) {
            toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
            toast.setAttribute('aria-atomic', 'true');
            const close = toast.querySelector('.toast-close');
            if (close) {
                close.type = 'button';
                close.textContent = '×';
                close.setAttribute('aria-label', 'Dismiss notification');
            }
        }
    }

    window.onboardingComplete = message => {
        const text = String(message || 'Your information was submitted successfully for review.');
        try {
            sessionStorage.setItem(storageKey, JSON.stringify({ message: text, createdAt: Date.now() }));
        } catch (_) {
            // Server flash messages remain available when browser storage is disabled.
        }
        window.location.assign(overviewUrl.href);
    };

    // Highlight invalid fields and summarize them in one shared toast.
    let invalidControls = [];
    document.addEventListener('invalid', event => {
        if (!event.target.closest('form')) return;
        event.preventDefault();
        const control = event.target;
        control.setAttribute('aria-invalid', 'true');
        const clear = () => { if (control.checkValidity()) control.removeAttribute('aria-invalid'); };
        control.addEventListener('input', clear, { once: true });
        control.addEventListener('change', clear, { once: true });
        if (!invalidControls.includes(control)) invalidControls.push(control);
        if (validationTimer) return;
        validationTimer = setTimeout(() => {
            validationTimer = null;
            const batch = invalidControls;
            invalidControls = [];
            batch[0]?.focus();
            window.onboardingToasts(batch.map(item => {
                const label = (item.labels?.[0]?.textContent || '').trim().replace(/\s+/g, ' ').replace(/\s*\*$/, '');
                return `${label ? label + ': ' : ''}${item.validationMessage}`;
            }), 'error');
        }, 100);
    }, true);

    document.addEventListener('DOMContentLoaded', () => {
        let pending = null;
        try {
            pending = JSON.parse(sessionStorage.getItem(storageKey) || 'null');
            sessionStorage.removeItem(storageKey);
        } catch (_) { /* Server flash messages also cover normal redirects. */ }
        let flash = { success: [], errors: [] };
        try { flash = JSON.parse(onboardingData.textContent); } catch (_) { /* No flash data. */ }
        if (pending?.message && Date.now() - pending.createdAt < 60000) {
            window.onboardingToast(pending.message, 'success');
        } else {
            const uniqueSuccess = [...new Set((flash.success || []).map(s => String(s).trim()).filter(Boolean))];
            if (uniqueSuccess.length > 0) {
                // Show only the latest/single relevant success message instead of looping all session keys
                window.onboardingToast(uniqueSuccess[uniqueSuccess.length - 1], 'success');
            }
        }
        if (flash.errors?.length) {
            const uniqueErrors = [...new Set(flash.errors.map(e => String(e).trim()).filter(Boolean))];
            if (uniqueErrors.length > 0) {
                window.onboardingToasts(uniqueErrors, 'error');
            }
        }
    });
})();
