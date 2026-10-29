 <div class="w-full px-3 lg:pr-6 flex justify-center lg:justify-start" id="global-search-container">

     <div class="relative w-full max-w-md">

         <!-- Module filter & Search buttons -->
         <div class="absolute inset-y-0 left-0 flex items-center pl-2 pr-1">
             <!-- Module filter dropdown -->
             <button
                 id="global-search-module-btn"
                 type="button"
                 class="flex items-center justify-center rounded-lg transition bg-dark"
                 aria-label="Filter by module"
                 title="Filter by module">
                <span id="global-search-module-badge" class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-medium bg-[var(--color-black)] text-white max-w-[10rem] truncate">
                    All
                </span>
             </button>

         </div>

         <!-- Search Input -->
         <input
             id="global-search-input"
             autocomplete="off"
            class="block w-full pl-4 pr-10 py-2 rounded-lg bg-[var(--color-input-bg)] 
                     border border-[var(--color-border-gray)] focus:ring-1 
                     focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)]
                     text-[var(--color-text-gray)] placeholder-[var(--color-text-gray)] 
                     transition-all duration-200 outline-none"
             placeholder="Search (press '/' to focus)"
             aria-label="Global search" />


         <!-- Module filter dropdown menu -->
         <div
             id="global-search-module-menu"
             class="hidden absolute top-full left-0 mt-2 bg-white rounded-lg shadow-lg border border-gray-200 z-50 min-w-max"
             role="menu">
             <div id="global-search-module-list" class="py-1">
                 <!-- Filled by JS -->
             </div>
         </div>

         <!-- Shortcut hint -->
         <div
             id="global-search-shortcuts"
             class="absolute inset-y-0 right-0 flex items-center pr-3 
                   text-xs text-gray-400 pointer-events-none">
             /
         </div>

         <!-- Overlay (blur removed) -->
         <div
             id="global-search-overlay"
             class="hidden fixed inset-0 bg-black/40 z-40"></div>

         <!-- Results Panel -->
         <div
             id="global-search-panel"
             class="hidden absolute left-0 right-0 mt-2 bg-white rounded-xl shadow-xl 
                   border border-gray-100 z-50 max-h-[60vh] overflow-auto w-full"
             role="dialog"
             aria-modal="true"
             aria-label="Global search results">
             <div id="global-search-results" class="py-2"></div>
         </div>

     </div>
 </div>