(() => {
    const dialog = document.getElementById('onboarding-confirmation');
    const message = document.getElementById('onboarding-confirmation-message');
    let pending = null;
    let previousFocus = null;

    window.confirmOnboardingSubmission = text => {
        if (pending) return Promise.resolve(false);
        message.textContent = text;
        previousFocus = document.activeElement;
        dialog.returnValue = '';
        return new Promise(resolve => {
            pending = resolve;
            dialog.showModal();
            document.getElementById('onboarding-confirm-cancel').focus();
        });
    };

    document.getElementById('onboarding-confirm-accept').addEventListener('click', () => dialog.close('confirmed'));
    document.getElementById('onboarding-confirm-cancel').addEventListener('click', () => dialog.close('cancelled'));
    dialog.addEventListener('cancel', event => {
        event.preventDefault();
        dialog.close('cancelled');
    });
    dialog.addEventListener('close', () => {
        const resolve = pending;
        pending = null;
        previousFocus?.focus();
        resolve?.(dialog.returnValue === 'confirmed');
    });
})();
