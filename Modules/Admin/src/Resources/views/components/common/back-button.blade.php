@props(['route' => '#', 'name' => 'Back'])

<a href="{{ $route }}" class="inline-flex items-center gap-2 text-sm text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50 hover:shadow-sm transition-all">
    <span class="material-icons-outlined">arrow_back</span>
    @if(!empty($name))
        <span class="font-medium">{{ $name }}</span>
    @endif
</a>
