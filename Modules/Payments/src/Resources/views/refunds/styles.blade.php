<style>
.refund-page { color: #111827; max-width: 1440px; margin: 0 auto; }
.refund-page .refund-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 18px; padding: 24px; margin-bottom: 22px; box-shadow: 0 1px 3px #10182808; }
.refund-page .refund-head { border-top: 3px solid #ff6210; margin-top: 16px;  display: flex; flex-wrap: wrap; gap: 16px; justify-content: space-between; align-items: center; margin-bottom: 22px; }
.refund-page h1 { font-size: clamp(20px, 2vw, 25px); overflow-wrap: anywhere; font-weight: 700; margin: 6px 0; }
.refund-page h2 { line-height: 1.4;  font-size: 18px; font-weight: 600; margin-bottom: 16px; }
.refund-page .refund-muted { color: #6b7280; font-size: 13px; line-height: 1.6; }
.refund-page a.refund-link { color: #c2410c; text-decoration: none; font-weight: 600; }
.refund-page a.refund-link:hover { text-decoration: underline; }
.refund-page .refund-badge { display: inline-block; border-radius: 999px; background: #f3f4f6; color: #4b5563; padding: 5px 11px; font-size: 12px; text-transform: capitalize; white-space: nowrap; }
.refund-page .refund-badge[data-status="succeeded"] { background: #ecfdf5; color: #047857; }
.refund-page .refund-badge[data-status="pending"], .refund-page .refund-badge[data-status="processing"] { background: #fff7ed; color: #c2410c; }
.refund-page .refund-badge[data-status="failed"], .refund-page .refund-badge[data-status="rejected"] { background: #fef2f2; color: #b91c1c; }
.refund-page .refund-table-wrap { overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 12px; margin-top: 18px; }
.refund-page .refund-table-wrap table { min-width: 720px; }
.refund-page table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
.refund-page th, .refund-page td { padding: 14px 12px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
.refund-page th { background: #f9fafb; color: #6b7280; font-weight: 600; white-space: nowrap; }
.refund-page td { overflow-wrap: anywhere; }
.refund-page .refund-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(300px, 360px); gap: 22px; align-items: start; }
.refund-page .refund-grid > * { min-width: 0; }
.refund-page dl > div { display: flex; align-items: baseline; gap: 20px; justify-content: space-between; padding: 11px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
.refund-page dt { color: #6b7280; flex: 0 0 38%; }
.refund-page dd { min-width: 0;  margin: 0; text-align: right; overflow-wrap: anywhere; max-width: 60%; }
.refund-page label { display: block; font-size: 13px; font-weight: 600; margin: 16px 0 6px; }
.refund-page input, .refund-page select, .refund-page textarea { box-sizing: border-box; width: 100%; padding: 11px 12px; border: 1px solid #d1d5db; border-radius: 10px; background: white; color: #111827; font: inherit; font-size: 14px; }
.refund-page input:focus, .refund-page select:focus, .refund-page textarea:focus { outline: 2px solid #ff621050; border-color: #ff6210; }
.refund-page .refund-btn { display: inline-flex; justify-content: center; align-items: center; border-radius: 999px; padding: 11px 22px; background: #ff6210; border: none; color: white; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; }
.refund-page .refund-btn:hover { background: #e85a0e; }
.refund-page .refund-btn:focus-visible { outline: 3px solid #ff621050; outline-offset: 3px; }
.refund-page .refund-alert { padding: 14px 18px; border-radius: 12px; background: #fff7ed; color: #9a3412; font-size: 13px; margin-bottom: 18px; }
.refund-page .refund-success { background: #ecfdf5; color: #047857; }
.refund-page .refund-eyebrow { color: #c2410c; text-transform: uppercase; letter-spacing: .1em; font-size: 11px; font-weight: 600; margin-bottom: 8px; }
.refund-page .refund-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); width: 100%; gap: 20px; border-top: 1px solid #f3f4f6; padding-top: 20px; margin-top: 4px; }
.refund-page .refund-summary > div { min-width: 0; display: flex; flex-direction: column; gap: 8px; overflow-wrap: anywhere; }
.refund-page .refund-summary span { font-size: 12px; color: #6b7280; }
.refund-page .refund-summary strong { font-size: clamp(18px, 1.6vw, 23px); font-variant-numeric: tabular-nums; line-height: 1.4; }
.refund-page .refund-summary a { font-size: 14px; }
.refund-page .refund-section-description { margin: -8px 0 12px; }
.refund-page .refund-table-wrap td { line-height: 1.7; }
.refund-page .refund-empty { text-align: center; padding: 28px; color: #6b7280; }
.refund-page .refund-alert { line-height: 1.7; }
.refund-page a:focus-visible { outline: 2px solid #ff6210; outline-offset: 3px; border-radius: 3px; }
.refund-page dl > div:last-child { border-bottom: 0; }
.refund-page aside.refund-card dl > div { flex-wrap: wrap; gap: 6px; }
.refund-page aside.refund-card dd { max-width: 100%; }
@media (max-width: 640px) { .refund-page .refund-summary { grid-template-columns: 1fr; gap: 16px; } .refund-page dl > div { flex-direction: column; gap: 6px; } .refund-page dd { max-width: 100%; text-align: left; } }
@media (max-width: 640px) { .refund-page .refund-card { padding: 18px; } }
@media (max-width: 1000px) { .refund-page .refund-grid { grid-template-columns: minmax(0, 1fr); } }

/* Refund detail page: keep editing controls separate from the saved and projected totals. */
.refund-detail-page { --refund-accent:#f56316; --refund-ink:#172033; --refund-border:#e5e9f0; max-width:1480px; }
.refund-detail-page [hidden] { display:none !important; }
.refund-detail-page .refund-back { display:inline-flex;align-items:center;gap:8px;font-size:13px;margin:0 0 16px; }
.refund-detail-page .refund-card { border-color:var(--refund-border);border-radius:14px;box-shadow:0 1px 2px #17203305;padding:24px;margin:0; }
.refund-detail-page .refund-head { display:block;border-top:1px solid var(--refund-border);margin-bottom:24px; }
.refund-detail-page .refund-header-main { display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:20px; }
.refund-detail-page h1 { color:var(--refund-ink);font-size:22px;font-weight:700;letter-spacing:-.03em;margin:4px 0 7px; }
.refund-detail-page h2 { color:var(--refund-ink);font-size:17px;font-weight:650;line-height:1.4;margin:0 0 5px; }
.refund-detail-page .refund-eyebrow { color:#768194;font-size:10px;font-weight:650;letter-spacing:.12em;margin:0 0 5px; }
.refund-detail-page .refund-muted { color:#647084;font-size:12px;line-height:1.7;margin:0; }
.refund-detail-page .refund-summary { gap:24px;margin:0;padding-top:20px; }
.refund-detail-page .refund-summary > div { gap:5px; }
.refund-detail-page .refund-summary strong { font-size:22px;letter-spacing:-.025em;color:var(--refund-ink); }
.refund-detail-page .refund-summary > div + div { border-left:1px solid var(--refund-border);padding-left:24px; }
.refund-detail-page .refund-summary a { display:inline-flex;align-items:center;gap:7px;line-height:1.6; }
.refund-detail-page .refund-summary a i,.refund-detail-page .refund-detail-list a i,.refund-detail-page .refund-section-link i { font-size:10px; }
.refund-detail-page .refund-detail-grid { display:grid;grid-template-columns:minmax(0,1fr) 420px;gap:24px;align-items:start; }
.refund-detail-page .refund-detail-main { display:grid;grid-template-columns:minmax(0,1fr);gap:24px;min-width:0; }
.refund-detail-page .refund-detail-main > * { min-width:0; }
.refund-detail-page .refund-update-card { min-width:0; }
.refund-detail-page .refund-panel-heading { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:16px; }
.refund-detail-page .refund-panel-heading .refund-eyebrow { margin-bottom:5px; }
.refund-detail-page .refund-badge { display:inline-flex;align-items:center;gap:6px;font-size:11px;line-height:1.4;padding:6px 10px; }
.refund-detail-page .refund-badge::before { content:'';width:5px;height:5px;border-radius:50%;background:currentColor;flex:none; }
.refund-detail-page .refund-badge[data-status=on_hold] { color:#92400e;background:#fffbeb; }
.refund-detail-page .refund-badge[data-status=processed] { color:#047857;background:#ecfdf5; }
.refund-detail-page .refund-type-badge { flex:none;color:#596579;background:#f1f4f8;border:1px solid #e7ebf1;border-radius:6px;padding:5px 9px;font-size:11px;font-weight:600; }
.refund-detail-page .refund-impact-card { border-top:3px solid #f9a066; }
.refund-detail-page .refund-impact-card .refund-panel-heading { margin-bottom:5px; }
.refund-detail-page .refund-impact-totals,.refund-detail-page .refund-current-totals { display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin:20px 0 12px; }
.refund-detail-page .refund-impact-totals > div { border:1px solid var(--refund-border);background:#fafbfd;border-radius:10px;padding:16px 14px;min-width:0; }
.refund-detail-page .refund-impact-totals .refund-impact-customer { border-color:#d6eee2;background:#f2faf6; }
.refund-detail-page .refund-impact-totals span,.refund-detail-page .refund-current-totals span { display:block;font-size:11px;line-height:1.6;color:#637084;margin-bottom:7px; }
.refund-detail-page .refund-impact-totals strong,.refund-detail-page .refund-current-totals strong { display:block;font-size:clamp(17px,1.3vw,22px);line-height:1.4;font-weight:650;letter-spacing:-.025em;font-variant-numeric:tabular-nums;overflow-wrap:anywhere;color:var(--refund-ink); }
.refund-detail-page .refund-impact-customer strong { color:#15804e; }
.refund-detail-page .refund-current-totals { border-bottom:1px solid var(--refund-border);padding-bottom:20px;margin-top:0; }
.refund-detail-page .refund-current-totals > div { min-width:0; }
.refund-detail-page dl.refund-detail-list { margin:0; }
.refund-detail-page dl.refund-detail-list > div { display:grid;grid-template-columns:minmax(120px,1fr) minmax(0,1fr);align-items:start;gap:20px;padding:11px 0;border-bottom:1px solid #f0f2f5;font-size:12px;line-height:1.65; }
.refund-detail-page dl.refund-detail-list > div:last-child { border:0; }
.refund-detail-page dl.refund-detail-list dt { color:#647084;flex:initial; }
.refund-detail-page dl.refund-detail-list dd { color:var(--refund-ink);font-weight:500;text-align:right;max-width:none;margin:0;font-variant-numeric:tabular-nums; }
.refund-detail-page .refund-reference-value { font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:11px;overflow-wrap:anywhere; }
.refund-detail-page .refund-preview-note { display:flex;align-items:flex-start;gap:8px;padding:12px 14px;border-radius:8px;background:#f8f9fc;font-size:11px;line-height:1.75;color:#667286;margin:12px 0 0; }
.refund-detail-page .refund-preview-note i { margin-top:4px;color:#8a94a3; }
.refund-detail-page .refund-recovery-row dt,.refund-detail-page .refund-recovery-row dd { color:#b42318 !important; }
.refund-detail-page dl.refund-fact-grid { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 24px;margin:0 0 16px;padding-bottom:20px;border-bottom:1px solid var(--refund-border); }
.refund-detail-page dl.refund-fact-grid > div { display:block;border:0;padding:0;min-width:0; }
.refund-detail-page .refund-fact-grid dt { font-size:11px;color:#6b7789;margin-bottom:5px; }
.refund-detail-page .refund-fact-grid dd { max-width:none;text-align:left;font-size:13px;font-weight:500;color:var(--refund-ink);margin:0; }
.refund-detail-page .refund-reason { padding:13px 15px;border-radius:8px;background:#f8f9fc;margin-top:16px; }
.refund-detail-page .refund-reason > span { display:block;color:#6b7789;font-size:11px;margin-bottom:4px; }
.refund-detail-page .refund-reason p { font-size:13px;color:#354158;line-height:1.7;margin:0;overflow-wrap:anywhere;white-space:pre-line; }
.refund-detail-page .refund-section-link { display:inline-flex;align-items:center;gap:6px;font-size:12px;margin-top:14px; }
.refund-detail-page .refund-update-heading { display:flex;align-items:center;gap:12px;margin-bottom:18px; }
.refund-detail-page .refund-update-heading h2 { font-size:18px; }
.refund-detail-page .refund-update-icon { display:flex;align-items:center;justify-content:center;flex:none;width:40px;height:40px;border:1px solid #ffeadf;background:#fff5ee;color:#e45c12;border-radius:10px;font-size:15px; }
.refund-detail-page .refund-provider-note { display:flex;align-items:flex-start;gap:8px;padding:12px 14px;background:#f7f9fc;border:1px solid #eef1f5;border-radius:9px;color:#627086;font-size:11px;line-height:1.8;margin:0 0 22px; }
.refund-detail-page .refund-provider-note i { flex:none;color:#8995a6;margin-top:4px; }
.refund-detail-page .refund-form-field { margin-top:20px; }
.refund-detail-page .refund-form-field:first-of-type { margin-top:0; }
.refund-detail-page label { font-size:12px;font-weight:600;color:#354158;margin:0 0 8px; }
.refund-detail-page .refund-field-heading { display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px; }
.refund-detail-page .refund-field-heading label { margin:0; }
.refund-detail-page .refund-required { color:#dc5e26;margin-left:2px; }
.refund-detail-page .refund-optional { color:#8a94a3;font-size:11px;font-weight:400;margin-left:4px; }
.refund-detail-page .refund-text-button { background:none;border:0;padding:0;color:#c64d0b;font:inherit;font-size:11px;font-weight:600;cursor:pointer; }
.refund-detail-page .refund-text-button:focus-visible { outline:2px solid #ffac79;outline-offset:4px;border-radius:3px; }
.refund-detail-page .refund-amount-control { display:flex;align-items:center;border:1px solid #d9dfe8;border-radius:9px;overflow:hidden;background:#fff; }
.refund-detail-page .refund-amount-control > span { padding:0 14px;font-size:12px;font-weight:600;color:#667286;border-right:1px solid #e7ebf1; }
.refund-detail-page .refund-amount-control input { border:0;border-radius:0;padding:13px 14px;font-size:21px;font-weight:600;color:var(--refund-ink);height:56px;box-shadow:none;outline:0;min-width:0; }
.refund-detail-page .refund-amount-control:focus-within { border-color:#f69c67;box-shadow:0 0 0 3px #fff1e8; }
.refund-detail-page input,.refund-detail-page select,.refund-detail-page textarea { border-color:#d9dfe8;border-radius:8px;font-size:12px;line-height:1.6;padding:11px 12px; }
.refund-detail-page input::placeholder,.refund-detail-page textarea::placeholder { color:#99a3b3; }
.refund-detail-page textarea { resize:vertical;min-height:88px; }
.refund-detail-page input.refund-range { display:block;width:100%;height:5px;border:0;border-radius:99px;margin:18px 0 10px;padding:0;appearance:none;-webkit-appearance:none;background:linear-gradient(to right,#f56316 var(--refund-progress,100%),#e7ebf1 var(--refund-progress,100%));cursor:pointer; }
.refund-detail-page input.refund-range::-webkit-slider-thumb { -webkit-appearance:none;width:15px;height:15px;border-radius:50%;background:#f56316;border:2px solid white;box-shadow:0 0 0 1px #f56316; }
.refund-detail-page input.refund-range::-moz-range-thumb { width:12px;height:12px;border-radius:50%;background:#f56316;border:2px solid white;box-shadow:0 0 0 1px #f56316; }
.refund-detail-page .refund-range-labels { display:flex;justify-content:space-between;gap:12px;font-size:10px;color:#7b8798;margin-bottom:9px;font-variant-numeric:tabular-nums; }
.refund-detail-page .refund-field-help { font-size:11px;line-height:1.7;color:#788497;margin:7px 0 0; }
.refund-detail-page #refund-status-help { min-height:36px; }
.refund-detail-page .refund-form-footer { padding-top:18px;margin-top:20px;border-top:1px solid var(--refund-border); }
.refund-detail-page .refund-form-footer .refund-btn { display:flex;align-items:center;gap:8px;width:100%;border-radius:9px;padding:12px 18px;min-height:44px;background:var(--refund-accent);font-size:13px; }
.refund-detail-page .refund-form-footer .refund-field-help { display:flex;align-items:center;justify-content:center;gap:6px;margin-top:12px;text-align:center; }
.refund-detail-page .refund-form-footer .refund-field-help i { font-size:10px; }
.refund-detail-page .refund-table-wrap { margin-top:18px; }
.refund-detail-page .refund-table-wrap table { min-width:680px;font-size:11px; }
.refund-detail-page th { padding:12px;font-size:10px;text-transform:uppercase;letter-spacing:.04em; }
.refund-detail-page td { padding:14px 12px; }
.refund-detail-page .refund-table-wrap .refund-muted { font-size:10px;line-height:1.7; }
.refund-detail-page .refund-history-status { color:#7a8595;margin-bottom:5px; }
.refund-detail-page .refund-history-card .refund-badge { padding:4px 7px;font-size:10px; }
.refund-detail-page .refund-alert ul { margin:8px 0 0;padding-left:18px;list-style:disc; }
@media(min-width:1600px) { .refund-detail-page .refund-update-card { position:sticky;top:96px; } }
@media(max-width:1200px) { .refund-detail-page .refund-detail-grid { grid-template-columns:minmax(0,1fr); } .refund-detail-page .refund-update-card { grid-row:1; } }
@media(max-width:640px) { .refund-detail-page .refund-card { padding:20px; } .refund-detail-page .refund-head { padding:20px; } .refund-detail-page h1 { font-size:18px; } .refund-detail-page .refund-header-main { gap:12px; } .refund-detail-page .refund-summary { gap:14px; } .refund-detail-page .refund-summary > div + div { border-left:0;padding-left:0; } .refund-detail-page .refund-summary strong { font-size:20px; } .refund-detail-page .refund-impact-totals,.refund-detail-page .refund-current-totals { grid-template-columns:minmax(0,1fr);gap:10px; } .refund-detail-page .refund-impact-totals > div { padding:13px 14px; } .refund-detail-page .refund-current-totals { gap:16px; } .refund-detail-page dl.refund-detail-list > div { grid-template-columns:minmax(0,1fr);gap:4px; } .refund-detail-page dl.refund-detail-list dd { text-align:left; } .refund-detail-page .refund-panel-heading { flex-wrap:wrap;gap:10px; } .refund-detail-page .refund-update-heading h2 { font-size:17px; } }
</style>
