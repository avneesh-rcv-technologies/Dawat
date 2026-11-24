/**
 * Web password transport (admin + restaurant partner login / reset forms).
 *
 * For a form with data-password-challenge-url:
 *  1. PasswordTransport.prepare(form) fetches a single-use nonce + RSA public key,
 *     computes SHA-256(password) and encrypts {nonce, hash, password} with RSA-OAEP
 *     for every non-empty password field ("v1." + base64url ciphertext).
 *  2. The next `new FormData(form)` (the existing submit handlers) receives the
 *     envelopes instead of the plain values via the `formdata` event, so the
 *     inputs keep their names and server-side field errors still map to them.
 *
 * The server (DecryptClientPasswords) only accepts envelopes on these routes and
 * each nonce works once, so a captured request cannot be replayed.
 */
(() => {
    if (window.PasswordTransport) return;

    const PREFIX = 'v1.';
    const MAX_PASSWORD_BYTES = 128;
    const DEFAULT_FIELDS = ['password', 'password_confirmation', 'current_password'];
    const pending = new WeakMap();
    const encoder = new TextEncoder();

    const base64ToBytes = base64 => {
        const binary = atob(base64);
        const bytes = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
        return bytes;
    };

    const bytesToBase64Url = bytes => {
        let binary = '';
        bytes.forEach(byte => { binary += String.fromCharCode(byte); });
        return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    };

    const sha256Hex = async bytes => {
        const digest = await window.crypto.subtle.digest('SHA-256', bytes);
        return Array.from(new Uint8Array(digest), byte => byte.toString(16).padStart(2, '0')).join('');
    };

    const fetchChallenge = async url => {
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (response.status === 429) throw new Error('Too many attempts. Please wait a minute and try again.');
        if (!response.ok) throw new Error('Unable to secure your password. Please refresh the page and try again.');

        const challenge = await response.json();
        const publicKey = await window.crypto.subtle.importKey(
            'spki',
            base64ToBytes(challenge.public_key),
            { name: 'RSA-OAEP', hash: challenge.hash || 'SHA-1' },
            false,
            ['encrypt']
        );

        return { nonce: challenge.nonce, publicKey };
    };

    const encrypt = async (password, challenge) => {
        const bytes = encoder.encode(password);
        if (bytes.length > MAX_PASSWORD_BYTES) throw new Error('The password is too long.');

        const body = { n: challenge.nonce, h: await sha256Hex(bytes), p: bytesToBase64Url(bytes) };
        const cipher = await window.crypto.subtle.encrypt(
            { name: 'RSA-OAEP' },
            challenge.publicKey,
            encoder.encode(JSON.stringify(body))
        );

        return PREFIX + bytesToBase64Url(new Uint8Array(cipher));
    };

    const fieldNames = form => (form.dataset.passwordFields || DEFAULT_FIELDS.join(','))
        .split(',').map(name => name.trim()).filter(Boolean);

    /**
     * Encrypt the form's password fields for its next FormData build.
     * Throws an Error with a user-facing message on failure.
     */
    const prepare = async form => {
        pending.delete(form);

        const values = {};
        fieldNames(form).forEach(name => {
            const input = form.elements.namedItem(name);
            if (input && typeof input.value === 'string' && input.value !== '') values[name] = input.value;
        });
        if (!Object.keys(values).length) return;

        const url = form.dataset.passwordChallengeUrl;
        if (!url) throw new Error('Unable to secure your password. Please refresh the page and try again.');
        if (!window.isSecureContext || !window.crypto?.subtle) {
            throw new Error('Secure password encryption needs HTTPS and an up-to-date browser.');
        }

        try {
            const challenge = await fetchChallenge(url);
            const envelopes = {};
            for (const [name, value] of Object.entries(values)) {
                envelopes[name] = await encrypt(value, challenge);
            }
            pending.set(form, envelopes);
        } catch (error) {
            // Network failures stay TypeErrors; Web Crypto DOMExceptions get a readable message.
            if (error instanceof DOMException) {
                throw new Error('Unable to secure your password. Please refresh the page and try again.');
            }
            throw error;
        }
    };

    // `new FormData(form)` fires `formdata`; swap plain passwords for the one-time envelopes.
    document.addEventListener('formdata', event => {
        const envelopes = pending.get(event.target);
        if (!envelopes) return;
        pending.delete(event.target);
        Object.entries(envelopes).forEach(([name, value]) => event.formData.set(name, value));
    });

    window.PasswordTransport = { prepare };
})();
