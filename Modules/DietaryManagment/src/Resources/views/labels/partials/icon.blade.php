@php
    $icon = trim($icon ?? '');
    // Seeded icon names use aliases; render them with the admin's Material font.
    $icon = [
        'shield-check' => 'verified_user',
        'sparkles' => 'auto_awesome',
        'leaf' => 'eco',
        'wheat' => 'grain',
        'milk-off' => 'format_color_reset',
    ][$icon] ?? $icon;
    $isIconName = preg_match('/^[a-z][a-z0-9_]*$/i', $icon);
    $isImagePath = $icon !== '' && !str_starts_with($icon, '//')
        && !preg_match('/^[a-z][a-z0-9+.-]*:/i', $icon);
    $isImageUrl = preg_match('#^https?://#i', $icon);
@endphp

@if ($isIconName)
    <span class="material-icons-outlined" role="img" aria-label="{{ str_replace('_', ' ', $icon) }}" title="{{ $icon }}">{{ $icon }}</span>
@elseif ($isImagePath || $isImageUrl)
    <img src="{{ $isImageUrl ? $icon : asset($icon) }}" alt="Dietary label icon" class="h-6 w-6 object-contain" width="24" height="24" loading="lazy">
@else
    <span>—</span>
@endif
