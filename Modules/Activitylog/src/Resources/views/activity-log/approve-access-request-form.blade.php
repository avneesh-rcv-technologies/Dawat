<form class="form-ajax" method="POST" action="{{ route('activity-log.access-requests.approve', $requestItem->id) }}">
    @csrf

    <div class="mb-3">
        <label for="approve_ip_address" class="custom-label">IP Address</label>
        <input type="text" id="approve_ip_address" class="input-field" value="{{ $requestItem->ip_address }}" readonly>
    </div>

    <div class="mb-3">
        <label for="approve_reason" class="custom-label">Reason</label>
        <textarea id="approve_reason" class="input-field" rows="3" readonly>{{ $requestItem->reason }}</textarea>
    </div>

    <div class="mb-3">
        <label for="approval_type" class="custom-label required">Access Type</label>
        <select name="approval_type" id="approval_type" class="input-field" required>
            <option value="permanent">Permanent</option>
            <option value="temporary">Temporary</option>
        </select>
    </div>

    <div class="mb-3" id="allow-duration-wrap">
        <label for="allow_duration" class="custom-label">Temporary Duration (minutes)</label>
        <input type="number" min="1" name="allow_duration" id="allow_duration" class="input-field" value="60" />
    </div>

    <div class="mb-3">
        <label for="review_note" class="custom-label">Review Note</label>
        <textarea name="review_note" id="review_note" rows="2" class="input-field" placeholder="Optional note"></textarea>
    </div>

    <div class="mt-4 flex justify-end">
        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Approve Access</button>
    </div>
</form>