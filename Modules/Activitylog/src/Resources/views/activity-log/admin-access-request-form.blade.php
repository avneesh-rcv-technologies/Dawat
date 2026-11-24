<form class="form-ajax" method="POST" action="{{ route('activitylog.access-request.store') }}">
    @csrf

    <input type="hidden" name="requested_path" value="{{ $requestedPath ?? '/' }}">

    <div class="mb-4">
        <label for="ip_address" class="custom-label">IP Address</label>
        <input type="text" id="ip_address" value="{{ $ipAddress ?? request()->ip() }}" class="input-field" readonly>
    </div>

    <div class="mb-4">
        <label for="reason" class="custom-label required">Reason for Admin Access</label>
        <textarea name="reason" id="reason" rows="4" class="input-field" placeholder="Explain why you need admin access" required></textarea>
    </div>

    <div class="mt-5 flex justify-end">
        <button type="submit" name="button" class="btn-primary">
            Submit Request
        </button>
    </div>
</form>
