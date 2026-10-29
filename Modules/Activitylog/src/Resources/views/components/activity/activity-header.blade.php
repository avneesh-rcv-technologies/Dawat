@props(['stats'])

<div class="activity-log-hero rounded-lg shadow-sm p-6 bg-[var(--card-background)] border border-1">
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-bold mb-2" style="color: var(--foreground);">System Activity</h1>
            <p class="mb-4" style="color: var(--muted-foreground);">Monitor and track all system activities across entities with advanced insights</p>
            <div class="flex items-center space-x-6 text-sm text-gray-500">
                <div class="flex items-center" style="color: var(--muted-foreground);">
                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ $stats['total'] ?? 0 }} Total Activities
                </div>
                <div class="flex items-center" style="color: var(--muted-foreground);">
                    <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    Real-time Monitoring
                </div>
            </div>
        </div>
        <div class="flex space-x-3">
             <!-- Export CSV Button -->
            <button
                id="export-csv-btn-agency"
                class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-yellow-500 border border-gray-300 rounded-md hover:bg-yellow-700 hover:text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                <svg id="export-icon" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <svg id="export-loading" class="w-4 h-4 mr-2 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span id="export-text">Export CSV</span>
            </button>
           
        </div>
    </div>
</div>

@section('scripts')
<script>
    class CsvExporter {
    constructor() {
        this.exportBtn = document.getElementById('export-csv-btn-agency');
        this.exportIcon = document.getElementById('export-icon');
        this.exportLoading = document.getElementById('export-loading');
        this.exportText = document.getElementById('export-text');
        this.exportUrl = '{{ route("activity-log.agency-export-csv") }}';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        this.init();
    }

    init() {
        this.exportBtn.addEventListener('click', () => {
            this.exportCsv();
        });
    }

    async exportCsv() {
        try {
            this.setLoadingState(true);

            // Get current filters from URL and staff filter
            const urlParams = new URLSearchParams(window.location.search);
            const exportUrl = new URL(this.exportUrl);

            // Add all current filters to export URL
            for (const [key, value] of urlParams.entries()) {
                if (key !== '_t') { // Exclude timestamp parameter
                    exportUrl.searchParams.set(key, value);
                }
            }

            // Add staff filter if selected
            const staffFilter = window.staffFilterInstance;
            if (staffFilter && staffFilter.selectedStaffId) {
                exportUrl.searchParams.set('staff_id', staffFilter.selectedStaffId);
            }

            console.log('Exporting CSV with URL:', exportUrl.toString());

            // Create a temporary link and trigger download
            const link = document.createElement('a');
            link.href = exportUrl.toString();
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Show success message
            this.showSuccessMessage();

        } catch (error) {
            console.error('Export error:', error);
            this.showErrorMessage('Failed to export CSV file. Please try again.');
        } finally {
            // Reset button state after a delay
            setTimeout(() => {
                this.setLoadingState(false);
            }, 2000);
        }
    }

    setLoadingState(isLoading) {
        if (isLoading) {
            this.exportBtn.disabled = true;
            this.exportIcon.classList.add('hidden');
            this.exportLoading.classList.remove('hidden');
            this.exportText.textContent = 'Exporting...';
            this.exportBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            this.exportBtn.disabled = false;
            this.exportIcon.classList.remove('hidden');
            this.exportLoading.classList.add('hidden');
            this.exportText.textContent = 'Export CSV';
            this.exportBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    showSuccessMessage() {
        // Create and show success toast
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center';
        toast.innerHTML = `
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span>CSV file downloaded successfully!</span>
        `;

        document.body.appendChild(toast);

        // Remove toast after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3000);
    }

    showErrorMessage(message) {
        // Create and show error toast
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center';
        toast.innerHTML = `
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <span>${message}</span>
        `;

        document.body.appendChild(toast);

        // Remove toast after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 5000);
    }
}

    // Initialize CSV exporter
    new CsvExporter();
</script>
@endsection
