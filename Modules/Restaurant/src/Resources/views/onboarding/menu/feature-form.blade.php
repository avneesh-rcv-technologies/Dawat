@extends($menu->layout)

@section('title', $feature ? 'Edit feature' : 'Add feature')
@section('back_url', $menu->route('index', ['tab' => 'features']))
@section('back_label', 'Back to features')
@section('heading', $feature ? 'Edit feature' : 'Add feature')
@section('subheading', 'Features describe your dishes, e.g. spice level, portion size or allergens.')

@section('menu_content')
    @php
        $currentType = old('feature_type', $feature?->feature_type && isset($featureTypes[$feature->feature_type]) ? $feature->feature_type : 'S');
        $options = old('variants', $feature
            ? $feature->variants->map(fn ($variant) => ['variant_id' => $variant->variant_id, 'description' => $variant->description->description ?? ''])->all()
            : [['description' => ''], ['description' => '']]);
        if (count($options) < 2) {
            $options = array_pad($options, 2, ['description' => '']);
        }
    @endphp

    @if ($errors->any())
        <div class="menu-alert" role="alert">
            <strong>Please correct the following:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" class="menu-form"
          action="{{ $feature ? $menu->route('features.update', ['id' => $feature->feature_id]) : $menu->route('features.store') }}">
        @csrf
        @if ($feature) @method('PUT') @endif

        <section class="menu-section">
            <h3>Feature details</h3>
            <p class="menu-section-sub">Only your restaurant can see and use this feature.</p>
            <div class="menu-fields">
                <div>
                    <label for="feature-name" class="form-label">Name <span class="text-red-500">*</span></label>
                    <input id="feature-name" name="name" class="input-field" maxlength="255" value="{{ old('name', $feature?->description?->name) }}" placeholder="e.g. Spice level" required>
                    @error('name')<p class="error-message">{{ $message }}</p>@enderror
                </div>
                <div>
                    <span class="form-label">Type <span class="text-red-500">*</span></span>
                    <div class="menu-check-grid">
                        @foreach ($featureTypes as $typeKey => $type)
                            <label class="menu-check" title="{{ $type['help'] }}">
                                <input type="radio" name="feature_type" value="{{ $typeKey }}" data-has-options="{{ $type['options'] ? 1 : 0 }}" data-help="{{ $type['help'] }}" @checked($currentType === $typeKey)>
                                <span>{{ $type['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="menu-help" id="feature-type-help">{{ $featureTypes[$currentType]['help'] }}</p>
                    @error('feature_type')<p class="error-message">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="feature-status" class="form-label">Status</label>
                    <select id="feature-status" name="status" class="input-field">
                        <option value="A" @selected(old('status', $feature->status ?? 'A') === 'A')>Active</option>
                        <option value="H" @selected(old('status', $feature->status ?? 'A') === 'H')>Hidden</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="menu-section" id="feature-options" @if (!$featureTypes[$currentType]['options']) hidden @endif>
            <h3>Options</h3>
            <p class="menu-section-sub">The choices customers can see, e.g. Mild, Medium, Hot. Add at least two.</p>
            <div id="feature-option-list" class="menu-fields">
                @foreach (array_values($options) as $index => $option)
                    <div class="feature-option-row" style="display: flex; gap: 8px;">
                        @if (!empty($option['variant_id']))<input type="hidden" name="variants[{{ $index }}][variant_id]" value="{{ $option['variant_id'] }}">@endif
                        <input name="variants[{{ $index }}][description]" class="input-field" maxlength="255" value="{{ $option['description'] ?? '' }}" placeholder="Option {{ $index + 1 }}" aria-label="Option {{ $index + 1 }}">
                        <button type="button" class="menu-btn menu-btn-danger menu-btn-sm" data-remove-option aria-label="Remove option">✕</button>
                    </div>
                @endforeach
            </div>
            @error('variants')<p class="error-message">{{ $message }}</p>@enderror
            <button type="button" id="add-feature-option" class="menu-btn menu-btn-ghost menu-btn-sm" style="margin-top: 14px;">+ Add option</button>
        </section>

        <div class="menu-actions">
            <a href="{{ $menu->route('index', ['tab' => 'features']) }}" class="menu-btn menu-btn-ghost">Cancel</a>
            <button type="submit" class="menu-btn">{{ $feature ? 'Update feature' : 'Create feature' }}</button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const section = document.getElementById('feature-options');
    const list = document.getElementById('feature-option-list');
    if (!section || !list) return;
    let nextIndex = list.querySelectorAll('.feature-option-row').length;

    const syncType = () => {
        const checked = document.querySelector('input[name="feature_type"]:checked');
        section.hidden = !checked || checked.dataset.hasOptions !== '1';
        document.getElementById('feature-type-help').textContent = checked?.dataset.help || '';
    };
    document.querySelectorAll('input[name="feature_type"]').forEach(input => input.addEventListener('change', syncType));

    document.getElementById('add-feature-option').addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'feature-option-row';
        row.style.cssText = 'display: flex; gap: 8px;';
        row.innerHTML = `<input name="variants[${nextIndex}][description]" class="input-field" maxlength="255" placeholder="Option ${nextIndex + 1}" aria-label="Option ${nextIndex + 1}">
            <button type="button" class="menu-btn menu-btn-danger menu-btn-sm" data-remove-option aria-label="Remove option">✕</button>`;
        nextIndex++;
        list.appendChild(row);
        row.querySelector('input').focus();
    });
    list.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-option]');
        if (button && list.querySelectorAll('.feature-option-row').length > 2) button.closest('.feature-option-row').remove();
    });
    syncType();
});
</script>
@endpush
