{{-- Delivery Shipping Settings --}}
<div class="shipping-settings delivery-settings">
    <div class="settings-section">
        <h3 class="section-title">Delivery Settings</h3>
        
        <div class="form-row">
            <div class="form-field checkbox-field">
                <label class="checkbox-label">
                    <input type="checkbox" id="is_enabled" name="configuration[is_enabled]" value="1" 
                           {{ ($configuration['is_enabled'] ?? true) ? 'checked' : '' }}>
                    <span class="checkmark"></span>
                    Enable Delivery Service
                </label>
                <span class="field-description">Allow customers to choose delivery option</span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label class="field-label">Base Delivery Fee ($) {{ $configuration['base_delivery_fee'] ?? '5.00' }}</label>
                <span class="field-description">Standard delivery charge for all orders</span>
                <input type="number" class="form-input" id="base_delivery_fee" 
                       name="configuration[base_delivery_fee]" 
                       value="{{ $configuration['base_delivery_fee'] ?? 5.00 }}" 
                       step="0.01" min="0" placeholder="5.00">
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label class="field-label">Minimum Order Amount ($) {{ $configuration['minimum_order'] ?? '10.00' }}</label>
                <span class="field-description">Minimum order value required for delivery</span>
                <input type="number" class="form-input" id="minimum_order" 
                       name="configuration[minimum_order]" 
                       value="{{ $configuration['minimum_order'] ?? 10.00 }}" 
                       step="0.01" min="0" placeholder="10.00">
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label class="field-label">Free Delivery Threshold ($) {{ $configuration['free_delivery_threshold'] ?? '' }}</label>
                <span class="field-description">Orders above this amount get free delivery</span>
                <input type="number" class="form-input" id="free_delivery_threshold" 
                       name="configuration[free_delivery_threshold]" 
                       value="{{ $configuration['free_delivery_threshold'] ?? '' }}" 
                       step="0.01" min="0" placeholder="Optional">
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label class="field-label">Rate per KM ($) {{ $configuration['per_km_rate'] ?? '1.00' }}</label>
                <span class="field-description">Additional charge per kilometer</span>
                <input type="number" class="form-input" id="per_km_rate" 
                       name="configuration[per_km_rate]" 
                       value="{{ $configuration['per_km_rate'] ?? 1.00 }}" 
                       step="0.01" min="0" placeholder="1.00">
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label class="field-label">Delivery Radius (km) {{ $configuration['delivery_radius'] ?? '10' }}</label>
                <span class="field-description">Maximum delivery distance</span>
                <input type="number" class="form-input" id="delivery_radius" 
                       name="configuration[delivery_radius]" 
                       value="{{ $configuration['delivery_radius'] ?? 10 }}" 
                       min="1" placeholder="10">
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label class="field-label">Preparation Time (minutes) {{ $configuration['preparation_time'] ?? '20' }}</label>
                <span class="field-description">Time needed to prepare the order</span>
                <input type="number" class="form-input" id="preparation_time" 
                       name="configuration[preparation_time]" 
                       value="{{ $configuration['preparation_time'] ?? 20 }}" 
                       min="1" placeholder="20">
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label class="field-label">Buffer Time (minutes) {{ $configuration['buffer_time'] ?? '10' }}</label>
                <span class="field-description">Extra time buffer for order preparation</span>
                <input type="number" class="form-input" id="buffer_time" 
                       name="configuration[buffer_time]" 
                       value="{{ $configuration['buffer_time'] ?? 10 }}" 
                       min="0" placeholder="10">
            </div>
        </div>

        <div class="form-section">
            <h4 class="subsection-title">Delivery Hours</h4>
            <div class="form-row">
                <div class="form-field">
                    <label class="field-label">Opening Time {{ $configuration['delivery_hours']['start'] ?? '10:00' }}</label>
                    <input type="time" class="form-input" id="delivery_hours_start" 
                           name="configuration[delivery_hours][start]" 
                           value="{{ $configuration['delivery_hours']['start'] ?? '10:00' }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label class="field-label">Closing Time {{ $configuration['delivery_hours']['end'] ?? '23:00' }}</label>
                    <input type="time" class="form-input" id="delivery_hours_end" 
                           name="configuration[delivery_hours][end]" 
                           value="{{ $configuration['delivery_hours']['end'] ?? '23:00' }}">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h4 class="subsection-title">Store Location</h4>
            <div class="form-row">
                <div class="form-field">
                    <label class="field-label">Latitude {{ $configuration['store_coordinates']['lat'] ?? '' }}</label>
                    <span class="field-description">Your store's latitude coordinate</span>
                    <input type="number" class="form-input" id="store_coordinates_lat" 
                           name="configuration[store_coordinates][lat]" 
                           value="{{ $configuration['store_coordinates']['lat'] ?? '' }}" 
                           step="any" placeholder="e.g., 40.7128">
                </div>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label class="field-label">Longitude {{ $configuration['store_coordinates']['lng'] ?? '' }}</label>
                    <span class="field-description">Your store's longitude coordinate</span>
                    <input type="number" class="form-input" id="store_coordinates_lng" 
                           name="configuration[store_coordinates][lng]" 
                           value="{{ $configuration['store_coordinates']['lng'] ?? '' }}" 
                           step="any" placeholder="e.g., -74.0060">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h4 class="subsection-title">Delivery Zones</h4>
            <div id="delivery-zones" class="delivery-zones-container">
                @if(isset($configuration['delivery_zones']) && is_array($configuration['delivery_zones']))
                    @foreach($configuration['delivery_zones'] as $index => $zone)
                        <div class="delivery-zone-item" data-index="{{ $index }}">
                            <div class="zone-header">
                                <span class="zone-title">Zone {{ $index + 1 }}</span>
                                <button type="button" class="remove-zone-btn remove-zone">×</button>
                            </div>
                            <div class="zone-fields">
                                <div class="form-row">
                                    <div class="form-field">
                                        <label class="field-label">Zone Name</label>
                                        <input type="text" class="form-input" 
                                               name="configuration[delivery_zones][{{ $index }}][name]" 
                                               value="{{ $zone['name'] ?? '' }}" 
                                               placeholder="e.g., Downtown">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-field">
                                        <label class="field-label">Postcodes</label>
                                        <span class="field-description">Comma separated</span>
                                        <input type="text" class="form-input" 
                                               name="configuration[delivery_zones][{{ $index }}][postcodes]" 
                                               value="{{ $zone['postcodes'] ?? '' }}" 
                                               placeholder="12345, 12346">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-field">
                                        <label class="field-label">Delivery Fee ($)</label>
                                        <input type="number" class="form-input" 
                                               name="configuration[delivery_zones][{{ $index }}][delivery_fee]" 
                                               value="{{ $zone['delivery_fee'] ?? '' }}" 
                                               step="0.01" placeholder="5.00">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-field">
                                        <label class="field-label">Delivery Time (minutes)</label>
                                        <input type="number" class="form-input" 
                                               name="configuration[delivery_zones][{{ $index }}][delivery_time]" 
                                               value="{{ $zone['delivery_time'] ?? '' }}" 
                                               placeholder="30">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <button type="button" id="add-delivery-zone" class="add-zone-btn">+ Add Delivery Zone</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let zoneIndex = {{ count($configuration['delivery_zones'] ?? []) }};
    
    // Add new delivery zone
    document.getElementById('add-delivery-zone').addEventListener('click', function() {
        console.log('Add Delivery Zone button clicked');
        const zonesContainer = document.getElementById('delivery-zones');
        const zoneHtml = `
            <div class="delivery-zone-item" data-index="${zoneIndex}">
                <div class="zone-header">
                    <span class="zone-title">Zone ${zoneIndex + 1}</span>
                    <button type="button" class="remove-zone-btn remove-zone">×</button>
                </div>
                <div class="zone-fields">
                    <div class="form-row">
                        <div class="form-field">
                            <label class="field-label">Zone Name</label>
                            <input type="text" class="form-input" 
                                   name="configuration[delivery_zones][${zoneIndex}][name]" 
                                   placeholder="e.g., Downtown">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label class="field-label">Postcodes</label>
                            <span class="field-description">Comma separated</span>
                            <input type="text" class="form-input" 
                                   name="configuration[delivery_zones][${zoneIndex}][postcodes]" 
                                   placeholder="12345, 12346">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label class="field-label">Delivery Fee ($)</label>
                            <input type="number" class="form-input" 
                                   name="configuration[delivery_zones][${zoneIndex}][delivery_fee]" 
                                   step="0.01" placeholder="5.00">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label class="field-label">Delivery Time (minutes)</label>
                            <input type="number" class="form-input" 
                                   name="configuration[delivery_zones][${zoneIndex}][delivery_time]" 
                                   placeholder="30">
                        </div>
                    </div>
                </div>
            </div>
        `;
        zonesContainer.insertAdjacentHTML('beforeend', zoneHtml);
        zoneIndex++;
        
        // Log for debugging
        console.log('Zone added, new index:', zoneIndex);
    });
    
    // Remove delivery zone
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-zone') || e.target.closest('.remove-zone')) {
            const zoneItem = e.target.closest('.delivery-zone-item');
            if (zoneItem) {
                zoneItem.remove();
                console.log('Zone removed');
            }
        }
    });
    
    // Toggle delivery settings based on enable/disable
    const enableCheckbox = document.getElementById('is_enabled');
    const settingsContainer = document.querySelector('.delivery-settings');
    
    function toggleSettings() {
        const isEnabled = enableCheckbox.checked;
        const formFields = settingsContainer.querySelectorAll('.form-field:not(.checkbox-field)');
        
        formFields.forEach(field => {
            field.style.opacity = isEnabled ? '1' : '0.5';
            const inputs = field.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.disabled = !isEnabled;
            });
        });
    }
    
    if (enableCheckbox) {
        enableCheckbox.addEventListener('change', toggleSettings);
        toggleSettings(); // Initial state
    }
    
    // Debug: Check if button exists
    const addButton = document.getElementById('add-delivery-zone');
    console.log('Add button found:', addButton ? 'Yes' : 'No');
    console.log('Initial zone index:', zoneIndex);
});
</script>

<style>
.shipping-settings {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.settings-section {
    padding: 24px;
}

.section-title {
    font-size: 20px;
    font-weight: 600;
    color: #333;
    margin: 0 0 24px 0;
    padding-bottom: 12px;
    border-bottom: 2px solid #e5e7eb;
}

.subsection-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin: 32px 0 16px 0;
}

.form-section {
    margin-top: 32px;
}

.form-row {
    margin-bottom: 20px;
}

.form-field {
    position: relative;
}

.field-label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
    line-height: 1.4;
}

.field-description {
    display: block;
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 8px;
    line-height: 1.3;
}

.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 6px;
    font-size: 14px;
    color: #374151;
    background: #fff;
    transition: border-color 0.2s ease;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input:disabled {
    background: #f9fafb;
    color: #9ca3af;
    cursor: not-allowed;
}

.checkbox-field {
    margin-bottom: 24px;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    line-height: 1.4;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-right: 12px;
    margin-top: 1px;
    accent-color: #3b82f6;
}

.delivery-zones-container {
    margin-top: 16px;
}

.delivery-zone-item {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: 16px;
    background: #f9fafb;
}

.zone-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: #f3f4f6;
    border-bottom: 1px solid #e5e7eb;
}

.zone-title {
    font-weight: 600;
    color: #374151;
    font-size: 14px;
}

.remove-zone-btn {
    background: #ef4444;
    color: white;
    border: none;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s ease;
}

.remove-zone-btn:hover {
    background: #dc2626;
}

.zone-fields {
    padding: 16px;
}

.add-zone-btn {
    background: #10b981;
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.add-zone-btn:hover {
    background: #059669;
}

.form-field:disabled,
.form-field[disabled] {
    opacity: 0.5;
}
</style>

