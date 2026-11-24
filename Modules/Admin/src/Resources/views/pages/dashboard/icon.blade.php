@php
    $paths = [
        'store' => ['M3 10h18M4 10v11h16V10M3 10l2-7h14l2 7M9 21v-7h6v7', 'M3 10a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0'],
        'clock' => ['M12 8v4l3 2', 'M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0'],
        'document' => ['M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13h8M8 17h5'],
        'check' => ['M22 11.1V12a10 10 0 1 1-5.9-9.1', 'm9 11 3 3L22 4'],
        'users' => ['M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8', 'M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0'],
        'orders' => ['M9 3h6v4H9zM9 5H5v16h14V5h-4M8 12h8M8 16h5'],
        'alert' => ['m10.3 3.9-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3.1l-8-14a2 2 0 0 0-3.4 0ZM12 9v4M12 17h.01'],
        'refund' => ['M3 10h11a6 6 0 0 1 0 12h-3M3 10l5-5M3 10l5 5M16 2h5v5'],
        'calendar' => ['M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2'],
        'star' => ['m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3z'],
        'refresh' => ['M20 7a9 9 0 1 0 1 9M20 2v5h-5'],
        'arrow' => ['M7 17 17 7M7 7h10v10'],
    ];
@endphp
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">@foreach($paths[$name] ?? $paths['orders'] as $path)<path d="{{ $path }}"/>@endforeach</svg>
