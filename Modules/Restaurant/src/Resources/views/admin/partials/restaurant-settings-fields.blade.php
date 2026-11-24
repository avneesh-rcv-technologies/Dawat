@once
<style>
    .restaurant-preferences { --rp-accent: #ff650f; --rp-line: #e5e7eb; --rp-muted: #788397; color: #171717; }
    .restaurant-preferences * { box-sizing: border-box; }
    .restaurant-preferences .rp-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; padding-bottom: 28px; }
    .restaurant-preferences h3 { margin: 0 0 6px; font-size: 20px; font-weight: 600; }
    .restaurant-preferences p { margin: 0; color: var(--rp-muted); font-size: 13px; line-height: 1.6; }
    .restaurant-preferences .rp-status { width: 240px; flex-shrink: 0; }
    .restaurant-preferences .rp-section { display: grid; grid-template-columns: minmax(180px, 1fr) minmax(0, 2.3fr); gap: 32px; padding: 28px 0; border-top: 1px solid var(--rp-line); }
    .restaurant-preferences h4 { margin: 0 0 6px; font-size: 15px; font-weight: 600; }
    .restaurant-preferences .rp-section-intro p { max-width: 250px; }
    .restaurant-preferences .form-label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; }
    .restaurant-preferences .input-field { width: 100%; min-height: 46px; padding: 11px 14px; border: 1px solid #dce0e6; border-radius: 10px; background-color: #fff; color: #171717; font-size: 14px; }
    .restaurant-preferences .input-field:focus { border-color: var(--rp-accent); outline: 3px solid #fff0e7; }
    .restaurant-preferences .rp-food-select { max-width: 440px; }
    .restaurant-preferences .rp-services { display: grid; gap: 12px; }
    .restaurant-preferences .rp-service { display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 18px 20px; border: 1px solid var(--rp-line); border-radius: 12px; background: #fff; cursor: pointer; transition: border-color .2s, background-color .2s; }
    .restaurant-preferences .rp-service:has(input:checked) { border-color: #ffd5bd; background: #fffaf6; }
    .restaurant-preferences .rp-service-title { display: block; margin-bottom: 3px; font-size: 14px; font-weight: 500; }
    .restaurant-preferences .rp-service-description { display: block; color: var(--rp-muted); font-size: 12px; line-height: 1.5; }
    .restaurant-preferences .rp-switch { position: relative; display: inline-flex; flex: 0 0 42px; width: 42px; height: 24px; }
    .restaurant-preferences .rp-switch input { position: absolute; inset: 0; width: 100%; height: 100%; margin: 0; opacity: 0; cursor: pointer; }
    .restaurant-preferences .rp-switch-track { width: 42px; height: 24px; border-radius: 999px; background: #d4d9e1; pointer-events: none; transition: background .2s; }
    .restaurant-preferences .rp-switch-track::after { content: ''; display: block; width: 18px; height: 18px; margin: 3px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px #0002; transition: transform .2s; }
    .restaurant-preferences .rp-switch input:checked + .rp-switch-track { background: var(--rp-accent); }
    .restaurant-preferences .rp-switch input:checked + .rp-switch-track::after { transform: translateX(18px); }
    .restaurant-preferences .rp-switch input:focus-visible + .rp-switch-track { outline: 3px solid #ffc8a8; outline-offset: 3px; }
    .restaurant-preferences .rp-time { display: flex; align-items: center; max-width: 220px; position: relative; }
    .restaurant-preferences .rp-time input { padding-right: 68px; }
    .restaurant-preferences .rp-time span { position: absolute; right: 16px; color: var(--rp-muted); font-size: 13px; pointer-events: none; }
    .restaurant-preferences .rp-help { margin-top: 8px; font-size: 12px; }
    .restaurant-preferences .rp-status-options { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 12px; }
    .restaurant-preferences .rp-status-option { display: flex; align-items: center; gap: 9px; padding: 12px; border: 1px solid var(--rp-line); border-radius: 9px; font-size: 13px; cursor: pointer; }
    .restaurant-preferences .rp-status-option:has(input:checked) { border-color: #ffd5bd; background: #fffaf6; }
    .restaurant-preferences .rp-status-option input { width: 16px; height: 16px; accent-color: var(--rp-accent); flex-shrink: 0; }
    .restaurant-preferences .rp-cancellation-statuses { margin: 24px 0 0; padding: 0; border: 0; min-width: 0; }
    .restaurant-preferences .rp-cancellation-statuses:disabled { opacity: .5; }
    .restaurant-preferences .rp-cancellation-statuses:disabled .rp-status-option,
    .restaurant-preferences .rp-cancellation-statuses:disabled input { cursor: not-allowed; }
    .restaurant-preferences .rp-preview { display: flex; gap: 12px; margin-top: 20px; padding: 16px; border: 1px solid #ffe0ca; border-radius: 10px; background: #fff8f2; }
    .restaurant-preferences .rp-preview svg { width: 19px; height: 19px; flex-shrink: 0; color: #df651c; margin-top: 2px; }
    .restaurant-preferences .rp-preview h5 { margin: 0 0 4px; font-size: 13px; font-weight: 600; }
    @media (max-width: 900px) {
        .restaurant-preferences .rp-section { grid-template-columns: 1fr; gap: 18px; }
        .restaurant-preferences .rp-section-intro p { max-width: none; }
        .restaurant-preferences .rp-status-options { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 540px) {
        .restaurant-preferences .rp-header { flex-direction: column; }
        .restaurant-preferences .rp-status { width: 100%; }
        .restaurant-preferences .rp-status-options { grid-template-columns: 1fr; }
        .restaurant-preferences .rp-service { padding: 16px; }
    }
</style>
@endonce

<div class="restaurant-preferences">
    <div class="rp-header">
        <div>
            <h3>Restaurant preferences</h3>
            <p>Manage how your restaurant serves customers and handles orders.</p>
        </div>
        <div class="rp-status">
            <label class="form-label" for="restaurant-preference-status">Restaurant status</label>
            <select id="restaurant-preference-status" name="status" class="input-field">
                @foreach(['draft' => 'Draft', 'active' => 'Publish (Active)', 'inactive' => 'Inactive'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $restaurant->status ?? 'draft') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <section class="rp-section" aria-labelledby="rp-food-title">
        <div class="rp-section-intro">
            <h4 id="rp-food-title">Food type</h4>
            <p>Choose the category that best describes your menu.</p>
        </div>
        <div class="rp-food-select">
            <label class="form-label" for="restaurant-food-type">Food category</label>
            <select id="restaurant-food-type" name="food_type" class="input-field">
                @foreach(['all' => 'All Types', 'veg' => 'Vegetarian Only', 'non_veg' => 'Non-Vegetarian', 'vegan' => 'Vegan Only'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('food_type', $restaurant->food_type ?? 'all') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </section>

    <section class="rp-section" aria-labelledby="rp-services-title">
        <div class="rp-section-intro">
            <h4 id="rp-services-title">Service options</h4>
            <p>Choose how customers can order from your restaurant.</p>
        </div>
        <div class="rp-services">
            @foreach([
                'is_takeaway' => ['Enable takeaway', 'Allow customers to pick up their orders.'],
                'is_delivery' => ['Enable delivery', 'Offer delivery to your customers.'],
                'is_accepting_orders' => ['Accept orders', 'Allow customers to place new orders.'],
            ] as $field => [$label, $description])
                <label class="rp-service">
                    <span>
                        <span class="rp-service-title">{{ $label }}</span>
                        <span class="rp-service-description">{{ $description }}</span>
                    </span>
                    <span class="rp-switch">
                        <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $restaurant->{$field} ?? false)) aria-label="{{ $label }}">
                        <span class="rp-switch-track" aria-hidden="true"></span>
                    </span>
                </label>
            @endforeach
        </div>
    </section>

    <section class="rp-section" aria-labelledby="rp-orders-title">
        <div class="rp-section-intro">
            <h4 id="rp-orders-title">Order management</h4>
            <p>Set the time customers have to edit an order or request a refund.</p>
        </div>
        <div>
            <label class="form-label" for="restaurant-order-time">Order edit / refund time limit</label>
            <div class="rp-time">
                <input id="restaurant-order-time" type="number" name="order_time_limit" class="input-field" value="{{ old('order_time_limit', $restaurant->order_time_limit ?? 30) }}" min="0" max="1440" placeholder="30" aria-describedby="rp-order-time-help">
                <span>hours</span>
            </div>
            <p class="rp-help" id="rp-order-time-help">Customers can edit orders or request refunds within this time limit.</p>
        </div>
    </section>

    <section class="rp-section" aria-labelledby="rp-cancellation-title">
        <div class="rp-section-intro">
            <h4 id="rp-cancellation-title">Cancellation policy</h4>
            <p>Control when customers can cancel their orders.</p>
        </div>
        <div>
            <label class="rp-service">
                <span>
                    <span class="rp-service-title">Allow customer cancellations</span>
                    <span class="rp-service-description">Let customers cancel orders based on the statuses below.</span>
                </span>
                <span class="rp-switch">
                    <input type="checkbox" name="allow_order_cancellation" value="1" @checked(old('allow_order_cancellation', $restaurant->allow_order_cancellation ?? true)) aria-label="Allow customer cancellations">
                    <span class="rp-switch-track" aria-hidden="true"></span>
                </span>
            </label>
            <fieldset class="rp-cancellation-statuses" aria-labelledby="rp-statuses-label" @disabled(!old('allow_order_cancellation', $restaurant->allow_order_cancellation ?? true))>
                <input type="hidden" name="cancellation_time_limit" value="15">
                <span class="form-label" id="rp-statuses-label">Cancellable order statuses</span>
                <p>Customers can cancel an order while it has one of these statuses.</p>
                <div class="rp-status-options">
                    @php
                        $allStatuses = fn_get_order_statuses('O');
                        $selectedStatuses = old('cancellable_statuses', $restaurant->cancellable_statuses ?? ['P', 'O', 'H']);
                    @endphp
                    @foreach($allStatuses as $status)
                        <label class="rp-status-option">
                            <input type="checkbox" name="cancellable_statuses[]" value="{{ $status->status_code }}" @checked(in_array($status->status_code, $selectedStatuses))>
                            <span>{{ $status->name }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <div class="rp-preview">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7v1"/></svg>
                <div>
                    <h5>Saved cancellation policy</h5>
                    <p id="policy-preview-text">
                        @if(isset($restaurant) && $restaurant->id)
                            {{ $restaurant->getCancellationPolicyText() }}
                        @else
                            Save your settings to apply your cancellation policy.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </section>
</div>

@once
<script>
(() => {
    const initializeCancellationFields = () => {
        document.querySelectorAll('.restaurant-preferences').forEach(preferences => {
            const toggle = preferences.querySelector('[name="allow_order_cancellation"]');
            const fields = preferences.querySelector('.rp-cancellation-statuses');
            if (!toggle || !fields) return;
            const sync = () => { fields.disabled = !toggle.checked; };
            toggle.addEventListener('change', sync);
            toggle.form?.addEventListener('reset', () => setTimeout(sync, 0));
            sync();
        });
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeCancellationFields, { once: true });
    } else {
        initializeCancellationFields();
    }
})();
</script>
@endonce
