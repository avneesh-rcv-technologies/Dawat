<link rel="stylesheet" href="{{ asset('css/correction-request.css') }}">
<div id="correction-actions-menu" class="correction-actions-menu" role="menu" aria-label="Submission actions" hidden>
    <a href="#" role="menuitem" data-correction-view-link hidden>View submission</a>
    <button type="button" role="menuitem" data-correction-request>Request changes</button>
    <p class="correction-menu-note" data-correction-menu-note hidden>Available after this step is submitted.</p>
</div>
<dialog id="correction-request-dialog" class="correction-dialog" aria-labelledby="correction-dialog-title" aria-describedby="correction-dialog-description"
    data-has-errors="{{ ($errors->has('reason') || $errors->has('step_key')) ? 'true' : 'false' }}"
    data-previous-step="{{ old('step_key') }}" data-previous-reason="{{ old('reason') }}"
    data-previous-error="{{ $errors->first('reason') ?: $errors->first('step_key') }}">
    <form method="POST" id="correction-request-form">
        @csrf
        <input type="hidden" name="step_key" id="correction-step">
        <input type="hidden" name="action" value="request_changes" id="correction-action">
        <header class="correction-dialog-header">
            <div><p class="correction-eyebrow">REVIEW REQUEST</p><h2 id="correction-dialog-title">Request changes</h2></div>
            <button type="button" class="correction-dialog-close" data-correction-close aria-label="Close request changes">×</button>
        </header>
        <div class="correction-dialog-body">
            <p class="correction-target" id="correction-target"></p>
            <p id="correction-dialog-description">Explain what needs to be corrected or reuploaded. The owner must resubmit this information for review.</p>
            <div id="correction-edit-panel">
                <label for="correction-reason">Reason and required corrections <span aria-hidden="true">*</span></label>
                <textarea name="reason" id="correction-reason" rows="5" required maxlength="500" placeholder="Describe the details or documents the owner needs to update…" aria-describedby="correction-reason-count correction-request-error"></textarea>
                <p id="correction-reason-count" class="correction-reason-count">0 / 500</p>
                <p id="correction-kyc-note" class="correction-info" hidden>This requests all submitted KYC documents again. Use a document’s action menu to request changes to just that document.</p>
            </div>
            <div id="correction-confirm-panel" hidden>
                <h3>Send this change request?</h3>
                <p>The selected information will reopen for corrections and require a new review.</p>
                <p id="correction-confirm-reason" class="correction-confirm-reason"></p>
            </div>
            <p id="correction-request-error" class="correction-error" role="alert" hidden></p>
        </div>
        <footer class="correction-dialog-footer">
            <button type="button" class="correction-button-secondary" data-correction-close>Cancel</button>
            <button type="button" class="correction-button-secondary" id="correction-edit-back" hidden>Back to edit</button>
            <button type="submit" class="correction-button-primary" id="correction-request-submit">Continue</button>
        </footer>
    </form>
</dialog>
<script src="{{ asset('js/correction-request.js') }}" defer></script>
