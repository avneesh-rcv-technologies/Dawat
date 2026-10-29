
    <form class="form-ajax" action="{{ route('ratings.update', $rating->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <!-- Rating -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Rating
                </label>
                <select name="rating" class="input-field" required>
                    @for ($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" {{ $rating->rating == $i ? 'selected' : '' }}>
                            {{ str_repeat('⭐', $i) }} ({{ $i }})
                        </option>
                    @endfor
                </select>
            </div>

            <!-- Review Text -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Review
                </label>
                <textarea name="review" rows="4" class="input-field" placeholder="Enter review text...">{{ $rating->review }}</textarea>
            </div>

            <!-- Approval Status -->
            <div>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_approved" value="0">
                    <input type="checkbox" name="is_approved" value="1" {{ $rating->is_approved ? 'checked' : '' }}
                        class="sr-only peer">
                    <div
                        class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                    </div>
                    <span class="ml-2 text-sm font-medium text-gray-700">Approved</span>
                </label>
            </div>

            <!-- Status -->
            <div>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="hidden" name="status" value="0">
                    <input type="checkbox" name="status" value="1" {{ $rating->status ? 'checked' : '' }}
                        class="sr-only peer">
                    <div
                        class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600">
                    </div>
                    <span class="ml-2 text-sm font-medium text-gray-700">Active</span>
                </label>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-3 pt-6 border-t">                
                <x-button type="submit"                     
                    class="btn-primary" 
                    label="Save" 
                    icon=''
                    name="button" 
                />
            </div>
        </div>
    </form>
