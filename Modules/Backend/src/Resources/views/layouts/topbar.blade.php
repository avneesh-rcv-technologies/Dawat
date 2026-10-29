<header tabindex="0" class="sticky top-0 z-40 border-b border-white/10 bg-neutral-950 text-white shadow-sm">
    <div class="mx-auto max-w-full px-3 sm:px-5 lg:px-7">
        <div class="flex h-16 items-center justify-between">
            <!-- Left: Skip link + Brand + Mobile menu -->
                
            <div class="flex items-center gap-3">
                <!-- Mobile hamburger for small screens -->
                <button id="mobileSidebarToggle" type="button" aria-controls="sidebar" aria-expanded="false"
                    aria-label="Open navigation menu" class="ml-1 inline-flex h-10 w-10 items-center justify-center rounded-lg text-neutral-300 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                        aria-hidden="true">
                        <path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>


            <!-- Right: actions -->
            <div class="flex items-center justify-end gap-2 sm:gap-3">
                <div class="flex items-center gap-2 sm:gap-3">
                    @hook('backend:topbar_items.pre')

                    <div class="h-7 border-l border-white/15" aria-hidden="true"></div>

                    <div class="flex items-center gap-2">
                        @include('backend::components.common.top-profile')
                    </div>
                    @hook('backend:topbar_items.post')
                </div>

            </div>
        </div>
    </div>


</header>
