<div class="mb-6 group bg-white border-t pt-6">
    <label class="custom-label">Order Notes</label>
    <textarea 
        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-100 focus:border-primary-100 resize-none transition-colors duration-200"
        name="notes" 
        rows="6"
        placeholder="Add order notes here..."
    >{{ $order_note ?? '' }}</textarea>
</div>