<form id="stateForm" class="form-ajax" method="POST"
      action="@isset($state) {{ route('settings.states.update', $state->id) }} @else {{ route('settings.states.store') }} @endisset">
    @csrf
    @isset($state) @method('PUT') @endisset

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- State Name -->
        <div class="col-span-1">
            <label for="name" class="custom-label">State Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name"
                   value="{{ old('name', $state->name ?? '') }}"
                   class="input-field" required>
        </div>

        <!-- State Code -->
        <div class="col-span-1">
            <label for="code" class="custom-label">Code <span class="text-red-500">*</span></label>
            <input type="text" name="code" id="code"
                   value="{{ old('code', $state->code ?? '') }}"
                   class="input-field" required>
        </div>

        <!-- Country Name -->
        <div class="col-span-1">
            <label for="country" class="custom-label">Country Name <span class="text-red-500">*</span></label>
            <select name="country_id" id="country" class="input-field" required>
                <option value="">-- Select Country --</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}"
                            data-code="{{ $country->code }}"
                            {{ old('country_id', $state->country_id ?? null) == $country->id ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Country Code -->
        <div class="col-span-1">
            <label for="country_code" class="custom-label">Country Code <span class="text-red-500">*</span></label>
            <input type="text" name="country_code" id="country_code"
                   value="{{ old('country_code', $state->country_code ?? '') }}"
                   class="input-field"
                   readonly required>
        </div>

        <!-- Latitude -->
        <div class="col-span-1">
            <label for="latitude" class="custom-label">Latitude</label>
            <input type="text" name="latitude" id="latitude"
                   value="{{ old('latitude', $state->latitude ?? '') }}"
                   class="input-field">
        </div>

        <!-- Longitude -->
        <div class="col-span-1">
            <label for="longitude" class="custom-label">Longitude</label>
            <input type="text" name="longitude" id="longitude"
                   value="{{ old('longitude', $state->longitude ?? '') }}"
                   class="input-field">
        </div>

        <!-- Flag -->
        <div class="col-span-1">
            <label for="flag" class="custom-label">Flag</label>
            <input type="text" name="flag" id="flag"
                   value="{{ old('flag', $state->flag ?? '') }}"
                   class="input-field">
        </div>

        <!-- WikiData ID -->
        <div class="col-span-1">
            <label for="wikiDataId" class="custom-label">WikiData ID</label>
            <input type="text" name="wikiDataId" id="wikiDataId"
                   value="{{ old('wikiDataId', $state->wikiDataId ?? '') }}"
                   class="input-field">
        </div>

    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('settings.states.index') }}" class="btn-cancel">Cancel</a>

        <button type="submit" name="button" class="btn-primary">
            @isset($state) Update State @else Create State @endisset
        </button>
    </div>
</form>




