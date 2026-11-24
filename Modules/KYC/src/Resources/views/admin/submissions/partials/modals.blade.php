<!-- Reject Modal -->
<div id="rejectModal" class="hidden" role="dialog" aria-modal="true" aria-labelledby="reject-modal-title" aria-describedby="reject-modal-description">
    <div class="ks-modal">
        <div class="ks-modal-head">
            <span class="ks-modal-icon" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></svg>
            </span>
            <div>
                <h3 id="reject-modal-title">Reject submission</h3>
                <p id="reject-modal-description">Tell the owner what needs to be corrected.</p>
            </div>
            <button type="button" onclick="closeRejectModal()" class="ks-modal-close" aria-label="Close">
                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="ks-modal-body">
            <form id="rejectForm" onsubmit="event.preventDefault(); rejectSubmission();">
                @csrf
                <label for="rejection_reason">
                    Reason for rejection <span class="text-red-600" aria-hidden="true">*</span>
                </label>
                <textarea id="rejection_reason"
                          rows="4"
                          required
                          placeholder="Explain why this submission is being rejected…" minlength="10" maxlength="100"></textarea>
                <p class="ks-modal-note">This reason is shared with the restaurant owner.</p>
            </form>
        </div>
        <div class="ks-modal-foot">
            <button type="button" onclick="closeRejectModal()" class="ks-btn">Cancel</button>
            <button type="button" onclick="rejectSubmission()" class="ks-btn ks-btn--danger-solid">Confirm rejection</button>
        </div>
    </div>
</div>
