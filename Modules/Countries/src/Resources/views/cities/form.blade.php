<form id="cityForm" class="form-ajax" method="POST"
    action="@isset($city) {{ route('settings.cities.update', $city->id) }} @else {{ route('settings.cities.store') }} @endisset">
    @csrf
    @isset($city) @method('PUT') @endisset

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- City Name -->
        <div class="col-span-1">
            <label for="name" class="custom-label">City Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name', $city->name ?? '') }}" class="input-field"
                required>
        </div>

        <!-- Country -->
        <div class="col-span-1">
            <x-advanced-selector name="country_id" table="countries" search-column="name" value-column="id"
                display-column="name" :multiple="false" :required="false" :selected="isset($city) && $city->country_id ? [$city->country_id] : []" placeholder="Select country..." label="Country" order-by="name"
                order-direction="asc" :ajax="true" />
        </div>

        <!-- State -->
        <div class="col-span-1">
            <x-advanced-selector name="state_id" table="country_states" search-column="name" value-column="id"
                display-column="name" :multiple="false" :required="false" :selected="isset($city) && $city->state_id ? [$city->state_id] : []" placeholder="Select state..." label="State" order-by="name"
                order-direction="asc" :ajax="true"  :filters="[]"/>
        </div>

        <!-- Latitude -->
        <div class="col-span-1">
            <label for="latitude" class="custom-label">Latitude</label>
            <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $city->latitude ?? '') }}"
                class="input-field">
        </div>

        <!-- Longitude -->
        <div class="col-span-1">
            <label for="longitude" class="custom-label">Longitude</label>
            <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $city->longitude ?? '') }}"
                class="input-field">
        </div>

        <!-- Flag -->
        <div class="col-span-1">
            <label for="flag" class="custom-label">Flag</label>
            <input type="text" name="flag" id="flag" value="{{ old('flag', $city->flag ?? '') }}" class="input-field">
        </div>

        <!-- WikiData ID -->
        <div class="col-span-1">
            <label for="wikiDataId" class="custom-label">WikiData ID</label>
            <input type="text" name="wikiDataId" id="wikiDataId"
                value="{{ old('wikiDataId', $city->wikiDataId ?? '') }}" class="input-field">
        </div>

    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('settings.cities.index') }}" class="btn-cancel">Cancel</a>

        <button type="submit" name="button" class="btn-primary">
            @isset($city) Update City @else Create City @endisset
        </button>
    </div>
</form>