@props([
    'href' => '#',
    'icon' => 'dashboard',
    'label' => '',
    'active' => false,
    'badge' => null
    ])

@php
$classes = $active
    ? 'active text-[var(--sidebar-text-color)]'
    : 'text-[var(--sidebar-text-color)]';
@endphp

<a href="{{ $href }}" title="{{ $label }}"
    class="flex items-center px-1 py-2 rounded-md transition-colors duration-200 text-xs font-medium {{ $classes }}" 
    id="super-sidebar-collapse-icon"
    style="{{ $active ? 'background-color: var(--sidebar-bg-active);' : '' }}"
    onmouseover="if(!this.classList.contains('active')) this.style.backgroundColor = 'var(--sidebar-bg-hover)'"
    onmouseout="if(!this.classList.contains('active')) this.style.backgroundColor = 'transparent'">

    <span class="flex-1 sidebar-collapse-text">{!! $label !!}</span>

    @if($badge)
    <span class="ml-auto bg-[var(--color-primary)]  text-xs font-semibold px-2 py-0.5 rounded-full">
        {{ $badge }}
    </span>
    @endif
</a>