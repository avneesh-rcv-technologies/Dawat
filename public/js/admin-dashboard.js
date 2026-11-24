(() => {
    const form = document.querySelector('[data-dashboard-filters]');
    if (form) {
        const period = form.querySelector('[data-dashboard-period]');
        const dates = [...form.querySelectorAll('.ad-custom-date')];
        const start = form.elements.namedItem('start_date');
        const end = form.elements.namedItem('end_date');
        const sync = () => {
            const custom = period.value === 'custom';
            dates.forEach(label => {
                label.hidden = !custom;
                label.querySelector('input').disabled = !custom;
            });
            end.min = start.value;
        };
        period.addEventListener('change', sync);
        start.addEventListener('change', () => { end.min = start.value; });
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            button.textContent = 'Updating…';
            form.setAttribute('aria-busy', 'true');
        });
        window.addEventListener('pageshow', () => {
            const button = form.querySelector('button[type="submit"]');
            button.disabled = false;
            button.textContent = 'Apply filters';
            form.removeAttribute('aria-busy');
            sync();
        });
        sync();
    }

    document.querySelectorAll('[data-dashboard-chart]').forEach(chart => {
        let config;
        try { config = JSON.parse(chart.dataset.dashboardChart); } catch { return; }
        if (!config.points?.length || !config.series?.length) return;
        const svg = chart.querySelector('svg');
        const canvas = chart.querySelector('.ad-chart-canvas');
        const tooltip = chart.querySelector('.ad-chart-tooltip');
        const tooltipDate = chart.querySelector('[data-chart-tooltip-date]');
        const tooltipValues = chart.querySelector('[data-chart-tooltip-values]');
        const buttons = [...chart.querySelectorAll('[data-chart-series]')];
        const line = chart.querySelector('[data-chart-line]');
        const area = chart.querySelector('[data-chart-area]');
        const guide = chart.querySelector('[data-chart-guide]');
        const dot = chart.querySelector('[data-chart-dot]');
        let active = config.series[0];
        let coordinates = [];
        let current = config.points.length - 1;
        let pinned = false;
        let viewWidth = 744, right = 710;
        const exactNumber = new Intl.NumberFormat('en-US', { minimumFractionDigits: config.currency?.decimals ?? 0, maximumFractionDigits: config.currency?.decimals ?? 0 });
        const exact = value => (config.currency ? `${config.currency.symbol} ` : '') + exactNumber.format(Number(value));
        const compact = value => {
            let scaled = Math.abs(value), unit = 0;
            const units = ['', 'K', 'M', 'B', 'T', 'Q', 'Qi'];
            while (scaled >= 1000 && unit < units.length - 1) { scaled /= 1000; unit++; }
            let rounded = unit ? Math.round(scaled * 10) / 10 : scaled;
            if (rounded >= 1000 && unit < units.length - 1) { rounded /= 1000; unit++; }
            return (value < 0 ? '-' : '') + new Intl.NumberFormat('en-US', { maximumFractionDigits: unit ? 1 : (config.currency?.decimals ?? 0) }).format(rounded) + units[unit];
        };
        const hide = () => {
            tooltip.hidden = true;
            guide.setAttribute('visibility', 'hidden');
            dot.setAttribute('visibility', 'hidden');
        };
        const show = (index, keyboard = false) => {
            current = Math.max(0, Math.min(config.points.length - 1, index));
            const point = config.points[current], position = coordinates[current];
            tooltipDate.textContent = point.label;
            tooltipValues.replaceChildren();
            config.series.forEach(series => {
                const row = document.createElement('div');
                const label = document.createElement('span');
                const marker = document.createElement('i');
                marker.style.background = series.color;
                label.append(marker, document.createTextNode(series.label));
                const value = document.createElement('strong');
                value.textContent = exact(point[series.key] ?? 0);
                row.append(label, value);
                tooltipValues.append(row);
            });
            tooltip.setAttribute('aria-live', keyboard ? 'polite' : 'off');
            tooltip.hidden = false;
            const width = canvas.clientWidth, height = canvas.clientHeight;
            const pixelX = position[0] / viewWidth * width;
            const pixelY = position[1] / 255 * height;
            tooltip.style.left = `${Math.max(8, Math.min(width - tooltip.offsetWidth - 8, pixelX - tooltip.offsetWidth / 2))}px`;
            tooltip.style.top = `${Math.max(6, Math.min(height - tooltip.offsetHeight - 6, pixelY - tooltip.offsetHeight - 15))}px`;
            guide.setAttribute('x1', position[0]); guide.setAttribute('x2', position[0]);
            dot.setAttribute('cx', position[0]); dot.setAttribute('cy', position[1]);
            guide.setAttribute('visibility', 'visible'); dot.setAttribute('visibility', 'visible');
            if (keyboard) svg.setAttribute('aria-label', `${point.label}. ${active.label}: ${exact(point[active.key] ?? 0)}. Use arrow keys to explore.`);
        };
        const draw = key => {
            active = config.series.find(series => series.key === key) ?? config.series[0];
            const values = config.points.map(point => Number(point[active.key] ?? 0));
            let low = Math.min(0, ...values), high = Math.max(0, ...values);
            if (low === 0 && high === 0) high = 1;
            if (config.currency) {
                const magnitude = Math.max(Math.abs(low), Math.abs(high), .04);
                const step = 10 ** Math.floor(Math.log10(magnitude));
                low = Math.floor(low / step) * step;
                high = Math.max(step, Math.ceil(high / step) * step);
            } else { low = 0; high = Math.max(4, Math.ceil(high / 4) * 4); }
            const y = value => 213 - (value - low) / (high - low) * 166;
            viewWidth = Math.max(280, canvas.clientWidth); right = viewWidth - 20;
            svg.setAttribute('viewBox', `0 0 ${viewWidth} 255`);
            chart.querySelectorAll('[data-chart-grid]').forEach(grid => grid.setAttribute('x2', right));
            coordinates = values.map((value, index) => [52 + (values.length > 1 ? index / (values.length - 1) * (right - 52) : (right - 52) / 2), y(value)]);
            const labelStep = Math.max(1, Math.ceil(values.length / Math.max(2, Math.floor(viewWidth / 110))));
            chart.querySelectorAll('[data-chart-x-index]').forEach(label => {
                const index = Number(label.dataset.chartXIndex);
                label.setAttribute('x', coordinates[index][0]);
                label.style.display = index === 0 || index === values.length - 1 || (index % labelStep === 0 && index < values.length - Math.ceil(labelStep / 2)) ? '' : 'none';
            });
            const path = coordinates.map((point, index) => `${index ? 'L' : 'M'}${point[0]} ${point[1]}`).join(' ');
            line.setAttribute('d', path); line.setAttribute('stroke', active.color);
            area.setAttribute('d', `M${coordinates[0][0]} ${y(0)} ${path.replace(/^M/, 'L')} L${coordinates.at(-1)[0]} ${y(0)} Z`);
            dot.setAttribute('fill', active.color);
            chart.querySelectorAll('[data-chart-gradient]').forEach(stop => stop.setAttribute('stop-color', active.color));
            chart.querySelectorAll('[data-chart-y-label]').forEach((label, index) => { label.textContent = compact(low + (high - low) * index / 4); });
            chart.querySelector('[data-chart-total]').textContent = active.short;
            chart.querySelector('[data-chart-total]').title = active.full;
            chart.querySelector('[data-chart-caption]').textContent = active.label;
            chart.querySelector('[data-chart-empty]').hidden = values.some(value => value !== 0);
            buttons.forEach(button => {
                const selected = button.dataset.chartSeries === active.key;
                button.setAttribute('aria-pressed', String(selected)); button.classList.toggle('is-active', selected);
            });
            hide();
        };
        const inspect = event => {
            const bounds = svg.getBoundingClientRect();
            const x = (event.clientX - bounds.left) / bounds.width * viewWidth;
            show(Math.round((x - 52) / (right - 52) * (config.points.length - 1)));
        };
        buttons.forEach(button => button.addEventListener('click', () => draw(button.dataset.chartSeries)));
        svg.addEventListener('pointermove', inspect);
        svg.addEventListener('pointerdown', event => { pinned = event.pointerType === 'touch'; inspect(event); });
        svg.addEventListener('pointerleave', () => { if (!pinned && document.activeElement !== svg) hide(); });
        svg.addEventListener('focus', () => show(current, true));
        svg.addEventListener('blur', () => { pinned = false; hide(); });
        document.addEventListener('pointerdown', event => { if (!chart.contains(event.target)) { pinned = false; hide(); } });
        svg.addEventListener('keydown', event => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End', 'Escape'].includes(event.key)) return;
            event.preventDefault();
            if (event.key === 'Escape') { hide(); return; }
            show(event.key === 'Home' ? 0 : event.key === 'End' ? config.points.length - 1 : current + (event.key === 'ArrowRight' ? 1 : -1), true);
        });
        draw(active.key);
        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(() => draw(active.key)).observe(canvas);
        } else { window.addEventListener('resize', () => draw(active.key)); }
    });
    document.querySelectorAll('.ad-status-chart-layout').forEach(chart => {
        const slices = [...chart.querySelectorAll('[data-status-code]')];
        const labels = [...chart.querySelectorAll('[data-status-label]')];
        const highlight = code => {
            const exists = slices.some(slice => slice.dataset.statusCode === code);
            slices.forEach(slice => {
                slice.style.opacity = exists && slice.dataset.statusCode !== code ? '.25' : '1';
                slice.setAttribute('stroke-width', exists && slice.dataset.statusCode === code ? '30' : '26');
            });
            labels.forEach(label => label.classList.toggle('is-highlighted', exists && label.dataset.statusLabel === code));
        };
        [...slices, ...labels].forEach(element => {
            const code = element.dataset.statusCode ?? element.dataset.statusLabel;
            element.addEventListener('pointerenter', () => highlight(code));
            element.addEventListener('pointerleave', () => highlight(null));
            element.addEventListener('focus', () => highlight(code));
            element.addEventListener('blur', () => highlight(null));
        });
    });
})();
