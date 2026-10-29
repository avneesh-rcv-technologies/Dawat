<form id="currencyForm" class="form-ajax" method="POST"
    action="@isset($currency) {{ route('settings.currencies.update', $currency->id) }} @else {{ route('settings.currencies.store') }} @endisset">
    @csrf
    @isset($currency) @method('PUT') @endisset

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Code Field -->
        <div class="col-span-1">
            <label for="code" class="custom-label">Code</label>
            <input type="text" name="code" id="code" value="{{ $currency->code ?? old('code') }}" class="input-field"
                required>
        </div>

        <!-- Currency Name Field -->
        <div class="col-span-1">
            <label for="currency_name" class="custom-label">Currency Name</label>
            <input type="text" name="name" id="currency_name" value="{{ $currency->name ?? old('name') }}"
                class="input-field" required>
        </div>

        <!-- Currency Symbol Field -->
        <div class="col-span-1">
            <label for="symbol" class="custom-label">Symbol</label>
            <input type="text" name="symbol" id="symbol" value="{{ $currency->symbol ?? old('symbol') }}"
                class="input-field" required>
        </div>

        <!-- Currency Decimal Field -->
        <div class="col-span-1">
            <label for="decimal" class="custom-label">Currency Decimal</label>
            <input type="text" name="decimal" id="decimal" value="{{ $currency->decimal ?? old('decimal') }}"
                class="input-field" required>
        </div>

    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('settings.currencies.index') }}" class="btn-cancel">Cancel</a>

        <button type="submit" name="button" class="btn-primary">
            @isset($currency) Update Currency @else Create Currency @endisset
        </button>
    </div>

</form>