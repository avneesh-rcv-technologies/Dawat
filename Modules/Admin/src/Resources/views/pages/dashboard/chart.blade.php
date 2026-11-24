@php
    $activeSeries = $series[0];
    $values = array_map(fn ($point) => (float) ($point[$activeSeries['key']] ?? 0), $points);
    $lowest = min(0, min($values));
    $highest = max($values);
    if ($highest == 0 && $lowest == 0) $highest = 1;
    if (empty($chartCurrency)) {
        $axisMin = 0; $axisMax = max(4, (int) ceil($highest / 4) * 4);
    } else {
        $magnitude = max(abs($lowest), abs($highest), 0.01);
        $step = pow(10, floor(log10($magnitude)));
        $axisMin = floor($lowest / $step) * $step;
        $axisMax = max($step, ceil($highest / $step) * $step);
    }
    $coordinates = []; $pointCount = count($points);
    foreach ($values as $i => $value) {
        $coordinates[] = [52 + ($pointCount > 1 ? $i / ($pointCount - 1) * 658 : 329), 213 - ($value - $axisMin) / ($axisMax - $axisMin) * 166];
    }
    $linePath = implode(' ', array_map(fn ($point, $i) => ($i ? 'L' : 'M').implode(' ', $point), $coordinates, array_keys($coordinates)));
    $zeroY = 213 - (0 - $axisMin) / ($axisMax - $axisMin) * 166;
    $payload = ['points' => $points, 'series' => $series, 'currency' => $chartCurrency ?? null];
@endphp
<div class="ad-interactive-chart" data-dashboard-chart="{{ json_encode($payload) }}">
    <div class="ad-chart-controls" role="group" aria-label="{{ $chartTitle }} series">
        @foreach($series as $index => $item)
            <button type="button" data-chart-series="{{ $item['key'] }}" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}" class="{{ $index === 0 ? 'is-active' : '' }}" style="--series-color:{{ $item['color'] }}"><i></i>{{ $item['label'] }}</button>
        @endforeach
    </div>
    <div class="ad-chart-value"><strong data-chart-total title="{{ $activeSeries['full'] }}">{{ $activeSeries['short'] }}</strong><span data-chart-caption>{{ $activeSeries['label'] }}</span><span class="ad-chart-unit">{{ ['day'=>'Daily','week'=>'Weekly','month'=>'Monthly'][$chartUnit] }} view</span></div>
    <div class="ad-chart-canvas">
        <svg class="ad-explorer-svg" viewBox="0 0 744 255" role="img" tabindex="0" aria-label="{{ $chartTitle }}. Use left and right arrow keys to explore values.">
            <title>{{ $chartTitle }}</title>
            <defs><linearGradient id="{{ $chartId }}-fill" x1="0" y1="0" x2="0" y2="1"><stop data-chart-gradient offset="0%" stop-color="{{ $activeSeries['color'] }}" stop-opacity=".23"/><stop data-chart-gradient offset="100%" stop-color="{{ $activeSeries['color'] }}" stop-opacity=".01"/></linearGradient></defs>
            @for($i = 0; $i <= 4; $i++)
                <line data-chart-grid x1="52" x2="710" y1="{{ 213-$i*41.5 }}" y2="{{ 213-$i*41.5 }}" class="ad-explorer-grid"/>
                <text data-chart-y-label x="40" y="{{ 217-$i*41.5 }}" text-anchor="end">{{ !empty($chartCurrency) && abs($axisMin + ($axisMax-$axisMin)*$i/4) < 1000 ? number_format($axisMin + ($axisMax-$axisMin)*$i/4, $chartCurrency['decimals']) : \Modules\Admin\Support\DashboardNumber::compact($axisMin + ($axisMax-$axisMin)*$i/4) }}</text>
            @endfor
            <path data-chart-area d="M{{ $coordinates[0][0] }} {{ $zeroY }} {{ preg_replace('/^M/', 'L', $linePath) }} L{{ $coordinates[$pointCount-1][0] }} {{ $zeroY }} Z" fill="url(#{{ $chartId }}-fill)"/>
            <path data-chart-line d="{{ $linePath }}" fill="none" stroke="{{ $activeSeries['color'] }}" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"/>
            <line data-chart-guide x1="52" x2="52" y1="40" y2="213" stroke="#9aa4b3" stroke-dasharray="4 4" visibility="hidden"/>
            <circle data-chart-dot cx="52" cy="213" r="5" fill="{{ $activeSeries['color'] }}" stroke="#fff" stroke-width="3" visibility="hidden"/>
            @foreach($points as $i => $point)
                <text data-chart-x-index="{{ $i }}" x="{{ $coordinates[$i][0] }}" y="244" text-anchor="middle" @if($pointCount > 6 && $i !== 0 && $i !== $pointCount-1 && $i % max(1,(int)ceil($pointCount/5)) !== 0) style="display:none" @endif>{{ $point['label'] }}</text>
            @endforeach
        </svg>
        <div class="ad-chart-tooltip" role="tooltip" hidden><strong data-chart-tooltip-date></strong><div data-chart-tooltip-values></div></div>
    </div>
    <div class="ad-chart-bottom"><span data-chart-empty @if(array_sum(array_map('abs',$values)) > 0) hidden @endif>No activity for this series in the selected period.</span><span class="ad-chart-instruction">Hover to inspect · Arrow keys to explore</span></div>
</div>
