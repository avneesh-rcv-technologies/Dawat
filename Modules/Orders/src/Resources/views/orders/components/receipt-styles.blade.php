<style>
    .order-receipt { color: #172033; }
    .order-receipt .receipt-detail-link { color: #a43c0c; text-decoration: underline; text-underline-offset: 3px; overflow-wrap: anywhere; }
    .order-receipt .receipt-detail-link:hover { color: #e0530b; }
    .order-receipt .receipt-detail-link:focus-visible { outline: 2px solid #e0530b; outline-offset: 4px; border-radius: 2px; }
    .order-receipt .receipt-detail-link i { font-size: 11px; margin-left: 4px; }
    .order-receipt .receipt-item { border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; padding: 20px; background: #fff; }
    .order-receipt .receipt-item-heading { display: flex; gap: 16px; align-items: flex-start; }
    .order-receipt .receipt-image { width: 72px; height: 72px; flex-shrink: 0; object-fit: cover; border-radius: 10px; }
    .order-receipt .receipt-placeholder { display: flex; align-items: center; justify-content: center; background: #fff4ee; color: #c24c12; font-size: 24px; }
    .order-receipt .receipt-item-title { flex: 1; min-width: 0; overflow-wrap: anywhere; }
    .order-receipt .receipt-item-title h3 { font-size: 17px; line-height: 1.4; font-weight: 700; margin: 2px 0 6px; }
    .order-receipt .receipt-categories { display: flex; flex-wrap: wrap; gap: 6px 12px; color: #687386; font-size: 12px; margin-bottom: 4px; }
    .order-receipt .receipt-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .order-receipt .receipt-tags span { padding: 4px 9px; border-radius: 6px; background: #fff4ee; color: #a43c0c; font-size: 12px; font-weight: 600; }
    .order-receipt .receipt-tags .receipt-type { background: #f1f5f9; color: #475569; }
    .order-receipt .receipt-item-amount { display: flex; flex-direction: column; gap: 4px; text-align: right; flex-shrink: 0; }
    .order-receipt .receipt-item-amount strong { font-size: 19px; font-weight: 700; font-variant-numeric: tabular-nums; }
    .order-receipt .receipt-label { font-size: 11px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #64748b; }
    .order-receipt .receipt-muted { font-size: 12px; color: #64748b; }
    .order-receipt .receipt-serving-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px 24px; background: #f8fafc; border: 1px solid #edf0f4; border-radius: 8px; padding: 16px; margin-top: 20px; }
    .order-receipt .receipt-serving-grid dt, .order-receipt .receipt-delivery-details dt { color: #64748b; font-size: 12px; margin-bottom: 4px; }
    .order-receipt .receipt-serving-grid dd { font-size: 14px; font-weight: 600; overflow-wrap: anywhere; }
    .order-receipt .receipt-item-pricing { margin-top: 18px; }
    .order-receipt .receipt-rows { margin: 8px 0 0; font-size: 14px; }
    .order-receipt .receipt-rows > div { display: flex; justify-content: space-between; align-items: baseline; gap: 20px; padding: 7px 0; }
    .order-receipt .receipt-rows dt { color: #596579; }
    .order-receipt .receipt-rows dd { font-weight: 600; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .order-receipt .receipt-rows .receipt-row-total { border-top: 1px solid #e5e7eb; margin-top: 8px; padding-top: 12px; font-weight: 700; }
    .order-receipt .receipt-row-total dt { color: #172033; }
    .order-receipt .receipt-saving, .order-receipt .receipt-saving dt { color: #15803d; }
    .order-receipt .receipt-footnote { color: #64748b; font-size: 12px; margin-top: 12px; line-height: 1.6; }
    .order-receipt .receipt-instructions { display: flex; gap: 10px; background: #fffbeb; border-left: 3px solid #f5c45b; border-radius: 4px; padding: 12px; margin-top: 16px; color: #78521d; font-size: 12px; overflow-wrap: anywhere; }
    .order-receipt .receipt-instructions i { margin-top: 3px; }
    .order-receipt .receipt-instructions p { margin-top: 4px; }
    .order-receipt .receipt-summary-heading { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 16px; }
    .order-receipt .receipt-summary-heading > span { font-size: 12px; font-weight: 600; color: #64748b; border: 1px solid #e5e7eb; padding: 4px 8px; border-radius: 5px; }
    .order-receipt .receipt-coupon { display: inline-block; font-size: 11px; border: 1px dashed #86b89a; padding: 2px 6px; margin-left: 6px; border-radius: 4px; }
    .order-receipt .receipt-grand-total { display: flex; justify-content: space-between; align-items: center; gap: 16px; background: #fff4ee; border-radius: 8px; padding: 18px; margin-top: 18px; }
    .order-receipt .receipt-grand-total span { font-size: 16px; font-weight: 700; }
    .order-receipt .receipt-grand-total p { color: #876959; font-size: 12px; margin-top: 4px; }
    .order-receipt .receipt-grand-total strong { font-size: 23px; font-weight: 700; color: #a43c0c; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .order-receipt .receipt-delivery-details { display: flex; flex-wrap: wrap; gap: 16px 32px; border-top: 1px solid #edf0f4; padding-top: 18px; margin-top: 20px; }
    .order-receipt .receipt-delivery-details dd { font-size: 13px; font-weight: 600; }
    .order-receipt .receipt-refund-notice { border: 1px solid; border-radius: 10px; padding: 14px; margin-bottom: 14px; font-size: 13px; line-height: 1.7; }
    .order-receipt .receipt-refund-heading { display: flex; align-items: center; gap: 8px; }
    .order-receipt .receipt-refund-heading strong { font-size: 13px; font-weight: 600; }
    .order-receipt .receipt-refund-notice p { margin: 6px 0 10px; }
    .order-receipt .receipt-refund-meta { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 6px 12px; font-size: 12px; }
    .order-receipt .receipt-refund-meta span { font-weight: 600; font-variant-numeric: tabular-nums; }
    .order-receipt .receipt-refund-link { color: inherit; font-weight: 600; text-decoration: underline; text-underline-offset: 3px; overflow-wrap: anywhere; }
    .order-receipt .receipt-refund-link:focus-visible { outline: 2px solid currentColor; outline-offset: 3px; border-radius: 2px; }
    .order-receipt .receipt-refund-link i { font-size: 10px; margin-left: 3px; }
    @media (max-width: 640px) {
        .order-receipt .receipt-item { padding: 14px; }
        .order-receipt .receipt-item-heading { flex-wrap: wrap; gap: 12px; }
        .order-receipt .receipt-image { width: 52px; height: 52px; }
        .order-receipt .receipt-item-title { flex-basis: calc(100% - 64px); }
        .order-receipt .receipt-item-amount { width: 100%; text-align: left; border-top: 1px solid #edf0f4; padding-top: 12px; }
        .order-receipt .receipt-serving-grid { padding: 12px; gap: 14px; }
        .order-receipt .receipt-grand-total { flex-wrap: wrap; padding: 14px; }
    }

    .order-receipt .receipt-image-shell { position:relative;width:72px;height:72px;flex:none;border-radius:10px;overflow:hidden;background:#fff4ee; }
    .order-receipt .receipt-image-fallback { position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#cc651f;font-size:22px; }
    .order-receipt .receipt-image-shell .receipt-image { position:absolute;inset:0;width:100%;height:100%;border-radius:inherit;background:#fff4ee; }
    .order-receipt .receipt-image-shell img[hidden] { display:none; }
    .order-detail-view { --receipt-border:#e5e9f0;--receipt-ink:#172033;--receipt-accent:#f56316;max-width:1480px; }
    .order-detail-view .receipt-back { display:inline-flex;align-items:center;gap:8px;color:#c64d0b;font-size:12px;font-weight:600;margin:0 0 16px; }
    .order-detail-view .receipt-back i { font-size:11px; }
    .order-detail-view .receipt-detail-link { color:#c64d0b;text-decoration:none;font-weight:500; }
    .order-detail-view .receipt-detail-link:hover { color:#ab3b03;text-decoration:underline; }
    .order-detail-view .receipt-order-header,.order-detail-view .receipt-main-column > .bg-white,.order-detail-view .receipt-sidebar-column > .bg-white { border:1px solid var(--receipt-border);border-radius:14px;background:#fff;box-shadow:0 1px 2px #17203305;min-width:0; }
    .order-detail-view .receipt-order-header { padding:24px;margin-bottom:24px; }
    .order-detail-view .receipt-order-header > div { flex-direction:row;align-items:center;justify-content:space-between;gap:24px; }
    .order-detail-view .receipt-order-title-line { flex-wrap:wrap;gap:14px;align-items:center;margin-bottom:12px; }
    .order-detail-view .receipt-order-title-line > div { min-width:0; }
    .order-detail-view .receipt-order-eyebrow { font-size:10px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:#7b8798;margin:0 0 6px; }
    .order-detail-view h1 { font-size:23px;letter-spacing:-.03em;line-height:1.3;overflow-wrap:anywhere; }
    .order-detail-view .receipt-status-badge { display:inline-flex;align-items:center;font-size:11px;font-weight:600;border-radius:6px;padding:5px 9px;line-height:1.3; }
    .order-detail-view .receipt-order-meta { font-size:12px;color:#69778b;gap:16px;align-items:center; }
    .order-detail-view .receipt-order-meta > span { gap:5px; }
    .order-detail-view .receipt-order-meta > span > i { color:#8995a6;margin:0; }
    .order-detail-view .receipt-header-total { flex:none;text-align:right;padding-left:28px;border-left:1px solid var(--receipt-border); }
    .order-detail-view .receipt-header-total > span { display:block;font-size:11px;color:#718096;margin-bottom:6px; }
    .order-detail-view .receipt-header-total strong { font-size:26px;font-weight:700;letter-spacing:-.035em;font-variant-numeric:tabular-nums;color:var(--receipt-ink);white-space:nowrap; }
    .order-detail-view .receipt-order-grid { display:grid;grid-template-columns:minmax(0,1fr) 400px;gap:24px;align-items:start; }
    .order-detail-view .receipt-main-column,.order-detail-view .receipt-sidebar-column { display:grid;grid-template-columns:minmax(0,1fr);grid-column:auto;gap:24px;align-content:start;min-width:0; }
    .order-detail-view .receipt-main-column > *,.order-detail-view .receipt-sidebar-column > * { margin-block:0;min-width:0; }
    .order-detail-view .receipt-main-column > .p-6,.order-detail-view .receipt-sidebar-column > .p-6 { padding:24px; }
    .order-detail-view .receipt-main-column h2,.order-detail-view .receipt-main-column h3,.order-detail-view .receipt-sidebar-column h3 { font-size:16px;font-weight:650;letter-spacing:-.015em; }
    .order-detail-view .receipt-sidebar-column > .bg-white > h3 { display:flex;align-items:center;gap:10px;margin-bottom:18px; }
    .order-detail-view .receipt-sidebar-column > .bg-white > h3 > i,.order-detail-view .receipt-panel-header h2 > i { display:flex;align-items:center;justify-content:center;width:32px;height:32px;background:#fff5ee;border:1px solid #ffeadf;color:#e45c12;font-size:13px;border-radius:8px;margin:0;flex:none; }
    .order-detail-view .receipt-panel-header { padding:16px 24px;border-bottom-color:var(--receipt-border); }
    .order-detail-view .receipt-panel-header h2 { gap:10px; }
    .order-detail-view .receipt-count { display:inline-flex;align-items:center;justify-content:center;min-width:23px;height:23px;padding:0 7px;background:#f1f4f8;color:#6a778b;font-size:11px;font-weight:600;border-radius:6px;margin-left:2px; }
    .order-detail-view .receipt-items-panel > .p-6 { padding:24px; }
    .order-detail-view .receipt-items-panel .receipt-item { border:0;padding:0;background:none;border-radius:0;overflow:visible; }
    .order-detail-view .receipt-items-panel .receipt-item + .receipt-item { border-top:1px solid var(--receipt-border);padding-top:24px;margin-top:24px; }
    .order-detail-view .receipt-item-heading { align-items:center;gap:16px; }
    .order-detail-view .receipt-image-shell { width:64px;height:64px;border:1px solid #f8e9dd;border-radius:10px; }
    .order-detail-view .receipt-item-title h3 { font-size:17px;font-weight:650;margin:3px 0; }
    .order-detail-view .receipt-item-title h3 a { color:#28354b; }
    .order-detail-view .receipt-item-title h3 a i { color:#c66a30;font-size:10px; }
    .order-detail-view .receipt-categories { font-size:11px;color:#8791a1;margin-bottom:4px; }
    .order-detail-view .receipt-item-amount { gap:5px; }
    .order-detail-view .receipt-item-amount strong { font-size:21px;letter-spacing:-.025em;color:var(--receipt-ink); }
    .order-detail-view .receipt-label { font-size:10px;letter-spacing:.07em;color:#7a8799; }
    .order-detail-view .receipt-muted { font-size:11px;color:#7d899b; }
    .order-detail-view .receipt-tags { margin-top:6px; }
    .order-detail-view .receipt-tags span { font-size:10px;line-height:1.5;padding:3px 7px; }
    .order-detail-view .receipt-serving-grid { background:#f8f9fc;border:1px solid #edf0f5;border-radius:9px;margin-top:20px;padding:15px 16px;gap:16px 24px; }
    .order-detail-view .receipt-serving-grid dt { font-size:11px;color:#7a8799;margin-bottom:5px; }
    .order-detail-view .receipt-serving-grid dd { font-size:13px;font-weight:600;color:#354158;margin:0; }
    .order-detail-view .receipt-item-pricing { margin-top:20px; }
    .order-detail-view .receipt-rows { font-size:12px;margin-top:10px; }
    .order-detail-view .receipt-rows > div { gap:16px;padding:8px 0; }
    .order-detail-view .receipt-rows dt { color:#748195;line-height:1.6; }
    .order-detail-view .receipt-rows dd { font-size:13px;font-weight:500;color:#354158; }
    .order-detail-view .receipt-rows .receipt-row-total { margin-top:10px;padding-top:14px;border-color:var(--receipt-border); }
    .order-detail-view .receipt-row-total dt,.order-detail-view .receipt-row-total dd { font-weight:650;color:var(--receipt-ink); }
    .order-detail-view .receipt-rows .receipt-saving dd,.order-detail-view .receipt-rows .receipt-saving dt { color:#15803d; }
    .order-detail-view .receipt-summary-heading { margin-bottom:14px; }
    .order-detail-view .receipt-summary-heading > span { font-size:10px;padding:4px 7px;border-radius:6px;border-color:var(--receipt-border); }
    .order-detail-view .receipt-grand-total { margin-top:18px;padding:17px 18px;border:1px solid #ffeadf;background:#fff6f0;border-radius:10px; }
    .order-detail-view .receipt-grand-total span { font-size:14px;font-weight:650; }
    .order-detail-view .receipt-grand-total p { font-size:11px;color:#9c806e; }
    .order-detail-view .receipt-grand-total strong { font-size:24px;color:#bb4d13; }
    .order-detail-view .receipt-footnote { font-size:11px;line-height:1.8;color:#8590a1; }
    .order-detail-view .receipt-panel-description { color:#7a8799;font-size:11px;line-height:1.7;margin:-7px 0 18px; }
    .order-detail-view .receipt-field-label { display:block;color:#46536a;font-size:12px;font-weight:600;margin:0 0 8px; }
    .order-detail-view .receipt-sidebar-column select,.order-detail-view .receipt-sidebar-column textarea { width:100%;box-sizing:border-box;border:1px solid #d9dfe8;border-radius:8px;background:#fff;padding:11px 12px;font:inherit;font-size:12px;line-height:1.6;color:#354158; }
    .order-detail-view .receipt-sidebar-column select { min-height:44px; }
    .order-detail-view .receipt-sidebar-column textarea { resize:vertical; }
    .order-detail-view .receipt-sidebar-column textarea::placeholder { color:#99a3b3; }
    .order-detail-view .receipt-sidebar-column select:focus-visible,.order-detail-view .receipt-sidebar-column textarea:focus-visible { outline:2px solid #fed0b5;border-color:#f49a64;outline-offset:1px; }
    .order-detail-view .receipt-action-button { display:flex;align-items:center;justify-content:center;gap:8px;min-height:44px;width:100%;border:0;border-radius:8px;padding:11px 14px;background:var(--receipt-accent);color:white;font:inherit;font-size:12px;font-weight:600;line-height:1.5;cursor:pointer; }
    .order-detail-view .receipt-action-button:hover { background:#e55a0e; }
    .order-detail-view .receipt-action-button:focus-visible { outline:3px solid #fed0b5;outline-offset:3px; }
    .order-detail-view .receipt-action-button:disabled { opacity:.5;cursor:not-allowed; }
    .order-detail-view .receipt-action-button i { margin:0;font-size:12px; }
    .order-detail-view .receipt-status-panel > .text-xs { color:#8591a2;font-size:10px;line-height:1.7; }
    .order-detail-view .receipt-status-facts { border-top:1px solid var(--receipt-border);margin-top:18px;padding-top:5px; }
    .order-detail-view .receipt-status-facts > div { display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.15fr);gap:12px;align-items:start;padding:12px 0;border-bottom:1px solid #f0f2f6; }
    .order-detail-view .receipt-status-facts > div:last-child { padding-bottom:0;border:0; }
    .order-detail-view .receipt-status-facts dt { font-size:11px;color:#7a8799; }
    .order-detail-view .receipt-status-facts dd { font-size:11px;font-weight:500;line-height:1.7;color:#46536a;text-align:right;margin:0;overflow-wrap:anywhere; }
    .order-detail-view .receipt-status-reason { background:#f8f9fc;border-radius:8px;padding:12px 14px;margin-top:16px; }
    .order-detail-view .receipt-status-reason > span { font-size:10px;color:#8590a1; }
    .order-detail-view .receipt-status-reason p { font-size:12px;line-height:1.7;color:#46536a;margin-top:4px;overflow-wrap:anywhere; }
    .order-detail-view .receipt-payment-panel > .mb-3 { margin-bottom:14px; }
    .order-detail-view .receipt-payment-panel > .mb-3 .text-sm { font-size:12px;color:#758296;font-weight:400; }
    .order-detail-view .receipt-payment-panel .rounded-full { border-radius:6px;font-size:10px;line-height:1.4;padding:5px 8px; }
    .order-detail-view .receipt-cod-collection { border-top:1px solid var(--receipt-border);padding-top:20px;margin:20px 0 0; }
    .order-detail-view .receipt-cod-heading { display:flex;align-items:flex-start;gap:10px;margin-bottom:16px; }
    .order-detail-view .receipt-cod-heading > span { display:flex;align-items:center;justify-content:center;width:30px;height:30px;flex:none;border-radius:8px;background:#eff9f3;color:#178155; }
    .order-detail-view .receipt-cod-heading h4 { font-size:13px;color:#354158;font-weight:650;margin:0 0 4px; }
    .order-detail-view .receipt-cod-heading p { font-size:11px;line-height:1.75;color:#8590a1;margin:0; }
    .order-detail-view .receipt-cod-amount { display:flex;align-items:center;justify-content:space-between;gap:12px;background:#f3faf6;border:1px solid #dcefe3;border-radius:9px;padding:14px;margin-bottom:18px; }
    .order-detail-view .receipt-cod-amount > span { font-size:11px;color:#61816e; }
    .order-detail-view .receipt-cod-amount small { display:block;font-size:10px;color:#8fa496;margin-top:4px; }
    .order-detail-view .receipt-cod-amount strong { font-size:22px;font-weight:650;letter-spacing:-.025em;color:#21734d;font-variant-numeric:tabular-nums;white-space:nowrap; }
    .order-detail-view .receipt-optional { color:#97a1b0;font-size:10px;font-weight:400;margin-left:4px; }
    .order-detail-view .receipt-collection-notes { display:block;min-height:78px;margin-bottom:14px; }
    .order-detail-view .receipt-action-help { display:flex;align-items:baseline;gap:5px;font-size:10px;line-height:1.8;color:#8994a5;margin-top:10px; }
    .order-detail-view .receipt-action-help i { font-size:10px; }
    .order-detail-view .receipt-payment-detail { box-shadow:none;border-color:#edf0f5;background:#f8f9fc;margin-top:18px; }
    .order-detail-view .receipt-payment-detail .text-sm { font-size:12px; }
    .order-detail-view .receipt-payment-detail .text-xs { font-size:10px; }
    .order-detail-view #payment-detail-action > p { margin-top:16px;color:#8994a5;font-size:11px; }
    .order-detail-view .receipt-contact-details > div { display:grid;grid-template-columns:65px minmax(0,1fr);gap:12px;align-items:start;line-height:1.75;font-size:12px; }
    .order-detail-view .receipt-contact-details > div > :first-child { color:#7a8799;font-weight:400; }
    .order-detail-view .receipt-contact-details > div > :last-child { min-width:0;overflow-wrap:anywhere; }
    .order-detail-view .receipt-payout-panel dl > div { padding:10px 0;margin:0;border-bottom:1px solid #f0f2f6;line-height:1.6;font-size:11px;gap:14px; }
    .order-detail-view .receipt-payout-panel dl > div:last-child { border:0; }
    .order-detail-view .receipt-payout-panel dd { font-variant-numeric:tabular-nums; }
    .order-detail-view .receipt-payout-panel .receipt-payout-total { border:0;border-radius:8px;background:#fff6f0;padding:12px;margin:12px 0; }
    .order-detail-view .receipt-payout-panel .receipt-detail-link { font-size:12px; }
    .order-detail-view .receipt-payout-panel > .text-sm { font-size:12px;line-height:1.75;color:#8590a1; }
    @media(max-width:1150px) { .order-detail-view .receipt-order-grid { grid-template-columns:minmax(0,1fr); } }
    @media(max-width:640px) {
        .order-receipt .receipt-image-shell { width:52px;height:52px; }
        .order-detail-view .receipt-order-header,.order-detail-view .receipt-main-column > .p-6,.order-detail-view .receipt-sidebar-column > .p-6 { padding:20px; }
        .order-detail-view .receipt-order-header > div { align-items:flex-start;flex-direction:column;gap:18px; }
        .order-detail-view h1 { font-size:19px; }
        .order-detail-view .receipt-order-title-line { gap:10px; }
        .order-detail-view .receipt-order-meta { font-size:11px;gap:10px 14px; }
        .order-detail-view .receipt-header-total { display:flex;justify-content:space-between;align-items:center;width:100%;text-align:left;border-left:0;border-top:1px solid var(--receipt-border);padding:15px 0 0;gap:12px; }
        .order-detail-view .receipt-header-total > span { margin:0; }
        .order-detail-view .receipt-header-total strong { font-size:23px; }
        .order-detail-view .receipt-panel-header { padding:15px 20px; }
        .order-detail-view .receipt-items-panel > .p-6 { padding:20px; }
        .order-detail-view .receipt-item-title { flex-basis:calc(100% - 70px); }
        .order-detail-view .receipt-item-heading { gap:12px; }
        .order-detail-view .receipt-item-amount { flex-direction:row;flex-wrap:wrap;align-items:center;justify-content:space-between;text-align:left;gap:8px;border-top:0;padding-top:8px; }
        .order-detail-view .receipt-item-amount strong { font-size:20px; }
        .order-detail-view .receipt-item-amount .receipt-muted { width:100%; }
        .order-detail-view .receipt-serving-grid { gap:14px;padding:13px;margin-top:16px; }
        .order-detail-view .receipt-rows > div { gap:12px; }
        .order-detail-view .receipt-rows dt { min-width:0; }
        .order-detail-view .receipt-rows dd { white-space:normal;text-align:right;overflow-wrap:anywhere; }
        .order-detail-view .receipt-grand-total { gap:12px; }
        .order-detail-view .receipt-grand-total strong { font-size:22px; }
    }
</style>
