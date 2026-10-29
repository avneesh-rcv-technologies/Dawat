(() => {
    function initSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar || sidebar.dataset.initialized === 'true') return;
        sidebar.dataset.initialized = 'true';
        const desktop = window.matchMedia('(min-width: 1024px)');
        const collapseButton = document.getElementById('super-sidebar-collapse-button');
        const collapseIcon = document.getElementById('super-sidebar-collapse-icon');
        const collapseLabel = document.getElementById('sidebar-collapse-label');
        const mobileButton = document.getElementById('mobileSidebarToggle');
        const mobileClose = document.getElementById('sidebar-mobile-close');
        const overlay = document.getElementById('sidebar-mobile-overlay');
        const navigation = document.getElementById('sidebarMenu');
        const mainLayout = document.getElementById('backend-main-layout');
        let collapsed = false;
        let activeGroup = null;
        let hideTimer;
        let overlayPressed = false;
        try { collapsed = localStorage.getItem('adminSidebarCollapsed') === 'true'; } catch {}

        const groups = [...sidebar.querySelectorAll('.sidebar-parent-group')].map(element => {
            const button = element.querySelector('.sidebar-toggle-btn');
            const submenu = document.getElementById(button.dataset.target);
            const popover = document.getElementById(`${submenu.id}-popover`);
            // Fixed flyouts belong to the viewport, outside the scrolling navigation.
            document.body.appendChild(popover);
            return { element, button, submenu, popover, expanded: !submenu.hidden };
        });

        function isCollapsed() { return desktop.matches && collapsed; }

        function closePopover(restoreFocus = false) {
            clearTimeout(hideTimer);
            if (!activeGroup) return;
            const group = activeGroup;
            activeGroup = null;
            group.popover.hidden = true;
            group.button.setAttribute('aria-expanded', String(!isCollapsed() && group.expanded));
            if (restoreFocus) group.button.focus();
        }

        function positionPopover(group) {
            const anchor = group.button.getBoundingClientRect();
            const box = group.popover.getBoundingClientRect();
            const left = Math.min(anchor.right + 10, window.innerWidth - box.width - 12);
            const top = Math.min(anchor.top, window.innerHeight - box.height - 12);
            group.popover.style.left = `${Math.max(12, left)}px`;
            group.popover.style.top = `${Math.max(12, top)}px`;
        }

        function showPopover(group, focusFirst = false) {
            if (!isCollapsed()) return;
            clearTimeout(hideTimer);
            if (activeGroup !== group) closePopover();
            activeGroup = group;
            group.popover.hidden = false;
            group.button.setAttribute('aria-expanded', 'true');
            positionPopover(group);
            if (focusFirst) group.popover.querySelector('a')?.focus();
        }

        function scheduleClose(group) {
            clearTimeout(hideTimer);
            hideTimer = setTimeout(() => {
                if (activeGroup === group && !group.popover.contains(document.activeElement)) closePopover();
            }, 180);
        }

        function syncSidebar() {
            closePopover();
            sidebar.classList.toggle('collapsed', isCollapsed());
            const label = isCollapsed() ? 'Expand sidebar' : 'Collapse sidebar';
            collapseIcon.textContent = isCollapsed() ? 'last_page' : 'first_page';
            collapseLabel.textContent = label;
            collapseButton.title = label;
            collapseButton.setAttribute('aria-label', label);
            collapseButton.setAttribute('aria-expanded', String(!isCollapsed()));
            groups.forEach(group => {
                group.submenu.hidden = !group.expanded;
                group.button.setAttribute('aria-controls', isCollapsed() ? group.popover.id : group.submenu.id);
                group.button.setAttribute('aria-expanded', String(!isCollapsed() && group.expanded));
            });
        }

        groups.forEach(group => {
            group.button.addEventListener('click', () => {
                if (isCollapsed()) {
                    showPopover(group, true);
                } else {
                    group.expanded = !group.expanded;
                    group.submenu.hidden = !group.expanded;
                    group.button.setAttribute('aria-expanded', String(group.expanded));
                }
            });
            group.button.addEventListener('keydown', event => {
                if (event.key === 'ArrowDown' && isCollapsed()) {
                    event.preventDefault();
                    showPopover(group, true);
                }
            });
            group.element.addEventListener('mouseenter', () => showPopover(group));
            group.element.addEventListener('mouseleave', () => scheduleClose(group));
            group.popover.addEventListener('mouseenter', () => clearTimeout(hideTimer));
            group.popover.addEventListener('mouseleave', () => scheduleClose(group));
            [group.element, group.popover].forEach(element => element.addEventListener('focusout', event => {
                if (!group.element.contains(event.relatedTarget) && !group.popover.contains(event.relatedTarget)) scheduleClose(group);
            }));
        });

        collapseButton.addEventListener('click', () => {
            collapsed = !collapsed;
            syncSidebar();
            try { localStorage.setItem('adminSidebarCollapsed', String(collapsed)); } catch {}
        });

        function setMobileOpen(open, restoreFocus = true) {
            open = open && !desktop.matches;
            const wasOpen = sidebar.classList.contains('is-mobile-open');
            sidebar.classList.toggle('is-mobile-open', open);
            overlay.hidden = !open;
            mobileButton?.setAttribute('aria-expanded', String(open));
            mobileButton?.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
            if (open) {
                sidebar.inert = false;
                mobileClose.focus({ preventScroll: true });
                if (mainLayout) mainLayout.inert = true;
            } else {
                if (mainLayout) mainLayout.inert = false;
                if (wasOpen && restoreFocus) mobileButton?.focus({ preventScroll: true });
                sidebar.inert = !desktop.matches;
            }
        }

        function openMobileMenu(event) {
            if (desktop.matches) return;
            event.preventDefault();
            event.stopPropagation();
            setMobileOpen(true);
        }
        // Touch browsers can consume the first synthesized click to establish hover.
        // Opening is idempotent, so the following click cannot toggle the drawer shut.
        mobileButton?.addEventListener('pointerup', event => {
            if (event.pointerType === 'touch' || event.pointerType === 'pen') openMobileMenu(event);
        });
        mobileButton?.addEventListener('click', openMobileMenu);
        mobileClose.addEventListener('click', () => setMobileOpen(false));
        // The compatibility click following an opening tap can land on the new overlay.
        // Dismiss only when a separate press actually starts on the overlay.
        overlay.addEventListener('pointerdown', () => { overlayPressed = true; });
        overlay.addEventListener('pointercancel', () => { overlayPressed = false; });
        overlay.addEventListener('click', event => {
            if (overlayPressed || event.detail === 0) setMobileOpen(false);
            overlayPressed = false;
        });
        navigation.addEventListener('scroll', () => closePopover());
        window.addEventListener('resize', () => { if (activeGroup) positionPopover(activeGroup); });
        desktop.addEventListener('change', () => { setMobileOpen(false, false); syncSidebar(); });
        document.addEventListener('click', event => {
            if (activeGroup && !activeGroup.element.contains(event.target) && !activeGroup.popover.contains(event.target)) closePopover();
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                if (activeGroup) { event.preventDefault(); closePopover(true); }
                if (sidebar.classList.contains('is-mobile-open')) setMobileOpen(false);
            }
            if (event.key === 'Tab' && sidebar.classList.contains('is-mobile-open')) {
                const focusable = [...sidebar.querySelectorAll('a[href], button')].filter(element => element.getClientRects().length);
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
                else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
            }
        });
        syncSidebar();
        setMobileOpen(false, false);

        // Keep the existing account dropdown available after replacing the sidebar script.
        const userButton = document.getElementById('user-menu-toggle');
        const userDropdown = document.getElementById('user-dropdown');
        if (userButton && userDropdown) {
            function closeUserMenu() {
                userDropdown.classList.add('hidden');
                userButton.setAttribute('aria-expanded', 'false');
            }
            closeUserMenu();
            userButton.addEventListener('click', () => {
                const hidden = userDropdown.classList.toggle('hidden');
                userButton.setAttribute('aria-expanded', String(!hidden));
            });
            document.addEventListener('click', event => {
                if (!userButton.contains(event.target) && !userDropdown.contains(event.target)) closeUserMenu();
            });
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && !userDropdown.classList.contains('hidden')) { closeUserMenu(); userButton.focus(); }
            });
        }
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initSidebar);
    else initSidebar();
})();
