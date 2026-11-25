(() => {
    const form = document.getElementById('partner-signup-form');
    if (!form) return;
    const field = name => form.elements.namedItem(name);
    const submit = form.querySelector('[type="submit"]');
    const status = document.getElementById('partner-signup-status');
    const toast = (message, type = 'error') => {
        // The shared toast renderer accepts HTML; keep API messages as plain text.
        const escaped = String(message).replace(/[&<>"']/g, character => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        })[character]);
        window.showToast(escaped, type, type === 'success' ? 'Success' : 'Error');
    };
    if (!form.dataset.sendOtpUrl) return;
    form.noValidate = true;
    const panel = document.getElementById('partner-otp-panel');
    const otpForm = document.getElementById('partner-otp-form');
    const code = document.getElementById('partner-otp-code');
    const verify = document.getElementById('partner-otp-verify');
    const resend = document.getElementById('partner-otp-resend');
    const back = document.getElementById('partner-otp-back');
    const otpStatus = document.getElementById('partner-otp-status');
    const expiry = document.getElementById('partner-otp-expiry');
    const title = document.getElementById('partner-signup-title');
    const accountLink = document.querySelector('.partner-signup-login');
    const draftKey = 'dawat.restaurant-interest';
    const email = () => field('email').value.trim().toLowerCase();
    const errors = new Map();
    let busy = false;
    let completed = false;
    let sentEmail = '';
    let verifiedEmail = '';
    let verificationToken = '';
    let captchaToken = '';
    let captchaEmail = '';
    let captchaExpiresAt = 0;
    const captchaError = document.getElementById('partner-signup-captcha-error');
    const verifyCaptcha = async () => {
        if (form.dataset.captchaEnabled !== 'true') return;
        if (captchaToken && captchaEmail === email() && Date.now() < captchaExpiresAt) return;
        captchaError.textContent = '';
        const response = field('g-recaptcha-response')?.value;
        if (!response) {
            throw new Error('Please complete the reCAPTCHA challenge.');
        }
        try {
            const requestedEmail = email();
            const data = await request(form.dataset.captchaUrl, { email: requestedEmail, 'g-recaptcha-response': response });
            if (requestedEmail !== email()) {
                throw new Error('Your email changed. Please complete the reCAPTCHA challenge again.');
            }
            captchaToken = data.verification_token;
            captchaEmail = requestedEmail;
            captchaExpiresAt = Date.now() + Number(data.expires_in) * 1000;
        } catch (error) {
            window.grecaptcha?.reset();
            throw error;
        }
    };
    let resendAt = 0;
    let expiresAt = 0;
    const emailFeedback = document.getElementById('partner-signup-email-status');
    let emailCheckState = 'idle';
    let checkedEmail = '';
    let checkedAt = 0;
    let emailCheckTimer;
    let emailRevision = 0;
    let activeEmailCheck = null;

    const message = (text, error = false) => {
        status.textContent = text;
        otpStatus.textContent = text;
        [status, otpStatus].forEach(output => { output.dataset.error = String(error); });
    };
    const showError = (input, text) => {
        if (input.name === 'email') {
            errors.set(input, emailFeedback);
            input.setAttribute('aria-describedby', emailFeedback.id);
            if (text || !['valid', 'checking', 'error'].includes(emailCheckState)) {
                emailFeedback.textContent = text;
                emailFeedback.dataset.state = text ? 'invalid' : 'idle';
            }
            input.setAttribute('aria-invalid', String(Boolean(text)));
            return !text;
        }
        if (!errors.has(input)) {
            const output = document.createElement('span');
            output.id = `${input.id || input.name}-error`;
            output.className = 'partner-auth-error';
            output.setAttribute('aria-live', 'polite');
            const errorAnchor = input.type === 'checkbox'
                ? input.closest('.partner-signup-agreement')
                : input.closest('.partner-signup-password');
            (errorAnchor || input).insertAdjacentElement('afterend', output);
            input.setAttribute('aria-describedby', output.id);
            errors.set(input, output);
        }
        errors.get(input).textContent = text;
        input.setAttribute('aria-invalid', String(Boolean(text)));
        return !text;
    };
    const validate = input => {
        const value = input.value.trim();
        let text = '';
        if (input.name === 'terms_accepted') {
            if (!input.checked) text = 'Please accept the Terms and Conditions.';
        } else if (!value) text = `Please enter ${input.name === 'owner_name' ? 'your name' : input.name === 'password_confirmation' ? 'the password confirmation' : `your ${input.name}`}.`;
        else if (input.name === 'owner_name' &&
            (value.length < 2 || value.length > 255 || !/^[\p{L}\p{M} .’'-]+$/u.test(value) || !/\p{L}/u.test(value))) {
            text = 'Enter a name with 2 to 255 characters, using letters, spaces, periods, apostrophes or hyphens.';
        } else if (input.name === 'email' && (value.length > 254 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value))) {
            text = 'Enter a valid email address.';
        } else if (input.name === 'email' && emailCheckState === 'invalid' && checkedEmail === email()) {
            input.setAttribute('aria-invalid', 'true');
            return false;
        } else if (input.name === 'password') {
            const password = input.value;
            if (password.length < 8 || password.length > 16) text = 'Use a password with 8 to 16 characters.';
            else if (!/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/[0-9]/.test(password) || !/[^a-zA-Z0-9\s]/.test(password)) {
                text = 'Include an uppercase letter, a lowercase letter, a number and a special character.';
            }
        } else if (input.name === 'password_confirmation' && input.value !== field('password').value) {
            text = 'The passwords do not match.';
        }
        return showError(input, text);
    };
    const required = [...form.querySelectorAll('[required]')];
    required.forEach(input => {
        input.addEventListener('blur', () => validate(input));
        input.addEventListener(input.type === 'checkbox' ? 'change' : 'input', () => {
            if (errors.has(input)) validate(input);
            if (input.name === 'password' && errors.has(field('password_confirmation'))) validate(field('password_confirmation'));
        });
    });
    field('email').addEventListener('input', () => {
        emailRevision++;
        activeEmailCheck?.controller.abort();
        activeEmailCheck = null;
        checkedEmail = '';
        clearTimeout(emailCheckTimer);
        showEmailCheck('idle', "We'll check the email format and domain before sending an OTP.");
        if (errors.has(field('email'))) validate(field('email'));
        if (email()) emailCheckTimer = setTimeout(checkEmail, 700);
        verificationToken = '';
        verifiedEmail = '';
        sentEmail = '';
        resendAt = 0;
        expiresAt = 0;
        code.value = '';
        message('');
        update();
    });
    field('email').addEventListener('blur', () => checkEmail());
    const readDraft = () => {
        let draft;
        try { draft = JSON.parse(sessionStorage.getItem(draftKey) || 'null'); } catch { /* Handled below. */ }
        if (!draft || typeof draft !== 'object' ||
            !['restaurant_name', 'phone', 'address', 'postal_code'].every(key => typeof draft[key] === 'string' && draft[key].trim()) ||
            !['country_id', 'state_id'].every(key => /^\d+$/.test(String(draft[key] || ''))) ||
            (draft.city_id != null && draft.city_id !== '' && !/^[0-9]+$/.test(String(draft.city_id))) ||
            (draft.phone_country_id != null && !/^[0-9]+$/.test(String(draft.phone_country_id))) ||
            draft.restaurant_name.trim().length < 2 || draft.restaurant_name.length > 150 ||
            !/\p{L}/u.test(draft.restaurant_name) || draft.address.length > 100 ||
            (draft.postal_code.trim().length < 5 || draft.postal_code.trim().length > 9) ||
            !/^[0-9]{7,16}$/.test(draft.phone)) {
            const error = new Error('Your restaurant information is missing or incomplete. Please complete it on the Partner with us page before signing up.');
            error.code = 'missing_restaurant_information';
            throw error;
        }
        return draft;
    };
    const update = () => {
        submit.disabled = busy || completed || emailCheckState === 'checking';
        form.setAttribute('aria-busy', String(busy));
        required.forEach(input => { input.disabled = busy || completed; });
        const remaining = Math.max(0, Math.ceil((resendAt - Date.now()) / 1000));
        verify.disabled = busy || completed || !/^[0-9]{6}$/.test(code.value) || sentEmail !== email() || Date.now() >= expiresAt;
        resend.disabled = busy || completed || remaining > 0;
        resend.textContent = remaining ? `Resend OTP (${remaining}s)` : 'Resend OTP';
        back.disabled = busy || completed;
        code.disabled = busy || completed;
        if (sentEmail) {
            const seconds = Math.max(0, Math.ceil((expiresAt - Date.now()) / 1000));
            expiry.textContent = seconds ? `OTP expires in ${Math.floor(seconds / 60)}:${String(seconds % 60).padStart(2, '0')}.` : 'Code expired. Please request another OTP.';
        }
    };
    const request = async (url, payload, controller = new AbortController()) => {
        const timeout = setTimeout(() => controller.abort(), 30000);
        try {
            const response = await fetch(url, {
                method: 'POST', credentials: 'same-origin', signal: controller.signal,
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': field('_token').value },
                body: JSON.stringify(payload),
            });
            let data;
            try { data = await response.json(); } catch { data = {}; }
            if (!response.ok || data.success !== true) {
                const fallback = response.status === 419 ? 'Your session expired. Refresh the page and try again.'
                    : response.status === 429 ? 'Too many requests. Please wait a minute and try again.'
                    : 'Unable to complete your request. Please try again.';
                const error = new Error(Object.values(data.errors || {}).flat()[0] || data.error || data.message || fallback);
                error.errors = data.errors || {};
                error.retryAfter = response.status === 429 ? Number(response.headers.get('Retry-After')) || 60 : 0;
                throw error;
            }
            return data;
        } catch (error) {
            if (error.name === 'AbortError') throw new Error('The request timed out. Please try again.');
            if (error instanceof TypeError) throw new Error('Unable to connect. Check your connection and try again.');
            throw error;
        } finally {
            clearTimeout(timeout);
        }
    };
    const showEmailCheck = (state, text) => {
        emailCheckState = state;
        emailFeedback.dataset.state = state;
        emailFeedback.textContent = text;
        field('email').setAttribute('aria-busy', String(state === 'checking'));
        field('email').setAttribute('aria-invalid', String(state === 'invalid'));
        update();
    };
    const checkEmail = () => {
        clearTimeout(emailCheckTimer);
        const requestedEmail = email();
        if (!requestedEmail || requestedEmail.length > 254 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(requestedEmail)) {
            showEmailCheck('invalid', requestedEmail ? 'Enter a valid email address.' : 'Please enter your email.');
            return Promise.resolve(false);
        }
        if (emailCheckState === 'valid' && checkedEmail === requestedEmail && Date.now() - checkedAt < 60000) return Promise.resolve(true);
        if (activeEmailCheck?.email === requestedEmail) return activeEmailCheck.promise;
        const revision = emailRevision;
        const controller = new AbortController();
        showError(field('email'), '');
        showEmailCheck('checking', 'Checking email format and domain…');
        const promise = (async () => {
            try {
                const data = await request(form.dataset.emailCheckUrl, { email: requestedEmail }, controller);
                if (revision !== emailRevision || requestedEmail !== email()) return false;
                checkedEmail = requestedEmail;
                checkedAt = Date.now();
                showEmailCheck('valid', data.message || 'Email format and domain are valid.');
                return true;
            } catch (error) {
                if (revision !== emailRevision || requestedEmail !== email()) return false;
                checkedEmail = requestedEmail;
                showEmailCheck(error.errors?.email ? 'invalid' : 'error', error.message);
                return false;
            } finally {
                if (revision === emailRevision) activeEmailCheck = null;
            }
        })();
        activeEmailCheck = { email: requestedEmail, controller, promise };
        return promise;
    };
    const reportFailure = error => {
        if (error.errors?.captcha_verification_token) {
            captchaToken = '';
            captchaExpiresAt = 0;
            window.grecaptcha?.reset();
        }
        if (captchaError && (error.errors?.['g-recaptcha-response'] || error.errors?.captcha_verification_token)) {
            captchaError.textContent = error.message;
        }
        if (error.code === 'missing_restaurant_information') {
            window.location.assign(`${form.dataset.partnerUrl}?restaurant_information=required#restaurant-interest-form`);
            return;
        }
        message(error.message, true);
        document.getElementById('partner-signup-information-link').hidden = error.code !== 'missing_restaurant_information';
        toast(error.message);
        for (const [name, messages] of Object.entries(error.errors || {})) {
            if (name === 'g-recaptcha-response') continue;
            const input = field(name);
            if (input && input.type !== 'hidden') showError(input, Array.isArray(messages) ? messages.join(' ') : String(messages));
        }
    };
    const openOtp = () => {
        form.hidden = true;
        title.hidden = true;
        accountLink.hidden = true;
        panel.hidden = false;
        document.getElementById('partner-otp-email').textContent = email();
        code.focus();
    };
    const sendCode = async () => {
        if (busy || completed) return;
        busy = true;
        update();
        message('Sending verification code…');
        try {
            const data = await request(sentEmail === email() ? form.dataset.resendOtpUrl : form.dataset.sendOtpUrl,
                { email: email(), type: form.dataset.otpType });
            sentEmail = email();
            expiresAt = Date.now() + (Number(data.expires_in) || 120) * 1000;
            resendAt = Date.now() + (Number(data.resend_after) || 60) * 1000;
            code.value = '';
            if (panel.hidden) openOtp();
            message('OTP sent. Enter the code to verify your email and submit your application.');
            toast('Verification code sent to your email.', 'success');
        } catch (error) {
            if (error.retryAfter) resendAt = Date.now() + error.retryAfter * 1000;
            if (error.errors?.email) {
                panel.hidden = true;
                form.hidden = false;
                title.hidden = false;
                accountLink.hidden = false;
            }
            reportFailure(error);
            if (error.errors?.email) field('email').focus();
        } finally {
            busy = false;
            update();
            if (!panel.hidden) code.focus();
        }
    };
    const register = async () => {
        const draft = readDraft();
        const data = await request(form.action, {
            restaurant_name: draft.restaurant_name.trim(), phone: draft.phone.trim(),
            phone_country_id: draft.phone_country_id || draft.country_id,
            country_id: draft.country_id, state_id: draft.state_id, city_id: draft.city_id || null,
            address_line1: draft.address.trim(), owner_name: field('owner_name').value.trim(), email: email(),
            postal_code: draft.postal_code.trim(),
            password: field('password').value, password_confirmation: field('password_confirmation').value,
            terms_accepted: field('terms_accepted').checked,
            email_verification_token: verificationToken,
            captcha_verification_token: captchaToken,
        });
        completed = true;
        try { sessionStorage.removeItem(draftKey); } catch { /* Storage may be unavailable. */ }
        verificationToken = '';
        verifiedEmail = '';
        sentEmail = '';
        expiresAt = 0;
        resendAt = 0;
        code.value = '';
        form.reset();
        form.hidden = true;
        panel.hidden = true;
        title.hidden = true;
        accountLink.hidden = true;
        message('');
        clearInterval(ticker);
        window.location.assign(data.redirect_url || form.dataset.loginUrl);
    };
    const submitVerified = async () => {
        try { await register(); } catch (error) {
            if (error.errors?.email_verification_token || /verify your email/i.test(error.errors?.email?.[0] || '')) {
                verificationToken = '';
                verifiedEmail = '';
                expiresAt = 0;
            }
            panel.hidden = true;
            form.hidden = false;
            title.hidden = false;
            accountLink.hidden = false;
            reportFailure(error);
        }
    };
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (busy || completed) return;
        const invalid = required.filter(input => !validate(input));
        if (invalid.length) {
            invalid[0].focus();
            toast(errors.get(invalid[0])?.textContent || emailFeedback.textContent);
            return;
        }
        try { readDraft(); } catch (error) { reportFailure(error); return; }
        busy = true;
        update();
        try {
            await verifyCaptcha();
        } catch (error) {
            if (captchaError) captchaError.textContent = error.message;
            reportFailure(error);
            return;
        } finally {
            busy = false;
            update();
        }
        if (verificationToken && verifiedEmail === email()) {
            busy = true;
            update();
            message('Submitting your application…');
            try { await submitVerified(); } finally { busy = false; update(); }
            return;
        }
        busy = true;
        update();
        let emailIsValid;
        try { emailIsValid = await checkEmail(); } finally { busy = false; update(); }
        if (!emailIsValid) {
            field('email').focus();
            toast(emailFeedback.textContent);
            return;
        }
        if (sentEmail === email() && Date.now() < expiresAt) {
            openOtp();
            update();
        }
        else await sendCode();
    });
    otpForm.addEventListener('submit', async event => {
        event.preventDefault();
        if (verify.disabled || busy || completed) return;
        busy = true;
        update();
        message('Verifying your email…');
        try {
            const data = await request(form.dataset.verifyOtpUrl, { email: email(), otp: code.value, type: form.dataset.otpType });
            if (!data.verified || !/^[a-zA-Z0-9]{64}$/.test(data.verification_token || '')) {
                throw new Error('Email verification was not completed. Please try again.');
            }
            verifiedEmail = email();
            verificationToken = data.verification_token;
            message('Email verified. Submitting your application…');
            await submitVerified();
        } catch (error) { reportFailure(error); } finally { busy = false; update(); }
    });
    code.addEventListener('input', () => {
        code.value = code.value.replace(/\D/g, '').slice(0, 6);
        update();
    });
    resend.addEventListener('click', () => { if (!resend.disabled) sendCode(); });
    back.addEventListener('click', () => {
        if (busy) return;
        panel.hidden = true;
        form.hidden = false;
        title.hidden = false;
        accountLink.hidden = false;
        field('email').focus();
    });
    const ticker = setInterval(update, 1000);
    update();
})();
