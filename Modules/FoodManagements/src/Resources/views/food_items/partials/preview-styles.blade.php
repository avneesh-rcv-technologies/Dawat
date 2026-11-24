<style>
    .food-preview { color: #172033; }
    .food-preview .food-preview-back { display: inline-flex; align-items: center; gap: 8px; color: #64748b; font-size: 13px; margin-bottom: 0; text-decoration: none; }
    .food-preview-back svg { width: 16px; height: 16px; flex-shrink: 0; }
    .food-preview .food-preview-back:hover { color: #b8430a; }
    .food-preview .food-preview-header { display: flex; justify-content: space-between; align-items: center; gap: 24px; padding: 24px; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; }
    .food-preview-heading { min-width: 0; }
    .food-preview-eyebrow { color: #64748b; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .07em; margin: 0 0 6px; }
    .food-preview .food-preview-heading h2 { font-size: 25px; font-weight: 700; line-height: 1.3; overflow-wrap: anywhere; margin: 0; }
    .food-preview-restaurant { margin-top: 7px; font-size: 13px; color: #64748b; }
    .food-preview-restaurant a:hover { color: #b8430a; text-decoration: underline; text-underline-offset: 3px; }
    .food-preview-statuses { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
    .food-preview-badge { display: inline-flex; align-items: center; gap: 5px; border-radius: 6px; padding: 4px 9px; font-size: 12px; font-weight: 500; line-height: 1.5; }
    .food-preview-badge.is-active { color: #166534; background: #ecfdf3; }
    .food-preview-badge.is-available { color: #334155; background: #f1f5f9; }
    .food-preview-badge.is-inactive { color: #64748b; background: #f1f5f9; }
    .food-preview-badge > span { font-size: 8px; }
    .food-preview .food-preview-edit { display: inline-flex; align-items: center; justify-content: center; gap: 8px; flex-shrink: 0; min-height: 44px; padding: 11px 20px; border: 1px solid #e0530b; border-radius: 8px; background: #ff6210; color: #fff; font-size: 14px; font-weight: 600; line-height: 20px; text-decoration: none; box-shadow: 0 1px 2px #17203312; transition: background-color .15s, box-shadow .15s; }
    .food-preview .food-preview-edit svg { width: 18px; height: 18px; display: block; flex-shrink: 0; }
    .food-preview .food-preview-edit:hover { background: #e0530b; box-shadow: 0 3px 8px #e0530b26; }
    .food-preview a:focus-visible { outline: 2px solid #e0530b; outline-offset: 4px; border-radius: 4px; }
    .food-preview-overview { display: grid; grid-template-columns: minmax(240px, 1fr) minmax(0, 2fr); gap: 24px; align-items: start; }
    .food-preview .food-preview-photo { display: flex; align-items: center; justify-content: center; min-height: 280px; aspect-ratio: 4 / 3; background: #f8fafc; }
    .food-preview-main-image { width: 100%; height: 100%; aspect-ratio: 4 / 3; object-fit: cover; }
    .food-preview-photo [hidden] { display: none !important; }
    .food-preview-image-action { display: inline-block; color: #b8430a; font-size: 12px; margin-top: 12px; text-decoration: underline; text-underline-offset: 3px; }
    .food-preview .food-preview-options { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 320px), 1fr)); gap: 20px; }
    .food-preview .food-preview-option { background: #fff; border-radius: 10px; padding: 20px; }
    .food-preview-option h4 { overflow-wrap: anywhere; }
    .food-preview-option dd { font-variant-numeric: tabular-nums; text-align: right; }
    .food-preview-original-price { color: #64748b; text-decoration: line-through; }
    .food-preview .food-preview-price-total { display: flex; justify-content: space-between; gap: 16px; align-items: center; border-top: 1px solid #e5e7eb; background: #fff4ee; padding: 14px; border-radius: 6px; margin-top: 16px; font-weight: 700; }
    .food-preview-price-total small { display: block; color: #876959; font-size: 11px; font-weight: 400; margin-top: 3px; }
    .food-preview-price-total dd { font-size: 20px; color: #a43c0c; white-space: nowrap; }
    @media (max-width: 900px) {
        .food-preview-overview { grid-template-columns: minmax(0, 1fr); }
        .food-preview-gallery { max-width: 440px; width: 100%; }
    }
    @media (max-width: 640px) {
        .food-preview .food-preview-header { flex-direction: column; align-items: stretch; gap: 20px; padding: 20px; }
        .food-preview .food-preview-heading h2 { font-size: 22px; }
        .food-preview .food-preview-edit { width: 100%; }
        .food-preview .food-preview-photo { min-height: 220px; }
        .food-preview-price-total dd { font-size: 18px; }
    }
</style>
