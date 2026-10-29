@php
    $dynamicAttributes = collect($calls)
        ->map(fn($value, $key) => $key . '="' . e($value) . '"')
        ->implode(' ');
@endphp

@php
    $customClass = "inline-flex items-center px-4 py-2 border rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 hover:text-$class-300 text-$class-600 bg-$class-100";
@endphp
@if ($as === 'a')
    <a href="{{ $href }}" id="{{ $id }}" name="{{ $name }}" {!! $dynamicAttributes !!} class="{{ $customClass }}">
        @if ($icon)
            {!! $icon !!}
        @endif
        {!! $label !!}
        @if ($badge)
            <span
                class="ml-2 inline-block bg-{{$class ?? 'blue'}}-100 text-{{$class ?? 'blue'}}-600 text-xs px-2 rounded-full">{{ $badge }}</span>
        @endif
    </a>
@elseif($as === 'link')
    <a href="{{ $href }}" id="{{ $id }}" name="{{ $name }}" {!! $dynamicAttributes !!} class="{{ $customClass }}">
        @if ($icon)
            {!! $icon !!}
        @endif
        {!! $label !!}
        @if ($badge)
            <span
                class="ml-2 inline-block bg-{{$class ?? 'blue'}}-100 text-{{$class ?? 'blue'}}-600 text-xs px-2 rounded-full">{{ $badge }}</span>
        @endif
    </a>
@elseif($as === 'general')
    <button type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" {!! $dynamicAttributes !!} class="{{ $customClass }}">
        @if ($icon)
            {!! $icon !!}
        @endif
        {!! $label !!}
        @if ($badge)
            <span class="ml-2 inline-block text-{{$class ?? 'blue'}}-600 text-xs px-2">{{ $badge }}</span>
        @endif
    </button>
@else
    
    <button type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" {!! $dynamicAttributes !!}
        class="{{$class}}">
        @if ($icon)
            {!! $icon !!}
        @endif
        {!! $label !!}
        @if ($badge)
            <span
                class="mx-2 inline-block bg-{{$class ?? 'blue'}}-100 text-{{$class ?? 'blue'}}-600 text-xs px-2 rounded-full">{{ $badge }}</span>
        @endif
    </button>
@endif