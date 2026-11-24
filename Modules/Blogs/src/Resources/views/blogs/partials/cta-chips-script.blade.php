{{-- CTA chip helpers for TinyMCE long_description (Workflow-style tokens) --}}
<script>
(function () {
    const editorId = 'long-description-editor';
    const form = document.getElementById('blog-form');
    const textarea = document.getElementById(editorId);
    if (!textarea) return;

    const TOKEN_REGEX = /\{\{\s*([a-z0-9][a-z0-9\-_]*)\s*\}\}/gi;
    let applyingChips = false;
    let boundEditorId = null;

    const escapeHtml = (value) => {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    };

    const tokenFromSlug = (slug) => '{' + '{' + slug + '}' + '}';

    const labelForToken = (token) => {
        const match = String(token).match(/^\{\{\s*([a-z0-9][a-z0-9\-_]*)\s*\}\}$/i);
        const slug = match ? match[1] : '';
        if (!slug) return token;
        const btn = document.querySelector('[data-insert-cta="' + slug + '"]');
        return (btn && (btn.getAttribute('data-cta-label') || btn.querySelector('[data-cta-name]')?.textContent)) || token;
    };

    const createCtaChipHtml = (token, label) => {
        const display = label || labelForToken(token) || token;
        return '<span class="blog-cta-chip mceNonEditable" contenteditable="false" data-mce-contenteditable="false" data-token="' + escapeHtml(token) + '"'
            + ' style="display:inline-flex;align-items:center;gap:6px;margin:4px 0;padding:6px 12px;border-radius:9999px;'
            + 'background:#f5f3ff;border:1px solid #c4b5fd;color:#5b21b6;font-size:13px;font-weight:600;'
            + 'white-space:nowrap;cursor:default;user-select:none;vertical-align:middle;">'
            + '<span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;'
            + 'border-radius:9999px;background:#7c3aed;color:#fff;font-size:10px;font-weight:700;">CTA</span>'
            + escapeHtml(display)
            + '</span>';
    };

    const normalizeStoredCtaMarkup = (value) => {
        if (!value) return '';
        const parser = new DOMParser();
        const doc = parser.parseFromString(value, 'text/html');
        doc.querySelectorAll('.blog-cta-chip').forEach((chip) => {
            const token = chip.getAttribute('data-token') || chip.textContent || '';
            chip.replaceWith(doc.createTextNode(token));
        });
        return doc.body.innerHTML;
    };

    const replaceTokensInTextNodes = (root) => {
        if (!root) return;
        const localRegex = /\{\{\s*([a-z0-9][a-z0-9\-_]*)\s*\}\}/gi;

        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
            acceptNode(node) {
                if (!node.nodeValue || !node.nodeValue.trim()) return NodeFilter.FILTER_REJECT;
                const parent = node.parentElement;
                if (!parent || parent.closest('a, .blog-cta-chip, code, pre')) return NodeFilter.FILTER_REJECT;
                localRegex.lastIndex = 0;
                if (!localRegex.test(node.nodeValue)) return NodeFilter.FILTER_REJECT;
                return NodeFilter.FILTER_ACCEPT;
            }
        });

        const textNodes = [];
        while (walker.nextNode()) textNodes.push(walker.currentNode);

        textNodes.forEach((node) => {
            const content = node.nodeValue;
            localRegex.lastIndex = 0;
            if (!localRegex.test(content)) return;
            localRegex.lastIndex = 0;

            const html = content.replace(localRegex, (full, slug) => {
                const token = tokenFromSlug(slug);
                return createCtaChipHtml(token, labelForToken(token));
            });

            const wrapper = document.createElement('span');
            wrapper.innerHTML = html;
            const fragment = document.createDocumentFragment();
            while (wrapper.firstChild) fragment.appendChild(wrapper.firstChild);
            node.parentNode.replaceChild(fragment, node);
        });
    };

    const renderCtaChips = (value) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(normalizeStoredCtaMarkup(value || ''), 'text/html');
        replaceTokensInTextNodes(doc.body);
        return doc.body.innerHTML;
    };

    const serializeCtaChips = (html) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html || '', 'text/html');
        doc.querySelectorAll('.blog-cta-chip').forEach((chip) => {
            const token = chip.getAttribute('data-token') || chip.textContent || '';
            chip.replaceWith(doc.createTextNode(token));
        });
        return doc.body.innerHTML;
    };

    const getEditor = () => (typeof tinymce !== 'undefined' ? tinymce.get(editorId) : null);

    const syncSerializedToTextarea = () => {
        const editor = getEditor();
        if (editor) {
            textarea.value = serializeCtaChips(editor.getContent());
            return;
        }
        textarea.value = serializeCtaChips(textarea.value);
    };

    const applyChipsToEditor = () => {
        const editor = getEditor();
        if (!editor || !editor.initialized) return false;

        applyingChips = true;
        try {
            const source = textarea.value || editor.getContent() || '';
            editor.setContent(renderCtaChips(source));
            textarea.value = serializeCtaChips(editor.getContent());
        } finally {
            window.setTimeout(() => { applyingChips = false; }, 0);
        }

        return true;
    };

    const bindEditorEvents = (editor) => {
        if (!editor || boundEditorId === editor.id) return;
        boundEditorId = editor.id;

        editor.on('change keyup NodeChange', () => {
            if (applyingChips) return;
            window.clearTimeout(editor._blogCtaSyncTimer);
            editor._blogCtaSyncTimer = window.setTimeout(() => {
                if (applyingChips) return;
                textarea.value = serializeCtaChips(editor.getContent());
            }, 50);
        });
    };

    const attachToEditor = () => {
        const editor = getEditor();
        if (!editor) return false;

        const run = () => {
            bindEditorEvents(editor);
            applyChipsToEditor();
            // TinyMCE init handlers may overwrite content — re-apply shortly after
            window.setTimeout(() => applyChipsToEditor(), 50);
            window.setTimeout(() => applyChipsToEditor(), 250);
        };

        if (editor.initialized) {
            run();
            return true;
        }

        editor.on('init', run);
        return true;
    };

    // Insert chip from sidebar
    document.querySelectorAll('[data-insert-cta]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const slug = btn.getAttribute('data-insert-cta');
            if (!slug) return;
            const token = tokenFromSlug(slug);
            const label = btn.getAttribute('data-cta-label') || labelForToken(token);
            const chipHtml = createCtaChipHtml(token, label);
            const editor = getEditor();

            if (editor) {
                editor.focus();
                applyingChips = true;
                editor.insertContent('<p>' + chipHtml + '</p>');
                applyingChips = false;
                textarea.value = serializeCtaChips(editor.getContent());
                return;
            }

            const start = textarea.selectionStart || 0;
            const end = textarea.selectionEnd || 0;
            const value = textarea.value || '';
            textarea.value = value.slice(0, start) + token + value.slice(end);
            textarea.focus();
        });
    });

    // Serialize chips to shortcodes before ajax/normal submit
    if (form) {
        form.addEventListener('submit', () => {
            syncSerializedToTextarea();
        }, true);
    }

    // Re-apply when Content tab is opened (editor may init while hidden)
    document.querySelectorAll('.blog-tab-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (btn.getAttribute('data-tab') === 'content') {
                window.setTimeout(() => {
                    attachToEditor();
                    applyChipsToEditor();
                }, 100);
            }
        });
    });

    // Wait for TinyMCE instance, then hydrate after init
    let attempts = 0;
    const hydrate = window.setInterval(() => {
        attempts += 1;
        if (attachToEditor() || attempts > 60) {
            window.clearInterval(hydrate);
        }
    }, 150);
})();
</script>
