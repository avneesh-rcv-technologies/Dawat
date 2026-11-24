@push('styles')
<style>
    .commission-kpis { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px; }
    .commission-kpi { display: flex; flex-direction: column; gap: 16px; min-width: 0; padding: 20px; border: 1px solid #e5e7eb; border-radius: 14px; background: #fff; box-shadow: 0 1px 3px #10182808; }
    .commission-kpi-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .commission-kpi-head h2 { margin: 0; color: #6b7280; font-size: 13px; line-height: 1.5; font-weight: 500; }
    .commission-kpi-icon { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 36px; width: 36px; height: 36px; border-radius: 10px; }
    .commission-kpi-icon .material-icons-outlined { font-size: 20px; }
    .commission-kpi-values { display: flex; flex-direction: column; gap: 8px; margin-top: -5px; }
    .commission-kpi-value { display: flex; flex-wrap: nowrap; align-items: baseline; min-width: 0; gap: 6px; color: #111827; }
    .commission-kpi-value strong { font-size: clamp(20px, 1.6vw, 25px); font-weight: 700; line-height: 1.3; letter-spacing: -.4px; font-variant-numeric: tabular-nums; white-space: nowrap; min-width: 0; }
    .commission-kpi-value span { color: #6b7280; font-size: 11px; font-weight: 600; }
    .commission-kpi-help { margin: auto 0 0; color: #9ca3af; font-size: 12px; line-height: 1.6; }
    @media (max-width: 1280px) { .commission-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 900px) { .commission-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 540px) { .commission-kpis { grid-template-columns: minmax(0, 1fr); gap: 12px; } .commission-kpi { gap: 12px; padding: 18px; } }
    .commission-kpi-value[title] { position: relative; }
    .commission-kpi-value[title]:focus::after { content: attr(title); position: absolute; top: 100%; left: 0; z-index: 20; width: max-content; max-width: 260px; padding: 6px 10px; border-radius: 6px; background: #111827; color: #fff; font-size: 12px; font-weight: 400; white-space: normal; overflow-wrap: anywhere; }
</style>
@endpush
@php
    $compactValue = static function ($value, $decimals = 2) {
        $value = (float) $value;
        $units = ['', 'K', 'M', 'B', 'T', 'Qa', 'Qi'];
        $unit = 0;
        while (abs($value) >= 1000 && $unit < count($units) - 1) {
            $value /= 1000;
            $unit++;
        }
        // Promote rounded 999.999K to 1M instead of displaying 1,000K.
        if ($unit > 0 && abs(round($value, 2)) >= 1000 && $unit < count($units) - 1) {
            $value /= 1000;
            $unit++;
        }
        return $unit === 0 ? number_format($value, $decimals) : rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . $units[$unit];
    };

@endphp
<section class="commission-kpis" aria-label="{{ $overviewLabel }}">
    @foreach ($cards as $card)
        <article class="commission-kpi">
            <div class="commission-kpi-head">
                <h2>{{ $card['label'] }}</h2>
                <span class="commission-kpi-icon" style="background: {{ $card['background'] }}; color: {{ $card['color'] }};" aria-hidden="true"><span class="material-icons-outlined">{{ $card['icon'] }}</span></span>
            </div>
            <div class="commission-kpi-values">
                @if ($card['field'] === null)
                    <div class="commission-kpi-value" tabindex="0" title="{{ number_format($summary['total_records']) }}" aria-label="{{ number_format($summary['total_records']) }} records"><strong>{{ $compactValue($summary['total_records'], 0) }}</strong></div>
                @else
                    @forelse ($summary['currencies'] as $total)
                        <div class="commission-kpi-value" tabindex="0" title="{{ fn_format_currency_amount((float) $total->{$card['field']}, $total->currency) }}" aria-label="{{ fn_format_currency_amount((float) $total->{$card['field']}, $total->currency) }}"><span>{{ fn_get_currency_symbol($total->currency) ?? $total->currency }}</span><strong>{{ $compactValue($total->{$card['field']}) }}</strong></div>
                    @empty
                        <div class="commission-kpi-value"><strong>0.00</strong></div>
                    @endforelse
                @endif
            </div>
            <p class="commission-kpi-help">{{ $card['help'] }}</p>
        </article>
    @endforeach
</section>
