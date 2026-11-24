(() => {
    'use strict';
    document.querySelectorAll('[data-kyc-reject-open]').forEach(button => {
        button.addEventListener('click', () => document.getElementById(button.dataset.kycRejectOpen)?.showModal());
    });
    document.querySelectorAll('[data-kyc-reject-close]').forEach(button => {
        button.addEventListener('click', () => button.closest('dialog')?.close());
    });
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
            if (!form.reportValidity()) return;
            const isReject = form.elements.namedItem('action')?.value === 'reject';
            const confirmed = await window.appConfirm({
                title: form.dataset.kycReviewConfirm,
                message: isReject ? 'The owner will be asked to correct and resubmit this document.' : 'The owner will be notified that this document has been approved.',
                confirmText: isReject ? 'Reject document' : 'Approve',
                tone: isReject ? 'danger' : 'primary',
            });
            if (!confirmed || submitting) return;
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
