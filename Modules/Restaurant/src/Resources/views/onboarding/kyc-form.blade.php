<style>
    #onboarding-kyc-form [hidden], #kyc-message[hidden], #kyc-retry[hidden] { display: none !important; }
    .kyc-intro { margin: 20px 0 32px; }
    .kyc-intro h3 { font-size: clamp(24px, 3vw, 36px); line-height: 1.2; margin-bottom: 12px; }
    .kyc-intro p { color: #456471; font-size: 18px; }
    .kyc-card { padding: 24px 0; border-bottom: 1px solid #e1e9ec; }
    .kyc-card-header { display: flex; align-items: center; gap: 18px; }
    .kyc-marker { flex-shrink: 0; display: grid; place-items: center; width: 52px; height: 52px; border-radius: 50%; background: #222; color: white; font-size: 22px; }
    .kyc-card-approved .kyc-marker { background: #00c477; color: #103647; }
    .kyc-card-heading { flex: 1; min-width: 0; }
    .kyc-card-heading h4 { margin: 0; font-size: 20px; }
    .kyc-description { color: #526873; overflow-wrap: anywhere; }
    .kyc-action, .kyc-submit { background: #111; color: #fff; white-space: nowrap; }
    .kyc-action:hover, .kyc-submit:hover { background: #264b59; }
    .kyc-card .kyc-action { background: var(--status-bg); color: var(--status-ink); border-color: var(--status-border); }
    .kyc-card .kyc-action:hover { background: var(--status-ink); color: #fff; }
    .kyc-view { border: 0; border-radius: 4px; padding: 8px 4px; background: transparent; color: #103647; text-decoration: underline; text-underline-offset: 4px; white-space: nowrap; }
    .kyc-view:hover { background: #edf5f5; }
    .kyc-uploaded { margin: 16px 0 0 70px; }
    .kyc-editor { border: 0; padding: 24px 0 0 70px; margin: 0; min-width: 0; }
    .kyc-field { margin-bottom: 20px; }
    .kyc-field label { display: block; font-weight: 700; margin-bottom: 8px; }
    .kyc-field input, .kyc-field select { width: 100%; padding: 12px; font: inherit; border: 1px solid #bdc8cc; border-radius: 5px; background: #fafbfb; color: #103647; }
    .kyc-field input:focus, .kyc-field select:focus { outline: 2px solid #31be91; outline-offset: 2px; }
    .kyc-file-dropzone { position: relative; display: flex; align-items: center; gap: 16px; min-height: 112px; padding: 20px; border: 2px dashed #bdcdd2; border-radius: 12px; background: #f7fafb; color: #103647; transition: border-color .18s ease, background .18s ease, box-shadow .18s ease; }
    .kyc-file-dropzone:hover, .kyc-file-dropzone:focus-within, .kyc-file-dropzone.is-dragging { border-color: #00b978; background: #effbf6; box-shadow: 0 0 0 3px rgba(0, 185, 120, .1); }
    .kyc-file-dropzone-icon { display: grid; place-items: center; flex: 0 0 46px; width: 46px; height: 46px; border-radius: 50%; background: #dcf8ed; color: #008f5e; font-size: 24px; }
    .kyc-file-dropzone-copy { min-width: 0; }
    .kyc-file-dropzone-copy strong { display: block; font-size: 15px; }
    .kyc-file-dropzone-copy span { display: block; margin-top: 4px; color: #526873; font-size: 13px; overflow-wrap: anywhere; }
    .kyc-file-dropzone input[type="file"] { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .kyc-file-dropzone input[type="file"]:focus-visible { outline: 3px solid #00b978; outline-offset: 3px; border-radius: 10px; }
    .kyc-help { color: #526873; font-size: 13px; }
    .kyc-error { color: #a52e25; margin-top: 8px; font-size: 14px; white-space: pre-line; }
    .kyc-message { padding: 14px; background: #edf5f5; border-radius: 8px; margin: 16px 0; white-space: pre-line; }
    .kyc-message[data-error="true"] { background: #fff0ed; color: #973b21; }
    .kyc-submit { display: block; width: min(100%, 540px); margin: 36px auto 16px; }
    .kyc-submit:disabled, .kyc-action:disabled { opacity: .5; cursor: wait; }
    .kyc-saved { margin: 16px 0 0 70px; }
    .kyc-saved div { padding: 8px 0; }
    .kyc-saved dd { margin-top: 4px; }
    .kyc-previews { display: flex; flex-wrap: wrap; gap: 12px; margin: 12px 0; }
    .kyc-preview { border: 1px solid #d4dfe3; padding: 6px; border-radius: 8px; background: #f5f8f8; }
    .kyc-preview img { display: block; width: 220px; max-width: 100%; height: 150px; object-fit: contain; border-radius: 4px; }
    .kyc-image-dialog { max-width: min(960px, 94vw); max-height: 92vh; padding: 20px; border: 0; border-radius: 12px; }
    .kyc-image-dialog::backdrop { background: rgba(0, 0, 0, .65); }
    .kyc-image-dialog img { display: block; max-width: 100%; max-height: 72vh; object-fit: contain; margin: 16px auto 0; }
    @media (max-width: 600px) {
        .kyc-card-header { flex-wrap: wrap; gap: 12px; }
        .kyc-card-heading { flex-basis: calc(100% - 70px); }
        .kyc-action { margin-left: 64px; padding: 8px 18px; }
        .kyc-editor { padding-left: 0; }
        .kyc-saved { margin-left: 0; }
        .kyc-uploaded { margin-left: 0; }
    }
</style>
<div class="kyc-intro">
    <h3>Upload documents to verify your business</h3>
    <p>We'll review your documents to confirm your business can operate on Dawat Eats.</p>
</div>
<p class="kyc-message" id="kyc-message" role="status" aria-live="polite">Loading document requirements…</p>
<button id="kyc-retry" type="button" hidden>Retry loading</button>
<form id="onboarding-kyc-form" enctype="multipart/form-data">
    @csrf
    <div id="kyc-documents"></div>
    <button class="kyc-submit" id="kyc-submit" type="submit" hidden>Submit Documents</button>
</form>
<dialog id="kyc-image-dialog" class="kyc-image-dialog" aria-label="Uploaded document image">
    <form method="dialog"><button type="submit">Close preview</button></form>
    <img id="kyc-image-full" alt="Uploaded document">
</dialog>
<noscript><p class="reason">Enable JavaScript to load and upload your KYC documents.</p></noscript>
<script>
(() => {
    const urls = {
        get: @json(route('api.restaurant.onboarding.documents')),
        upload: @json(route('api.restaurant.onboarding.documents.upload')),
        reupload: @json(route('api.restaurant.onboarding.documents.reupload')),
    };
    const today = @json(now()->toDateString());
    const form = document.getElementById('onboarding-kyc-form');
    const container = document.getElementById('kyc-documents');
    const message = document.getElementById('kyc-message');
    const retry = document.getElementById('kyc-retry');
    const submit = document.getElementById('kyc-submit');
    const previewDialog = document.getElementById('kyc-image-dialog');
    const previewImage = document.getElementById('kyc-image-full');
    previewDialog.addEventListener('close', () => previewImage.removeAttribute('src'));
    let rows = [];
    let successMessage = @json(session('kyc_success'));
    const el = (tag, text, className) => {
        const node = document.createElement(tag);
        if (text !== undefined) node.textContent = text;
        if (className) node.className = className;
        return node;
    };
    function notify(text, error = false) {
        message.textContent = text;
        message.hidden = !text;
        message.dataset.error = String(error);
        if (error && text) window.onboardingToast(text, 'error');
    }
    async function api(url, body) {
        const response = await fetch(url, {
            method: body ? 'POST' : 'GET', credentials: 'same-origin', body,
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': form.elements.namedItem('_token').value },
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok || result.success !== true) {
            const error = new Error([401, 419].includes(response.status)
                ? 'Your session expired. Refresh the page and sign in again.'
                : (result.message || 'Unable to load or submit documents. Please try again.'));
            error.errors = result.errors || {};
            throw error;
        }
        return result;
    }
    function fileTypes(rules) {
        const value = rules.allowed_types;
        return (Array.isArray(value) ? value : (value ? value.split(',') : ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']))
            .map(value => value.trim().toLowerCase());
    }
    function savedPreviews(files, label) {
        const previews = el('div', undefined, 'kyc-previews');
        for (const file of files) {
            if (!file.url) continue;
            const url = new URL(file.url, window.location.origin);
            if (!['http:', 'https:'].includes(url.protocol)) continue;
            if (file.is_image) {
                const button = el('button', undefined, 'kyc-preview');
                button.type = 'button';
                button.setAttribute('aria-label', `View uploaded ${label} image`);
                const image = el('img');
                image.src = url.href; image.alt = `Uploaded ${label}`; image.loading = 'lazy';
                button.append(image);
                button.addEventListener('click', () => {
                    previewImage.src = url.href; previewImage.alt = `Uploaded ${label}`;
                    previewDialog.showModal();
                });
                previews.append(button);
            } else {
                const link = el('a', 'View uploaded document');
                link.href = url.href; link.target = '_blank'; link.rel = 'noopener noreferrer';
                previews.append(link);
            }
        }
        return previews;
    }
    function makeField(field, row, index) {
        const wrapper = el('div', undefined, 'kyc-field');
        const id = `kyc-${index}-${field.id}`;
        const label = el('label', field.field_name + (field.is_required ? ' *' : ''));
        label.htmlFor = id;
        const control = el(field.field_type === 'select' ? 'select' : 'input');
        let savedFilePreviews = null;
        let fileName = null;
        control.id = id;
        control.required = !!field.is_required;
        const rules = field.validation_rules || {};
        const help = el('p', undefined, 'kyc-help');
        if (field.field_type === 'select') {
            control.add(new Option('Select an option', ''));
            for (const [value, label] of Object.entries(field.options || {})) control.add(new Option(String(label), value));
        } else if (field.field_type === 'file') {
            control.type = 'file';
            control.accept = fileTypes(rules).map(type => '.' + type).join(',');
            control.className = 'kyc-file-input';
            const dropzone = el('div', undefined, 'kyc-file-dropzone');
            dropzone.append(el('span', '⇧', 'kyc-file-dropzone-icon'));
            const copy = el('div', undefined, 'kyc-file-dropzone-copy');
            copy.append(el('strong', 'Choose a file or drag it here'));
            fileName = el('span', 'No file selected');
            copy.append(fileName);
            dropzone.append(copy, control);
            control.addEventListener('change', () => {
                const files = Array.from(control.files || []);
                fileName.textContent = files.length ? files.map(file => `${file.name} (${formatFileSize(file.size)})`).join(', ') : 'No file selected';
            });
            ['dragenter', 'dragover'].forEach(eventName => dropzone.addEventListener(eventName, event => {
                event.preventDefault(); dropzone.classList.add('is-dragging');
            }));
            ['dragleave', 'drop'].forEach(eventName => dropzone.addEventListener(eventName, event => {
                event.preventDefault(); dropzone.classList.remove('is-dragging');
            }));
            dropzone.addEventListener('drop', event => {
                if (!event.dataTransfer?.files?.length) return;
                try {
                    control.files = event.dataTransfer.files;
                    control.dispatchEvent(new Event('change', { bubbles: true }));
                } catch (_) { /* Keep the native picker available if the browser blocks file assignment. */ }
            });
            help.textContent = `Allowed: ${fileTypes(rules).join(', ')}. Maximum size: ${Math.round((rules.max_size_kb ?? 10240) / 1024 * 10) / 10} MB.`;
        } else if (field.field_type === 'number') {
            control.type = 'number'; control.step = 'any';
            if (rules.min !== undefined && rules.min !== null && rules.min !== '') control.min = rules.min;
            if (rules.max !== undefined && rules.max !== null && rules.max !== '') control.max = rules.max;
        } else if (field.field_type === 'date') {
            control.type = 'date';
            if (rules.min_date) control.min = rules.min_date;
            if (rules.max_date) control.max = rules.max_date === 'today' ? today : rules.max_date;
        } else {
            control.type = rules.format === 'email' ? 'email' : 'text';
            control.maxLength = rules.max_length ?? 255;
            if (rules.min_length) control.minLength = rules.min_length;
            const patterns = { letters_only: '[A-Za-z ]+', numbers_only: '[0-9]+', alphanumeric: '[A-Za-z0-9 ]+', alphanumeric_strict: '[A-Za-z0-9]+' };
            if (patterns[rules.format]) control.pattern = patterns[rules.format];
        }
        if (field.field_type !== 'file') control.value = row.item.current_submission?.values?.[field.field_key] ?? '';
        else {
            const saved = row.item.current_submission?.files?.filter(file => Number(file.field_id) === Number(field.id)) || [];
            if (saved.length) {
                help.textContent += ' Choose a new file only when replacing this document.';
                savedFilePreviews = savedPreviews(saved, field.field_name);
            }
        }
        const error = el('p', '', 'kyc-error');
        error.id = id + '-error';
        control.setAttribute('aria-describedby', id + '-help ' + error.id);
        help.id = id + '-help';
        control.addEventListener('input', () => { control.setCustomValidity(''); error.textContent = ''; control.removeAttribute('aria-invalid'); });
        wrapper.append(label);
        if (savedFilePreviews) wrapper.append(savedFilePreviews);
        if (field.field_type === 'file') wrapper.append(control.parentElement);
        else wrapper.append(control);
        wrapper.append(help, error);
        row.controls.push({ field, control, error });
        return wrapper;
    }
    function formatFileSize(bytes) {
        if (!bytes) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB'];
        const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        const size = bytes / (1024 ** index);
        return `${size.toFixed(size >= 10 || index === 0 ? 0 : 1)} ${units[index]}`;
    }
    function render(items) {
        container.replaceChildren();
        rows = [];
        items.forEach((item, index) => {
            const type = item.kyc_type, saved = item.current_submission;
            const approved = saved?.status === 'approved';
            const tone = approved ? 'approved' : (saved?.refill_requested ? 'attention' : (saved?.status === 'rejected' ? 'rejected'
                : (saved?.status === 'under_review' ? 'review' : (saved ? 'pending' : 'draft'))));
            const card = el('section', undefined, 'kyc-card' + (approved ? ' kyc-card-approved' : ''));
            card.dataset.tone = tone;
            const header = el('div', undefined, 'kyc-card-header');
            const heading = el('div', undefined, 'kyc-card-heading');
            heading.append(el('h4', type.name));
            if (type.description) heading.append(el('p', type.description, 'kyc-description'));
            const statusBadge = el('p', approved ? 'Approved · Read only' : (saved?.refill_requested ? 'Refill requested' : (saved ? saved.status.replaceAll('_', ' ').replace(/^./, letter => letter.toUpperCase()) : 'Not submitted')), 'badge status-badge');
            statusBadge.dataset.tone = tone;
            heading.append(statusBadge);
            const statusMessage = saved?.refill_requested ? 'A refill has been requested. Read the reason below, update the details or upload a corrected document, and resubmit for review.' : approved ? 'This document is approved. No further action is needed.'
                : (saved?.status === 'rejected' ? 'Review the feedback and reupload a corrected document.'
                    : (saved?.status === 'under_review' ? 'Our team is reviewing this document.'
                        : (saved ? 'Your document has been submitted and is waiting for review.' : 'Upload the required information for this document.')));
            heading.append(el('p', statusMessage, 'step-message'));
            if (saved?.rejection_reason) heading.append(el('p', saved.rejection_reason, 'reason'));
            header.append(el('span', approved ? '✓' : String(index + 1), 'kyc-marker'), heading);
            card.append(header);
            const row = { item, card, controls: [], active: false };
            if (approved) {
                const details = el('dl', undefined, 'kyc-saved');
                for (const field of type.fields) {
                    const entry = el('div');
                    entry.append(el('dt', field.field_name));
                    if (field.field_type === 'file') {
                        const files = (saved.files || []).filter(file => Number(file.field_id) === Number(field.id));
                        const value = el('dd');
                        value.append(files.length ? savedPreviews(files, field.field_name) : el('span', 'No document uploaded'));
                        entry.append(value);
                    } else {
                        const value = field.field_type === 'select' ? (field.options?.[saved.values?.[field.field_key]] ?? saved.values?.[field.field_key]) : saved.values?.[field.field_key];
                        entry.append(el('dd', value === null || value === undefined || value === '' ? 'Not provided' : String(value)));
                    }
                    details.append(entry);
                }
                card.append(details);
            } else {
                const uploadedFiles = (saved?.files || []).filter(file => file.url);
                if (uploadedFiles.length) {
                    const view = el('button', 'View uploaded documents', 'kyc-view');
                    view.type = 'button'; view.setAttribute('aria-expanded', 'false');
                    const gallery = el('div', undefined, 'kyc-uploaded');
                    gallery.id = `kyc-uploaded-${type.id}`; gallery.hidden = true;
                    view.setAttribute('aria-controls', gallery.id);
                    for (const field of type.fields.filter(field => field.field_type === 'file')) {
                        const files = uploadedFiles.filter(file => Number(file.field_id) === Number(field.id));
                        if (!files.length) continue;
                        gallery.append(el('p', field.field_name, 'kyc-help'), savedPreviews(files, field.field_name));
                    }
                    view.addEventListener('click', () => {
                        gallery.hidden = !gallery.hidden;
                        view.setAttribute('aria-expanded', String(!gallery.hidden));
                        view.textContent = gallery.hidden ? 'View uploaded documents' : 'Hide uploaded documents';
                    });
                    header.append(view); card.append(gallery);
                }
                const button = el('button', (saved ? 'Reupload' : 'Upload') + ' ↑', 'kyc-action');
                button.type = 'button'; button.setAttribute('aria-expanded', 'false');
                const editor = el('fieldset', undefined, 'kyc-editor');
                editor.id = `kyc-editor-${type.id}`;
                button.setAttribute('aria-controls', editor.id);
                editor.hidden = true; editor.disabled = true;
                if (saved) editor.append(el('p', 'Submit all required fields and choose replacement files for this document.', 'kyc-help'));
                for (const field of type.fields) editor.append(makeField(field, row, index));
                button.addEventListener('click', () => {
                    row.active = !row.active;
                    editor.hidden = !row.active; editor.disabled = !row.active;
                    button.setAttribute('aria-expanded', String(row.active));
                    submit.hidden = !rows.some(row => row.active);
                    if (row.active) row.controls[0]?.control.focus();
                });
                row.editor = editor; row.button = button;
                header.append(button); card.append(editor);
            }
            rows.push(row); container.append(card);
        });
        submit.hidden = true;
    }
    async function load() {
        retry.hidden = true; submit.hidden = true;
        notify('Loading document requirements…');
        try {
            const result = await api(urls.get);
            render(result.data);
            notify(successMessage || (result.data.length ? '' : 'No KYC documents are currently required.'));
            successMessage = null;
            return true;
        } catch (error) { notify(error.message, true); retry.hidden = false; return false; }
    }
    retry.addEventListener('click', load);
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const selected = rows.filter(row => row.active && row.item.current_submission?.status !== 'approved');
        if (!selected.length || submit.disabled) return;
        const unchanged = selected.find(row => row.item.current_submission && !row.controls.some(({field, control}) => {
            if (field.field_type === 'file') return Boolean(control.files[0]);
            const saved = row.item.current_submission.values?.[field.field_key] ?? '';
            if (field.field_type === 'number' && control.value !== '' && saved !== '') return Number(control.value) !== Number(saved);
            return control.value.trim() !== String(saved).trim();
        }));
        if (unchanged) {
            notify(`No changes were made to ${unchanged.item.kyc_type.name}. Please update a field or choose a replacement document before submitting again.`, true);
            message.scrollIntoView({block: 'center'});
            return;
        }
        for (const row of selected) for (const { field, control, error } of row.controls) {
            error.textContent = ''; control.setCustomValidity('');
            if (field.field_type === 'file' && control.files[0]) {
                const file = control.files[0], rules = field.validation_rules || {};
                if (file.size > (rules.max_size_kb ?? 10240) * 1024) control.setCustomValidity('This file exceeds the maximum allowed size.');
                if (!fileTypes(rules).includes(file.name.split('.').pop().toLowerCase())) control.setCustomValidity('This file type is not allowed.');
            }
        }
        if (!form.reportValidity()) return;
        if (submit.disabled) return;
        if (!await window.confirmOnboardingSubmission(selected.some(row => row.item.current_submission)
            ? 'Submit the selected KYC documents and any replacements for review?'
            : 'Submit the selected KYC documents for review?')) return;
        submit.disabled = true;
        rows.forEach(row => { if (row.editor) row.editor.disabled = true; if (row.button) row.button.disabled = true; });
        let savedCount = 0;
        try {
            for (const reupload of [false, true]) {
                const batch = selected.filter(row => !!row.item.current_submission === reupload);
                if (!batch.length) continue;
                const body = new FormData();
                batch.forEach((row, index) => {
                    body.append(`documents[${index}][kyc_type_id]`, row.item.kyc_type.id);
                    for (const { field, control } of row.controls) {
                        if (field.field_type === 'file') { if (control.files[0]) body.append(`documents[${index}][${field.field_key}]`, control.files[0]); }
                        else body.append(`documents[${index}][${field.field_key}]`, control.value);
                    }
                });
                notify('Submitting documents…');
                try { await api(reupload ? urls.reupload : urls.upload, body); }
                catch (error) {
                    for (const [key, errors] of Object.entries(error.errors || {})) {
                        const parts = key.split('.');
                        const row = parts[0] === 'documents' ? batch[Number(parts[1])] : null;
                        const entry = row?.controls.find(entry => entry.field.field_key === parts[2]);
                        if (entry) { entry.error.textContent = Array.isArray(errors) ? errors.join(' ') : String(errors); entry.control.setAttribute('aria-invalid', 'true'); }
                        else error.message += '\n' + (Array.isArray(errors) ? errors.join(' ') : String(errors));
                    }
                    throw error;
                }
                savedCount += batch.length;
                batch.forEach(row => { row.active = false; row.editor.hidden = true; row.item.current_submission = { status: 'pending' }; row.button.textContent = 'Reupload ↑'; row.button.setAttribute('aria-expanded', 'false'); });
            }
            window.onboardingComplete(`${savedCount} document(s) submitted successfully for review.`);
        } catch (error) {
            notify((savedCount ? `${savedCount} document(s) saved. ` : '') + error.message, true);
        } finally {
            submit.disabled = false;
            rows.forEach(row => { if (row.editor) row.editor.disabled = !row.active; if (row.button) row.button.disabled = false; });
            submit.hidden = !rows.some(row => row.active);
            form.querySelector('[aria-invalid="true"]')?.focus();
        }
    });
    load();
})();
</script>
