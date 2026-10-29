<form id="userForm" class="form-ajax" method="POST"
    action="@isset($country) {{ route('settings.countries.update', $country->id) }} @else {{ route('settings.countries.store') }} @endisset">
    @csrf
    @isset($country) @method('PUT') @endisset

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Code Field -->
        <div class="col-span-1">
            <label for="code" class="custom-label">Code <span class="text-red-500">*</span></label>
            <input type="text" name="code" id="code" value="{{ $country->code ?? old('code') }}" class="input-field"
                required>
        </div>

        <!-- Country Name Field  -->
        <div class="col-span-1">
            <label for="name" class="custom-label">Country Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ $country->name ?? old('name') }}" class="input-field"
                required>
        </div>

        <!-- ISO3 Field -->
        <div class="col-span-1">
            <label for="iso3" class="custom-label">ISO3</label>
            <input type="text" name="iso3" id="iso3" value="{{ $country->iso3 ?? old('iso3') }}" class="input-field"
                maxlength="3">
        </div>

        <!-- Numeric Code Field -->
        <div class="col-span-1">
            <label for="numeric_code" class="custom-label">Numeric Code</label>
            <input type="text" name="numeric_code" id="numeric_code" value="{{ $country->numeric_code ?? old('numeric_code') }}" class="input-field">
        </div>

        <!-- Phone Code Field -->
        <div class="col-span-1">
            <label for="phonecode" class="custom-label">Phone Code</label>
            <input type="text" name="phonecode" id="phonecode" value="{{ $country->phonecode ?? old('phonecode') }}" class="input-field">
        </div>

        <!-- Capital Field -->
        <div class="col-span-1">
            <label for="capital" class="custom-label">Capital</label>
            <input type="text" name="capital" id="capital" value="{{ $country->capital ?? old('capital') }}" class="input-field">
        </div>

        <!-- Currency Field -->
        <div class="col-span-1">
            <label for="currency" class="custom-label">Currency</label>
            <input type="text" name="currency" id="currency" value="{{ $country->currency ?? old('currency') }}" class="input-field">
        </div>

        <!-- Region Field -->
        <div class="col-span-1">
            <label for="region" class="custom-label">Region</label>
            <input type="text" name="region" id="region" value="{{ $country->region ?? old('region') }}" class="input-field">
        </div>

        <!-- Subregion Field -->
        <div class="col-span-1">
            <label for="subregion" class="custom-label">Subregion</label>
            <input type="text" name="subregion" id="subregion" value="{{ $country->subregion ?? old('subregion') }}" class="input-field">
        </div>

        <!-- Latitude Field -->
        <div class="col-span-1">
            <label for="latitude" class="custom-label">Latitude</label>
            <input type="text" name="latitude" id="latitude" value="{{ $country->latitude ?? old('latitude') }}" class="input-field">
        </div>

        <!-- Longitude Field -->
        <div class="col-span-1">
            <label for="longitude" class="custom-label">Longitude</label>
            <input type="text" name="longitude" id="longitude" value="{{ $country->longitude ?? old('longitude') }}" class="input-field">
        </div>

        <!-- Flag Field -->
        <div class="col-span-1">
            <label for="flag" class="custom-label">Flag</label>
            <input type="text" name="flag" id="flag" value="{{ $country->flag ?? old('flag') }}" class="input-field">
        </div>

        <!-- WikiData ID Field -->
        <div class="col-span-1">
            <label for="wikiDataId" class="custom-label">WikiData ID</label>
            <input type="text" name="wikiDataId" id="wikiDataId" value="{{ $country->wikiDataId ?? old('wikiDataId') }}" class="input-field">
        </div>
    </div>


    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('settings.countries.index') }}" class="btn-cancel">Cancel</a>

        <button type="submit" name="button" class="btn-primary">
            @isset($country) Update Country @else Create Country @endisset
        </button>
    </div>

</form>