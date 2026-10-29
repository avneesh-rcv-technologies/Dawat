(() => {
    const form = document.getElementById('onboarding-form');
    if (!form) return;
    const email = form.elements.email;
    const token = form.elements.email_verification_token;
    const submit = document.getElementById('onboarding-submit');
    const dialog = document.getElementById('onboarding-verification');
    const code = document.getElementById('onboarding-otp');
    const verify = document.getElementById('onboarding-verify');
    const resend = document.getElementById('onboarding-resend');
    const openOtp = document.getElementById('onboarding-open-otp');
    const status = document.getElementById('onboarding-verification-status');
    const formStatus = document.getElementById('onboarding-otp-status');
    const expiry = document.getElementById('onboarding-otp-expiry');
    const normalizedEmail = () => email.value.trim().toLowerCase();
    let verifiedEmail = token.value ? normalizedEmail() : '';
    let sentEmail = '';
    let busy = false;
    let submitting = false;
    let confirming = false;
    let submissionConfirmed = false;
    let resendAt = 0;
    let expiresAt = 0;
    let revision = 0;

    const message = (text, error = false) => {
        status.textContent = text;
        status.dataset.error = String(error);
        formStatus.textContent = text;
        formStatus.dataset.error = String(error);
    };
    const update = () => {
        const remaining = Math.max(0, Math.ceil((resendAt - Date.now()) / 1000));
        submit.disabled = busy || submitting;
        verify.disabled = busy || submitting || !/^[0-9]{6}$/.test(code.value) || sentEmail !== normalizedEmail() || Date.now() >= expiresAt;
        resend.disabled = busy || submitting || remaining > 0;
        resend.textContent = remaining ? `Resend (${remaining}s)` : 'Resend OTP';
        openOtp.hidden = !sentEmail || sentEmail !== normalizedEmail() || submitting;
        openOtp.disabled = busy;
        if (sentEmail) {
            const seconds = Math.max(0, Math.ceil((expiresAt - Date.now()) / 1000));
            expiry.textContent = seconds ? `Code expires in ${Math.floor(seconds / 60)}:${String(seconds % 60).padStart(2, '0')}.` : 'Code expired. Please resend OTP.';
        }
    };
    const request = async (url, payload) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': form.elements._token.value },
            body: JSON.stringify({ ...payload, type: form.dataset.otpType }),
        });
        let data;
        try { data = await response.json(); } catch { throw new Error('Unable to complete verification. Please try again.'); }
        if (!response.ok || !data.success) {
            throw new Error(Object.values(data.errors || {}).flat()[0] || data.error || data.message || 'Please try again.');
        }
        return data;
    };
    const sendCode = async () => {
        if (busy || submitting) return;
        const requestedEmail = normalizedEmail();
        const requestedRevision = revision;
        busy = true;
        message('Sending verification code…');
        update();
        try {
            const data = await request(sentEmail === requestedEmail ? form.dataset.resendOtpUrl : form.dataset.sendOtpUrl, { email: requestedEmail });
            if (requestedRevision !== revision) return;
            sentEmail = requestedEmail;
            resendAt = Date.now() + data.resend_after * 1000;
            expiresAt = Date.now() + data.expires_in * 1000;
            code.value = '';
            message('OTP sent. Enter the code to submit your application.');
            if (dialog.open) code.focus();
        } catch (error) {
            if (requestedRevision === revision) message(error.message, true);
        } finally {
            busy = false;
            update();
        }
    };

    email.addEventListener('input', () => {
        revision++;
        verifiedEmail = '';
        token.value = '';
        code.value = '';
        message("We'll verify your email with an OTP before submitting.");
        update();
    });
    const confirmation = form.elements.password_confirmation;
    const password = form.elements.password;
    const passwordError = document.getElementById('password-error');
    const confirmationError = document.getElementById('password-confirmation-error');
    const validatePasswords = (showErrors = false) => {
        const value = password.value;
        let error = '';
        if (!value) error = 'Please enter a password.';
        else if (value.length < 8 || value.length > 16) error = 'Use between 8 and 16 characters.';
        else {
            const missing = [];
            if (!/[A-Z]/.test(value)) missing.push('an uppercase letter');
            if (!/[a-z]/.test(value)) missing.push('a lowercase letter');
            if (!/[0-9]/.test(value)) missing.push('a number');
            if (!/[^a-zA-Z0-9\s]/.test(value)) missing.push('a special character');
            if (missing.length) error = `Your password must contain ${missing.join(', ')}.`;
        }
        const mismatch = !confirmation.value ? 'Please confirm your password.' : confirmation.value !== value ? 'The passwords do not match.' : '';
        [[password, passwordError, error], [confirmation, confirmationError, mismatch]].forEach(([input, output, text]) => {
            input.setCustomValidity(text);
            const visible = showErrors || Boolean(input.value) || Boolean(output.textContent);
            output.textContent = visible ? text : '';
            input.setAttribute('aria-invalid', String(visible && Boolean(text)));
        });
        return !error && !mismatch;
    };
    [password, confirmation].forEach(input => {
        input.addEventListener('input', () => validatePasswords());
        input.addEventListener('blur', () => validatePasswords());
        input.addEventListener('invalid', () => validatePasswords(true));
    });
    validatePasswords();

    const openVerification = () => {
        document.getElementById('onboarding-verification-email').textContent = normalizedEmail();
        if (!dialog.open) dialog.showModal();
        code.focus();
    };
    openOtp.addEventListener('click', () => { if (!busy && !submitting) openVerification(); });
    form.addEventListener('submit', async event => {
        if (submitting) { event.preventDefault(); return; }
        if (!validatePasswords(true)) {
            event.preventDefault();
            (password.validationMessage ? password : confirmation).reportValidity();
            return;
        }
        if (token.value && verifiedEmail === normalizedEmail()) {
            if (!submissionConfirmed) {
                event.preventDefault();
                if (confirming) return;
                confirming = true;
                const requestedEmail = normalizedEmail();
                const requestedToken = token.value;
                try {
                    const accepted = await window.confirmOnboardingSubmission('Submit your restaurant registration for review?');
                    if (accepted && requestedEmail === normalizedEmail() && requestedToken === token.value) {
                        submissionConfirmed = true;
                        form.requestSubmit(submit);
                    } else {
                        message('Email verified. You can review your application before submitting.');
                    }
                } finally {
                    confirming = false;
                }
                return;
            }
            submissionConfirmed = false;
            email.value = normalizedEmail();
            submitting = true;
            update();
            return;
        }
        event.preventDefault();
        if (busy) return;
        openVerification();
        if (sentEmail === normalizedEmail() && Date.now() < expiresAt) {
            code.focus();
        } else {
            sendCode();
        }
    });
    code.addEventListener('input', () => {
        code.value = code.value.replace(/\D/g, '').slice(0, 6);
        update();
    });
    code.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            if (!verify.disabled) verify.click();
        }
    });
    verify.addEventListener('click', async () => {
        if (verify.disabled) return;
        const requestedEmail = normalizedEmail();
        const requestedRevision = revision;
        busy = true;
        message('Verifying your email…');
        update();
        try {
            const data = await request(form.dataset.verifyOtpUrl, { email: requestedEmail, otp: code.value });
            if (requestedRevision !== revision) return;
            if (!data.verified || !data.verification_token) throw new Error('Email verification was not completed. Please try again.');
            verifiedEmail = requestedEmail;
            token.value = data.verification_token;
            busy = false;
            message('Email verified. Submitting your application…');
            dialog.close();
            update();
            form.requestSubmit(submit);
        } catch (error) {
            if (requestedRevision === revision) message(error.message, true);
        } finally {
            busy = false;
            update();
        }
    });
    resend.addEventListener('click', () => { if (!resend.disabled) sendCode(); });
    document.getElementById('onboarding-cancel').addEventListener('click', () => dialog.close());
    setInterval(update, 1000);
    update();
})();
