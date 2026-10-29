@once
    <style>
        .dataview-table { width: 100%; max-width: 100%; min-width: 0; overflow: hidden; border: 1px solid #dce4eb; border-radius: 14px; background: #fff; box-shadow: 0 4px 14px rgb(15 23 42 / 5%); }
        .dataview-table-scroll { width: 100%; max-width: 100%; min-width: 0; overflow: auto; overscroll-behavior-x: contain; scrollbar-color: #cbd5e1 #f8fafc; scrollbar-width: thin; }
        .dataview-table-scroll:focus-visible { outline: 3px solid #93c5fd; outline-offset: -3px; }
        .dataview-table-scroll table { width: 100%; min-width: 760px; border-collapse: separate; border-spacing: 0; }
        .dataview-table-scroll thead { background: #f8fafc; }
        .dataview-table-scroll th { position: sticky; top: 0; z-index: 1; white-space: nowrap; padding: 14px 16px !important; border-bottom: 1px solid #dce4eb; color: #526273 !important; font-size: 10px; font-weight: 700 !important; letter-spacing: .075em; }
        .dataview-table-scroll th:first-child { width: 48px; }
        .dataview-table-scroll th a { gap: 4px; text-decoration: none; }
        .dataview-table-scroll th a:hover { color: #103647; }
        .dataview-table-scroll tbody tr { transition: background-color .15s ease; }
        .dataview-table-scroll tbody tr:hover { background: #f7fbff; }
        .dataview-table-scroll tbody tr:last-child td { border-bottom: 0; }
        .dataview-table-scroll tbody td { padding: 13px 16px; border-bottom: 1px solid #edf1f5; }
        .dataview-table-scroll tbody td:first-child { width: 48px; }
        .dataview-table-scroll .dataview-table-cell { min-width: 112px; max-width: 340px; white-space: normal; overflow-wrap: anywhere; vertical-align: middle; color: #243746; line-height: 1.45; }
        .dataview-table-scroll tbody tr:hover .dataview-table-cell { color: #102f40; }
        .dataview-table-actions { display: flex; align-items: center; flex-wrap: nowrap; gap: 6px; }
        .dataview-table-actions > * { flex-shrink: 0; }
        .dataview-table-actions a, .dataview-table-actions button { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; margin: 0 !important; border: 1px solid transparent; border-radius: 9px; color: #475569 !important; transition: color .15s ease, background-color .15s ease, border-color .15s ease; }
        .dataview-table-actions a:hover, .dataview-table-actions button:hover { border-color: #dbeafe; background: #eff6ff; color: #1d4ed8 !important; }
        .dataview-table-actions svg { width: 19px; height: 19px; }
        .dataview-table input[type="checkbox"] { flex-shrink: 0; width: 16px; height: 16px; accent-color: #103647; cursor: pointer; }
        .dataview-mobile-card { min-width: 0; padding: 16px !important; border-color: #dce4eb !important; border-radius: 12px !important; box-shadow: 0 2px 8px rgb(15 23 42 / 4%) !important; }
        .dataview-mobile-heading { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .dataview-mobile-heading > div { min-width: 0; overflow-wrap: anywhere; color: #102f40; }
        .dataview-mobile-fields { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 3fr); gap: 10px 16px; margin-top: 16px; padding-top: 14px; border-top: 1px solid #edf1f5; }
        .dataview-mobile-fields > * { min-width: 0; margin: 0; overflow-wrap: anywhere; }
        .dataview-mobile-fields dt { color: #64748b; font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
        .dataview-mobile-fields dd { color: #243746; font-size: 13px; }
        .dataview-mobile-fields img { max-width: 100%; height: auto; }
        .dataview-mobile-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-top: 14px; padding-top: 12px; border-top: 1px solid #edf1f5; }
        .dataview-mobile-actions a, .dataview-mobile-actions button { display: inline-flex; align-items: center; justify-content: center; min-width: 44px; min-height: 44px; border: 1px solid #e2e8f0; border-radius: 9px; background: #fff; }
        #all { min-height: 42px; border-color: #d5dee8; border-radius: 10px; box-shadow: 0 1px 2px rgb(15 23 42 / 3%); }
        #all:focus { border-color: #60a5fa; box-shadow: 0 0 0 3px rgb(96 165 250 / 18%); }
        #filterToggle { position: relative; display: inline-flex; width: 42px; min-width: 42px; height: 42px; min-height: 42px; align-items: center; justify-content: center; gap: 0; padding: 0; border-color: #dce4eb !important; border-radius: 10px !important; box-shadow: 0 2px 5px rgb(15 23 42 / 7%) !important; transition: background-color .15s ease, color .15s ease; }
        #filterToggle .filter-toggle-icon { display: block; width: 18px; height: 18px; flex: 0 0 18px; color: #52677b; }
        #filterToggle .filter-toggle-count { position: absolute; top: -6px; right: -6px; min-width: 18px; height: 18px; padding: 0 4px; border: 2px solid #fff; background: #126746; color: #fff; font-size: 9px; }
        #filterToggle:hover { background: #f1f7fb; color: #103647; }
        .dataview-table ~ div.flex.items-center.justify-between { flex-wrap: wrap; gap: 14px; padding: 14px 4px !important; color: #526273; }
        .dataview-table ~ div.flex.items-center.justify-between nav { gap: 6px !important; }
        .dataview-table ~ div.flex.items-center.justify-between nav a { display: inline-flex; align-items: center; justify-content: center; min-width: 38px; min-height: 38px; border-color: #dce4eb; border-radius: 9px; transition: background-color .15s ease, color .15s ease, border-color .15s ease; }
        .dataview-table ~ div.flex.items-center.justify-between nav a:hover:not(.cursor-not-allowed) { border-color: #b8c9d8; background: #f5f9fc; color: #103647; }
        .dataview-table ~ div.flex.items-center.justify-between select { min-height: 38px; border-color: #dce4eb; border-radius: 9px; }
        @media (max-width: 767px) { .dataview-table { border-radius: 12px; } .dataview-mobile-fields { grid-template-columns: minmax(0, 1fr) minmax(0, 1.35fr); } }
        @media (max-width: 420px) { .dataview-mobile-fields { grid-template-columns: minmax(0, 1fr); gap: 5px; } .dataview-mobile-fields dd { margin-bottom: 8px; } .dataview-table ~ div.flex.items-center.justify-between { align-items: flex-start; flex-direction: column; } }
    </style>
@endonce
<div class="dataview-table bg-[var(--color-white)] shadow">
    <div class="dataview-table-scroll hidden md:block" tabindex="0" role="region" aria-label="Data table, scroll horizontally to see all columns">
        <table>
            <thead class="">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 pb-6 text-left text-xs font-medium text-[var(--color-text-secondary)] uppercase tracking-wider">
                        <input type="checkbox" id="select-all" aria-label="Select all rows" onclick="toggleSelectAll(this)">
                    </th>
                    @foreach($data['columns'] as $column)
                    @if($column['visibility'])
                    <th scope="col"
                        class="px-6 py-3 pb-6 text-left text-xs font-semibold text-[#3A3A49] uppercase tracking-wider">
                        @if($column['sortable'])
                        <div class="flex items-center">
                            @php
                            $currentSort = request('sort', []);
                            $isSortedColumn = isset($currentSort['column']) && $currentSort['column'] === $column['index'];
                            $nextOrder = $isSortedColumn && ($currentSort['order'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ request()->fullUrlWithQuery(['sort[column]' => $column['index'], 'sort[order]' => $nextOrder]) }}"
                                class="hover:text-[var(--color-text-primary)] flex items-center">
                                {{ $column['label'] }}

                                @if($isSortedColumn)
                                <span class="ml-1">
                                    @if(($currentSort['order'] ?? 'asc') === 'asc')
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6l-4 4h8l-4-4z" />
                                    </svg>
                                    @else
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 14l4-4H6l4 4z" />
                                    </svg>
                                    @endif
                                </span>
                                @endif
                            </a>
                        </div>
                        @else
                        {{ $column['label'] }}
                        @endif
                    </th>
                    @endif
                    @endforeach
                    <th scope="col"
                        class="px-6 py-3 pb-6 text-left text-xs font-semibold text-[#3A3A49] uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white-600">
                @forelse($data['records'] as $record)
                <tr style="border-top: 1px solid #E9EFF4;margin-top:2px !important; border-radius: 5px !important;">
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if(!empty($record->{$data['meta']['primary_column']}))
                        <input type="checkbox" class="row-checkbox" aria-label="Select row {{ $record->{$data['meta']['primary_column']} }}"
                            value="{{ $record->{$data['meta']['primary_column']} }}"
                            onclick="updateMassActionsVisibility()">
                        @endif
                    </td>

                    @foreach($data['columns'] as $column)
                    @if($column['visibility'])
                    <td class="dataview-table-cell px-6 py-4 text-sm text-[var(--color-text-primary)]">
                        {!! $record->{$column['index']} !!}
                    </td>
                    @endif
                    @endforeach
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--color-text-primary)]">
                        <div class="dataview-table-actions">
                        @foreach($record->actions as $action)

                        @if($action['method'] === 'DELETE')
                        <button type="button" aria-label="{{ $action['title'] ?? 'Delete record' }}" title="{{ $action['title'] ?? 'Delete record' }}" onclick="openDeleteModal({{ json_encode($action['url']) }})"
                            class=" mr-3 cursor-pointer">

                            <span class=""><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M19.3248 9.46777C19.3248 9.46777 18.7818 16.2028 18.4668 19.0398C18.3168 20.3948 17.4798 21.1888 16.1088 21.2138C13.4998 21.2608 10.8878 21.2638 8.27979 21.2088C6.96079 21.1818 6.13779 20.3778 5.99079 19.0468C5.67379 16.1848 5.13379 9.46777 5.13379 9.46777" stroke="#130F26" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M20.708 6.23926H3.75" stroke="#130F26" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M17.4406 6.23949C16.6556 6.23949 15.9796 5.68449 15.8256 4.91549L15.5826 3.69949C15.4326 3.13849 14.9246 2.75049 14.3456 2.75049H10.1126C9.53358 2.75049 9.02558 3.13849 8.87558 3.69949L8.63258 4.91549C8.47858 5.68449 7.80258 6.23949 7.01758 6.23949" stroke="#130F26" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </button>

                        @elseif($action['method'] === 'POST')
                        <form action="{{ $action['url'] }}" method="POST" class="inline-block mr-3">
                            @csrf
                            <button type="submit" class="text-[var(--color-gray-800)] hover:text-[var(--color-gray-800)]/80 cursor-pointer" title="{{ $action['title'] }}">
                                @if(!empty($action['icon']))
                                    <span class="material-icons-outlined">{{ $action['icon'] }}</span>
                                @else
                                    {{ $action['title'] }}
                                @endif
                            </button>
                        </form>

                        @else

                        @php
                        $editSvg = <<<SVG
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.492 2.78906H7.753C4.678 2.78906 2.75 4.96606 2.75 8.04806V16.3621C2.75 19.4441 4.669 21.6211 7.753 21.6211H16.577C19.662 21.6211 21.581 19.4441 21.581 16.3621V12.3341" stroke="#130F26" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.82763 10.921L16.3006 3.44799C17.2316 2.51799 18.7406 2.51799 19.6716 3.44799L20.8886 4.66499C21.8196 5.59599 21.8196 7.10599 20.8886 8.03599L13.3796 15.545C12.9726 15.952 12.4206 16.181 11.8446 16.181H8.09863L8.19263 12.401C8.20663 11.845 8.43363 11.315 8.82763 10.921Z" stroke="#130F26" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M15.165 4.60254L19.731 9.16854" stroke="#130F26" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            SVG;
                            @endphp

                            @php
                            $viewSvg = <<<SVG
                                <svg width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21.2746 11.5C21.2746 12.8482 18.9151 19.9267 11.4996 19.9267C4.08409 19.9267 1.72461 12.8482 1.72461 11.5C1.72461 10.1517 4.08409 3.07324 11.4996 3.07324C18.9151 3.07324 21.2746 10.1517 21.2746 11.5Z" stroke="#130F26" stroke-width="1.725" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M11.4994 14.5336C13.1748 14.5336 14.533 13.1754 14.533 11.5C14.533 9.82456 13.1748 8.46636 11.4994 8.46636C9.824 8.46636 8.4658 9.82456 8.4658 11.5C8.4658 13.1754 9.824 14.5336 11.4994 14.5336Z" stroke="#130F26" stroke-width="1.725" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                SVG;
                                @endphp

                                @php
                                    // Decide icon HTML
                                    $iconHtml = '';


                                    if (!empty($action['icon'])) {
                                        if ($action['icon'] == 'edit') {
                                            $iconHtml = $editSvg;
                                        } elseif ($action['icon'] == 'visibility') {
                                            $iconHtml = $viewSvg;
                                        } else {
                                            // Default material icon
                                            $iconHtml = "<span class='material-icons-outlined'>{$action['icon']}</span>";
                                        }
                                    } else {
                                        $iconHtml = $action['title'];
                                    }
                                @endphp

                                @if(!empty($action['is_popup']))
                                <x-modal
                                    buttonText="{!! $iconHtml !!}"
                                    modalTitle="{{ $action['title'] }}"
                                    id="{{ $action['index'] }}"
                                    ajaxUrl="{{ $action['url'] }}"
                                    color="text-[var(--color-gray-800)] hover:text-[var(--color-gray-800)]/80 flex justify-center items-center"
                                    modalSize="2xl" />
                                @else
                                <a href="{{ $action['url'] }}"
                                    class="text-[var(--color-gray-800)] hover:text-[var(--color-gray-800)]/80 mr-3"
                                    title="{{ $action['title'] }}">
                                    {!! $iconHtml !!}
                                </a>
                                @endif



                                @endif
                                @endforeach
                        </div>
                    </td>

                    {{-- <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                            @include('dataview::components.dataView.components.actions-dropdown', ['actions' => $record->actions, "id" => $record->{$data['meta']['primary_column']}])
                        </td> --}}
                </tr>
                @empty
                    <tr><td colspan="{{ collect($data['columns'])->where('visibility', true)->count() + 2 }}" class="px-6 py-10 text-center text-sm text-[var(--color-text-secondary)]">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile: stacked cards (visible on small screens) -->
    <div class="p-3 md:hidden space-y-3">
        @forelse($data['records'] as $record)
        <div class="dataview-mobile-card bg-white border border-[var(--color-border-light)] rounded-lg shadow-sm p-3">
            <div class="dataview-mobile-heading">
                @if(!empty($record->{$data['meta']['primary_column']}))
                    <input type="checkbox" class="row-checkbox" aria-label="Select row {{ $record->{$data['meta']['primary_column']} }}" value="{{ $record->{$data['meta']['primary_column']} }}" onclick="updateMassActionsVisibility()">
                @endif
                @php
                    $headingColumn = collect($data['columns'])->first(fn ($column) => $column['visibility']);
                    $headingValue = $headingColumn ? strip_tags($record->{$headingColumn['index']} ?? '') : ($record->{$data['meta']['primary_column']} ?? '');
                @endphp
                <div class="text-sm font-semibold text-[var(--color-text-primary)]">{{ Str::limit($headingValue, 80) }}</div>
            </div>
            <dl class="dataview-mobile-fields">
                @foreach($data['columns'] as $column)
                    @if($column['visibility'])
                        <dt class="text-xs text-[var(--color-text-secondary)]">{{ $column['label'] }}</dt>
                        <dd class="text-sm text-[var(--color-text-primary)]">{!! $record->{$column['index']} !!}</dd>
                    @endif
                @endforeach
            </dl>
            @if(count($record->actions))
                <div class="dataview-mobile-actions">
                        @foreach($record->actions as $action)

                            @if($action['method'] === 'DELETE')
                                <button type="button" aria-label="{{ $action['title'] ?? 'Delete record' }}" title="{{ $action['title'] ?? 'Delete record' }}" onclick="openDeleteModal({{ json_encode($action['url']) }})" class="text-[var(--color-danger)]">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19.3248 9.46777C19.3248 9.46777 18.7818 16.2028 18.4668 19.0398C18.3168 20.3948 17.4798 21.1888 16.1088 21.2138C13.4998 21.2608 10.8878 21.2638 8.27979 21.2088C6.96079 21.1818 6.13779 20.3778 5.99079 19.0468C5.67379 16.1848 5.13379 9.46777 5.13379 9.46777" stroke="#130F26" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            @elseif($action['method'] === 'POST')
                                <form action="{{ $action['url'] }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="text-[var(--color-gray-800)] hover:text-[var(--color-gray-800)]/80" title="{{ $action['title'] }}">
                                        @if(!empty($action['icon']))
                                            <span class="material-icons-outlined text-sm">{{ $action['icon'] }}</span>
                                        @else
                                            {{ $action['title'] }}
                                        @endif
                                    </button>
                                </form>
                            @else
                                @php
                                $editSvg = <<<SVG
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.492 2.78906H7.753C4.678 2.78906 2.75 4.96606 2.75 8.04806V16.3621C2.75 19.4441 4.669 21.6211 7.753 21.6211H16.577C19.662 21.6211 21.581 19.4441 21.581 16.3621V12.3341" stroke="#130F26" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M8.82763 10.921L16.3006 3.44799C17.2316 2.51799 18.7406 2.51799 19.6716 3.44799L20.8886 4.66499C21.8196 5.59599 21.8196 7.10599 20.8886 8.03599L13.3796 15.545C12.9726 15.952 12.4206 16.181 11.8446 16.181H8.09863L8.19263 12.401C8.20663 11.845 8.43363 11.315 8.82763 10.921Z" stroke="#130F26" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M15.165 4.60254L19.731 9.16854" stroke="#130F26" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    SVG;
                                $viewSvg = <<<SVG
                                    <svg width="18" height="18" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M21.2746 11.5C21.2746 12.8482 18.9151 19.9267 11.4996 19.9267C4.08409 19.9267 1.72461 12.8482 1.72461 11.5C1.72461 10.1517 4.08409 3.07324 11.4996 3.07324C18.9151 3.07324 21.2746 10.1517 21.2746 11.5Z" stroke="#130F26" stroke-width="1.725" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M11.4994 14.5336C13.1748 14.5336 14.533 13.1754 14.533 11.5C14.533 9.82456 13.1748 8.46636 11.4994 8.46636C9.824 8.46636 8.4658 9.82456 8.4658 11.5C8.4658 13.1754 9.824 14.5336 11.4994 14.5336Z" stroke="#130F26" stroke-width="1.725" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    SVG;
                                @endphp

                                @php
                                    $iconHtml = '';
                                    if (!empty($action['icon'])) {
                                        if ($action['icon'] == 'edit') {
                                            $iconHtml = $editSvg;
                                        } elseif ($action['icon'] == 'visibility') {
                                            $iconHtml = $viewSvg;
                                        } else {
                                            $iconHtml = "<span class='material-icons-outlined'>{$action['icon']}</span>";
                                        }
                                    } else {
                                        $iconHtml = $action['title'];
                                    }
                                @endphp

                                @if(!empty($action['is_popup']))
                                    <x-modal buttonText="{!! $iconHtml !!}" modalTitle="{{ $action['title'] }}" id="{{ $action['index'] }}" ajaxUrl="{{ $action['url'] }}" color="text-[var(--color-gray-800)] hover:text-[var(--color-gray-800)]/80 flex justify-center items-center" modalSize="2xl" />
                                @else
                                    <a href="{{ $action['url'] }}" class="text-[var(--color-gray-800)] hover:text-[var(--color-gray-800)]/80" title="{{ $action['title'] }}">{!! $iconHtml !!}</a>
                                @endif
                            @endif

                        @endforeach
                </div>
            @endif
        </div>
        @empty
            <p class="px-3 py-8 text-center text-sm text-[var(--color-text-secondary)]">No records found.</p>
        @endforelse
    </div>
</div>
