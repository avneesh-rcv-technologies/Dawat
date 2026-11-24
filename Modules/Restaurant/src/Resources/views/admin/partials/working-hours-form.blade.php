{{-- ===== WORKING HOURS ===== --}}
@php
    $isWholeDay = !empty($hours['is_open_whole_day']);
    $todayKey = strtolower(now()->format('l'));
    $hourDays = [];
    foreach (($hours ?? []) as $day => $data) {
        if ($day === 'is_open_whole_day') {
            continue;
        }
        // Normalise to an array of intervals.
        $segments = [];
        if (is_array($data) && count($data) && array_keys($data) !== range(0, count($data) - 1)) {
            $segments[] = $data;
        } elseif (is_array($data)) {
            $segments = array_values($data);
        }
        if (empty($segments)) {
            $segments = [['open' => false, 'from' => '09:00', 'to' => '22:00']];
        }
        $hourDays[$day] = [
            'segments' => $segments,
            'open' => collect($segments)->contains(fn ($s) => !empty($s['open'])),
        ];
    }
@endphp

<style>
    .wh { --wh-brand: #ff6210; --wh-brand-soft: #fff4ed; --wh-brand-ring: rgba(255, 98, 16, .18);
          --wh-ink: #111827; --wh-muted: #6b7280; --wh-line: #e5e7eb; --wh-danger: #dc2626; }
    .wh.section-card { background: #fff; border: 1px solid var(--wh-line); border-radius: 14px; box-shadow: none; overflow: hidden; }
    .wh-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 24px; border-bottom: 1px solid var(--wh-line); background: #fff; }
    .wh-header-main { display: flex; align-items: center; gap: 14px; }
    .wh-icon { width: 42px; height: 42px; border-radius: 10px; display: grid; place-items: center; background: var(--wh-brand-soft); color: var(--wh-brand); flex-shrink: 0; }
    .wh-title { font-size: 16px; font-weight: 600; color: var(--wh-ink); line-height: 1.3; }
    .wh-desc { font-size: 13px; color: var(--wh-muted); margin-top: 2px; }
    .wh-summary { font-size: 12px; font-weight: 600; color: var(--wh-brand); background: var(--wh-brand-soft); padding: 6px 12px; border-radius: 999px; white-space: nowrap; }
    .wh-body { padding: 24px; }

    /* Switch built on a real checkbox */
    .wh-switch { position: relative; display: inline-flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
    .wh-switch input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .wh-switch-track { width: 40px; height: 22px; border-radius: 999px; background: #d1d5db; position: relative; transition: background-color .15s; flex-shrink: 0; }
    .wh-switch-track::after { content: ''; position: absolute; top: 3px; left: 3px; width: 16px; height: 16px; border-radius: 50%; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,.2); transition: transform .15s; }
    .wh-switch input:checked + .wh-switch-track { background: var(--wh-brand); }
    .wh-switch input:checked + .wh-switch-track::after { transform: translateX(18px); }
    .wh-switch input:focus-visible + .wh-switch-track { box-shadow: 0 0 0 3px var(--wh-brand-ring); }
    .wh-switch input:disabled + .wh-switch-track { opacity: .5; cursor: not-allowed; }
    .wh-switch-label { font-size: 13px; font-weight: 500; color: #374151; min-width: 48px; }

    .wh-allday { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 16px 18px; border: 1px solid var(--wh-line); border-radius: 12px; background: #fafafa; margin-bottom: 20px; transition: border-color .15s, background-color .15s; }
    .wh-allday.is-on { border-color: #fdba8c; background: var(--wh-brand-soft); }
    .wh-allday h4 { font-size: 14px; font-weight: 600; color: var(--wh-ink); }
    .wh-allday p { font-size: 13px; color: var(--wh-muted); margin-top: 2px; }

    .wh-days { border: 1px solid var(--wh-line); border-radius: 12px; overflow: hidden; }
    .wh-day { display: grid; grid-template-columns: minmax(0, 1fr); gap: 12px; padding: 16px 18px; border-bottom: 1px solid #f1f2f4; background: #fff; transition: background-color .15s; }
    .wh-day:last-child { border-bottom: none; }
    .wh-day:hover { background: #fcfcfd; }
    @media (min-width: 900px) { .wh-day { grid-template-columns: 150px 130px minmax(0, 1fr) auto; align-items: start; gap: 20px; } }
    .wh-day-name { display: flex; align-items: center; gap: 8px; min-height: 40px; font-size: 14px; font-weight: 600; color: var(--wh-ink); }
    .wh-today { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 999px; background: var(--wh-brand-soft); color: var(--wh-brand); }
    .wh-day-toggle { display: flex; align-items: center; min-height: 40px; }

    .wh-intervals { display: flex; flex-direction: column; gap: 8px; }
    .wh-day.is-closed .wh-intervals, .wh-day.is-closed .wh-add { display: none; }
    .wh-closed-note { display: none; align-items: center; min-height: 40px; font-size: 13px; color: #9ca3af; }
    .wh-day.is-closed .wh-closed-note { display: flex; }
    .wh-row { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .wh-row.is-stashed { display: none; }
    .wh-time { height: 40px; width: 140px; padding: 0 10px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; font-size: 14px; color: var(--wh-ink); transition: border-color .15s, box-shadow .15s; }
    .wh-time:focus { outline: none; border-color: var(--wh-brand); box-shadow: 0 0 0 3px var(--wh-brand-ring); }
    .wh-time[readonly] { background: #f9fafb; color: #4b5563; }
    .wh-to { font-size: 13px; color: #9ca3af; }
    .wh-overnight { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 999px; background: #fffbeb; color: #b45309; }
    .wh-overnight[hidden] { display: none; }

    .wh-icon-btn { width: 36px; height: 36px; border-radius: 8px; display: grid; place-items: center; color: #9ca3af; background: transparent; border: none; cursor: pointer; transition: color .15s, background-color .15s; }
    .wh-icon-btn:hover { color: var(--wh-danger); background: #fef2f2; }
    .wh-icon-btn:focus-visible { outline: none; box-shadow: 0 0 0 3px rgba(220, 38, 38, .2); }
    .wh-icon-btn[hidden] { display: none; }

    .wh-actions { display: flex; align-items: center; gap: 4px; min-height: 40px; justify-content: flex-start; }
    @media (min-width: 900px) { .wh-actions { justify-content: flex-end; } }
    .wh-link { height: 34px; padding: 0 10px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: var(--wh-brand); background: transparent; border: none; cursor: pointer; white-space: nowrap; transition: background-color .15s; }
    .wh-link:hover { background: var(--wh-brand-soft); }
    .wh-link:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--wh-brand-ring); }
    .wh-link.is-muted { color: var(--wh-muted); }
    .wh-link.is-muted:hover { background: #f3f4f6; color: var(--wh-ink); }
    .wh-link:disabled { opacity: .4; cursor: not-allowed; background: transparent; }

    .wh-days.is-locked .wh-day { background: #fafafa; }
    .wh-days.is-locked .wh-add, .wh-days.is-locked .wh-copy, .wh-days.is-locked .wh-icon-btn { display: none; }

    .wh-alert { margin-bottom: 20px; padding: 12px 14px; border-radius: 8px; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: 13px; }
    .wh-toast { font-size: 13px; color: #15803d; }

    .wh-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin: 24px -24px -24px; padding: 16px 24px; background: #fafafa; border-top: 1px solid var(--wh-line); }
    .wh-footer-note { font-size: 13px; color: var(--wh-muted); }
    .wh-footer-note.is-dirty { color: #b45309; font-weight: 500; }
    .wh-btn { height: 44px; padding: 0 20px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; font-weight: 600; border: 1px solid transparent; background: var(--wh-brand); color: #fff; cursor: pointer; transition: background-color .15s, box-shadow .15s; }
    .wh-btn:hover { background: #e85508; }
    .wh-btn:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--wh-brand-ring); }
    .wh fieldset:disabled { opacity: .7; }
    .wh fieldset:disabled .wh-btn, .wh fieldset:disabled .wh-add, .wh fieldset:disabled .wh-copy, .wh fieldset:disabled .wh-icon-btn { display: none; }

    @media (max-width: 640px) {
        .wh-header, .wh-body { padding: 16px; }
        .wh-summary { display: none; }
        .wh-time { width: 124px; }
        .wh-footer { margin: 20px -16px -16px; padding: 16px; }
        .wh-btn { width: 100%; }
    }
</style>

<div class="section-card wh">
    <div class="wh-header">
        <div class="wh-header-main">
            <span class="wh-icon" aria-hidden="true">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <div>
                <h3 class="wh-title">Working Hours</h3>
                <p class="wh-desc">Set operating days and times for the restaurant</p>
            </div>
        </div>
        <span class="wh-summary" id="wh-summary"></span>
    </div>

    <div class="wh-body">
        <form method="POST" id="working-hours-form"
            action="{{ $hoursAction ?? (isset($restaurantId) ? route('admin.restaurant.update.hours', $restaurantId) : '') }}">
            @csrf
            <fieldset @disabled(!($canUpdateHours ?? true))>

            @if ($errors->has('hours') || $errors->has('hours.*'))
                <div role="alert" class="wh-alert">
                    @foreach ($errors->getMessages() as $field => $messages)
                        @if ($field === 'hours' || str_starts_with($field, 'hours.'))
                            @foreach ($messages as $message)
                                <p>{{ $message }}</p>
                            @endforeach
                        @endif
                    @endforeach
                </div>
            @endif

            <div class="wh-allday {{ $isWholeDay ? 'is-on' : '' }}" id="wh-allday">
                <div>
                    <h4>Open 24/7</h4>
                    <p>The restaurant takes orders around the clock, every day of the week.</p>
                </div>
                <label class="wh-switch">
                    <input type="checkbox" name="is_open_whole_day" value="1" class="whole-day-checkbox" @checked($isWholeDay)
                        aria-describedby="wh-allday">
                    <span class="wh-switch-track" aria-hidden="true"></span>
                    <span class="wh-switch-label">{{ $isWholeDay ? 'On' : 'Off' }}</span>
                </label>
            </div>

            <div class="wh-days" id="hours-container">
                @foreach ($hourDays as $day => $info)
                    <div class="wh-day hours-day {{ $info['open'] ? '' : 'is-closed' }}" data-day="{{ $day }}">
                        <div class="wh-day-name">
                            {{ ucfirst($day) }}
                            @if ($day === $todayKey)
                                <span class="wh-today">Today</span>
                            @endif
                        </div>

                        <div class="wh-day-toggle">
                            <label class="wh-switch">
                                <input type="checkbox" class="day-checkbox-master" data-day="{{ $day }}" @checked($info['open'])
                                    aria-label="{{ ucfirst($day) }} open">
                                <span class="wh-switch-track" aria-hidden="true"></span>
                                <span class="wh-switch-label" data-role="day-state">{{ $info['open'] ? 'Open' : 'Closed' }}</span>
                            </label>
                        </div>

                        <div>
                            <div class="wh-intervals intervals" id="intervals-{{ $day }}">
                                @foreach ($info['segments'] as $idx => $seg)
                                    <div class="wh-row interval-row">
                                        <input type="hidden" name="hours[{{ $day }}][{{ $idx }}][open]" value="{{ $info['open'] ? 1 : 0 }}" class="interval-open-hidden">
                                        <input type="time" aria-label="{{ ucfirst($day) }} opening time" name="hours[{{ $day }}][{{ $idx }}][from]"
                                            value="{{ $seg['from'] ?? '09:00' }}" class="wh-time">
                                        <span class="wh-to">to</span>
                                        <input type="time" aria-label="{{ ucfirst($day) }} closing time" name="hours[{{ $day }}][{{ $idx }}][to]"
                                            value="{{ $seg['to'] ?? '22:00' }}" class="wh-time">
                                        <span class="wh-overnight" data-role="overnight" title="The closing time is the same as or earlier than the opening time." hidden>Check times</span>
                                        <button type="button" class="wh-icon-btn remove-interval" aria-label="Remove {{ ucfirst($day) }} time slot" title="Remove time slot">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <p class="wh-closed-note">Closed all day</p>
                        </div>

                        <div class="wh-actions">
                            <button type="button" class="wh-link wh-add add-interval" data-day="{{ $day }}">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                                Add slot
                            </button>
                            <button type="button" class="wh-link is-muted wh-copy" data-day="{{ $day }}" title="Copy these hours to every day">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Copy to all
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="wh-footer">
                <p class="wh-footer-note" id="wh-save-note" role="status" aria-live="polite">Changes apply after you save your working hours.</p>
                <button type="submit" class="wh-btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save working hours
                </button>
            </div>
            </fieldset>
        </form>
    </div>
</div>

<template id="wh-row-template">
    <div class="wh-row interval-row">
        <input type="hidden" data-field="open" value="1" class="interval-open-hidden">
        <input type="time" data-field="from" value="09:00" class="wh-time">
        <span class="wh-to">to</span>
        <input type="time" data-field="to" value="22:00" class="wh-time">
        <span class="wh-overnight" data-role="overnight" title="The closing time is the same as or earlier than the opening time." hidden>Check times</span>
        <button type="button" class="wh-icon-btn remove-interval" title="Remove time slot">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>

<script>
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('working-hours-form');
        if (!form) return;

        const container = document.getElementById('hours-container');
        const wholeDay = form.querySelector('.whole-day-checkbox');
        const allDayPanel = document.getElementById('wh-allday');
        const summary = document.getElementById('wh-summary');
        const saveNote = document.getElementById('wh-save-note');
        const template = document.getElementById('wh-row-template');
        const days = Array.from(container.querySelectorAll('.wh-day'));
        const label = day => day.charAt(0).toUpperCase() + day.slice(1);

        function markDirty() {
            saveNote.textContent = 'You have unsaved changes.';
            saveNote.classList.add('is-dirty');
        }

        // Give every visible slot sequential indexes so names never collide.
        function reindex(dayEl) {
            const day = dayEl.dataset.day;
            const rows = Array.from(dayEl.querySelectorAll('.wh-row:not(.is-stashed)'));
            rows.forEach(function (row, idx) {
                row.querySelectorAll('[data-field], input[name^="hours["]').forEach(function (input) {
                    const field = input.dataset.field || input.name.match(/\[(open|from|to)\]$/)[1];
                    input.dataset.field = field;
                    input.name = `hours[${day}][${idx}][${field}]`;
                });
                row.querySelectorAll('.remove-interval').forEach(function (btn) {
                    btn.hidden = rows.length === 1;
                });
            });
        }

        function updateOvernight(row) {
            const from = row.querySelector('[data-field="from"]');
            const to = row.querySelector('[data-field="to"]');
            const badge = row.querySelector('[data-role="overnight"]');
            if (from && to && badge) badge.hidden = !(from.value && to.value && to.value <= from.value);
        }

        function setDayOpen(dayEl, open) {
            dayEl.classList.toggle('is-closed', !open);
            dayEl.querySelector('[data-role="day-state"]').textContent = open ? 'Open' : 'Closed';
            dayEl.querySelectorAll('.wh-row:not(.is-stashed) .interval-open-hidden').forEach(function (input) {
                input.value = open ? '1' : '0';
            });
        }

        function makeRow(day, from, to, open) {
            const row = template.content.firstElementChild.cloneNode(true);
            row.querySelector('[data-field="open"]').value = open ? '1' : '0';
            row.querySelector('[data-field="from"]').value = from;
            row.querySelector('[data-field="to"]').value = to;
            row.querySelector('[data-field="from"]').setAttribute('aria-label', `${label(day)} opening time`);
            row.querySelector('[data-field="to"]').setAttribute('aria-label', `${label(day)} closing time`);
            row.querySelector('.remove-interval').setAttribute('aria-label', `Remove ${label(day)} time slot`);
            return row;
        }

        function refreshSummary() {
            if (wholeDay.checked) {
                summary.textContent = 'Open 24/7';
                return;
            }
            const openDays = days.filter(d => !d.classList.contains('is-closed')).length;
            summary.textContent = openDays === 0 ? 'Closed every day'
                : openDays === days.length ? 'Open every day'
                : `Open ${openDays} of ${days.length} days`;
        }

        // 24/7 hides the real slots (disabled, so they are not submitted) and
        // shows a fixed 00:00–23:59 slot. Turning it off brings the edits back.
        function setWholeDay(enabled) {
            allDayPanel.classList.toggle('is-on', enabled);
            allDayPanel.querySelector('.wh-switch-label').textContent = enabled ? 'On' : 'Off';
            container.classList.toggle('is-locked', enabled);

            days.forEach(function (dayEl) {
                const day = dayEl.dataset.day;
                const toggle = dayEl.querySelector('.day-checkbox-master');
                const intervals = dayEl.querySelector('.wh-intervals');

                if (enabled) {
                    dayEl.dataset.wasOpen = toggle.checked ? '1' : '0';
                    intervals.querySelectorAll('.wh-row').forEach(function (row) {
                        row.classList.add('is-stashed');
                        row.querySelectorAll('input').forEach(i => { i.disabled = true; });
                    });
                    const fixed = makeRow(day, '00:00', '23:59', true);
                    fixed.classList.add('is-247');
                    fixed.querySelectorAll('.wh-time').forEach(i => { i.readOnly = true; });
                    intervals.appendChild(fixed);
                    toggle.checked = true;
                    toggle.disabled = true;
                    setDayOpen(dayEl, true);
                } else {
                    intervals.querySelectorAll('.is-247').forEach(r => r.remove());
                    intervals.querySelectorAll('.wh-row.is-stashed').forEach(function (row) {
                        row.classList.remove('is-stashed');
                        row.querySelectorAll('input').forEach(i => { i.disabled = false; });
                    });
                    toggle.disabled = false;
                    if (dayEl.dataset.wasOpen !== undefined) toggle.checked = dayEl.dataset.wasOpen === '1';
                    setDayOpen(dayEl, toggle.checked);
                }
                reindex(dayEl);
            });
            refreshSummary();
        }

        container.addEventListener('change', function (event) {
            const toggle = event.target.closest('.day-checkbox-master');
            if (toggle) {
                setDayOpen(toggle.closest('.wh-day'), toggle.checked);
                refreshSummary();
            }
        });

        container.addEventListener('input', function (event) {
            const row = event.target.closest('.wh-row');
            if (row) updateOvernight(row);
        });

        container.addEventListener('click', function (event) {
            const addBtn = event.target.closest('.add-interval');
            const removeBtn = event.target.closest('.remove-interval');
            const copyBtn = event.target.closest('.wh-copy');

            if (addBtn) {
                const dayEl = addBtn.closest('.wh-day');
                const rows = dayEl.querySelectorAll('.wh-row:not(.is-stashed)');
                const last = rows[rows.length - 1];
                // Start the new slot where the previous one ends.
                const start = last ? last.querySelector('[data-field="to"], input[name$="[to]"]').value || '17:00' : '09:00';
                const row = makeRow(dayEl.dataset.day, start, '22:00', true);
                dayEl.querySelector('.wh-intervals').appendChild(row);
                reindex(dayEl);
                updateOvernight(row);
                row.querySelector('[data-field="from"]').focus();
                markDirty();
            }

            if (removeBtn) {
                const dayEl = removeBtn.closest('.wh-day');
                removeBtn.closest('.wh-row').remove();
                reindex(dayEl);
                dayEl.querySelector('.add-interval').focus();
                markDirty();
            }

            if (copyBtn) {
                const source = copyBtn.closest('.wh-day');
                const sourceOpen = source.querySelector('.day-checkbox-master').checked;
                const slots = Array.from(source.querySelectorAll('.wh-row:not(.is-stashed)')).map(function (row) {
                    return [row.querySelector('input[name$="[from]"]').value, row.querySelector('input[name$="[to]"]').value];
                });
                days.forEach(function (dayEl) {
                    if (dayEl === source) return;
                    const intervals = dayEl.querySelector('.wh-intervals');
                    intervals.innerHTML = '';
                    slots.forEach(function (slot) {
                        intervals.appendChild(makeRow(dayEl.dataset.day, slot[0], slot[1], sourceOpen));
                    });
                    dayEl.querySelector('.day-checkbox-master').checked = sourceOpen;
                    setDayOpen(dayEl, sourceOpen);
                    reindex(dayEl);
                    dayEl.querySelectorAll('.wh-row').forEach(updateOvernight);
                });
                refreshSummary();
                markDirty();
                saveNote.textContent = `Copied ${label(source.dataset.day)}'s hours to every day. Save to apply.`;
            }
        });

        form.addEventListener('input', markDirty);
        form.addEventListener('change', markDirty);
        wholeDay.addEventListener('change', function () { setWholeDay(this.checked); });

        days.forEach(function (dayEl) {
            reindex(dayEl);
            dayEl.querySelectorAll('.wh-row').forEach(updateOvernight);
        });
        if (wholeDay.checked) setWholeDay(true);
        refreshSummary();
    });
})();
</script>
