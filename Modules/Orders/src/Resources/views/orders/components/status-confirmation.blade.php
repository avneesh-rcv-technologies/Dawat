<dialog id="order-status-confirmation" class="order-confirm-dialog" aria-labelledby="order-confirm-title" aria-describedby="order-confirm-description">
    <form method="dialog" id="order-confirm-dialog-form">
        <div class="order-confirm-icon" aria-hidden="true">!</div>
        <h2 id="order-confirm-title">Confirm order cancellation</h2>
        <p id="order-confirm-number"></p>
        <p id="order-confirm-description"></p>
        <label for="order-confirm-reason">Cancellation reason <span>(optional)</span></label>
        <textarea id="order-confirm-reason" maxlength="2000" rows="3"></textarea>
        <div id="order-confirm-reference-group" hidden>
            <label for="order-confirm-reference">Completed refund reference</label>
            <input id="order-confirm-reference" maxlength="255" placeholder="Gateway or bank refund reference">
            <p>Only confirm after you have refunded the payment manually.</p>
        </div>
        <div class="order-confirm-actions">
            <button type="button" id="order-confirm-back">Keep current status</button>
            <button type="submit" id="order-confirm-submit">Yes, cancel order</button>
        </div>
    </form>
</dialog>
@push('styles')
<style>
.order-confirm-dialog { width: min(480px, calc(100vw - 32px)); max-height: calc(100dvh - 40px); overflow-y: auto; padding: 26px; margin: auto; border: 1px solid #e5e7eb; border-radius: 18px; color: #111827; box-shadow: 0 20px 60px #0003; }
.order-confirm-dialog::backdrop { background: #11182780; }
.order-confirm-dialog h2 { font-size: 20px; font-weight: 700; margin: 14px 0 6px; }
.order-confirm-dialog p { color: #6b7280; font-size: 13px; line-height: 1.7; margin: 8px 0 16px; overflow-wrap: anywhere; }
.order-confirm-dialog .order-confirm-icon { display: grid; place-items: center; width: 42px; height: 42px; border-radius: 12px; color: #c2410c; background: #fff7ed; font-size: 24px; font-weight: 700; }
.order-confirm-dialog label { display: block; font-size: 13px; font-weight: 600; margin: 16px 0 6px; }
.order-confirm-dialog label span { font-weight: 400; color: #6b7280; }
.order-confirm-dialog input, .order-confirm-dialog textarea { width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font: inherit; font-size: 14px; }
.order-confirm-dialog :is(input, textarea, button):focus-visible { outline: 2px solid #ff6210; outline-offset: 3px; }
.order-confirm-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 10px; margin-top: 24px; }
.order-confirm-actions button { padding: 10px 16px; border: 1px solid #d1d5db; border-radius: 9px; background: white; font-size: 13px; font-weight: 600; cursor: pointer; }
.order-confirm-actions #order-confirm-submit { background: #ff6210; color: white; border-color: #ff6210; }
</style>
@endpush
@push('scripts')
<script>
(() => {
    if (window.orderStatusConfirmationBound) return;
    window.orderStatusConfirmationBound = true;
    const dialog = document.getElementById('order-status-confirmation');
    if (!dialog) return;
    let pendingForm = null;
    let approvedForm = null;
    let submitting = false;
    const reason = document.getElementById('order-confirm-reason');
    const reference = document.getElementById('order-confirm-reference');
    const close = () => {
        if (pendingForm && !submitting) pendingForm.elements.status.value = pendingForm.dataset.currentStatus;
        dialog.close();
        pendingForm = null;
    };
    document.getElementById('order-confirm-back').addEventListener('click', close);
    dialog.addEventListener('cancel', event => { event.preventDefault(); close(); });
    document.addEventListener('submit', event => {
        const form = event.target;
        if (form.id !== 'status-update-form') return;
        if (approvedForm === form) { approvedForm = null; return; }
        const target = form.elements.status.value;
        if (!['C', 'R'].includes(target)) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        pendingForm = form;
        submitting = false;
        const refund = target === 'R';
        const paid = ['paid', 'completed', 'captured', 'partially_refunded'].includes(form.dataset.paymentStatus);
        document.getElementById('order-confirm-title').textContent = refund ? 'Cancel & record manual refund?' : 'Cancel this order?';
        document.getElementById('order-confirm-number').textContent = form.dataset.orderNumber;
        document.getElementById('order-confirm-description').textContent = refund
            ? 'This cancels the order and records the captured payment refund as completed. Enter the reference of the refund you have already made. The app does not transfer money.'
            : paid ? 'This cancels the order and creates a pending refund request for its captured payment. Record completion from Refund requests after returning the payment manually.'
                : 'This cancels the order. No refund will be created unless a captured payment exists.';
        document.getElementById('order-confirm-reference-group').hidden = !refund;
        reference.required = refund;
        reference.value = '';
        reason.value = '';
        document.getElementById('order-confirm-submit').textContent = refund ? 'Yes, record completed refund' : paid ? 'Yes, cancel & request refund' : 'Yes, cancel order';
        dialog.showModal();
        document.getElementById('order-confirm-back').focus();
    }, true);
    document.getElementById('order-confirm-dialog-form').addEventListener('submit', event => {
        event.preventDefault();
        if (!pendingForm) return;
        const form = pendingForm;
        form.elements.confirmed.value = '1';
        form.elements.cancellation_reason.value = reason.value.trim();
        form.elements.stripe_refund_id.value = form.elements.status.value === 'R' ? reference.value.trim() : '';
        if (form.elements.status.value === 'R' && !form.elements.stripe_refund_id.value) { reference.focus(); return; }
        submitting = true;
        close();
        approvedForm = form;
        form.requestSubmit();
    });
})();
</script>
@endpush
