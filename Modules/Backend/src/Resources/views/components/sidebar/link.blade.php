@props([
    'href' => '#',
    'icon' => 'dashboard',
    'label' => '',
    'active' => false,
    'badge' => null
    ])

<a href="{{ $href }}" title="{{ $label }}"
    class="sidebar-nav-link {{ $active ? 'active' : '' }}" @if ($active) aria-current="page" @endif>
    <span class="material-icons-outlined sidebar-nav-icon" aria-hidden="true">
        {{ $icon }}
    </span>
    <span class="sidebar-link-label sidebar-collapse-text">{{ $label }}</span>

    @if($badge)
    <span class="sidebar-badge">
        {{ $badge }}
    </span>
    @endif
</a>
