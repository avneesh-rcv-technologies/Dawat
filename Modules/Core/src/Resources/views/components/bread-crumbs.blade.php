@php
    // Acceptable $breadcrumbs format: [ ['label' => 'Home', 'url' => '/'], ['label' => 'Section', 'url' => '/section'], ['label' => 'Page'] ]
    $hasBreadcrumbs = !empty($breadcrumbs) && is_array($breadcrumbs);
    $lastCrumb = null;
    if ($hasBreadcrumbs) {
        $lastCrumb = end($breadcrumbs);
    }
@endphp

@if($hasBreadcrumbs)
    <nav aria-label="Breadcrumb" class="gl-breadcrumbs border-b border-gray-200 px-1">
        <ol class="breadcrumb-list breadcrumb flex items-center gap-1 text-sm">
            @foreach($breadcrumbs as $index => $crumb)
                <li class="gl-breadcrumb-item gl-breadcrumb-item-sm">
                    @php $isLast = $index === array_key_last($breadcrumbs); @endphp
                    @if(!$isLast && !empty($crumb['url']))
                        <a href="{{ $crumb['url'] }}" class="text-gray-600 hover:text-gray-900">{{ $crumb['label'] }}</a>
                    @else
                        <span class="text-gray-800 font-medium">{{ $crumb['label'] }}</span>
                    @endif
                </li>                
            @endforeach
        </ol>
    </nav>

    <div class="pt-3 pb-4 px-6 md:px-28">
        <h1 class="text-2xl font-bold text-gray-800">{{ $name ?? ($lastCrumb['label'] ?? '') }}</h1>
    </div>

@else
    <div class="flex items-center pt-8 pb-4 py-28 ">
        @if(!empty($route))
            <a href="{{$route}}" class="mr-4 text-gray-600 hover:text-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>&nbsp;&nbsp;&nbsp;
        @endif
        <h1 class="text-2xl font-bold text-gray-800 px-28">{{$name}}</h1>
    </div>
@endif