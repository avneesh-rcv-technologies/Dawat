<form
    action="@if(!empty($payment)) {{ route('admin.payment_methods.update', $payment) }} @else {{ route('admin.payment_methods.store') }} @endif"
    method="POST" class="form-ajax payment-method-form"
    enctype="multipart/form-data" data-init="loadTemplate">
    @csrf

    @if(!empty($payment)) @method('PUT') @endif


    <input type="radio" name="tab" id="tab-general" class="hidden" checked>
    <input type="radio" name="tab" id="tab-config" class="hidden">

    <!-- Tab buttons (labels toggle the radios) -->
    <div class="payment-method-tabs border-b border-gray-200 mb-4">
        <div class="flex space-x-6" role="tablist" aria-label="Payment method sections">
            <label for="tab-general" class="cursor-pointer pb-2 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700">
                General
            </label>

            <label for="tab-config" class="cursor-pointer pb-2 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700">
                Configuration
            </label>
        </div>
    </div>

    <!-- Tab contents wrapper -->
    <div class="tab-contents">
        <!-- General content (hidden by default; shown when #tab-general is checked) -->
        <div class="tab-general space-y-4 p-1">
            <div class="payment-method-field">
                <label for="name" class="custom-label">Name</label>
                <input type="text" name="name" value="{{ $payment->merchant_name ?? ''}}"
                    class="input-field">
            </div>

            <div class="payment-method-field">
                <label for="processor" class="custom-label">Processor</label>
                <select name="processor" class="input-field">
                    <option value="">-- Select Processor --</option>
                    @foreach($processors as $processor)
                    <option value="{{ $processor->code }}" {{ (!empty($payment->payment_method_id) && $processor->id == $payment->payment_method_id) ? 'selected' : '' }}>
                        {{ $processor->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            @if(!empty($payment->id))

            <div class="payment-method-upload">
                @include('filemanager::components.file-uploader', ['object_type' => 'payment', 'object_id' => $payment->id, 'name' => 'image'])
            </div>
            @endif

            <div class="payment-method-field">
                <label for="amount" class="custom-label">Total Order Amount</label>
                <input type="text" name="amount" value="{{ $payment->amount ?? ''}}"
                    class="input-field">
            </div>

            <div class="payment-method-field">
                <label for="payment_status" class="custom-label">Status</label>
                <select id="payment_status" name="payment_data[is_active]" class="input-field">

                    <option value="1" {{ (!empty($payment->is_active) && $payment->is_active == 1) ? 'selected' : '' }}>
                        Active
                    </option>

                    <option value="0" {{ (!empty($payment) && $payment->is_active == 0) ? 'selected' : '' }}>
                        Inactive
                    </option>

                </select>
            </div>
        </div>

        <!-- Configuration content (hidden by default; shown when #tab-config is checked) -->
        <div class="tab-config space-y-4 p-1" id="payment_configuration">
                @foreach($processors as $processor)
                    @if(!empty($payment->payment_method_id) && $processor->id == $payment->payment_method_id)
                        @include($processor->template, ['payment_data' => !empty($payment->config) ? json_decode($payment->config, true) : []])
                    @endif
                @endforeach
        </div>
    </div>

    <div class="payment-method-actions flex justify-end">
        <x-button type="submit"
            class="btn btn-primary"
            label="Save"
            icon=''
            name="button" />
    </div>
</form>
<style>
    .payment-method-form { --payment-ink: #103647; --payment-muted: #667987; --payment-border: #dce5ea; }
    .payment-method-form .payment-method-tabs { margin: 0 -24px 22px; padding: 0 24px; border-color: var(--payment-border); }
    .payment-method-form .payment-method-tabs > div { gap: 8px; }
    .payment-method-form .payment-method-tabs label { position: relative; display: inline-flex; align-items: center; min-height: 46px; margin: 0; padding: 0 15px; border: 1px solid transparent; border-bottom: 0; border-radius: 9px 9px 0 0; color: var(--payment-muted); transition: background .16s ease, color .16s ease, border-color .16s ease; }
    .payment-method-form .payment-method-tabs label::before { margin-right: 8px; font-family: 'Material Icons Outlined'; font-size: 18px; }
    .payment-method-form .payment-method-tabs label[for="tab-general"]::before { content: 'tune'; }
    .payment-method-form .payment-method-tabs label[for="tab-config"]::before { content: 'settings'; }
    .payment-method-form .payment-method-tabs label:hover { background: #eef4f5; color: var(--payment-ink); }
    .payment-method-form:has(#tab-general:checked) .payment-method-tabs label[for="tab-general"],
    .payment-method-form:has(#tab-config:checked) .payment-method-tabs label[for="tab-config"] { border-color: var(--payment-border); background: #fff; color: #087b57; font-weight: 650; box-shadow: 0 -2px 7px rgba(16, 54, 71, .035); }
    .payment-method-form .tab-contents { padding: 22px; border: 1px solid var(--payment-border); border-radius: 0 0 12px 12px; background: #fff; }
    .payment-method-form .tab-contents .tab-general,
    .payment-method-form .tab-contents .tab-config { display: none; }
    .payment-method-form #tab-general:checked ~ .tab-contents .tab-general { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
    .payment-method-form #tab-config:checked ~ .tab-contents .tab-config { display: block; }
    .payment-method-form .payment-method-field { min-width: 0; padding: 15px; border: 1px solid #e7edf0; border-radius: 10px; background: #fbfcfd; }
    .payment-method-form .payment-method-field .custom-label { display: block; margin-bottom: 7px; color: #294653; font-size: 13px; font-weight: 650; }
    .payment-method-form .input-field { width: 100%; min-height: 42px; border: 1px solid #cbd7de; border-radius: 8px; background: #fff; color: var(--payment-ink); }
    .payment-method-form .input-field:focus { border-color: #00a86b; outline: 3px solid rgba(0, 168, 107, .12); }
    .payment-method-form .payment-method-upload { grid-column: 1 / -1; padding: 15px; border: 1px solid #e7edf0; border-radius: 10px; }
    .payment-method-form .payment-method-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    .payment-method-form .payment-method-actions .btn-primary { min-width: 125px; min-height: 42px; border-radius: 8px; font-weight: 650; }
    @media (max-width: 640px) {
        .payment-method-form .payment-method-tabs { margin-inline: -16px; padding-inline: 16px; }
        .payment-method-form .tab-contents { padding: 14px; }
        .payment-method-form #tab-general:checked ~ .tab-contents .tab-general { grid-template-columns: 1fr; gap: 12px; }
        .payment-method-form .payment-method-actions .btn-primary { width: 100%; }
    }
</style>
