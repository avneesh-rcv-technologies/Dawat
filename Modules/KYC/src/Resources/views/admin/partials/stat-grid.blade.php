@props(['stats' => []])

@if(!empty($stats))
    <div class="mk-stat-grid mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($stats as $stat)
            @include('admin::components.dashboard.stat-card', [
                'label' => $stat['label'] ?? '',
                'value' => $stat['value'] ?? '0',
                'icon' => $stat['icon'] ?? 'insights',
                'tone' => $stat['tone'] ?? 'indigo',
                'hint' => $stat['hint'] ?? '',
            ])
        @endforeach
    </div>
@endif
