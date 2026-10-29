<form id="notification-form-{{ $subscription->id }}" class="form-ajax" method="POST" 
    action="{{ route('subscriptions.sendNotification', $subscription->id) }}">
    @csrf
    @method('POST')

    <div class="space-y-4">
        <!-- Title Field -->
        <div>
            <label for="title-{{ $subscription->id }}" class="block text-sm font-medium text-gray-700 mb-2">
                Notification Title <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                id="title-{{ $subscription->id }}" 
                name="title"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                placeholder="Enter notification title" 
                required>
        </div>

        <!-- Message Field -->
        <div>
            <label for="message-{{ $subscription->id }}" class="block text-sm font-medium text-gray-700 mb-2">
                Message <span class="text-red-500">*</span>
            </label>
            <textarea id="message-{{ $subscription->id }}" 
                name="message" 
                rows="5"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                placeholder="Enter notification message" 
                required></textarea>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-3 mt-6">
        <button type="button" onclick="closeModal()"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            Cancel
        </button>
        <x-button type="submit" class="flex-1 btn-primary" label="Send Notification" icon="send" name="button" />
    </div>
</form>
