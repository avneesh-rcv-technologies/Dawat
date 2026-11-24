@once
<style>
    .delivery-preferences { color: #171717; }
    .delivery-preferences * { box-sizing: border-box; }
    .delivery-preferences .dp-header { padding-bottom: 28px; }
    .delivery-preferences h3 { margin: 0 0 6px; font-size: 20px; font-weight: 600; }
    .delivery-preferences p { margin: 0; color: #788397; font-size: 13px; line-height: 1.6; }
    .delivery-preferences .dp-section { display: grid; grid-template-columns: minmax(180px, 1fr) minmax(0, 2.3fr); gap: 32px; padding: 28px 0; border-top: 1px solid #e5e7eb; }
    .delivery-preferences h4 { margin: 0 0 6px; font-size: 15px; font-weight: 600; }
    .delivery-preferences .dp-intro p { max-width: 250px; }
    .delivery-preferences .dp-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px; }
    .delivery-preferences .form-label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; }
    .delivery-preferences .input-field { width: 100%; min-height: 46px; padding: 11px 14px; border: 1px solid #dce0e6; border-radius: 10px; background-color: #fff; color: #171717; font-size: 14px; }
    .delivery-preferences .input-field:focus { border-color: #ff650f; outline: 3px solid #fff0e7; }
    .delivery-preferences .dp-help { margin-top: 8px; font-size: 12px; }
    .delivery-preferences .dp-error { margin-top: 8px; color: #dc2626; font-size: 12px; }
    .delivery-preferences .dp-with-unit { position: relative; }
    .delivery-preferences .dp-with-unit input { padding-right: 68px; }
    .delivery-preferences .dp-with-unit > span { position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: #788397; font-size: 12px; pointer-events: none; }
    .delivery-preferences .dp-toggle { display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 18px 20px; border: 1px solid #e5e7eb; border-radius: 12px; cursor: pointer; }
    .delivery-preferences .dp-toggle:has(input:checked) { border-color: #ffd5bd; background: #fffaf6; }
    .delivery-preferences .dp-toggle-title { display: block; margin-bottom: 3px; font-size: 14px; font-weight: 500; }
    .delivery-preferences .dp-toggle-description { display: block; color: #788397; font-size: 12px; line-height: 1.5; }
    .delivery-preferences .dp-switch { position: relative; display: inline-flex; flex: 0 0 42px; width: 42px; height: 24px; }
    .delivery-preferences .dp-switch input { position: absolute; inset: 0; width: 100%; height: 100%; margin: 0; opacity: 0; cursor: pointer; }
    .delivery-preferences .dp-switch-track { width: 42px; height: 24px; border-radius: 999px; background: #d4d9e1; pointer-events: none; transition: background .2s; }
    .delivery-preferences .dp-switch-track::after { content: ''; display: block; width: 18px; height: 18px; margin: 3px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px #0002; transition: transform .2s; }
    .delivery-preferences .dp-switch input:checked + .dp-switch-track { background: #ff650f; }
    .delivery-preferences .dp-switch input:checked + .dp-switch-track::after { transform: translateX(18px); }
    .delivery-preferences .dp-switch input:focus-visible + .dp-switch-track { outline: 3px solid #ffc8a8; outline-offset: 3px; }
    .delivery-preferences .dp-threshold { max-width: 440px; margin-top: 24px; }
    .delivery-preferences .dp-note { display: flex; align-items: flex-start; gap: 10px; margin-top: 20px; padding: 14px 16px; border: 1px solid #ffe0ca; border-radius: 10px; background: #fff8f2; }
    .delivery-preferences .dp-note svg { width: 18px; height: 18px; margin-top: 2px; color: #df651c; flex-shrink: 0; }
    @media (max-width: 900px) {
        .delivery-preferences .dp-section { grid-template-columns: 1fr; gap: 18px; }
        .delivery-preferences .dp-intro p { max-width: none; }
    }
    @media (max-width: 540px) {
        .delivery-preferences .dp-fields { grid-template-columns: 1fr; }
        .delivery-preferences .dp-toggle { padding: 16px; }
    }
</style>
@endonce

<div class="delivery-preferences">
    <div class="dp-header">
        <h3>Delivery preferences</h3>
        <p>Set your delivery area, pricing and free delivery options.</p>
    </div>

    <section class="dp-section" aria-labelledby="dp-area-title">
        <div class="dp-intro">
            <h4 id="dp-area-title">Delivery area & pricing</h4>
            <p>Choose how far you deliver and the cost per mile.</p>
        </div>
        <div class="dp-fields">
            <div>
                <label for="delivery_radius_miles" class="form-label">Delivery radius</label>
                <div class="dp-with-unit">
                    <input id="delivery_radius_miles" name="delivery_radius_miles" type="number" min="0.01" max="999999.99" step="0.01" class="input-field" value="{{ old('delivery_radius_miles', $restaurant->delivery_radius_miles) }}" placeholder="e.g. 10.00" aria-describedby="dp-radius-help">
                    <span>miles</span>
                </div>
                <p class="dp-help" id="dp-radius-help">Maximum delivery distance. Leave blank if no radius is configured.</p>
                @error('delivery_radius_miles')<p class="dp-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="delivery_cost_per_mile" class="form-label">Delivery cost per mile</label>
                <input id="delivery_cost_per_mile" name="delivery_cost_per_mile" type="number" min="0" max="999999.99" step="0.01" required class="input-field" value="{{ old('delivery_cost_per_mile', $restaurant->delivery_cost_per_mile ?? '0.00') }}" aria-describedby="dp-cost-help">
                <p class="dp-help" id="dp-cost-help">Charge per mile in your restaurant's currency.</p>
                @error('delivery_cost_per_mile')<p class="dp-error" role="alert">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="dp-section" aria-labelledby="dp-free-title">
        <div class="dp-intro">
            <h4 id="dp-free-title">Free delivery</h4>
            <p>Offer free delivery when an order reaches your minimum value.</p>
        </div>
        <div>
            <input type="hidden" name="free_delivery_enabled" value="0">
            <label class="dp-toggle">
                <span>
                    <span class="dp-toggle-title">Enable free delivery</span>
                    <span class="dp-toggle-description">Apply free delivery to orders that meet the minimum order value.</span>
                </span>
                <span class="dp-switch">
                    <input type="checkbox" name="free_delivery_enabled" value="1" @checked(old('free_delivery_enabled', $restaurant->free_delivery_enabled ?? false)) aria-label="Enable free delivery">
                    <span class="dp-switch-track" aria-hidden="true"></span>
                </span>
            </label>
            @error('free_delivery_enabled')<p class="dp-error" role="alert">{{ $message }}</p>@enderror
            <div class="dp-threshold">
                <label for="free_delivery_threshold" class="form-label">Minimum order value for free delivery</label>
                <input id="free_delivery_threshold" name="free_delivery_threshold" type="number" min="0" max="999999.99" step="0.01" class="input-field" value="{{ old('free_delivery_threshold', $restaurant->free_delivery_threshold) }}" placeholder="e.g. 50.00" aria-describedby="dp-threshold-help">
                <p class="dp-help" id="dp-threshold-help">Required when free delivery is enabled. Use your restaurant's currency.</p>
                @error('free_delivery_threshold')<p class="dp-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div class="dp-note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7v1"/></svg>
                <p>When free delivery is enabled, orders below the minimum value use your delivery cost per mile.</p>
            </div>
        </div>
    </section>
</div>
