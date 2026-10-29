@once
@push('scripts')
<script>
    (function () {
        if (window.notificationReadActionsBound) return;
        window.notificationReadActionsBound = true;
        let pending = false;

        document.addEventListener('click', async function (event) {
            const button = event.target.closest('.notification-mark-all-read, .notification-read-toggle');
            if (!button) return;

            event.preventDefault();
            event.stopPropagation();
            if (pending || button.disabled) return;

            const errorId = 'notification-action-error';
            document.getElementById(errorId)?.remove();
            const buttons = Array.from(document.querySelectorAll('.notification-mark-all-read, .notification-read-toggle'));
            const disabledStates = buttons.map(function (item) { return item.disabled; });
            pending = true;
            buttons.forEach(function (item) { item.disabled = true; });
            button.setAttribute('aria-busy', 'true');

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!token) throw new Error('Please refresh the page and try again.');

                const response = await fetch(button.dataset.url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json().catch(function () {
                    throw new Error('Unable to update notifications. Please refresh the page and try again.');
                });
                if (!response.ok || result.success !== true) {
                    throw new Error(result.message || 'Unable to update notifications. Please try again.');
                }

                window.location.reload();
            } catch (error) {
                const message = error.message || 'Unable to update notifications. Please try again.';
                if (typeof showToast === 'function') {
                    showToast(message, 'error');
                } else {
                    const notice = document.createElement('p');
                    notice.id = errorId;
                    notice.className = 'text-sm text-red-600 mt-2';
                    notice.setAttribute('role', 'alert');
                    notice.textContent = message;
                    button.parentElement.insertAdjacentElement('afterend', notice);
                }
            } finally {
                pending = false;
                buttons.forEach(function (item, index) { item.disabled = disabledStates[index]; });
                button.removeAttribute('aria-busy');
            }
        }, true);
    })();
</script>
@endpush
@endonce
