(() => {
    const form = document.getElementById('onboarding-form');
    const email = document.getElementById('partner-email');
    const status = document.getElementById('email-dns-status');
    if (!form || !email || !status) return;
    const label = status.querySelector('span');
    const value = () => email.value.trim().toLowerCase();
    let timer;
    let revision = 0;
    let active;
    let checked = '';
    let checkedAt = 0;
    let state = 'idle';

    const show = (next, text) => {
        state = next;
        status.dataset.state = next;
        label.textContent = text;
        status.querySelectorAll('[data-email-icon]').forEach(icon => {
            icon.hidden = icon.dataset.emailIcon !== next;
        });
        email.setAttribute('aria-invalid', String(next === 'invalid'));
        email.setAttribute('aria-busy', String(next === 'checking'));
    };
    const fresh = () => state === 'valid' && checked === value() && Date.now() - checkedAt < 60000;
    const check = () => {
        clearTimeout(timer);
        if (fresh()) return Promise.resolve(true);
        if (active?.email === value()) return active.promise;
        if (!value() || email.validity.typeMismatch || email.validity.tooLong) {
            show(value() ? 'invalid' : 'idle', value() ? 'Enter a valid email address, such as name@example.com.' : "We'll check the email format, domain and availability before sending an OTP.");
            return Promise.resolve(false);
        }
        const requested = value();
        const requestedRevision = revision;
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 10000);
        email.setCustomValidity('');
        show('checking', 'Checking email format, domain and availability…');
        const promise = (async () => {
            try {
                const response = await fetch(form.dataset.emailCheckUrl, {
                    method: 'POST', signal: controller.signal,
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': form.elements._token.value },
                    body: JSON.stringify({ email: requested }),
                });
                if (requestedRevision !== revision) return false;
                if (response.status === 429) throw new Error('Too many checks. Please wait a minute and try again.');
                const data = await response.json();
                if (requestedRevision !== revision) return false;
                if (response.status === 422) {
                    const message = data.errors?.email?.[0] || 'This email domain could not be validated. Check the address and try again.';
                    email.setCustomValidity(message);
                    show('invalid', message);
                    return false;
                }
                if (!response.ok || !data.success) throw new Error('Email check failed');
                checked = requested;
                checkedAt = Date.now();
                email.setCustomValidity('');
                show('valid', data.message);
                return true;
            } catch (error) {
                if (requestedRevision !== revision) return false;
                show('error', error.message.startsWith('Too many checks') ? error.message : 'Unable to check email availability. Check your connection, then leave this field or submit to try again.');
                return false;
            } finally {
                clearTimeout(timeout);
                if (requestedRevision === revision) active = null;
            }
        })();
        active = { email: requested, controller, promise };
        return promise;
    };

    email.addEventListener('input', () => {
        revision++;
        active?.controller.abort();
        active = null;
        checked = '';
        email.setCustomValidity('');
        show('idle', "We'll check the email format, domain and availability before sending an OTP.");
        clearTimeout(timer);
        if (value()) timer = setTimeout(check, 700);
    });
    email.addEventListener('blur', check);
    form.addEventListener('submit', async event => {
        if (fresh()) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        const requested = value();
        const submitter = event.submitter;
        if (await check() && value() === requested) {
            form.requestSubmit(submitter || document.getElementById('onboarding-submit'));
        } else {
            email.focus();
        }
    }, true);
    if (value()) check();
})();
