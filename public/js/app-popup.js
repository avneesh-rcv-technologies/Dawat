/**
 * Dawat popup — themed replacement for window.confirm() / window.alert().
 *
 *   await appConfirm('Delete this FAQ?')                      -> true | false
 *   await appConfirm({ title, message, confirmText, cancelText, tone: 'danger' })
 *   await appAlert('Something went wrong', { tone: 'error' }) -> resolves when closed
 *
 * Declarative forms (no script needed):
 *   <form data-confirm="Delete this redirect?" data-confirm-title="Delete redirect"
 *         data-confirm-button="Delete" data-confirm-tone="danger">
 *
 * Tones: primary (default), danger, warning, success, error, info.
 * Popups are queued, so a second request waits for the first to close.
 */
(function () {
    if (window.appConfirm) return;

    const STYLE = `
.app-popup{--p-brand:#FF6210;--p-brand-dark:#E0530B;--p-brand-soft:#FFF4EE;--p-ink:#111111;--p-muted:#64748B;--p-line:#EEE9E4;--p-line-strong:#E4DED8;--p-surface:#FFFFFF;--p-surface-2:#FAF8F6;
  width:min(440px,calc(100vw - 32px));max-height:calc(100dvh - 32px);margin:auto;padding:0;overflow:auto;border:1px solid var(--p-line);border-radius:18px;background:var(--p-surface);color:var(--p-ink);
  font-family:'Urbanist','Plus Jakarta Sans',ui-sans-serif,system-ui,sans-serif;box-shadow:0 24px 80px rgba(17,17,17,.28)}
.app-popup[open]{animation:app-popup-in .18s ease-out}
.app-popup::backdrop{background:rgba(17,17,17,.5);backdrop-filter:blur(3px)}
.app-popup.is-dark{--p-ink:#FFFFFF;--p-muted:#A1A1AA;--p-line:#27272A;--p-line-strong:#3F3F46;--p-surface:#141417;--p-surface-2:#1C1C21;--p-brand-soft:rgba(255,98,16,.12)}
.app-popup-body{display:flex;gap:14px;align-items:flex-start;padding:24px 24px 18px}
.app-popup-icon{display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;width:44px;height:44px;border-radius:13px;background:var(--p-brand-soft);color:var(--p-brand)}
.app-popup[data-tone=danger] .app-popup-icon,.app-popup[data-tone=error] .app-popup-icon{background:#FEF2F2;color:#B91C1C}
.app-popup[data-tone=warning] .app-popup-icon{background:#FFFBEB;color:#B45309}
.app-popup[data-tone=success] .app-popup-icon{background:#F0FDF4;color:#15803D}
.app-popup.is-dark[data-tone=danger] .app-popup-icon,.app-popup.is-dark[data-tone=error] .app-popup-icon{background:rgba(239,68,68,.14);color:#FCA5A5}
.app-popup.is-dark[data-tone=warning] .app-popup-icon{background:rgba(245,158,11,.14);color:#FCD34D}
.app-popup.is-dark[data-tone=success] .app-popup-icon{background:rgba(34,197,94,.14);color:#86EFAC}
.app-popup-title{margin:2px 0 0;font-size:18px;line-height:1.35;font-weight:800;color:var(--p-ink)}
.app-popup-message{margin:6px 0 0;font-size:14px;line-height:1.6;color:var(--p-muted);white-space:pre-line;overflow-wrap:anywhere}
.app-popup-foot{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:10px;padding:14px 24px;border-top:1px solid var(--p-line);background:var(--p-surface-2)}
.app-popup-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;min-width:96px;padding:8px 18px;border:1px solid var(--p-line-strong);border-radius:10px;background:var(--p-surface);color:var(--p-ink);font:inherit;font-size:14px;font-weight:600;cursor:pointer;transition:background-color .15s,border-color .15s,color .15s,box-shadow .15s}
.app-popup-btn:hover{border-color:var(--p-brand);color:var(--p-brand-dark);background:var(--p-brand-soft)}
.app-popup-btn--confirm{border-color:var(--p-brand);background:var(--p-brand);color:#fff}
.app-popup-btn--confirm:hover{border-color:var(--p-brand-dark);background:var(--p-brand-dark);color:#fff;box-shadow:0 10px 22px -8px rgba(255,98,16,.55)}
.app-popup[data-tone=danger] .app-popup-btn--confirm,.app-popup[data-tone=error] .app-popup-btn--confirm{border-color:#B91C1C;background:#B91C1C}
.app-popup[data-tone=danger] .app-popup-btn--confirm:hover,.app-popup[data-tone=error] .app-popup-btn--confirm:hover{border-color:#991B1B;background:#991B1B;box-shadow:none}
.app-popup-btn:focus-visible{outline:2px solid var(--p-brand);outline-offset:2px}
@keyframes app-popup-in{from{opacity:0;transform:translateY(8px) scale(.98)}to{opacity:1;transform:none}}
@media (max-width:480px){.app-popup-body{padding:20px 18px 16px}.app-popup-foot{padding:12px 18px}.app-popup-btn{flex:1 1 120px;min-height:44px}}
@media (prefers-reduced-motion:reduce){.app-popup[open]{animation:none}}`;

    const ICONS = {
        primary: '<path d="M12 22c5.5 0 10-4.5 10-10S17.5 2 12 2 2 6.5 2 12s4.5 10 10 10Z"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3M12 17h.01"/>',
        danger: '<path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6"/>',
        warning: '<path d="M10.3 3.9 2.1 18a2 2 0 0 0 1.7 3h16.4a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/>',
        error: '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/>',
        success: '<circle cx="12" cy="12" r="10"/><path d="m8 12 3 3 5-6"/>',
        info: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
    };
    const DEFAULT_TITLES = {
        confirm: { primary: 'Please confirm', danger: 'Are you sure?', warning: 'Please confirm', success: 'Please confirm', error: 'Are you sure?', info: 'Please confirm' },
        alert: { primary: 'Notice', danger: 'Something went wrong', warning: 'Please check', success: 'Done', error: 'Something went wrong', info: 'Notice' },
    };

    let queue = Promise.resolve();

    function injectStyles() {
        if (document.getElementById('app-popup-styles')) return;
        const style = document.createElement('style');
        style.id = 'app-popup-styles';
        style.textContent = STYLE;
        document.head.appendChild(style);
    }

    function normalise(input, extra) {
        const options = typeof input === 'object' && input !== null ? { ...input } : { message: input };
        return { ...options, ...(extra || {}) };
    }

    function open(kind, options) {
        injectStyles();
        const tone = ICONS[options.tone] ? options.tone : (kind === 'alert' ? 'info' : 'primary');
        const dialog = document.createElement('dialog');
        dialog.className = 'app-popup' + (document.body.classList.contains('kyc-web-mode') || options.dark ? ' is-dark' : '');
        dialog.dataset.tone = tone;
        dialog.setAttribute('aria-labelledby', 'app-popup-title');
        dialog.setAttribute('aria-describedby', 'app-popup-message');
        if (kind === 'confirm') dialog.setAttribute('role', 'alertdialog');

        const body = document.createElement('div');
        body.className = 'app-popup-body';
        body.innerHTML = `<span class="app-popup-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${ICONS[tone]}</svg></span>`;
        const text = document.createElement('div');
        const title = document.createElement('h2');
        title.id = 'app-popup-title';
        title.className = 'app-popup-title';
        title.textContent = options.title || DEFAULT_TITLES[kind][tone];
        const message = document.createElement('p');
        message.id = 'app-popup-message';
        message.className = 'app-popup-message';
        message.textContent = options.message == null ? '' : String(options.message);
        text.append(title, message);
        body.appendChild(text);

        const foot = document.createElement('div');
        foot.className = 'app-popup-foot';
        const confirmButton = document.createElement('button');
        confirmButton.type = 'button';
        confirmButton.className = 'app-popup-btn app-popup-btn--confirm';
        confirmButton.textContent = options.confirmText || (kind === 'alert' ? 'OK' : 'Confirm');
        if (kind === 'confirm') {
            const cancelButton = document.createElement('button');
            cancelButton.type = 'button';
            cancelButton.className = 'app-popup-btn';
            cancelButton.textContent = options.cancelText || 'Cancel';
            cancelButton.addEventListener('click', () => dialog.close('cancel'));
            foot.appendChild(cancelButton);
        }
        confirmButton.addEventListener('click', () => dialog.close('confirm'));
        foot.appendChild(confirmButton);
        dialog.append(body, foot);

        // Clicking the backdrop dismisses (counts as cancel).
        dialog.addEventListener('click', event => {
            if (event.target !== dialog) return;
            const r = dialog.getBoundingClientRect();
            if (event.clientX < r.left || event.clientX > r.right || event.clientY < r.top || event.clientY > r.bottom) dialog.close('cancel');
        });

        const previousFocus = document.activeElement;
        document.body.appendChild(dialog);
        return new Promise(resolve => {
            dialog.addEventListener('close', () => {
                const confirmed = dialog.returnValue === 'confirm';
                dialog.remove();
                if (previousFocus && typeof previousFocus.focus === 'function' && document.contains(previousFocus)) previousFocus.focus();
                resolve(kind === 'confirm' ? confirmed : undefined);
            }, { once: true });
            dialog.showModal();
            // Destructive confirmations focus Cancel first so Enter doesn't trigger them by accident.
            (kind === 'confirm' && (tone === 'danger' || tone === 'error') ? foot.firstElementChild : confirmButton).focus();
        });
    }

    function enqueue(kind, options) {
        const run = queue.then(() => open(kind, options));
        queue = run.catch(() => {});
        return run;
    }

    window.appConfirm = (input, extra) => enqueue('confirm', normalise(input, extra));
    window.appAlert = (input, extra) => enqueue('alert', normalise(input, extra));

    // Declarative confirmation: <form data-confirm="..."> and <a|button data-confirm="...">.
    function optionsFrom(element) {
        return {
            message: element.dataset.confirm,
            title: element.dataset.confirmTitle,
            confirmText: element.dataset.confirmButton,
            cancelText: element.dataset.confirmCancel,
            tone: element.dataset.confirmTone || 'primary',
        };
    }

    document.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm')) return;
        if (form.dataset.confirmApproved === '1') {
            delete form.dataset.confirmApproved;
            return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        const submitter = event.submitter;
        window.appConfirm(optionsFrom(form)).then(ok => {
            if (!ok) return;
            form.dataset.confirmApproved = '1';
            submitter && form.contains(submitter) ? form.requestSubmit(submitter) : form.requestSubmit();
        });
    }, true);

    document.addEventListener('click', event => {
        const trigger = event.target.closest && event.target.closest('a[data-confirm], button[data-confirm]:not([type="submit"])');
        if (!trigger || trigger.form?.hasAttribute('data-confirm')) return;
        if (trigger.dataset.confirmApproved === '1') {
            delete trigger.dataset.confirmApproved;
            return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        window.appConfirm(optionsFrom(trigger)).then(ok => {
            if (!ok) return;
            trigger.dataset.confirmApproved = '1';
            trigger.click();
        });
    }, true);
})();
