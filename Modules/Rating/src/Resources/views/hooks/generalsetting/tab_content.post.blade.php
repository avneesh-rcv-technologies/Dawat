<section id="tab-rating" class="tab-panel">
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-5">
            <h2 class="text-lg font-medium text-gray-800">
                Ratings & Reviews Configuration
            </h2>
            <p class="mt-1 text-sm text-gray-500">Configure how ratings and reviews work on your site</p>
        </div>
        <div class="px-6 py-6 space-y-6">
            <!-- Who Can Review -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1">
                    <label class="custom-label">Who Can Review</label>
                    <p class="mt-1 text-sm text-gray-500">Choose who can submit reviews</p>
                </div>
                <div class="md:col-span-2">
                    <select name="settings[rating.who_can_review]" class="input-field">
                        @php
                            $whoCanReview = old('rating.who_can_review', fn_get_setting('rating.who_can_review', 'both'));
                        @endphp
                        <option value="both" {{ $whoCanReview == 'both' ? 'selected' : '' }}>Both Guests and Logged In Users</option>
                        <option value="logged_in" {{ $whoCanReview == 'logged_in' ? 'selected' : '' }}>Only Logged In Users</option>
                        <option value="guest" {{ $whoCanReview == 'guest' ? 'selected' : '' }}>Only Guests</option>
                    </select>
                </div>
            </div>

            <!-- Require Approval -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label class="custom-label">Require Approval</label>
                    <p class="mt-1 text-sm text-gray-500">Reviews must be approved before being visible</p>
                </div>
                <div class="md:col-span-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="hidden" name="settings[rating.require_approval]" value="0">
                        <input 
                            type="checkbox" 
                            name="settings[rating.require_approval]" 
                            value="1"
                            {{ old('rating.require_approval', fn_get_setting('rating.require_approval', true)) ? 'checked' : '' }}
                            class="sr-only peer hidden">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-700">Require admin approval</span>
                    </label>
                </div>
            </div>

            <!-- Allow Rating Edit -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label class="custom-label">Allow Edit</label>
                    <p class="mt-1 text-sm text-gray-500">Allow users to edit their reviews</p>
                </div>
                <div class="md:col-span-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="hidden" name="settings[rating.allow_rating_edit]" value="0">
                        <input 
                            type="checkbox" 
                            name="settings[rating.allow_rating_edit]" 
                            value="1"
                            {{ old('rating.allow_rating_edit', fn_get_setting('rating.allow_rating_edit', false)) ? 'checked' : '' }}
                            class="sr-only peer hidden">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-700">Allow users to edit</span>
                    </label>
                </div>
            </div>

            <!-- Require Review Text -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label class="custom-label">Require Review Text</label>
                    <p class="mt-1 text-sm text-gray-500">Make written review mandatory</p>
                </div>
                <div class="md:col-span-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="hidden" name="settings[rating.require_review_text]" value="0">
                        <input 
                            type="checkbox" 
                            name="settings[rating.require_review_text]" 
                            value="1"
                            {{ old('rating.require_review_text', fn_get_setting('rating.require_review_text', false)) ? 'checked' : '' }}
                            class="sr-only peer hidden">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-700">Review text required</span>
                    </label>
                </div>
            </div>

            <!-- Min Rating -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label class="custom-label">Minimum Rating</label>
                    <p class="mt-1 text-sm text-gray-500">Minimum rating value</p>
                </div>
                <div class="md:col-span-2">
                    <input 
                        name="settings[rating.min_rating]" 
                        type="number" 
                        min="1"
                        max="5"
                        value="{{ old('rating.min_rating', fn_get_setting('rating.min_rating', 1)) }}" 
                        class="input-field">
                </div>
            </div>

            <!-- Max Rating -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                <div class="md:col-span-1">
                    <label class="custom-label">Maximum Rating</label>
                    <p class="mt-1 text-sm text-gray-500">Maximum rating value</p>
                </div>
                <div class="md:col-span-2">
                    <input 
                        name="settings[rating.max_rating]" 
                        type="number" 
                        min="1"
                        max="10"
                        value="{{ old('rating.max_rating', fn_get_setting('rating.max_rating', 5)) }}" 
                        class="input-field">
                </div>
            </div>
        </div>
    </div>
</section>
