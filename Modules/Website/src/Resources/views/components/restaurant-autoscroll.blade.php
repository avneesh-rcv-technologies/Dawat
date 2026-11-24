<script>
    (() => {
        const scroller = document.getElementById('featured-restaurants');
        if (!scroller) return;

        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        const track = scroller.firstElementChild;
        const cards = Array.from(track.children);
        if (!cards.length) return;

        // Repeat the row so wrapping the scroll position has no visible seam.
        const copies = cards.map((card) => {
            const copy = card.cloneNode(true);
            copy.setAttribute('aria-hidden', 'true');
            copy.inert = true;
            [copy, ...copy.querySelectorAll('*')].forEach((element) => {
                element.removeAttribute('id');
                Array.from(element.attributes).forEach((attribute) => {
                    if (attribute.name.startsWith('data-aos')) element.removeAttribute(attribute.name);
                });
            });
            track.appendChild(copy);
            return copy;
        });

        let cycleWidth = 0;
        const updateLoop = () => {
            const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
            const rowWidth = cards.reduce((width, card) => width + card.offsetWidth, 0)
                + gap * (cards.length - 1);
            const overflowing = rowWidth > scroller.clientWidth;
            copies.forEach((copy) => { copy.style.display = overflowing ? '' : 'none'; });
            cycleWidth = overflowing ? copies[0].offsetLeft - cards[0].offsetLeft : 0;
        };
        updateLoop();
        new ResizeObserver(updateLoop).observe(scroller);

        let position = scroller.scrollLeft;
        let previousTime = null;
        let hovered = false;
        let dragging = false;
        let resumeAt = 0;

        scroller.addEventListener('pointerenter', (event) => {
            if (event.pointerType === 'mouse') hovered = true;
        });
        scroller.addEventListener('pointerleave', () => { hovered = false; });
        scroller.addEventListener('pointerdown', () => { dragging = true; });
        const endDrag = () => {
            if (!dragging) return;
            dragging = false;
            resumeAt = performance.now() + 2000;
        };
        window.addEventListener('pointerup', endDrag);
        window.addEventListener('pointercancel', endDrag);
        scroller.addEventListener('wheel', () => {
            resumeAt = performance.now() + 2000;
        }, { passive: true });

        const animate = (time) => {
            const elapsed = previousTime === null ? 0 : Math.min(time - previousTime, 50);
            previousTime = time;
            const paused = reducedMotion.matches || document.hidden || hovered || dragging
                || scroller.matches(':focus-within') || time < resumeAt;

            if (paused || cycleWidth <= 0) {
                position = scroller.scrollLeft;
            } else {
                // Travel at 35 pixels per second, always in the same direction.
                position = (position + elapsed * 0.035) % cycleWidth;
                scroller.scrollLeft = position;
            }

            requestAnimationFrame(animate);
        };

        requestAnimationFrame(animate);
    })();
</script>
