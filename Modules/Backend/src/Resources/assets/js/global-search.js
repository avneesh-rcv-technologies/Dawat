/* global-search.js
   Vanilla JS Shopware-like global search
   - Debounced AJAX to `/api/admin/search`
   - Overlay and centered panel with grouped results
   - Keyboard navigation (arrows, enter, esc), focusing via `/` shortcut
   - Highlighted match snippets
*/

(function () {
    'use strict';

    const API_URL = '/api/search';
    const DEBOUNCE_MS = 220;

    // Available modules for filtering (will be populated from server preferences)
    let MODULES = { 'all': 'All' };

    function $(sel, ctx = document) { return ctx.querySelector(sel); }
    function $all(sel, ctx = document) { return Array.from(ctx.querySelectorAll(sel)); }

    function debounce(fn, wait = DEBOUNCE_MS) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    function escapeHtml(str = '') {
        return String(str).replace(/[&<>"']/g, function (m) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m];
        });
    }

    function highlight(text = '', q = '') {
        if (!q) return escapeHtml(text);
        const re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'ig');
        return escapeHtml(text).replace(re, '<mark class="bg-yellow-100 text-yellow-900 rounded">$1</mark>');
    }

    function buildResultsHTML(results, q) {
        if (!results || results.length === 0) return '<div class="px-4 py-3 text-sm text-gray-500">No results</div>';

        // group by module_name
        const grouped = results.reduce((acc, r) => {
            const key = r.module_name || r.module || 'Other';
            acc[key] = acc[key] || [];
            acc[key].push(r);
            return acc;
        }, {});

        let html = '';
        Object.keys(grouped).forEach(moduleName => {
            html += `<div class="px-4 py-2 border-b border-gray-100"><div class="font-semibold text-sm text-gray-700">${escapeHtml(moduleName)}</div></div>`;
            grouped[moduleName].forEach(item => {
                const title = highlight(item.title || item.name || '', q);
                const subtitle = escapeHtml(item.module_name || item.module || '');
                html += `<a href="${escapeHtml(item.url || '#')}" data-url="${escapeHtml(item.url || '')}" class="block px-4 py-3 hover:bg-gray-50 result-item" data-id="${escapeHtml(String(item.id || ''))}" data-type="${escapeHtml(item.type || '')}">
                    <div class="flex items-center">
                        <span class="material-icons-outlined text-gray-400 mr-3 text-base">${escapeHtml(item.type || 'search')}</span>
                        <div>
                            <div class="font-medium text-sm leading-tight">${title}</div>
                            <div class="text-xs text-gray-500">${subtitle}</div>
                        </div>
                    </div>
                </a>`;
            });
        });

        return html;
    }

    function openPanel(panel, overlay) {
        panel.classList.remove('hidden');
        overlay.classList.remove('hidden');
        panel.classList.add('block');
        overlay.classList.add('block');
    }

    function closePanel(panel, overlay) {
        panel.classList.add('hidden');
        overlay.classList.add('hidden');
        panel.classList.remove('block');
        overlay.classList.remove('block');
    }

    function setupKeyboardNavigation(panel) {
        let current = -1;

        function items() { return Array.from(panel.querySelectorAll('.result-item')); }

        function update() {
            const list = items();
            list.forEach((el, idx) => el.classList.toggle('bg-gray-100', idx === current));
            if (current >= 0 && list[current]) {
                const el = list[current];
                // ensure visible
                el.scrollIntoView({ block: 'nearest' });
            }
        }

        function move(delta) {
            const list = items();
            if (!list.length) return;
            current = (current + delta + list.length) % list.length;
            update();
        }

        return {
            onKey(e) {
                if (e.key === 'ArrowDown') { e.preventDefault(); move(1); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); move(-1); }
                else if (e.key === 'Enter') {
                    const list = items();
                    if (current >= 0 && list[current]) {
                        const url = list[current].getAttribute('data-url');
                        if (url) window.location.href = url;
                    }
                } else if (e.key === 'Escape') {
                    // handled elsewhere
                }
            },
            reset() { current = -1; update(); }
        };
    }

    function renderModuleMenu(container, selectedModule, onSelect) {
        container.innerHTML = '';
        Object.entries(MODULES).forEach(([key, name]) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition';
            if (key === selectedModule) {
                btn.classList.add('bg-blue-50', 'text-blue-700', 'font-semibold');
            }
            btn.textContent = name;
            btn.addEventListener('click', () => {
                onSelect(key);
            });
            container.appendChild(btn);
        });
    }

    function init() {
        const input = $('#global-search-input');
        const panel = $('#global-search-panel');
        const results = $('#global-search-results');
        const overlay = $('#global-search-overlay');
        const openBtn = $('#global-search-open');
        const moduleBtn = $('#global-search-module-btn');
        const moduleMenu = $('#global-search-module-menu');
        const moduleList = $('#global-search-module-list');

        if (!input || !panel || !results || !overlay) return;

        let latestQuery = '';
        let selectedModule = 'all';
        const nav = setupKeyboardNavigation(results);

        // Fetch preferences from server and render module filter list
        (async () => {
            try {
                const prefRes = await axios.get(API_URL, { params: { query: '' } });
                const prefs = (prefRes.data && prefRes.data.preferences) ? prefRes.data.preferences : {};
                MODULES = Object.assign({ 'all': 'All' }, prefs);
            } catch (err) {
                // fallback: keep default MODULES
                console.warn('Could not load search preferences, using defaults', err);
            }

            const moduleBadge = $('#global-search-module-badge');
            function updateInputPadding() {
                try {
                    if (!moduleBadge) return;
                    const badgeRect = moduleBadge.getBoundingClientRect();
                    // add some spacing (8-12px) between badge and text
                    const pad = Math.round(badgeRect.width + 12);
                    const inp = $('#global-search-input');
                    if (inp) inp.style.paddingLeft = pad + 'px';
                } catch (err) {
                    // noop
                }
            }

            if (moduleBadge) moduleBadge.textContent = MODULES[selectedModule] || MODULES['all'] || 'All';
            // initial padding adjustment
            setTimeout(updateInputPadding, 0);

            renderModuleMenu(moduleList, selectedModule, (key) => {
                selectedModule = key;
                moduleMenu.classList.add('hidden');
                if (moduleBadge) moduleBadge.textContent = MODULES[key] || key;
                // update padding after badge text change
                setTimeout(updateInputPadding, 0);
                input.focus();
                // Trigger search if there's text
                if (input.value.trim().length >= 2) {
                    doSearch(input.value.trim(), selectedModule);
                }
            });
                // recompute padding on window resize
                window.addEventListener('resize', () => {
                    try { updateInputPadding(); } catch (e) { /* noop */ }
                });
        })();

        // Module filter toggle
        moduleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            moduleMenu.classList.toggle('hidden');
        });

        // Close module menu on outside click
        document.addEventListener('click', (e) => {
            if (!moduleMenu.contains(e.target) && e.target !== moduleBtn) {
                moduleMenu.classList.add('hidden');
            }
        });

        const doSearch = debounce(async (q, module = 'all') => {
            if (!q || q.length < 2) {
                results.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500">Type at least 2 characters</div>';
                nav.reset();
                return;
            }

            latestQuery = q;
            results.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500">Searching…</div>';

            try {
                const res = await axios.get(API_URL, { params: { query: q, module: module } });
                const arr = (res.data && Array.isArray(res.data.results)) ? res.data.results : [];
                // only render if query unchanged
                if (latestQuery === q) {
                    results.innerHTML = buildResultsHTML(arr, q);
                    nav.reset();
                }
            } catch (err) {
                console.error('Global search error', err);
                results.innerHTML = '<div class="px-4 py-3 text-sm text-red-500">Search failed</div>';
            }
        }, DEBOUNCE_MS);

        // focus shortcut '/'
        document.addEventListener('keydown', (e) => {
            const active = document.activeElement;
            if (e.key === '/' && active && (active.tagName !== 'INPUT' && active.tagName !== 'TEXTAREA')) {
                e.preventDefault();
                input.focus();
            }
        });

        input.addEventListener('input', (e) => {
            const v = e.target.value.trim();
            // Only show results panel when user typed at least 2 chars
            if (v.length < 2) {
                results.innerHTML = '';
                closePanel(panel, overlay);
                nav.reset();
                return;
            }

            // Show panel and perform search
            openPanel(panel, overlay);
            doSearch(v, selectedModule);
        });

        // Only open panel on focus if the input already contains a searchable value
        input.addEventListener('focus', () => {
            const v = input.value.trim();
            if (v.length >= 2) {
                openPanel(panel, overlay);
            }
        });
        openBtn && openBtn.addEventListener('click', () => input.focus());

        overlay.addEventListener('click', () => { closePanel(panel, overlay); input.blur(); });

        document.addEventListener('keydown', (e) => {
            if (panel.classList.contains('hidden')) return;
            if (e.key === 'Escape') { closePanel(panel, overlay); input.blur(); }
            else nav.onKey(e);
        });

        // delegate click on result links
        results.addEventListener('click', (e) => {
            const a = e.target.closest('.result-item');
            if (!a) return;
            const url = a.getAttribute('data-url');
            if (url) window.location.href = url;
        });
    }

    document.addEventListener('DOMContentLoaded', init);

})();
