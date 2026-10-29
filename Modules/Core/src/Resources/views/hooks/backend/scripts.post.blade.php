   {{-- notification toggle --}}
   <script>
       // Toggle notification dropdown
       const notificationButton = document.querySelector('.relative button[aria-label="Notifications"]');
       const notificationDropdown = document.querySelector('.relative .hidden');

       if (notificationButton && notificationDropdown) {
           notificationButton.addEventListener('click', (e) => {
               e.stopPropagation();
               notificationDropdown.classList.toggle('hidden');
           });

           // Close when clicking outside
           document.addEventListener('click', (e) => {
               if (!notificationButton.contains(e.target) && !notificationDropdown.contains(e.target)) {
                   notificationDropdown.classList.add('hidden');
               }
           });
       }

       // User dropdown menu
       const userMenuButton = document.getElementById('userMenuButton');
       const userMenu = document.getElementById('userMenu');

       if (userMenuButton && userMenu) {
           userMenuButton.addEventListener('click', (e) => {
               e.stopPropagation();
               userMenu.classList.toggle('hidden');
           });

           document.addEventListener('click', (e) => {
               if (!userMenuButton.contains(e.target) && !userMenu.contains(e.target)) {
                   userMenu.classList.add('hidden');
               }
           });
       }

       // Close dropdowns when pressing Escape
       document.addEventListener('keydown', (e) => {
           if (e.key === 'Escape') {
               notificationDropdown?.classList.add('hidden');
               userMenu?.classList.add('hidden');
           }
       });

       // Sidebar toggle for mobile
       document.addEventListener('DOMContentLoaded', () => {
           const sidebarToggle = document.getElementById('mobileSidebarToggle');
           const sidebar = document.getElementById('sidebar');

           if (sidebarToggle && sidebar) {
               sidebarToggle.addEventListener('click', () => {
                   sidebar.classList.toggle('-translate-x-full');
                   const isExpanded = !sidebar.classList.contains('-translate-x-full');
                   sidebarToggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
               });
           }
       });
   </script>

   <script>
       @if(session('success'))
        showToast(@json(session('success')), 'success', 'Success');
       @elseif(session('error'))
        showToast(@json(session('error')), 'error', 'Error');
       @elseif(session('info'))
        showToast(@json(session('info')), 'info', 'Information');
       @elseif(session('warning'))
        showToast(@json(session('warning')), 'warning', 'Warning');
       @endif
   </script>

   {{-- toast modal throgh sessions --}}
   <script>
       window.addEventListener('DOMContentLoaded', () => {
           const toast = sessionStorage.getItem('toastMessage');
           if (toast) {
               const {
                   message,
                   type,
                   title
               } = JSON.parse(toast);
               showToast(message, type, title); // Your existing toast function
               sessionStorage.removeItem('toastMessage'); // Clear after showing
           }
       });
   </script>
   {{-- delete modal --}}
   <script>
       function openDeleteModal(actionUrl) {
           const modal = document.getElementById('global-delete-modal');
           const form = document.getElementById('global-delete-form');

           form.setAttribute('action', actionUrl);
           modal.classList.remove('hidden');
       }

       function closeDeleteModal() {
           const modal = document.getElementById('global-delete-modal');
           const form = document.getElementById('global-delete-form');

           form.setAttribute('action', '');
           modal.classList.add('hidden');
       }
   </script>

   {{-- Status modal --}}
   <script>
       function openStatusModal(actionUrl) {
           const modal = document.getElementById('status-modal');
           const form = document.getElementById('status-form');

           form.setAttribute('action', actionUrl);
           modal.classList.remove('hidden');
       }

       function closeStatusModal() {
           const modal = document.getElementById('status-modal');
           const form = document.getElementById('status-form');

           form.setAttribute('action', '');
           modal.classList.add('hidden');
       }

       document.addEventListener('DOMContentLoaded', function() {
           const cancelBtn = document.getElementById('status-cancel-btn');
           if (cancelBtn) {
               cancelBtn.addEventListener('click', closeStatusModal);
           }
       });
   </script>
