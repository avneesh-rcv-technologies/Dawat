// Public auth forms only: no dashboard, modal, search, or tag-manager modules.
const notify = (message, type) => {
    if (typeof window.showToast === 'function' && document.getElementById('toast-container')) {
        window.showToast(message, type, type === 'success' ? 'Success' : 'Error');
    }
};

document.querySelectorAll('form.form-ajax').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (form.dataset.submitting === 'true' || !form.reportValidity()) return;
        form.querySelectorAll('[data-form-error]').forEach((error) => error.remove());
        form.querySelectorAll('[aria-invalid="true"]').forEach((field) => {
            field.removeAttribute('aria-invalid');
            field.classList.remove('!border', '!border-red-500');
        });
        const buttons = [...form.querySelectorAll('[type="submit"]')];
        const disabled = buttons.map((button) => button.disabled);
        form.dataset.submitting = 'true';
        form.setAttribute('aria-busy', 'true');
        buttons.forEach((button) => { button.disabled = true; });
        try {
            const response = await fetch(form.action, {
                method: form.method.toUpperCase(),
                body: new FormData(form),
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json();
            if (data.errors) {
                for (const [name, errors] of Object.entries(data.errors)) {
                    const field = form.elements.namedItem(name);
                    const error = document.createElement('div');
                    error.dataset.formError = '';
                    error.className = 'text-red-500 text-sm mt-1';
                    error.setAttribute('role', 'alert');
                    error.textContent = Array.isArray(errors) ? errors.join(' ') : String(errors);
                    if (field instanceof HTMLElement) {
                        field.setAttribute('aria-invalid', 'true');
                        field.classList.add('!border', '!border-red-500');
                        field.parentElement.appendChild(error);
                    } else form.appendChild(error);
                }
                form.querySelector('[aria-invalid="true"]')?.focus();
            } else if (!response.ok || data.success === false) {
                throw new Error(data.message || 'Unable to complete your request. Please try again.');
            } else {
                if (data.message) notify(data.message, 'success');
                if (data.redirect_url) window.location.assign(data.redirect_url);
            }
        } catch (error) {
            const message = error instanceof SyntaxError ? 'Unable to complete your request. Please try again.' : error.message;
            const status = document.createElement('p');
            status.dataset.formError = '';
            status.className = 'text-red-500 text-sm mt-1';
            status.setAttribute('role', 'alert');
            status.textContent = message;
            form.appendChild(status);
            notify(message, 'error');
        } finally {
            form.dataset.submitting = 'false';
            form.removeAttribute('aria-busy');
            buttons.forEach((button, index) => { button.disabled = disabled[index]; });
        }
    });
});
