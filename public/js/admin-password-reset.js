(() => {
    const form = document.getElementById('admin-reset-password-form');
    if (!form) return;
    const password = form.elements.password;
    const confirmation = form.elements.password_confirmation;
    const passwordError = document.getElementById('reset-password-error');
    const confirmationError = document.getElementById('reset-confirmation-error');
    form.querySelectorAll('[data-password-toggle]').forEach(button => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(visible));
            button.setAttribute('aria-label', `${visible ? 'Hide' : 'Show'} ${input === password ? 'new' : 'confirmed'} password`);
            button.querySelector('i').classList.toggle('fa-eye', !visible);
            button.querySelector('i').classList.toggle('fa-eye-slash', visible);
        });
    });
    const validate = (showAll = false) => {
        const value = password.value;
        let error = '';
        if (!value) error = 'Please enter a new password.';
        else if (new TextEncoder().encode(value).length < 8 || new TextEncoder().encode(value).length > 16) {
            error = 'The password must be between 8 and 16 characters.';
        } else {
            const missing = [];
            if (!/[A-Z]/.test(value)) missing.push('an uppercase letter');
            if (!/[a-z]/.test(value)) missing.push('a lowercase letter');
            if (!/[0-9]/.test(value)) missing.push('a number');
            if (!/[^a-zA-Z0-9\s]/.test(value)) missing.push('a special character');
            if (missing.length) error = `Your password must contain ${missing.join(', ')}.`;
        }
        const mismatch = !confirmation.value ? 'Please confirm your password.'
            : confirmation.value !== value ? 'The passwords do not match.' : '';
        [[password, passwordError, error], [confirmation, confirmationError, mismatch]].forEach(([input, output, text]) => {
            input.setCustomValidity(text);
            const visible = showAll || Boolean(input.value) || Boolean(output.textContent);
            output.textContent = visible ? text : '';
            input.setAttribute('aria-invalid', String(visible && Boolean(text)));
        });
        return !error && !mismatch;
    };
    [password, confirmation].forEach(input => {
        input.addEventListener('input', () => validate());
        input.addEventListener('blur', () => validate());
        input.addEventListener('invalid', () => validate(true));
    });
    // Second pass (after the passwords are encrypted) goes straight to the form-ajax handler.
    let secured = false;
    let securing = false;
    form.addEventListener('submit', event => {
        if (secured) {
            secured = false;
            return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        if (!validate(true)) {
            (password.validationMessage ? password : confirmation).reportValidity();
            return;
        }
        if (securing) return;
        securing = true;
        Promise.resolve()
            .then(() => {
                if (!window.PasswordTransport) throw new Error('Unable to secure your password. Please refresh the page and try again.');
                return window.PasswordTransport.prepare(form);
            })
            .then(() => {
                secured = true;
                form.requestSubmit();
            })
            .catch(error => {
                const message = error instanceof TypeError
                    ? 'Unable to connect. Check your connection and try again.'
                    : error.message;
                passwordError.textContent = message;
                window.showToast?.(message, 'error', 'Error');
            })
            .finally(() => {
                securing = false;
                secured = false;
            });
    }, true);
    validate();
})();
