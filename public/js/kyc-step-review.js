(() => {
    'use strict';
    document.querySelectorAll('form[data-kyc-review]').forEach(form => {
        let submitting = false;
        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (submitting) return;
            const reason = form.elements.namedItem('reason');
            if (reason) {
                reason.value = reason.value.trim();
                reason.setCustomValidity(reason.value ? '' : 'Enter a rejection reason.');
            }
            if (!form.reportValidity() || !window.confirm(form.dataset.kycReviewConfirm)) return;
            const button = event.submitter || form.querySelector('button[type="submit"]');
            const error = form.querySelector('[data-kyc-review-error]');
            const originalLabel = button.textContent;
            submitting = true;
            button.disabled = true;
            button.textContent = 'Saving…';
            error.hidden = true;
            try {
                const response = await fetch(form.getAttribute('action'), {
                    method: 'POST', body: new FormData(form),
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const result = await response.json();
                if (response.status === 401 && result.redirect_url) { window.location.replace(result.redirect_url); return; }
                if (!response.ok || !result.success) {
                    throw new Error(Object.values(result.errors || {}).flat().join(' ') || result.message || 'Unable to save this review. Please try again.');
                }
                window.location.reload();
            } catch (failure) {
                error.textContent = failure.message;
                error.hidden = false;
                submitting = false;
                button.disabled = false;
                button.textContent = originalLabel;
            }
        });
        form.elements.namedItem('reason')?.addEventListener('input', event => event.target.setCustomValidity(''));
    });
})();
