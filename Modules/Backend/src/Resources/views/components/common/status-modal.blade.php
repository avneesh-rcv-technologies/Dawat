
<div id="status-modal" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 hidden transition-opacity duration-300 ease-out">
    <div class="bg-[var(--color-white)] p-8 rounded-2xl shadow-[0_4px_20px_var(--color-shadow)] w-full max-w-md transform transition-all duration-300 ease-out scale-95 hover:scale-100">
        <div class="flex items-center gap-3 mb-4">
            <svg class="w-6 h-6 text-[var(--color-primary-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h2 class="text-2xl font-semibold text-[var(--color-text-primary)]">Change Status</h2>
        </div>
        <p class="mb-6 text-[var(--color-text-secondary)] text-base leading-relaxed">Are you sure you want to change the status of this item?</p>

        <form id="status-form" class="form-ajax" method="POST">
            @csrf
            <input type="hidden" name="status" id="status-value" value="">
            <div class="flex justify-end gap-4">
                <button type="button" id="status-cancel-btn" class="px-5 py-2.5 bg-[var(--color-primary-100)] text-[var(--color-primary-800)] rounded-lg hover:bg-[var(--color-primary-200)] focus:bg-[var(--color-primary-300)] focus:outline-none focus:ring-2 focus:ring-[var(--color-focus)] transition-all duration-200 text-sm font-medium">Cancel</button>
                <button type="submit" name="button" class="px-5 py-2.5 bg-[var(--color-primary-500)] text-[var(--color-text-inverted)] rounded-lg hover:bg-[var(--color-primary-600)] focus:bg-[var(--color-primary-700)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-400)] transition-all duration-200 text-sm font-medium">Change</button>
            </div>
        </form>
    </div>
</div>



