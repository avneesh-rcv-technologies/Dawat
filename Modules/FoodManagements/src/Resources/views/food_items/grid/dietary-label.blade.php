@if ($label)
    @php
        $aliases = ['shield-check' => 'verified_user', 'sparkles' => 'auto_awesome', 'leaf' => 'eco', 'wheat' => 'grain', 'milk-off' => 'format_color_reset'];
        $icon = $aliases[$label->icon ?? ''] ?? $label->icon;
    @endphp
    <span class="inline-flex items-center gap-2">
        @if ($icon)
            @include('dietarymanagment::labels.partials.icon', ['icon' => $icon])
        @endif
        <span>{{ $label->name }}</span>
    </span>
@else
    <span>—</span>
@endif
