{{-- Menu management styles, shared by onboarding (dark) and the restaurant panel (.menu-light).
     Everything is scoped to .menu-wrap so it never affects the rest of the admin panel. --}}
<style>
    .menu-wrap {
        --m-text: #FFFFFF; --m-label: #E4E4E7; --m-muted: #8E8E93; --m-muted-2: #A1A1AA;
        --m-surface: #141416; --m-surface-2: #18181C; --m-input: #1A1A1F; --m-hover: #1C1C22;
        --m-chip: #202026; --m-chip-text: #9CA3AF; --m-help-bg: #1F1F25; --m-thumb-icon: #52525B;
        --m-border: #232328; --m-border-2: #2D2D36; --m-border-3: #383842; --m-border-hover: #4A4A55; --m-file-border: #454550;
        --m-file-btn: #272730; --m-file-btn-text: #FFFFFF;
        --m-accent: #FF5500; --m-accent-hover: #FF6611; --m-accent-soft: rgba(255, 98, 16, 0.1); --m-accent-ring: rgba(255, 98, 16, 0.15);
        --m-accent-shadow: 0 4px 18px rgba(255, 85, 0, 0.22); --m-accent-shadow-hover: 0 6px 22px rgba(255, 85, 0, 0.32);
        --m-ok: #34D399; --m-ok-bg: rgba(16, 185, 129, 0.12);
        --m-warn: #F97316; --m-warn-2: #FDBA74; --m-warn-bg: rgba(249, 115, 22, 0.12);
        --m-danger: #FCA5A5; --m-danger-strong: #F87171; --m-danger-bg: rgba(239, 68, 68, 0.1); --m-danger-border: rgba(239, 68, 68, 0.35);
        --m-card-shadow: none;
        --m-select-arrow: url("data:image/svg+xml;charset=UTF-8,%3Csvg width='12' height='8' viewBox='0 0 12 8' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='%23A1A1AA' d='M1.41 0L6 4.58 10.59 0 12 1.41 6 7.41 0 1.41z'/%3E%3C/svg%3E");
        --m-select-arrow-focus: url("data:image/svg+xml;charset=UTF-8,%3Csvg width='12' height='8' viewBox='0 0 12 8' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='%23FF6210' d='M1.41 0L6 4.58 10.59 0 12 1.41 6 7.41 0 1.41z'/%3E%3C/svg%3E");
        color: var(--m-text);
    }
    .menu-wrap.menu-light {
        --m-text: #111827; --m-label: #374151; --m-muted: #6B7280; --m-muted-2: #4B5563;
        --m-surface: #FFFFFF; --m-surface-2: #F9FAFB; --m-input: #FFFFFF; --m-hover: #F3F4F6;
        --m-chip: #F3F4F6; --m-chip-text: #4B5563; --m-help-bg: #F3F4F6; --m-thumb-icon: #9CA3AF;
        --m-border: #E5E7EB; --m-border-2: #D1D5DB; --m-border-3: #D1D5DB; --m-border-hover: #9CA3AF; --m-file-border: #D1D5DB;
        --m-file-btn: #F3F4F6; --m-file-btn-text: #374151;
        --m-accent: #FF6210; --m-accent-hover: #E85A0E; --m-accent-soft: #FFF3EC; --m-accent-ring: rgba(255, 98, 16, 0.18);
        --m-accent-shadow: 0 1px 2px rgba(0, 0, 0, 0.08); --m-accent-shadow-hover: 0 4px 12px rgba(255, 98, 16, 0.25);
        --m-ok: #047857; --m-ok-bg: #ECFDF5;
        --m-warn: #C2410C; --m-warn-2: #C2410C; --m-warn-bg: #FFF7ED;
        --m-danger: #B91C1C; --m-danger-strong: #DC2626; --m-danger-bg: #FEF2F2; --m-danger-border: #FECACA;
        --m-card-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
        --m-select-arrow: url("data:image/svg+xml;charset=UTF-8,%3Csvg width='12' height='8' viewBox='0 0 12 8' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='%237A7570' d='M1.41 0L6 4.58 10.59 0 12 1.41 6 7.41 0 1.41z'/%3E%3C/svg%3E");
    }
    /* Contain absolutely positioned helpers (hidden inputs, dropdowns) so they can't stretch the page. */
    .menu-wrap { position: relative; }
    .menu-wrap .menu-image-picker, .menu-wrap .menu-feature-panel { position: relative; }
    .menu-wrap [hidden], .menu-wrap .hidden { display: none !important; }
    .menu-wrap a { color: inherit; }
    .menu-wrap .menu-link { color: var(--m-accent); font-weight: 600; text-decoration: none; }
    .menu-wrap .menu-link:hover { text-decoration: underline; }
    .menu-wrap a:focus-visible, .menu-wrap button:focus-visible { outline: 3px solid var(--m-accent-ring); outline-offset: 3px; }

    .menu-wrap .menu-panel { background: var(--m-surface); border: 1px solid var(--m-border); border-radius: 20px; padding: 28px; margin-bottom: 24px; box-shadow: var(--m-card-shadow); }
    .menu-wrap .menu-optional-tag { font-size: 11px; font-weight: 500; color: var(--m-muted); background: var(--m-chip); padding: 2px 8px; border-radius: 4px; vertical-align: middle; letter-spacing: 0; }

    /* Tabs */
    .menu-wrap .menu-tabs { display: flex; gap: 6px; padding: 6px; background: var(--m-surface); border: 1px solid var(--m-border); border-radius: 9999px; margin-bottom: 24px; overflow-x: auto; box-shadow: var(--m-card-shadow); }
    .menu-wrap .menu-tab { flex: 1 0 auto; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 18px; border-radius: 9999px; color: var(--m-muted-2); font-size: 14px; font-weight: 600; text-decoration: none; white-space: nowrap; transition: background-color .15s, color .15s; }
    .menu-wrap .menu-tab:hover { color: var(--m-text); background: var(--m-hover); }
    .menu-wrap .menu-tab.is-active { background: var(--m-accent); color: #FFFFFF; box-shadow: var(--m-accent-shadow); }
    .menu-wrap .menu-tab-count { font-size: 11px; padding: 1px 8px; border-radius: 9999px; background: rgba(127, 127, 127, 0.18); }
    .menu-wrap .menu-tab.is-active .menu-tab-count { background: rgba(255, 255, 255, 0.22); }

    /* Toolbar & buttons */
    .menu-wrap .menu-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
    .menu-wrap .menu-toolbar h2 { margin: 0; font-size: 18px; font-weight: 600; color: var(--m-text); }
    .menu-wrap .menu-toolbar p { margin: 2px 0 0; color: var(--m-muted); font-size: 13px; }
    .menu-wrap .menu-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 22px; border: 0; border-radius: 9999px; background: var(--m-accent); color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 700; text-decoration: none; cursor: pointer; box-shadow: var(--m-accent-shadow); transition: background-color .15s, transform .15s, box-shadow .15s; }
    .menu-wrap .menu-btn:hover { background: var(--m-accent-hover); color: #FFFFFF; transform: translateY(-1px); box-shadow: var(--m-accent-shadow-hover); }
    .menu-wrap .menu-btn-ghost { background: transparent; border: 1px solid var(--m-border-3); color: var(--m-label); box-shadow: none; }
    .menu-wrap .menu-btn-ghost:hover { background: var(--m-hover); border-color: var(--m-border-hover); color: var(--m-text); box-shadow: none; }
    .menu-wrap .menu-btn-sm { padding: 7px 14px; font-size: 13px; font-weight: 600; }
    .menu-wrap .menu-btn-danger { background: transparent; border: 1px solid var(--m-danger-border); color: var(--m-danger); box-shadow: none; }
    .menu-wrap .menu-btn-danger:hover { background: var(--m-danger-bg); color: var(--m-danger); box-shadow: none; }
    /* Icon-only actions (edit / delete / remove) */
    .menu-wrap .menu-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; padding: 0; border-radius: 10px; border: 1px solid var(--m-border-2); background: var(--m-surface); color: var(--m-muted-2); cursor: pointer; text-decoration: none; transition: background-color .15s, border-color .15s, color .15s; }
    .menu-wrap .menu-icon-btn:hover { border-color: var(--m-accent); background: var(--m-accent-soft); color: var(--m-accent); }
    .menu-wrap .menu-icon-btn.is-danger:hover { border-color: var(--m-danger-border); background: var(--m-danger-bg); color: var(--m-danger-strong); }
    .menu-wrap .menu-icon-btn svg { display: block; }
    .menu-wrap .menu-search { display: flex; gap: 8px; flex: 1 1 260px; max-width: 420px; }

    /* Lists */
    .menu-wrap .menu-list { display: flex; flex-direction: column; gap: 12px; }
    .menu-wrap .menu-row { display: flex; align-items: center; gap: 16px; padding: 16px 18px; background: var(--m-surface-2); border: 1px solid var(--m-border); border-radius: 16px; transition: border-color .2s; }
    .menu-wrap .menu-row:hover { border-color: var(--m-border-3); }
    .menu-wrap .menu-thumb { width: 56px; height: 56px; border-radius: 12px; background: var(--m-hover); object-fit: cover; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: var(--m-thumb-icon); }
    .menu-wrap .menu-row-body { flex: 1; min-width: 0; }
    .menu-wrap .menu-row-title { margin: 0; font-size: 15px; font-weight: 600; color: var(--m-text); overflow-wrap: anywhere; }
    .menu-wrap .menu-row-meta { margin: 4px 0 0; color: var(--m-muted); font-size: 13px; display: flex; flex-wrap: wrap; gap: 6px 14px; }
    .menu-wrap .menu-row-actions { display: flex; gap: 8px; flex-shrink: 0; }
    .menu-wrap .menu-row-actions form { margin: 0; }
    .menu-wrap .menu-chip { display: inline-block; font-size: 11.5px; font-weight: 500; padding: 3px 10px; border-radius: 9999px; background: var(--m-chip); color: var(--m-chip-text); }
    .menu-wrap .menu-chip.is-on { background: var(--m-ok-bg); color: var(--m-ok); }
    .menu-wrap .menu-chip.is-off { background: var(--m-warn-bg); color: var(--m-warn); }
    .menu-wrap .menu-empty { text-align: center; padding: 48px 20px; color: var(--m-muted); }
    .menu-wrap .menu-empty > svg { display: block; margin-inline: auto; }
    .menu-wrap .menu-empty h3 { margin: 12px 0 6px; color: var(--m-text); font-size: 17px; font-weight: 600; }
    .menu-wrap .menu-empty p { margin: 0 0 20px; font-size: 14px; }
    .menu-wrap .menu-pagination { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 20px; color: var(--m-muted); font-size: 13px; }
    .menu-wrap .menu-pagination .menu-btn[aria-disabled="true"] { opacity: .4; pointer-events: none; }

    /* Forms (also styles the shared serving-option partial) */
    .menu-wrap .menu-form { display: flex; flex-direction: column; gap: 24px; }
    .menu-wrap .menu-section { background: var(--m-surface); border: 1px solid var(--m-border); border-radius: 20px; padding: 24px; box-shadow: var(--m-card-shadow); }
    .menu-wrap .menu-section > h3 { margin: 0 0 4px; font-size: 18px; font-weight: 600; color: var(--m-text); }
    .menu-wrap .menu-section > .menu-section-sub { margin: 0 0 20px; color: var(--m-muted); font-size: 13px; }
    .menu-wrap .menu-fields { display: grid; gap: 18px; }
    .menu-wrap .menu-grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
    .menu-wrap .form-label { display: block; margin-bottom: 8px; color: var(--m-label); font-size: 13px; font-weight: 600; }
    .menu-wrap .input-field { width: 100%; padding: 12px 14px; background-color: var(--m-input); border: 1px solid var(--m-border-2); border-radius: 10px; color: var(--m-text); font: inherit; font-size: 14px; transition: border-color .15s, box-shadow .15s; }
    .menu-wrap .input-field:focus { outline: none; border-color: var(--m-accent); box-shadow: 0 0 0 3px var(--m-accent-ring); }
    .menu-wrap .input-field:disabled { opacity: .5; }
    .menu-wrap select.input-field option { background: var(--m-input); color: var(--m-text); }
    /* Own dropdown arrow (one, right-aligned) so the admin panel's select styles can't tile it. */
    .menu-wrap select.input-field,
    .menu-wrap select.input-field:focus {
        -webkit-appearance: none; -moz-appearance: none; appearance: none;
        padding-right: 40px;
        background-image: var(--m-select-arrow);
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 12px 8px;
    }
    .menu-wrap select.input-field:focus { background-image: var(--m-select-arrow-focus); }
    .menu-wrap textarea.input-field { resize: vertical; min-height: 100px; }
    .menu-wrap .menu-help, .menu-wrap .menu-form .text-xs { margin: 6px 0 0; color: var(--m-muted); font-size: 12px; line-height: 1.5; }
    .menu-wrap .error-message { margin: 6px 0 0; color: var(--m-danger); font-size: 12.5px; }
    .menu-wrap .text-red-500, .menu-wrap .text-red-600 { color: var(--m-danger-strong); }
    .menu-wrap .menu-alert { padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; border: 1px solid var(--m-danger-border); background: var(--m-danger-bg); color: var(--m-danger); }
    .menu-wrap .menu-alert ul { margin: 6px 0 0; padding-left: 18px; list-style: disc; }
    .menu-wrap .menu-file { display: block; width: 100%; padding: 7px; border: 1px dashed var(--m-file-border); border-radius: 12px; background: var(--m-surface-2); color: var(--m-label); font: inherit; font-size: 13px; cursor: pointer; }
    .menu-wrap .menu-file::file-selector-button { margin-right: 10px; padding: 8px 12px; border: 0; border-radius: 8px; background: var(--m-file-btn); color: var(--m-file-btn-text); font: inherit; font-size: 12px; font-weight: 600; cursor: pointer; }
    .menu-wrap .menu-file:hover { border-color: var(--m-accent); }
    .menu-wrap .menu-current-img { margin-top: 12px; width: 96px; height: 96px; border-radius: 12px; object-fit: cover; border: 1px solid var(--m-border-2); }
    .menu-wrap .menu-check-grid { display: flex; flex-wrap: wrap; gap: 10px; }
    .menu-wrap .menu-check { display: inline-flex; align-items: center; gap: 8px; padding: 9px 14px; border: 1px solid var(--m-border-2); border-radius: 9999px; background: var(--m-input); color: var(--m-label); font-size: 13px; cursor: pointer; transition: border-color .15s, background-color .15s; }
    .menu-wrap .menu-check:hover { border-color: var(--m-border-hover); }
    .menu-wrap .menu-check:has(input:checked) { border-color: var(--m-accent); background: var(--m-accent-soft); }
    .menu-wrap .menu-check.is-locked { opacity: .5; cursor: not-allowed; }
    .menu-wrap .menu-check input { accent-color: var(--m-accent); margin: 0; }
    .menu-wrap .menu-check.is-blocked { border-color: var(--m-danger-border); background: var(--m-danger-bg); }
    .menu-wrap .menu-cert-tag { font-size: 10.5px; font-weight: 600; padding: 1px 7px; border-radius: 9999px; background: var(--m-warn-bg); color: var(--m-warn-2); text-transform: capitalize; }
    .menu-wrap .menu-cert-tag.is-ok { background: var(--m-ok-bg); color: var(--m-ok); }
    .menu-wrap .menu-actions { display: flex; justify-content: flex-end; gap: 12px; flex-wrap: wrap; }

    .menu-wrap .serving-option { background: var(--m-surface-2); border: 1px solid var(--m-border); border-radius: 16px; padding: 20px; margin-top: 16px; }
    .menu-wrap .serving-option:first-child { margin-top: 0; }
    .menu-wrap .serving-option > div:first-of-type { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 16px; }
    .menu-wrap .serving-option h4 { margin: 0; font-size: 15px; font-weight: 600; color: var(--m-text); }
    .menu-wrap .serving-option .grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    .menu-wrap .serving-option [data-remove-serving] { background: none; border: 0; color: var(--m-danger-strong); font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; padding: 4px 8px; }
    .menu-wrap .serving-option [data-serve-help] { margin: 16px 0 0; padding: 10px 12px; border-radius: 10px; background: var(--m-help-bg); color: var(--m-muted-2); font-size: 12px; }
    .menu-wrap .serving-option label[data-serving-field="is_available"] { display: inline-flex; align-items: center; gap: 8px; margin-top: 16px; color: var(--m-label); font-size: 13px; }
    .menu-wrap .serving-option input[type="checkbox"] { accent-color: var(--m-accent); }
    .menu-wrap .serving-option [data-serving-total] { margin: 10px 0 0; color: var(--m-ok); font-size: 12.5px; }
    .menu-wrap #add-serving-option { margin-top: 16px; }
    .menu-wrap #add-serving-option:disabled { opacity: .4; cursor: not-allowed; }

    /* Feature assignment */
    .menu-wrap .feature-group { background: var(--m-surface-2); border: 1px solid var(--m-border); border-radius: 16px; padding: 18px; margin-top: 14px; }
    .menu-wrap .feature-group h4 { margin: 0 0 14px; font-size: 15px; font-weight: 600; color: var(--m-text); }
    .menu-wrap .feature-field + .feature-field { margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--m-border); }
    .menu-wrap .feature-field .input-field { max-width: 360px; }

    .menu-wrap .menu-visually-hidden { position: absolute !important; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }

    /* Multi-image picker */
    .menu-wrap .menu-image-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(104px, 1fr)); gap: 12px; }
    .menu-wrap .menu-image-tile { position: relative; margin: 0; aspect-ratio: 1; border-radius: 12px; overflow: hidden; border: 1px solid var(--m-border-2); background: var(--m-hover); }
    .menu-wrap .menu-image-tile img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .menu-wrap .menu-image-tile figcaption { position: absolute; left: 6px; bottom: 6px; padding: 1px 8px; border-radius: 9999px; font-size: 10.5px; font-weight: 600; background: rgba(0, 0, 0, 0.6); color: #FFFFFF; }
    .menu-wrap .menu-image-tile:not(.is-saved) figcaption { background: var(--m-accent); }
    .menu-wrap .menu-image-remove { position: absolute; top: 6px; right: 6px; width: 26px; height: 26px; border: 0; border-radius: 50%; background: rgba(0, 0, 0, 0.65); color: #FFFFFF; font-size: 18px; line-height: 26px; cursor: pointer; padding: 0; }
    .menu-wrap .menu-image-remove:hover { background: #DC2626; }
    .menu-wrap .menu-image-tile.is-removed img { opacity: .35; filter: grayscale(1); }
    .menu-wrap .menu-image-tile.is-removed figcaption { background: #DC2626; }
    .menu-wrap .menu-image-tile.is-removed .menu-image-remove { background: var(--m-accent); }
    .menu-wrap .menu-image-add { aspect-ratio: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; border: 1px dashed var(--m-file-border); border-radius: 12px; background: var(--m-surface-2); color: var(--m-muted-2); font-size: 12.5px; font-weight: 600; cursor: pointer; transition: border-color .15s, color .15s; }
    .menu-wrap .menu-image-add:hover { border-color: var(--m-accent); color: var(--m-accent); }

    /* Feature picker: one panel per chosen feature */
    .menu-wrap .menu-feature-panels { display: flex; flex-direction: column; gap: 12px; margin-top: 16px; }
    .menu-wrap .menu-feature-panel { background: var(--m-surface-2); border: 1px solid var(--m-border); border-radius: 14px; padding: 14px 16px; margin: 0; }
    .menu-wrap .menu-feature-panel + .menu-feature-panel { margin-top: 0; padding-top: 14px; border-top: 1px solid var(--m-border); }
    .menu-wrap .menu-feature-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
    .menu-wrap .menu-feature-inputs { border: 0; margin: 0; padding: 0; min-width: 0; }

    /* Advanced selector (Core component) themed for menu pages */
    /* The Features section heading already names the field; keep the label for screen readers. */
    .menu-wrap .menu-feature-picker .advanced-selector-wrapper > label { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; }
    .menu-wrap .advanced-selector-wrapper > label { display: block; margin-bottom: 8px; color: var(--m-label); font-size: 13px; font-weight: 600; }
    .menu-wrap .advanced-selector-wrapper .relative { position: relative; }
    .menu-wrap .advanced-selector-wrapper .selector-input-container .input-field { padding-right: 48px; }
    .menu-wrap .advanced-selector-wrapper .selector-input-container > .absolute { position: absolute; top: 0; bottom: 0; right: 0; display: flex; align-items: center; padding-right: 8px; }
    .menu-wrap .advanced-selector-wrapper .selector-input-container button { width: 32px; height: 32px; border: 0; border-radius: 8px; background: transparent; cursor: pointer; padding: 0;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%23FF6210' stroke-width='2' stroke-linecap='round'%3E%3Cpath d='M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: center; }
    .menu-wrap .advanced-selector-wrapper .selector-input-container button:hover { background-color: var(--m-hover); }
    .menu-wrap .advanced-selector-wrapper .selector-input-container button i { display: none; }
    .menu-wrap .advanced-selector-wrapper .suggestions-dropdown { position: absolute; z-index: 30; left: 0; right: 0; margin-top: 4px; max-height: 240px; overflow-y: auto; background: var(--m-surface); border: 1px solid var(--m-border-2); border-radius: 12px; box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18); }
    .menu-wrap .advanced-selector-wrapper .suggestion-item { padding: 10px 14px; cursor: pointer; border-bottom: 1px solid var(--m-border); color: var(--m-text); }
    .menu-wrap .advanced-selector-wrapper .suggestion-item:hover { background: var(--m-hover); }
    .menu-wrap .advanced-selector-wrapper .suggestion-item:last-child { border-bottom: 0; }
    .menu-wrap .advanced-selector-wrapper .font-medium { font-weight: 500; color: var(--m-text); }
    .menu-wrap .advanced-selector-wrapper .text-xs { font-size: 12px; color: var(--m-muted); margin-top: 2px; }
    .menu-wrap .advanced-selector-wrapper .text-sm { font-size: 13px; }
    .menu-wrap .advanced-selector-wrapper .text-center { text-align: center; color: var(--m-muted); padding: 12px; }
    .menu-wrap .advanced-selector-wrapper .flex { display: flex; }
    .menu-wrap .advanced-selector-wrapper .items-center { align-items: center; }
    .menu-wrap .advanced-selector-wrapper .flex-wrap { flex-wrap: wrap; }
    .menu-wrap .advanced-selector-wrapper .gap-2 { gap: 8px; }
    .menu-wrap .advanced-selector-wrapper .selected-items-display { margin-top: 10px; }
    .menu-wrap .advanced-selector-wrapper .selected-tag { display: inline-flex; align-items: center; gap: 6px; padding: 5px 6px 5px 12px; border-radius: 9999px; background: var(--m-accent-soft); border: 1px solid var(--m-accent); color: var(--m-text); font-size: 13px; font-weight: 500; }
    .menu-wrap .advanced-selector-wrapper .selected-tag .remove-btn { width: 20px; height: 20px; border: 0; border-radius: 50%; background: transparent; color: var(--m-accent); font-size: 16px; line-height: 20px; padding: 0; cursor: pointer; opacity: 1; }
    .menu-wrap .advanced-selector-wrapper .selected-tag .remove-btn:hover { background: var(--m-accent); color: #FFFFFF; }
    .menu-wrap .advanced-selector-wrapper .animate-spin { display: inline-block; width: 18px; height: 18px; border: 2px solid var(--m-border-2); border-top-color: var(--m-accent); border-radius: 50%; animation: menu-spin .8s linear infinite; }
    @keyframes menu-spin { to { transform: rotate(360deg); } }

    /* Selector modal */
    .menu-wrap .selector-modal { position: fixed; inset: 0; z-index: 1000; background: rgba(10, 10, 12, 0.55); backdrop-filter: blur(4px); }
    .menu-wrap .selector-modal > div { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 16px; }
    .menu-wrap .selector-modal > div > div { display: flex; flex-direction: column; width: 100%; max-width: 640px; max-height: 80vh; background: var(--m-surface); color: var(--m-text); border: 1px solid var(--m-border); border-radius: 18px; box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35); overflow: hidden; }
    .menu-wrap .selector-modal .border-b { border-bottom: 1px solid var(--m-border); }
    .menu-wrap .selector-modal .border-t { border-top: 1px solid var(--m-border); }
    .menu-wrap .selector-modal .px-6 { padding-left: 20px; padding-right: 20px; }
    .menu-wrap .selector-modal .py-4 { padding-top: 14px; padding-bottom: 14px; }
    .menu-wrap .selector-modal .justify-between { justify-content: space-between; }
    .menu-wrap .selector-modal .flex-1 { flex: 1; min-height: 0; overflow-y: auto; }
    .menu-wrap .selector-modal h3 { margin: 0; font-size: 16px; font-weight: 600; color: var(--m-text); }
    .menu-wrap .selector-modal button { font: inherit; cursor: pointer; }
    .menu-wrap .selector-modal .px-6.py-4.border-b button { border: 0; background: transparent; color: var(--m-muted); padding: 4px; border-radius: 8px; }
    .menu-wrap .selector-modal svg.w-6 { width: 22px; height: 22px; display: block; }
    .menu-wrap .selector-modal .space-y-2 > * + * { margin-top: 8px; }
    .menu-wrap .selector-modal .item-row { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1px solid var(--m-border); border-radius: 12px; background: var(--m-surface-2); cursor: pointer; }
    .menu-wrap .selector-modal .item-row:hover { background: var(--m-hover); }
    .menu-wrap .selector-modal .item-row.selected { background: var(--m-accent-soft); border-color: var(--m-accent); }
    .menu-wrap .selector-modal .item-checkbox { accent-color: var(--m-accent); width: 16px; height: 16px; margin: 0; }
    .menu-wrap .selector-modal .flex.space-x-3 { gap: 10px; }
    .menu-wrap .selector-modal .flex.space-x-3 > button { padding: 9px 18px; border-radius: 9999px; font-size: 13px; font-weight: 600; }
    .menu-wrap .selector-modal .flex.space-x-3 > button:first-child { background: transparent; border: 1px solid var(--m-border-3); color: var(--m-label); }
    .menu-wrap .selector-modal .flex.space-x-3 > button:last-child { background: var(--m-accent); border: 0; color: #FFFFFF; }
    .menu-wrap .selector-modal .text-gray-600, .menu-wrap .selector-modal .text-gray-500 { color: var(--m-muted); }

    @media (max-width: 720px) {
        .menu-wrap .menu-panel, .menu-wrap .menu-section { padding: 18px; }
        .menu-wrap .menu-grid-2, .menu-wrap .serving-option .grid { grid-template-columns: 1fr; }
        .menu-wrap .menu-row { flex-wrap: wrap; }
        .menu-wrap .menu-row-actions { width: 100%; justify-content: flex-end; }
        .menu-wrap .menu-actions .menu-btn { flex: 1; }
    }
    /* Food item detail page */
    .menu-wrap .menu-item-title-link { text-decoration: none; }
    .menu-wrap .menu-item-title-link:hover { color: var(--m-accent); text-decoration: underline; }
    .menu-wrap.menu-food-detail .menu-page-head { margin-bottom: 18px; }
    .menu-wrap.menu-food-detail .menu-page-back { margin-bottom: 0; }
    .menu-wrap.menu-food-detail .menu-page-sub { display: none; }
    .menu-wrap .food-detail-hero { display: grid; grid-template-columns: 184px minmax(0, 1fr) 220px; gap: 26px; align-items: center; padding: 24px; border-radius: 16px; }
    .menu-wrap .food-detail-cover { height: 184px; border-radius: 14px; overflow: hidden; background: var(--m-surface-2); border: 1px solid var(--m-border); }
    .menu-wrap .food-detail-cover > img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .menu-wrap .food-detail-placeholder { height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; color: var(--m-muted); font-size: 12px; }
    .menu-wrap .food-detail-placeholder svg { color: var(--m-thumb-icon); width: 38px; height: 38px; }
    .menu-wrap .food-detail-intro { min-width: 0; }
    .menu-wrap .food-detail-eyebrow { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 12px; margin: 0 0 10px; color: var(--m-muted); font-size: 12px; font-weight: 500; }
    .menu-wrap .food-detail-item-id { border-left: 1px solid var(--m-border-2); padding-left: 12px; }
    .menu-wrap .food-detail-intro h2 { margin: 0 0 14px; color: var(--m-text); font-size: clamp(22px, 2vw, 30px); font-weight: 700; letter-spacing: -.5px; line-height: 1.25; overflow-wrap: anywhere; }
    .menu-wrap .food-detail-chips { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .menu-wrap .food-detail-hero .menu-chip { font-size: 12px; padding: 4px 10px; font-weight: 500; }
    .menu-wrap .food-detail-description { white-space: pre-line; overflow-wrap: anywhere; color: var(--m-muted-2); font-size: 14px; line-height: 1.7; margin: 14px 0; }
    .menu-wrap .food-detail-summary { display: flex; flex-wrap: wrap; gap: 8px 18px; color: var(--m-muted); font-size: 12px; }
    .menu-wrap .food-detail-summary span { display: inline-flex; align-items: center; gap: 7px; }
    .menu-wrap .food-detail-summary span::before { content: ''; width: 4px; height: 4px; border-radius: 50%; background: var(--m-accent); }
    .menu-wrap .food-detail-price-card { display: flex; flex-direction: column; align-items: flex-start; justify-content: center; padding: 20px; background: var(--m-accent-soft); border: 1px solid var(--m-border); border-radius: 14px; min-width: 0; }
    .menu-wrap .food-detail-label { display: block; color: var(--m-muted-2); font-size: 12px; font-weight: 500; }
    .menu-wrap .food-detail-price { display: block; color: var(--m-accent); font-size: 30px; font-weight: 700; letter-spacing: -.6px; line-height: 1.4; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; max-width: 100%; }
    .menu-wrap .food-detail-tax { color: var(--m-muted); font-size: 12px; margin-top: 3px; }
    .menu-wrap .food-detail-edit { width: 100%; min-height: 42px; box-sizing: border-box; padding: 10px 14px; margin-top: 18px; font-size: 13px; white-space: nowrap; }
    .menu-wrap .food-detail-nav { display: flex; gap: 4px; padding: 6px; margin: 0 0 22px; overflow-x: auto; border: 1px solid var(--m-border); border-radius: 14px; background: var(--m-surface); scrollbar-width: thin; scrollbar-color: var(--m-border-2) transparent; }
    .menu-wrap .food-detail-nav a { display: inline-flex; align-items: center; justify-content: center; gap: 8px; flex: 1 0 auto; padding: 11px 16px; border-radius: 9px; color: var(--m-label); font-size: 13px; font-weight: 600; line-height: 1.4; text-decoration: none; white-space: nowrap; }
    .menu-wrap .food-detail-nav a:hover, .menu-wrap .food-detail-nav a:focus-visible { background: var(--m-accent-soft); color: var(--m-accent); }
    .menu-wrap .food-detail-nav a > span { font-size: 11px; font-weight: 500; color: var(--m-muted-2); background: var(--m-chip); border-radius: 6px; padding: 2px 6px; }
    .menu-wrap .food-detail-layout { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 22px; align-items: start; }
    .menu-wrap .food-detail-main, .menu-wrap .food-detail-sidebar { min-width: 0; }
    .menu-wrap .food-detail-sidebar { position: sticky; top: 90px; }
    .menu-wrap .food-detail-section { padding: 24px; border-radius: 16px; margin-bottom: 22px; scroll-margin-top: 90px; }
    .menu-wrap .food-detail-section .menu-toolbar { padding-bottom: 16px; margin-bottom: 18px; border-bottom: 1px solid var(--m-border); }
    .menu-wrap .food-detail-section .menu-toolbar h2 { display: flex; align-items: center; gap: 8px; font-size: 17px; font-weight: 700; line-height: 1.5; }
    .menu-wrap .food-detail-section .menu-toolbar p { margin-top: 5px; font-size: 13px; line-height: 1.6; }
    .menu-wrap .food-detail-section h3 { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; margin: 20px 0 10px; }
    .menu-wrap .food-detail-section > .menu-toolbar + h3 { margin-top: 0; }
    .menu-wrap .food-detail-overview-grid { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr); gap: 24px; padding-top: 6px; border-top: 1px solid var(--m-border); }
    .menu-wrap .food-detail-label-group { padding-left: 24px; border-left: 1px solid var(--m-border); }
    .menu-wrap .food-detail-empty { color: var(--m-muted); font-size: 13px; line-height: 1.7; margin: 10px 0; }
    .menu-wrap .food-detail-related { display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; padding: 14px; background: var(--m-surface-2); border: 1px solid var(--m-border); border-radius: 10px; margin-bottom: 10px; font-size: 13px; }
    .menu-wrap .food-detail-related > div { min-width: 0; }
    .menu-wrap .food-detail-related > .menu-chip { flex-shrink: 0; }
    .menu-wrap .food-detail-related strong { font-weight: 600; overflow-wrap: anywhere; }
    .menu-wrap .food-detail-related small, .menu-wrap .food-detail-table small { display: block; color: var(--m-muted); font-size: 12px; line-height: 1.6; margin-top: 4px; }
    .menu-wrap .food-detail-table-wrap { max-width: 100%; overflow-x: auto; border: 1px solid var(--m-border); border-radius: 10px; }
    .menu-wrap .food-detail-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; line-height: 1.6; }
    .menu-wrap .food-detail-table th { background: var(--m-surface-2); color: var(--m-muted-2); font-size: 12px; font-weight: 600; white-space: nowrap; }
    .menu-wrap .food-detail-table th, .menu-wrap .food-detail-table td { padding: 14px 16px; vertical-align: middle; border-bottom: 1px solid var(--m-border); }
    .menu-wrap .food-detail-table td { min-width: 100px; overflow-wrap: anywhere; }
    .menu-wrap .food-detail-table td strong { font-weight: 600; }
    .menu-wrap .food-detail-table tr:last-child td { border-bottom: 0; }
    .menu-wrap .food-detail-serving-table td:nth-child(4), .menu-wrap .food-detail-serving-table td:nth-child(5) { text-align: right; font-weight: 600; font-variant-numeric: tabular-nums; }
    .menu-wrap .food-detail-serving-table th:nth-child(4), .menu-wrap .food-detail-serving-table th:nth-child(5) { text-align: right; }
    .menu-wrap .food-detail-facts { margin: 0; font-size: 13px; line-height: 1.6; }
    .menu-wrap .food-detail-facts > div { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 16px; border-bottom: 1px solid var(--m-border); padding: 12px 0; }
    .menu-wrap .food-detail-facts > div:first-child { padding-top: 0; }
    .menu-wrap .food-detail-facts > div:last-child { border-bottom: 0; padding-bottom: 0; }
    .menu-wrap .food-detail-facts dt { color: var(--m-muted); }
    .menu-wrap .food-detail-facts dd { margin: 0; text-align: right; color: var(--m-label); font-weight: 500; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; min-width: 0; }
    .menu-wrap .food-detail-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 14px; }
    .menu-wrap .food-detail-gallery img { display: block; width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 10px; border: 1px solid var(--m-border); }
    @media (max-width: 1200px) {
        .menu-wrap .food-detail-hero { grid-template-columns: 150px minmax(0, 1fr) 190px; gap: 20px; }
        .menu-wrap .food-detail-cover { height: 170px; }
        .menu-wrap .food-detail-price-card { padding: 16px; }
        .menu-wrap .food-detail-layout { grid-template-columns: minmax(0, 1fr) 280px; }
    }
    @media (max-width: 1000px) {
        .menu-wrap .food-detail-hero { grid-template-columns: 150px minmax(0, 1fr); align-items: start; }
        .menu-wrap .food-detail-price-card { grid-column: 1 / -1; display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 2px 20px; }
        .menu-wrap .food-detail-price-card .food-detail-label, .menu-wrap .food-detail-price-card .food-detail-price, .menu-wrap .food-detail-price-card .food-detail-tax { grid-column: 1; }
        .menu-wrap .food-detail-edit { grid-column: 2; grid-row: 1 / 4; align-self: center; margin: 0; width: auto; }
        .menu-wrap .food-detail-layout { grid-template-columns: minmax(0, 1fr); }
        .menu-wrap .food-detail-sidebar { position: static; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 22px; }
        .menu-wrap .food-detail-sidebar .food-detail-section { margin-bottom: 0; }
    }
    @media (max-width: 640px) {
        .menu-wrap .food-detail-hero { grid-template-columns: 88px minmax(0, 1fr); gap: 18px 14px; padding: 18px; }
        .menu-wrap .food-detail-cover { height: 100px; border-radius: 10px; }
        .menu-wrap .food-detail-placeholder { font-size: 10px; gap: 8px; text-align: center; }
        .menu-wrap .food-detail-placeholder svg { width: 28px; height: 28px; }
        .menu-wrap .food-detail-intro h2 { font-size: 21px; margin-bottom: 10px; }
        .menu-wrap .food-detail-eyebrow { font-size: 11px; margin-bottom: 8px; }
        .menu-wrap .food-detail-item-id { border: 0; padding: 0; }
        .menu-wrap .food-detail-hero .menu-chip { font-size: 10px; padding: 3px 7px; }
        .menu-wrap .food-detail-description { font-size: 13px; }
        .menu-wrap .food-detail-summary { font-size: 11px; gap: 6px 12px; }
        .menu-wrap .food-detail-price { font-size: 26px; }
        .menu-wrap .food-detail-price-card { padding: 14px; gap: 2px 12px; }
        .menu-wrap .food-detail-edit { padding: 10px 12px; font-size: 12px; }
        .menu-wrap .food-detail-nav a { padding: 10px 12px; font-size: 12px; }
        .menu-wrap .food-detail-section { padding: 18px; }
        .menu-wrap .food-detail-sidebar, .menu-wrap .food-detail-overview-grid { grid-template-columns: minmax(0, 1fr); gap: 18px; }
        .menu-wrap .food-detail-label-group { padding: 0; border-left: 0; border-top: 1px solid var(--m-border); }
    }
</style>
