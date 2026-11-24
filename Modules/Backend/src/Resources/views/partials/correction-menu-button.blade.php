@if(($correctionAllowed ?? false) || filled($correctionView ?? null))
<button type="button" class="correction-menu-trigger" aria-label="Actions for {{ $correctionTitle }}"
    aria-haspopup="menu" aria-expanded="false" aria-controls="correction-actions-menu"
    data-correction-menu data-correction-title="{{ $correctionTitle }}"
    data-correction-url="{{ $correctionUrl ?? '' }}" data-correction-step="{{ $correctionStep ?? '' }}"
    data-correction-mode="{{ $correctionMode ?? 'form' }}" data-correction-allowed="{{ ($correctionAllowed ?? false) ? 'true' : 'false' }}"
    data-correction-disabled="{{ ($correctionDisabled ?? false) ? 'true' : 'false' }}"
    data-correction-disabled-note="{{ $correctionDisabledNote ?? 'Available after this step is submitted.' }}"
    data-correction-view="{{ $correctionView ?? '' }}" data-correction-view-label="{{ $correctionViewLabel ?? 'View submission' }}">
    @if(filled($correctionButtonLabel ?? null))
        <span>{{ $correctionButtonLabel }}</span>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
    @else
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
    @endif
</button>
@endif
