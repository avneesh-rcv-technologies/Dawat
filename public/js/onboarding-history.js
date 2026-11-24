(() => {
    'use strict';
    document.querySelectorAll('.history-compact').forEach(dialog => {
        const search = dialog.querySelector('[data-history-search]');
        const status = dialog.querySelector('[data-history-status]');
        const sort = dialog.querySelector('[data-history-sort]');
        const list = dialog.querySelector('.history-list');
        const entries = Array.from(dialog.querySelectorAll('[data-history-entry]'));
        const count = dialog.querySelector('[data-history-count]');
        const noResults = dialog.querySelector('[data-history-no-results]');
        const body = dialog.querySelector('.history-body');
        const filter = () => {
            const query = search.value.trim().toLocaleLowerCase();
            let visible = 0;
            entries.sort((a, b) => sort.value === 'oldest'
                ? Number(a.dataset.time) - Number(b.dataset.time)
                : Number(b.dataset.time) - Number(a.dataset.time));
            entries.forEach(entry => {
                entry.hidden = (status.value !== '' && entry.dataset.status !== status.value)
                    || !entry.textContent.toLocaleLowerCase().includes(query);
                if (!entry.hidden) visible++;
                list.appendChild(entry);
            });
            count.textContent = `${visible} of ${entries.length} ${entries.length === 1 ? 'activity' : 'activities'}`;
            noResults.hidden = visible > 0 || entries.length === 0;
            body.scrollTop = 0;
        };
        search.addEventListener('input', filter);
        status.addEventListener('change', filter);
        sort.addEventListener('change', filter);
        dialog.querySelector('[data-history-reset]').addEventListener('click', () => {
            search.value = '';
            status.value = '';
            sort.value = 'newest';
            filter();
            search.focus();
        });
        filter();
    });
})();
