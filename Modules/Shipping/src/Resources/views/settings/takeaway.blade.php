{{-- Takeaway Shipping Settings --}}
<div class="shipping-settings takeaway-settings">
    <div class="settings-section">
        <h3 class="section-title">Takeaway Settings</h3>
        
        <div class="form-row">
            <div class="form-field checkbox-field">
                <label class="checkbox-label">
                    <input type="checkbox" id="is_enabled" name="configuration[is_enabled]" value="1" 
                           {{ ($configuration['is_enabled'] ?? true) ? 'checked' : '' }}>
                    <span class="checkmark"></span>
                    Enable Takeaway
                </label>
                <span class="field-description">Allow customers to choose pickup option</span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label class="field-label">Takeaway Fee ($) {{ $configuration['takeaway_fee'] ?? '0' }}</label>
                <span class="field-description">(usually 0)</span>
                <input type="number" class="form-input" id="takeaway_fee" 
                       name="configuration[takeaway_fee]" 
                       value="{{ $configuration['takeaway_fee'] ?? 0 }}" 
                       step="0.01" min="0" placeholder="0.00">
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label class="field-label">Preparation Time (minutes) {{ $configuration['preparation_time'] ?? '15' }}</label>
                <span class="field-description">Time needed to prepare the order</span>
                <input type="number" class="form-input" id="preparation_time" 
                       name="configuration[preparation_time]" 
                       value="{{ $configuration['preparation_time'] ?? 15 }}" 
                       min="1" placeholder="15">
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label class="field-label">Buffer Time (minutes) {{ $configuration['buffer_time'] ?? '5' }}</label>
                <span class="field-description">Extra time buffer for order preparation</span>
                <input type="number" class="form-input" id="buffer_time" 
                       name="configuration[buffer_time]" 
                       value="{{ $configuration['buffer_time'] ?? 5 }}" 
                       min="0" placeholder="5">
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label class="field-label">Pickup Location Address</label>
                <span class="field-description">Full address where customers can pickup their orders</span>
                <textarea class="form-input" id="pickup_location" 
                          name="configuration[pickup_location]" 
                          rows="3" required 
                          placeholder="Enter your store address">{{ $configuration['pickup_location'] ?? '' }}</textarea>
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label class="field-label">Pickup Instructions</label>
                <span class="field-description">Special instructions for customers picking up orders</span>
                <textarea class="form-input" id="pickup_instructions" 
                          name="configuration[pickup_instructions]" 
                          rows="3" 
                          placeholder="e.g., Please call when you arrive, Use side entrance">{{ $configuration['pickup_instructions'] ?? '' }}</textarea>
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label class="field-label">Contact Phone Number {{ $configuration['contact_phone'] ?? 'e.g., +1 (555) 123-4567' }}</label>
                <span class="field-description">Phone number customers can call for pickup inquiries</span>
                <input type="text" class="form-input" id="contact_phone" 
                       name="configuration[contact_phone]" 
                       value="{{ $configuration['contact_phone'] ?? '' }}" 
                       placeholder="e.g., +1 (555) 123-4567">
            </div>
        </div>

        <div class="form-section">
            <h4 class="subsection-title">Operating Hours</h4>
            <div class="form-row">
                <div class="form-field">
                    <label class="field-label">Opening Time {{ $configuration['operating_hours']['open'] ?? '09:00' }}</label>
                    <input type="time" class="form-input" id="operating_hours_open" 
                           name="configuration[operating_hours][open]" 
                           value="{{ $configuration['operating_hours']['open'] ?? '09:00' }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label class="field-label">Closing Time {{ $configuration['operating_hours']['close'] ?? '22:00' }}</label>
                    <input type="time" class="form-input" id="operating_hours_close" 
                           name="configuration[operating_hours][close]" 
                           value="{{ $configuration['operating_hours']['close'] ?? '22:00' }}">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle takeaway settings based on enable/disable
    const enableCheckbox = document.getElementById('is_enabled');
    const settingsContainer = document.querySelector('.takeaway-settings');
    
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
    
    enableCheckbox.addEventListener('change', toggleSettings);
    toggleSettings(); // Initial state
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
    resize: vertical;
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

.form-field:disabled,
.form-field[disabled] {
    opacity: 0.5;
}
</style>