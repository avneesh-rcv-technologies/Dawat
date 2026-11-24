<script>
    (function() {
        const addBtn = document.getElementById('add-faq-btn');
        if (addBtn) {
            addBtn.addEventListener('click', function() {
                const container = document.getElementById('faq-entries');
                const index = document.querySelectorAll('.faq-entry').length;
                const entry = document.createElement('div');
                entry.className = 'faq-entry relative rounded-xl border border-gray-200 bg-gradient-to-b from-gray-50/80 to-white p-5 space-y-4';
                entry.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gray-900 text-xs font-bold text-white faq-number">${index + 1}</span>
                        <span class="text-sm font-semibold text-gray-800">Question</span>
                    </div>
                    <button type="button" onclick="this.closest('.faq-entry').remove(); renumberFaqs();"
                        class="remove-faq-btn text-xs font-medium text-gray-400 hover:text-red-500 transition">Remove</button>
                </div>
                <div>
                    <label class="custom-label">Question</label>
                    <input type="text" name="faqs[${index}][question]" class="input-field" required>
                </div>
                <div>
                    <label class="custom-label">Answer</label>
                    <textarea name="faqs[${index}][answer]" rows="3" class="input-field" required></textarea>
                </div>
                <div class="max-w-[140px]">
                    <label class="custom-label">Sort Order</label>
                    <input type="number" name="faqs[${index}][sort_order]" value="${index}" min="0" class="input-field">
                </div>
            `;
                container.appendChild(entry);
                renumberFaqs();
            });
        }

        window.renumberFaqs = function() {
            const entries = document.querySelectorAll('.faq-entry');
            entries.forEach(function(entry, i) {
                const number = entry.querySelector('.faq-number');
                if (number) number.textContent = String(i + 1);

                entry.querySelectorAll('input, textarea').forEach(el => {
                    if (el.name && el.name.includes('faqs[')) {
                        el.name = el.name.replace(/faqs\[\d+\]/, `faqs[${i}]`);
                    }
                });

                const removeBtn = entry.querySelector('.remove-faq-btn');
                if (removeBtn) {
                    if (entries.length === 1) {
                        removeBtn.classList.add('hidden');
                    } else {
                        removeBtn.classList.remove('hidden');
                    }
                }
            });

            const badge = document.getElementById('faq-count-badge');
            if (badge) {
                const count = entries.length;
                badge.textContent = count + (count === 1 ? ' question' : ' questions');
            }
        };

        renumberFaqs();
    })();
</script>
