<form method="POST" action="{{ route('restaurant.onboarding.corrections.menu') }}">
    @csrf
    @foreach($correctionData['items'] as $index => $item)
        <fieldset class="detail-field" style="margin:16px 0">
            <legend>{{ $item->name }}</legend>
            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
            <label>Name <input required maxlength="255" name="items[{{ $index }}][name]" value="{{ old('items.'.$index.'.name', $item->name) }}"></label>
            <label>Price <input required type="number" step="0.01" min="0" name="items[{{ $index }}][sale_price]" value="{{ old('items.'.$index.'.sale_price', $item->sale_price) }}"></label>
            <label>List price <input type="number" step="0.01" min="0" name="items[{{ $index }}][list_price]" value="{{ old('items.'.$index.'.list_price', $item->list_price) }}"></label>
            <label>Food type <select required name="items[{{ $index }}][food_type]">
                @foreach(['veg' => 'Vegetarian', 'non_veg' => 'Non-vegetarian'] as $value => $label)<option value="{{ $value }}" @selected(old('items.'.$index.'.food_type', $item->food_type) === $value)>{{ $label }}</option>@endforeach
            </select></label>
            <label>Availability <select required name="items[{{ $index }}][is_available]">
                <option value="1" @selected((bool) old('items.'.$index.'.is_available', $item->is_available))>Available</option>
                <option value="0" @selected(!(bool) old('items.'.$index.'.is_available', $item->is_available))>Unavailable</option>
            </select></label>
            <label style="display:block;margin-top:12px">Description <textarea maxlength="5000" name="items[{{ $index }}][description]" style="display:block;width:100%">{{ old('items.'.$index.'.description', $item->description) }}</textarea></label>
        </fieldset>
    @endforeach
    @if($correctionData['items']->isNotEmpty())<button type="submit">Submit menu corrections for review</button>@endif
</form>
