(() => {
    const form = document.querySelector('[data-admin-auth-form]') || document.getElementById('partner-signup-form');
    if (!form?.dataset.authMode || form.dataset.authInitialized === 'true') return;
    form.dataset.authInitialized = 'true';
    const mode = form.dataset.authMode;
    const fields = [...form.querySelectorAll('[required]')];
    const field = name => form.elements.namedItem(name);
    const button = form.querySelector('[type="submit"]');
    const status = form.querySelector('[data-auth-status]') || document.getElementById('partner-signup-status');
    const errors = new Map();
    const captchaError = form.querySelector('[data-captcha-error]') || document.getElementById('partner-signup-captcha-error');
    let busy = false;
    let redirecting = false;
    let currentToast = null;
    let toastKey = '';
    form.noValidate = true;

    const notify = (text, type = 'error') => {
        const key = `${type}:${text}`;
        if (key === toastKey && currentToast?.isConnected) return;
        const escaped = String(text).replace(/[&<>"']/g, character => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        })[character]);
        currentToast = window.showToast(escaped, type, type === 'success' ? 'Success' : type === 'info' ? 'Verify your email' : 'Error');
        toastKey = key;
    };
    const showError = (input, text) => {
        if (!errors.has(input)) {
            const output = document.createElement('span');
            output.id = `${input.id}-error`;
            output.className = 'partner-auth-error';
            output.setAttribute('aria-live', 'polite');
            (input.closest('.partner-signup-password, .glass-input') || input).insertAdjacentElement('afterend', output);
            input.setAttribute('aria-describedby', output.id);
            errors.set(input, output);
        }
        errors.get(input).textContent = text;
        input.setAttribute('aria-invalid', String(Boolean(text)));
        return !text;
    };
    const validate = input => {
        let text = '';
        if (!input.value.trim()) {
            text = input.name === 'email' ? 'Please enter your email.'
                : input.name === 'password_confirmation' ? 'Please confirm your password.' : 'Please enter your password.';
        } else if (input.name === 'email' && (input.value.trim().length > 255 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim()))) {
            text = 'Enter a valid email address.';
        } else if (mode === 'reset' && input.name === 'password') {
            const value = input.value;
            if (value.length < 8 || value.length > 16) text = 'Use a password with 8 to 16 characters.';
            else if (!/[A-Z]/.test(value) || !/[a-z]/.test(value) || !/[0-9]/.test(value) || !/[^a-zA-Z0-9\s]/.test(value)) {
                text = 'Include an uppercase letter, a lowercase letter, a number and a special character.';
            }
        } else if (input.name === 'password_confirmation' && input.value !== field('password').value) {
            text = 'The passwords do not match.';
        }
        return showError(input, text);
    };
    fields.forEach(input => {
        input.addEventListener('blur', () => validate(input));
        input.addEventListener('input', () => {
            if (errors.has(input)) validate(input);
            if (input.name === 'password' && field('password_confirmation') && errors.has(field('password_confirmation'))) {
                validate(field('password_confirmation'));
            }
        });
    });
    if (status.textContent.trim()) notify(status.textContent.trim(), status.dataset.error === 'true' ? 'error' : 'success');
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (busy || redirecting || form.dataset.linkValid === 'false') return;
        const invalid = fields.filter(input => !validate(input));
        if (invalid.length) {
            invalid[0].focus();
            notify(errors.get(invalid[0]).textContent);
            return;
        }
        if (captchaError) captchaError.textContent = '';
        if (form.dataset.captchaEnabled === 'true' && !field('g-recaptcha-response')?.value) {
            captchaError.textContent = 'Please complete the reCAPTCHA challenge.';
            notify(captchaError.textContent);
            return;
        }
        busy = true;
        button.disabled = true;
        form.setAttribute('aria-busy', 'true');
        status.textContent = mode === 'forgot' ? 'Sending reset link…' : mode === 'reset' ? 'Resetting your password…' : 'Logging in…';
        status.dataset.error = 'false';
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 30000);
        try {
            if (form.dataset.passwordChallengeUrl) {
                if (!window.PasswordTransport) throw new Error('Unable to secure your password. Please refresh the page and try again.');
                // Encrypts password fields; the FormData built below receives the one-time envelopes.
                await window.PasswordTransport.prepare(form);
            }
            const payload = Object.fromEntries(new FormData(form));
            payload.email = payload.email.trim().toLowerCase();
            if (mode === 'login') payload.remember = Boolean((field('remember') || field('remember-me'))?.checked);
            const response = await fetch(form.action, {
                method: 'POST', credentials: 'same-origin', signal: controller.signal,
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': field('_token').value },
                body: JSON.stringify(payload),
            });
            let data;
            try { data = await response.json(); } catch { data = {}; }
            if (data.verification_required) {
                const text = Object.values(data.errors || {}).flat()[0] || data.message || 'Please verify your email before logging in.';
                status.textContent = text;
                notify(text, 'info');
                return;
            }
            if (!response.ok || !data.success) {
                const fallback = response.status === 419 ? 'Your session expired. Refresh the page and try again.'
                    : response.status === 429 ? 'Too many attempts. Please wait a minute and try again.'
                    : 'Unable to complete your request. Please try again.';
                let hasFieldErrors = false;
                for (const [name, messages] of Object.entries(data.errors || {})) {
                    if (name === 'g-recaptcha-response' && captchaError) {
                        captchaError.textContent = Array.isArray(messages) ? messages.join(' ') : String(messages);
                        hasFieldErrors = true;
                        continue;
                    }
                    const input = field(name);
                    if (input && input.type !== 'hidden') {
                        showError(input, Array.isArray(messages) ? messages.join(' ') : String(messages));
                        hasFieldErrors = true;
                    }
                }
                const error = new Error(Object.values(data.errors || {}).flat()[0] || data.message || fallback);
                error.hasFieldErrors = hasFieldErrors;
                throw error;
            }
            status.textContent = data.message || 'Your request was completed successfully.';
            notify(status.textContent, 'success');
            if (data.redirect_url) {
                redirecting = true;
                if (field('password')) field('password').value = '';
                if (field('password_confirmation')) field('password_confirmation').value = '';
                window.location.assign(data.redirect_url);
            }
        } catch (error) {
            const text = error.name === 'AbortError' ? 'The request timed out. Please try again.'
                : error instanceof TypeError ? 'Unable to connect. Check your connection and try again.' : error.message;
            status.textContent = error.hasFieldErrors ? '' : text;
            status.dataset.error = 'true';
            notify(text);
        } finally {
            if (!redirecting && form.dataset.captchaEnabled === 'true') window.grecaptcha?.reset();
            clearTimeout(timeout);
            busy = false;
            button.disabled = redirecting;
            form.setAttribute('aria-busy', 'false');
        }
    });
})();
