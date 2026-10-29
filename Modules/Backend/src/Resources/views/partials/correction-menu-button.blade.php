@if(($correctionAllowed ?? false) || filled($correctionView ?? null))
<button type="button" class="correction-menu-trigger" aria-label="Actions for {{ $correctionTitle }}"
    aria-haspopup="menu" aria-expanded="false" aria-controls="correction-actions-menu"
    data-correction-menu data-correction-title="{{ $correctionTitle }}"
    data-correction-url="{{ $correctionUrl ?? '' }}" data-correction-step="{{ $correctionStep ?? '' }}"
    data-correction-mode="{{ $correctionMode ?? 'form' }}" data-correction-allowed="{{ ($correctionAllowed ?? false) ? 'true' : 'false' }}"
    data-correction-disabled="{{ ($correctionDisabled ?? false) ? 'true' : 'false' }}"
    data-correction-view="{{ $correctionView ?? '' }}" data-correction-view-label="{{ $correctionViewLabel ?? 'View submission' }}">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
</button>
@endif
