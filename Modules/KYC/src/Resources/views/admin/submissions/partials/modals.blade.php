<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-[#072B12]/40 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="kyc-panel w-full max-w-md mx-4 overflow-hidden">
        <div class="kyc-panel-header flex justify-between items-center">
            <span>Reject submission</span>
            <button type="button" onclick="closeRejectModal()" class="text-gray-500 hover:text-gray-800" aria-label="Close">
                <span class="material-icons-outlined text-xl">close</span>
            </button>
        </div>
        <div class="kyc-panel-body">
            <form id="rejectForm">
                @csrf
                <label class="custom-label" for="rejection_reason">
                    Reason for rejection <span class="text-red-500">*</span>
                </label>
                <textarea id="rejection_reason"
                          rows="4"
                          required
                          class="input-field mt-1 resize-none"
                          placeholder="Explain why this submission is being rejected…" minlength="10" maxlength="100"></textarea>
                <p class="mt-2 text-xs text-gray-500">This reason is shared with the restaurant owner.</p>
            </form>
        </div>
        <div class="px-5 py-4 border-t border-[rgba(36,27,18,0.08)] flex justify-end gap-3 bg-[#faf8f3]">
            <button type="button" onclick="closeRejectModal()" class="btn-secondary">Cancel</button>
            <button type="button" onclick="rejectSubmission()" class="btn-primary bg-red-900 border-red-900 hover:bg-red-950">
                Confirm rejection
            </button>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div id="messageModal" class="fixed inset-0 bg-[#072B12]/40 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="kyc-panel w-full max-w-md mx-4 overflow-hidden">
        <div class="kyc-panel-header flex justify-between items-center">
            <span>Send message</span>
            <button type="button" onclick="closeMessageModal()" class="text-gray-500 hover:text-gray-800" aria-label="Close">
                <span class="material-icons-outlined text-xl">close</span>
            </button>
        </div>
        <div class="kyc-panel-body">
            <form id="messageForm">
                @csrf
                <label class="custom-label" for="message">
                    Message <span class="text-red-500">*</span>
                </label>
                <textarea id="message"
                          rows="4"
                          required
                          class="input-field mt-1 resize-none"
                          placeholder="Type your message…"></textarea>
            </form>
        </div>
        <div class="px-5 py-4 border-t border-[rgba(36,27,18,0.08)] flex justify-end gap-3 bg-[#faf8f3]">
            <button type="button" onclick="closeMessageModal()" class="btn-secondary">Cancel</button>
            <button type="button" onclick="sendMessage()" class="btn-primary">Send message</button>
        </div>
    </div>
</div>
