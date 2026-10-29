/**
 * Payment module JavaScript functions
 */

// Initialize payment configuration loading
function loadTemplate() {
    const processorSelect = document.querySelector('select[name="processor"]');
    const configContainer = document.getElementById('payment_configuration');
    
    if (!processorSelect || !configContainer) {
        return;
    }

    // Load configuration on processor change
    processorSelect.addEventListener('change', function() {
        const processorCode = this.value;
        
        if (!processorCode) {
            configContainer.innerHTML = '';
            return;
        }

        // Show loading state
        configContainer.innerHTML = '<div class="text-center py-4">Loading configuration...</div>';

        // Make AJAX request to load configuration form
        fetch(`${window.location.origin}/admin/payment_methods/config/${processorCode}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.payment_configuration) {
                configContainer.innerHTML = data.payment_configuration;
            } else {
                configContainer.innerHTML = '<div class="text-red-500 py-4">Error loading configuration: ' + (data.errors || 'Unknown error') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error loading configuration:', error);
            configContainer.innerHTML = '<div class="text-red-500 py-4">Error loading configuration. Please try again.</div>';
        });
    });

    // Trigger change event if processor is already selected (for edit mode)
    if (processorSelect.value) {
        processorSelect.dispatchEvent(new Event('change'));
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a page that needs payment configuration loading
    if (document.querySelector('[data-init="loadTemplate"]')) {
        loadTemplate();
    }
});

// Make function globally available for data-init attribute
window.loadTemplate = loadTemplate;