@if (!empty($hours))
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-3">
    @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
    @php
        $raw = $hours[$day] ?? null;

        // Normalize into array of segments
        if (is_array($raw) && count($raw) && array_keys($raw) !== range(0, count($raw) - 1)) {
            $segments = [$raw];
        } elseif (is_array($raw)) {
            $segments = $raw;
        } else {
            $segments = [['open' => false, 'from' => '09:00', 'to' => '22:00']];
        }

        // Determine if any segment is open and prepare display text
        $isOpen = false;
        $displayParts = [];
        foreach ($segments as $seg) {
            $segOpen = isset($seg['open']) ? (bool)$seg['open'] : false;
            if ($segOpen) {
                $isOpen = true;
                $from = $seg['from'] ?? '';
                $to = $seg['to'] ?? '';
                if ($from && $to) {
                    $displayParts[] = "$from - $to";
                }
            }
        }
        $displayText = count($displayParts) ? implode(', ', $displayParts) : null;
    @endphp
    <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
        <div class="flex items-center space-x-3">
            <span class="text-sm font-medium text-gray-900 capitalize w-16">{{ $day }}</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                {{ $isOpen ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $isOpen ? 'Open' : 'Closed' }}
            </span>
        </div>
        @if ($isOpen && $displayText)
        <span class="text-xs text-gray-600 font-mono">{{ $displayText }}</span>
        @endif
    </div>
    @endforeach
</div>
@else
<div class="text-center py-8">
    <p class="text-gray-500">No working hours configured</p>
</div>
@endif
