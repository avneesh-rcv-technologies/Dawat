<!-- Filter Sidebar (positioned outside main flow) -->
@php
    $activeFilterCount = collect((array) request()->input('filters', []))
        ->except('all')
        ->filter(fn ($value) => collect(\Illuminate\Support\Arr::flatten((array) $value))->contains(fn ($item) => $item !== null && $item !== ''))
        ->count();
    $resetQuery = request()->query();
    unset($resetQuery['filters']);
    if (isset($resetQuery['pagination']) && is_array($resetQuery['pagination'])) {
        unset($resetQuery['pagination']['page']);
    }
    $resetUrl = url()->current() . (count($resetQuery) ? '?' . http_build_query($resetQuery) : '');
@endphp

<aside id="filterSidebar" role="dialog" aria-modal="true" aria-labelledby="filterTitle" aria-hidden="true" inert
    class="filter-drawer fixed inset-y-0 right-0 z-50 w-96 transform translate-x-full border-l border-[var(--color-border-light)] bg-white shadow-2xl transition-transform duration-300 ease-in-out">
    <form method="GET" action="{{ url()->current() }}" class="filter-drawer-form" id="filterForm">
        <div class="filter-drawer-header">
            <div>
                <div class="flex items-center gap-2">
                    <h3 id="filterTitle" class="text-xl font-semibold text-[var(--color-text-primary)]">Filters</h3>
                    @if($activeFilterCount > 0)
                        <span class="filter-count-badge" aria-label="{{ $activeFilterCount }} active filters">{{ $activeFilterCount }}</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-slate-500">Narrow the results using the options below.</p>
            </div>
            <button type="button" id="closeFilter" aria-label="Close filters"
                class="filter-close-button">
                <span class="material-icons-outlined" aria-hidden="true">close</span>
            </button>
        </div>
        <div class="filter-drawer-body">
            @foreach($data['columns'] as $column)
                @if($column['filterable'])
                    <section class="filter-field">
                        <div class="filter-field-label">{{ $column['label'] }}</div>

                        @if($column['filterable_type'] === 'dropdown' && !empty($column['filterable_options']))
                            @if($column['allow_multiple_values'])
                                @php
                                    $selectedValues = array_map('strval', (array) request()->input('filters.' . $column['index'], []));
                                @endphp
                                <div class="filter-checkbox-list mt-2 max-h-60 overflow-y-auto">
                                    @foreach($column['filterable_options'] as $option)
                                        <div class="filter-checkbox-row">
                                            <input id="filter-{{ $column['index'] }}-{{ $option['value'] }}"
                                                name="filters[{{ $column['index'] }}][]" type="checkbox" value="{{ $option['value'] }}"
                                                @checked(in_array((string) $option['value'], $selectedValues, true))
                                                class="filter-checkbox">
                                            <label for="filter-{{ $column['index'] }}-{{ $option['value'] }}"
                                                class="filter-checkbox-label">
                                                {{ $option['label'] }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <select id="filter-{{ $column['index'] }}" name="filters[{{ $column['index'] }}]" class="input-field">
                                    <option value="">Select {{ $column['label'] }}</option>
                                    @foreach($column['filterable_options'] as $option)
                                        <option value="{{ $option['value'] }}" @selected(request()->has('filters.' . $column['index']) && in_array((string) $option['value'], array_map('strval', (array) request()->input('filters.' . $column['index'])), true))>
                                            {{ $option['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                        @elseif($column['filterable_type'] === 'date_range')
                            <div class="filter-date-range grid grid-cols-1 gap-4" data-date-range>
                                <div>
                                    <label for="filter-{{ $column['index'] }}-from" class="custom-label">From</label>
                                    <input type="date" data-date-from name="filters[{{ $column['index'] }}][0][0]"
                                        id="filter-{{ $column['index'] }}-from" value="{{ request()->input('filters.' . $column['index'] . '.0.0') }}" class="input-field">
                                </div>
                                <div>
                                    <label for="filter-{{ $column['index'] }}-to" class="custom-label">To</label>
                                    <input type="date" data-date-to name="filters[{{ $column['index'] }}][0][1]"
                                        id="filter-{{ $column['index'] }}-to" value="{{ request()->input('filters.' . $column['index'] . '.0.1') }}" class="input-field">
                                </div>
                            </div>

                        @elseif($column['type'] === 'boolean')
                            <div class="filter-checkbox-row">
                                <input id="filter-{{ $column['index'] }}" type="checkbox" name="filters[{{ $column['index'] }}]" value="1"
                                    @if(request()->input('filters.' . $column['index']) == 1) checked @endif
                                    class="filter-checkbox">
                                <label for="filter-{{ $column['index'] }}" class="filter-checkbox-label">{{ $column['label'] }}</label>
                            </div>

                        @else
                            <input id="filter-{{ $column['index'] }}" type="{{ $column['type'] === 'integer' ? 'number' : 'text' }}"
                                name="filters[{{ $column['index'] }}]" value="{{ request()->input('filters.' . $column['index']) }}"
                                class="input-field" placeholder="Enter {{ strtolower($column['label']) }}">
                        @endif
                    </section>
                @endif
            @endforeach
        </div>

        <div class="filter-drawer-footer">
            <a href="{{ $resetUrl }}" class="filter-reset-button">
                Clear filters
            </a>
            <button type="submit" class="filter-apply-button">
                <span>Apply filters</span>
                <span class="material-icons-outlined" aria-hidden="true">arrow_forward</span>
            </button>
        </div>
    </form>
</aside>

<style>
    .filter-toggle-button { display: inline-flex; min-height: 40px; align-items: center; justify-content: center; gap: 8px; padding: 0 12px; border: 1px solid #d6dee7; border-radius: 9px; background: #fff; color: #334155; box-shadow: 0 2px 5px rgb(15 23 42 / 5%); font-size: 13px; font-weight: 600; transition: all .15s ease; }
    .filter-toggle-button:hover { border-color: #b8c4d1; background: #f8fafc; color: #102f40; box-shadow: 0 4px 10px rgb(15 23 42 / 8%); }
    .filter-toggle-icon { width: 18px; height: 18px; flex: 0 0 18px; color: #52677b; }
    .filter-toggle-count { display: inline-flex; min-width: 20px; height: 20px; align-items: center; justify-content: center; padding: 0 5px; border-radius: 999px; background: #e9f7f0; color: #126746; font-size: 10px; font-weight: 700; }
    .filter-drawer { width: min(440px, 100vw); height: 100dvh; background: #fff; }
    .filter-drawer-form { display: flex; height: 100%; flex-direction: column; overflow: hidden; }
    .filter-drawer-header { position: sticky; top: 0; z-index: 2; display: flex; flex-shrink: 0; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 24px; border-bottom: 1px solid #e8edf2; background: #fff; }
    .filter-drawer-header h3 { margin: 0; font-size: 18px; line-height: 1.3; }
    .filter-count-badge { display: inline-flex; min-width: 23px; height: 23px; align-items: center; justify-content: center; padding: 0 6px; border-radius: 999px; background: #e9f7f0; color: #126746; font-size: 11px; font-weight: 700; }
    .filter-close-button { display: grid; width: 38px; height: 38px; flex-shrink: 0; place-items: center; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; color: #64748b; transition: all .15s ease; }
    .filter-close-button:hover { border-color: #cbd5e1; background: #f8fafc; color: #102f40; }
    .filter-drawer-body { flex: 1; overflow-y: auto; padding: 8px 24px 24px; overscroll-behavior: contain; }
    .filter-field { padding: 17px 0; border-bottom: 1px solid #eef2f6; }
    .filter-field:last-child { border-bottom: 0; }
    .filter-field-label { display: block; margin-bottom: 8px; color: #334155; font-size: 13px; font-weight: 650; }
    .filter-field .input-field { width: 100%; min-height: 42px; border-color: #d6dee7; border-radius: 9px; font-size: 13px; }
    .filter-field .input-field:focus { border-color: #31be91; outline: 0; box-shadow: 0 0 0 3px rgb(49 190 145 / 14%); }
    .filter-checkbox-list { display: grid; gap: 2px; padding: 5px; border: 1px solid #edf1f5; border-radius: 10px; background: #fbfcfd; }
    .filter-checkbox-row { display: flex; min-height: 38px; align-items: center; gap: 10px; padding: 6px 9px; border-radius: 7px; }
    .filter-checkbox-row:hover { background: #f1f7f5; }
    /* Override the app-wide checkbox skin so the browser checkmark and custom
       checkmark cannot render on top of one another in the filter drawer. */
    .filter-drawer .filter-checkbox { appearance: none; -webkit-appearance: none; position: relative; width: 18px; height: 18px; flex: 0 0 18px; margin: 0; padding: 0; border: 1.5px solid #aab8c4; border-radius: 5px; background: #fff; box-shadow: none; cursor: pointer; transition: background-color .15s ease, border-color .15s ease; }
    .filter-drawer .filter-checkbox:checked { border-color: #16845b; background: #16845b; }
    .filter-drawer .filter-checkbox:checked::after { content: ''; position: absolute; left: 5px; top: 1px; width: 5px; height: 10px; border: solid #fff; border-width: 0 2px 2px 0; transform: rotate(45deg); animation: none; }
    .filter-drawer .filter-checkbox:focus { outline: none; box-shadow: none; }
    .filter-drawer .filter-checkbox:focus-visible { outline: 3px solid rgb(22 132 91 / 22%); outline-offset: 2px; }
    .filter-checkbox-label { flex: 1; color: #475569; font-size: 13px; line-height: 1.4; cursor: pointer; }
    .filter-date-range { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px !important; }
    .filter-drawer-footer { position: sticky; bottom: 0; z-index: 2; display: grid; flex-shrink: 0; grid-template-columns: 1fr 1.25fr; gap: 12px; padding: 16px 24px max(16px, env(safe-area-inset-bottom)); border-top: 1px solid #e8edf2; background: rgb(255 255 255 / 96%); box-shadow: 0 -5px 16px rgb(15 23 42 / 4%); }
    .filter-reset-button, .filter-apply-button { display: inline-flex; min-height: 44px; align-items: center; justify-content: center; gap: 7px; border-radius: 9px; font-size: 13px; font-weight: 650; text-decoration: none; transition: all .15s ease; }
    .filter-reset-button { border: 1px solid #d6dee7; background: #fff; color: #475569; }
    .filter-reset-button:hover { border-color: #b8c4d1; background: #f8fafc; color: #102f40; }
    .filter-apply-button { border: 1px solid #103647; background: #103647; color: #fff; }
    .filter-apply-button:hover { border-color: #17506a; background: #17506a; }
    .filter-apply-button .material-icons-outlined { font-size: 17px; }
    .filter-drawer :is(button, a, input, select):focus-visible { outline: 3px solid #6ee7b7; outline-offset: 2px; }
    @media (max-width: 480px) { .filter-drawer-header { padding: 17px 18px; } .filter-drawer-body { padding: 6px 18px 18px; } .filter-drawer-footer { padding-right: 18px; padding-left: 18px; } }
    @media (prefers-reduced-motion: reduce) { .filter-close-button, .filter-reset-button, .filter-apply-button { transition: none; } }
</style>
