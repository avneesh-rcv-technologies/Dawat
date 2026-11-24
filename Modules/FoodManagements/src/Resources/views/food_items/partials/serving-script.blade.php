<script>
function updateFoodServingUnit(unit, serveType, allowedUnits, defaultUnit) {
    if (unit.dataset.serveType === serveType) return;
    const previousUnit = unit.value;
    unit.replaceChildren(...allowedUnits.map(label => new Option(label, label)));
    unit.value = allowedUnits.includes(previousUnit) ? previousUnit : (defaultUnit || '');
    unit.dataset.serveType = serveType;
}

function applyFoodServingValidation(input, rules, row) {
    input.setCustomValidity('');
    ['min', 'max', 'step', 'maxlength'].forEach(attribute => input.removeAttribute(attribute));
    for (const rule of rules) {
        const [name, argument] = rule.split(':');
        if (name === 'integer') input.step = '1';
        if (name === 'decimal') input.step = String(10 ** -Number(argument.split(',').pop()));
        if (name === 'min') input.setAttribute(input.type === 'text' ? 'minlength' : 'min', argument);
        if (name === 'max') input.setAttribute(input.type === 'text' ? 'maxlength' : 'max', argument);
        if (name === 'gt' && argument === '0') input.min = input.step || '0.01';
        if (['lt', 'gte'].includes(name) && argument.startsWith('serving_options.*.')) {
            const otherName = argument.split('.').pop();
            const other = row.querySelector(`[data-serving-input="${otherName}"]`);
            if (input.value !== '' && other && other.value !== '') {
                if (name === 'lt' && Number(input.value) >= Number(other.value)) input.setCustomValidity('Discount price must be lower than price.');
                if (name === 'gte' && Number(input.value) < Number(other.value)) input.setCustomValidity('Maximum guests must be at least the minimum guests.');
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('food-item-image')?.addEventListener('change', event => {
        const files = Array.from(event.target.files || []);
        let message = null;
        const existingCount = Number(event.target.dataset.existingCount || 0);
        if (existingCount + files.length > 10) {
            message = `You already have ${existingCount} images and can add ${Math.max(0, 10 - existingCount)} more. Maximum 10 images in total.`;
        } else if (files.some(file => !/\.(jpe?g|png|webp)$/i.test(file.name))) {
            message = 'Only JPG, JPEG, PNG, and WebP images are allowed.';
        } else if (files.some(file => file.size > 2 * 1024 * 1024)) {
            message = 'Each food image must be 2 MB or smaller.';
        }
        if (message) {
            event.target.value = '';
            if (typeof showToast === 'function') {
                showToast(message, 'error', 'Upload Error', 5000);
            } else {
                appAlert(message, { tone: 'error', title: 'Upload error' });
            }
        }
    });
    const container = document.getElementById('serving-options');
    const config = JSON.parse(container.dataset.servingConfig);
    const tax = document.getElementById('food-item-tax');
    const description = document.getElementById('food-item-description');
    const addButton = document.getElementById('add-serving-option');
    let nextIndex = Math.max(-1, ...Array.from(container.querySelectorAll('[name]')).map(input => Number(input.name.match(/^serving_options\[(\d+)\]/)?.[1] ?? -1))) + 1;

    function refreshOptions() {
        const rows = container.querySelectorAll('[data-serving-option]');
        addButton.disabled = rows.length >= config.collection.max_items;
        rows.forEach((row, position) => {
            const serveType = row.querySelector('[data-serve-type]').value;
            const type = config.serve_types[serveType];
            const unit = row.querySelector('[data-serving-input="unit"]');
            updateFoodServingUnit(unit, serveType, type?.allowed_units || [], type?.defaults.unit);
            row.querySelectorAll('[data-serving-field]').forEach(group => {
                const name = group.dataset.servingField;
                const definition = config.fields[name];
                const visible = type?.enabled_fields.includes(name) || false;
                const required = visible && type.required_fields.includes(name);
                group.hidden = !visible;
                group.querySelectorAll('input, select').forEach(input => {
                    input.disabled = !visible;
                    input.required = required && input.type !== 'hidden' && input.type !== 'checkbox';
                    input.setCustomValidity('');
                });
                const marker = group.querySelector('[data-required-marker]');
                if (marker) marker.hidden = !required;
                if (!visible) group.querySelectorAll('[data-validation-error]').forEach(error => error.remove());
                const caption = group.querySelector('[data-serving-caption]');
                const unitDefinition = name === 'quantity' ? config.unit_fields[unit.value] : null;
                if (caption) caption.textContent = unitDefinition?.quantity_label || type?.field_labels[name] || definition.label;
                const help = group.querySelector('[data-field-help]');
                if (help) help.textContent = type?.field_help[name] || definition.help;
                const input = group.querySelector('[data-serving-input]');
                if (visible && input) {
                    const rules = unitDefinition?.quantity_validation || type.validation_overrides[name] || definition.validation;
                    applyFoodServingValidation(input, rules, row);
                    if (unitDefinition?.quantity_step) input.step = String(unitDefinition.quantity_step);
                }
            });
            row.querySelector('[data-serve-help]').textContent = type?.help || 'Choose a serve type.';
            row.querySelector('[data-serving-heading]').textContent = `Serving option ${position + 1}`;
            row.querySelector('[data-remove-serving]').disabled = rows.length <= config.collection.min_items;
            row.querySelector('[data-remove-serving]').style.opacity = rows.length <= config.collection.min_items ? '0.4' : '1';
            const priceField = row.querySelector('[name$="[price]"]');
            const price = Number(priceField.value);
            const discount = row.querySelector('[name$="[discount_price]"]');
            const effective = discount.value === '' ? price : Number(discount.value);
            const rate = Number(tax.value);
            const summary = row.querySelector('[data-serving-total]');
            if (priceField.value.trim() === '') {
                summary.textContent = 'Enter a price to preview the tax and total.';
            } else if (Number.isFinite(effective) && effective >= 0 && (discount.value === '' || effective < price) && Number.isFinite(rate) && rate >= 0 && rate <= 100) {
                const taxAmount = Math.round((effective * rate / 100 + Number.EPSILON) * 100) / 100;
                summary.textContent = `Tax (${rate}%): ${taxAmount.toFixed(2)} · Price including tax: ${(effective + taxAmount).toFixed(2)}${type?.pricing_basis === 'per_person' ? ' per person' : ''}`;
            } else {
                summary.textContent = '';
            }
        });
    }

    addButton.addEventListener('click', () => {
        if (container.querySelectorAll('[data-serving-option]').length >= config.collection.max_items) return;
        const html = document.getElementById('serving-option-template').innerHTML.replaceAll('__INDEX__', String(nextIndex++));
        container.insertAdjacentHTML('beforeend', html);
        refreshOptions();
        container.lastElementChild.querySelector('input').focus();
    });
    container.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-serving]');
        if (button && container.querySelectorAll('[data-serving-option]').length > config.collection.min_items) {
            button.closest('[data-serving-option]').remove();
            refreshOptions();
        }
    });
    container.addEventListener('input', refreshOptions);
    container.addEventListener('change', refreshOptions);
    tax.addEventListener('input', refreshOptions);
    const updateCount = () => document.getElementById('food-description-count').textContent = description.value.length;
    description.addEventListener('input', updateCount);
    updateCount();
    refreshOptions();
});
</script>
