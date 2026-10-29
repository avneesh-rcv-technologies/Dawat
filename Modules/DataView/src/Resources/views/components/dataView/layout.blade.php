<div class="pb-1" style="min-width: 0; max-width: 100%;">
    <!-- @if($title)
    @endif -->
    <!-- Top Controls -->
    <x-topbar :data="$data" />

    <div class="flex flex-col" style="min-width: 0; max-width: 100%;">
        <!-- Main Content -->
        <div class="flex-1" style="min-width: 0; max-width: 100%;">
            <x-table :data="$data" />
            <x-pagination :data="$data['meta']" />
        </div>
    </div>

    <x-sidebar :data="$data" />
    <!-- Overlay -->
    <div id="filterOverlay" aria-hidden="true" class="fixed inset-0 z-40 bg-slate-950/45 backdrop-blur-[1px] hidden"></div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterToggle = document.getElementById('filterToggle');
        const filterSidebar = document.getElementById('filterSidebar');
        const filterOverlay = document.getElementById('filterOverlay');
        const closeFilter = document.getElementById('closeFilter');
        let filterOpener = null;

        // Function to open sidebar
        function openSidebar() {
            if (!filterSidebar || !filterOverlay) return;
            filterOpener = document.activeElement;
            filterSidebar.classList.remove('translate-x-full');
            filterSidebar.setAttribute('aria-hidden', 'false');
            filterSidebar.inert = false;
            filterOverlay.classList.remove('hidden');
            filterOverlay.setAttribute('aria-hidden', 'false');
            filterToggle?.setAttribute('aria-expanded', 'true');
            document.body.classList.add('overflow-hidden');
            closeFilter?.focus();
        }

        // Function to close sidebar
        function closeSidebar() {
            if (!filterSidebar || !filterOverlay) return;
            filterSidebar.classList.add('translate-x-full');
            filterSidebar.setAttribute('aria-hidden', 'true');
            filterSidebar.inert = true;
            filterOverlay.classList.add('hidden');
            filterOverlay.setAttribute('aria-hidden', 'true');
            filterToggle?.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('overflow-hidden');
            filterOpener?.focus?.();
        }

        // Toggle sidebar
        filterToggle?.addEventListener('click', openSidebar);

        // Close sidebar
        closeFilter?.addEventListener('click', closeSidebar);

        // Close when clicking overlay
        filterOverlay?.addEventListener('click', closeSidebar);

        // Close when pressing Escape key
        document.addEventListener('keydown', function (e) {
            const isOpen = filterSidebar && !filterSidebar.classList.contains('translate-x-full');
            if (e.key === 'Escape' && isOpen) {
                closeSidebar();
                return;
            }
            if (e.key === 'Tab' && isOpen) {
                const focusable = Array.from(filterSidebar.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled])'));
                if (!focusable.length) return;
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        });

        const dateRanges = document.querySelectorAll('#filterForm [data-date-range]');
        dateRanges.forEach(range => {
            const from = range.querySelector('[data-date-from]');
            const to = range.querySelector('[data-date-to]');
            const validateRange = () => {
                to.setCustomValidity(from.value && to.value && from.value > to.value
                    ? 'To date must be on or after From date.' : '');
            };
            from.addEventListener('input', validateRange);
            to.addEventListener('input', validateRange);
            validateRange();
        });

        // Replace sidebar filters while preserving search, sorting, and page size.
        document.getElementById('filterForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!this.reportValidity()) return;

            const params = new URLSearchParams(window.location.search);
            for (const key of [...params.keys()]) {
                if (key.startsWith('filters[') && !key.startsWith('filters[all]')) {
                    params.delete(key);
                }
            }
            for (const [key, value] of new FormData(this)) {
                if (typeof value === 'string' && value.trim() !== '') {
                    params.append(key, value.trim());
                }
            }
            params.set('pagination[page]', '1');
            window.location.href = this.action + '?' + params.toString();
        });

        // The table is rendered after the top controls.
        updateMassActionsVisibility();
        document.querySelectorAll('.row-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                // Desktop and mobile layouts contain a checkbox for the same row.
                document.querySelectorAll('.row-checkbox').forEach(other => {
                    if (other.value === checkbox.value) other.checked = checkbox.checked;
                });
                updateMassActionsVisibility();
            });
        });
    });
</script>
