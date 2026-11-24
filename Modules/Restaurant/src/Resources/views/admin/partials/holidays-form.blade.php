{{-- ===== HOLIDAY MANAGEMENT ===== --}}
@php
    $holidayRows = collect(old('holidays', $holidays ?? []))
        ->filter(fn ($h) => is_array($h) && !empty($h['date']) && strtotime($h['date']) !== false)
        ->values()
        ->sortBy(fn ($h) => $h['date'] ?? '')
        ->values();
    $todayDate = \Illuminate\Support\Carbon::today();
    $upcomingHolidayCount = $holidayRows->filter(fn ($h) => !empty($h['date'])
        && \Illuminate\Support\Carbon::parse($h['date'])->gte($todayDate))->count();
@endphp

<style>
    .hm { --hm-brand: #ff6210; --hm-brand-soft: #fff4ed; --hm-brand-ring: rgba(255, 98, 16, .18);
          --hm-ink: #111827; --hm-muted: #6b7280; --hm-line: #e5e7eb; --hm-danger: #dc2626; }
    .hm.section-card { background: #fff; border: 1px solid var(--hm-line); border-radius: 14px; overflow: hidden; }
    .hm-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 24px; border-bottom: 1px solid var(--hm-line); }
    .hm-header-main { display: flex; align-items: center; gap: 14px; }
    .hm-icon { width: 42px; height: 42px; border-radius: 10px; display: grid; place-items: center; background: var(--hm-brand-soft); color: var(--hm-brand); flex-shrink: 0; }
    .hm-title { font-size: 16px; font-weight: 600; color: var(--hm-ink); line-height: 1.3; }
    .hm-desc { font-size: 13px; color: var(--hm-muted); margin-top: 2px; }
    .hm-count { font-size: 12px; font-weight: 600; color: var(--hm-brand); background: var(--hm-brand-soft); padding: 6px 12px; border-radius: 999px; white-space: nowrap; }

    .hm-body { padding: 24px; }
    .hm-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 24px; }
    @media (min-width: 1024px) { .hm-grid { grid-template-columns: minmax(280px, 360px) minmax(0, 1fr); } }

    .hm-add { border: 1px solid var(--hm-line); border-radius: 12px; padding: 20px; background: #fafafa; align-self: start; }
    @media (min-width: 1024px) { .hm-add { position: sticky; top: 16px; } }
    .hm-section-label { font-size: 13px; font-weight: 600; color: var(--hm-ink); margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .hm-field { margin-bottom: 16px; }
    .hm-label { display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px; }
    .hm-label .req { color: var(--hm-danger); }
    .hm-input { width: 100%; height: 44px; padding: 0 14px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; font-size: 14px; color: var(--hm-ink); transition: border-color .15s, box-shadow .15s; }
    .hm-input:focus { outline: none; border-color: var(--hm-brand); box-shadow: 0 0 0 3px var(--hm-brand-ring); }
    .hm-input[aria-invalid="true"] { border-color: var(--hm-danger); box-shadow: 0 0 0 3px rgba(220, 38, 38, .12); }
    .hm-error { margin-top: 6px; font-size: 12px; color: var(--hm-danger); display: flex; align-items: center; gap: 4px; }
    .hm-error.hidden { display: none; }
    .hm-hint { margin-top: 12px; font-size: 12px; color: var(--hm-muted); text-align: center; }

    .hm-btn { height: 44px; padding: 0 18px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; font-weight: 600; border: 1px solid transparent; cursor: pointer; transition: background-color .15s, box-shadow .15s, color .15s; }
    .hm-btn:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--hm-brand-ring); }
    .hm-btn-primary { background: var(--hm-brand); color: #fff; }
    .hm-btn-primary:hover { background: #e85508; }
    .hm-btn-outline { background: #fff; color: var(--hm-brand); border-color: var(--hm-brand); }
    .hm-btn-outline:hover { background: var(--hm-brand-soft); }
    .hm-btn-block { width: 100%; }

    .hm-list-head { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 12px; }
    .hm-list-head h4 { font-size: 13px; font-weight: 600; color: var(--hm-ink); }
    .hm-list-head span { font-size: 12px; color: var(--hm-muted); }
    .hm-list { display: flex; flex-direction: column; gap: 10px; }

    .hm-item { display: flex; align-items: center; gap: 16px; padding: 12px 12px 12px 12px; border: 1px solid var(--hm-line); border-radius: 12px; background: #fff; transition: border-color .15s, box-shadow .15s; }
    .hm-item:hover, .hm-item:focus-within { border-color: #fdba8c; box-shadow: 0 1px 3px rgba(17, 24, 39, .06); }
    .hm-item.is-past { background: #fafafa; }
    .hm-item.is-past .hm-date { background: #f3f4f6; color: var(--hm-muted); }
    .hm-item.is-past .hm-date-month { color: var(--hm-muted); }
    .hm-item.is-new { animation: hm-flash 1.2s ease-out; }
    @keyframes hm-flash { from { background: var(--hm-brand-soft); } to { background: #fff; } }

    .hm-date { width: 56px; flex-shrink: 0; border-radius: 10px; background: var(--hm-brand-soft); text-align: center; padding: 6px 0; color: var(--hm-ink); }
    .hm-date-month { display: block; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--hm-brand); }
    .hm-date-day { display: block; font-size: 22px; font-weight: 700; line-height: 1.1; }

    .hm-item-main { flex: 1; min-width: 0; }
    .hm-item-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; font-size: 13px; color: var(--hm-muted); margin-bottom: 4px; }
    .hm-item-meta strong { color: var(--hm-ink); font-weight: 600; }
    .hm-tag { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 999px; background: #f3f4f6; color: #4b5563; }
    .hm-tag.is-soon { background: var(--hm-brand-soft); color: var(--hm-brand); }
    .hm-reason { width: 100%; height: 34px; padding: 0 10px; margin-left: -10px; border: 1px solid transparent; border-radius: 6px; background: transparent; font-size: 14px; color: var(--hm-ink); transition: border-color .15s, background-color .15s, box-shadow .15s; }
    .hm-reason:hover { background: #f9fafb; border-color: var(--hm-line); }
    .hm-reason:focus { outline: none; background: #fff; border-color: var(--hm-brand); box-shadow: 0 0 0 3px var(--hm-brand-ring); }
    .hm-reason:invalid { border-color: #fca5a5; }

    .hm-remove { width: 36px; height: 36px; flex-shrink: 0; border-radius: 8px; display: grid; place-items: center; color: #9ca3af; background: transparent; border: none; cursor: pointer; transition: color .15s, background-color .15s; }
    .hm-remove:hover { color: var(--hm-danger); background: #fef2f2; }
    .hm-remove:focus-visible { outline: none; box-shadow: 0 0 0 3px rgba(220, 38, 38, .2); color: var(--hm-danger); }

    .hm-empty { text-align: center; padding: 48px 16px; border: 1px dashed #d1d5db; border-radius: 12px; color: var(--hm-muted); }
    .hm-empty-icon { width: 48px; height: 48px; margin: 0 auto 12px; border-radius: 12px; display: grid; place-items: center; background: #f3f4f6; color: #9ca3af; }
    .hm-empty p:first-of-type { font-size: 14px; font-weight: 600; color: #374151; }
    .hm-empty p + p { font-size: 13px; margin-top: 4px; }

    .hm-alert { margin-bottom: 20px; padding: 12px 14px; border-radius: 8px; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: 13px; }

    .hm-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--hm-line); }
    .hm-footer-note { font-size: 13px; color: var(--hm-muted); }
    .hm-footer-note.is-dirty { color: #b45309; font-weight: 500; }
    .hm fieldset:disabled { opacity: .65; }
    .hm fieldset:disabled .hm-remove { display: none; }

    @media (max-width: 640px) {
        .hm-header, .hm-body { padding: 16px; }
        .hm-count { display: none; }
        .hm-footer .hm-btn { width: 100%; }
    }
</style>

<div class="section-card hm">
    <div class="hm-header">
        <div class="hm-header-main">
            <span class="hm-icon" aria-hidden="true">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </span>
            <div>
                <h3 class="hm-title">Holiday Management</h3>
                <p class="hm-desc">Set dates when the restaurant will be closed</p>
            </div>
        </div>
        <span class="hm-count" id="holiday-upcoming-count">{{ $upcomingHolidayCount }} upcoming</span>
    </div>

    <div class="hm-body">
        <form id="holidays-form" method="POST"
            action="{{ $holidaysAction ?? (isset($restaurantId) ? route('admin.restaurant.update.holidays', $restaurantId) : '') }}">
            @csrf
            <fieldset @disabled(!($canUpdateHolidays ?? true))>

            @if ($errors->has('holidays') || $errors->has('holidays.*'))
                <div role="alert" class="hm-alert">
                    @foreach ($errors->getMessages() as $field => $messages)
                        @if ($field === 'holidays' || str_starts_with($field, 'holidays.'))
                            @foreach ($messages as $message)
                                <p>{{ $message }}</p>
                            @endforeach
                        @endif
                    @endforeach
                </div>
            @endif

            <div class="hm-grid">
                {{-- Add Holiday --}}
                <div class="hm-add">
                    <h4 class="hm-section-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--hm-brand)" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                        </svg>
                        Add a holiday
                    </h4>
                    <div class="hm-field">
                        <label for="new-holiday-date" class="hm-label">Date <span class="req">*</span></label>
                        <input id="new-holiday-date" type="date" name="new_holiday_date" class="hm-input"
                            aria-required="true" aria-describedby="holiday-date-error"
                            value="{{ old('new_holiday_date') }}" min="{{ date('Y-m-d') }}">
                        <p id="holiday-date-error" class="hm-error hidden" role="alert"></p>
                    </div>
                    <div class="hm-field">
                        <label for="new-holiday-reason" class="hm-label">Reason <span class="req">*</span></label>
                        <input id="new-holiday-reason" type="text" name="new_holiday_reason" class="hm-input"
                            maxlength="255" aria-required="true" aria-describedby="holiday-reason-error"
                            value="{{ old('new_holiday_reason') }}"
                            placeholder="e.g. Diwali, Staff training">
                        <p id="holiday-reason-error" class="hm-error hidden" role="alert"></p>
                    </div>
                    <button type="button" id="add-holiday-btn" class="hm-btn hm-btn-outline hm-btn-block">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                        </svg>
                        Add to list
                    </button>
                    <p class="hm-hint">Press Enter to add quickly</p>
                </div>

                {{-- Holidays List --}}
                <div>
                    <div class="hm-list-head">
                        <h4>Scheduled holidays</h4>
                        <span id="holiday-total-count">{{ $holidayRows->count() }} {{ \Illuminate\Support\Str::plural('date', $holidayRows->count()) }}</span>
                    </div>
                    <div id="holidays-list" class="hm-list">
                        @foreach ($holidayRows as $index => $holiday)
                            @php
                                $hDate = \Illuminate\Support\Carbon::parse($holiday['date']);
                                $daysAway = (int) $todayDate->diffInDays($hDate, false);
                            @endphp
                            <div class="holiday-item hm-item {{ $daysAway < 0 ? 'is-past' : '' }}" data-date="{{ $hDate->format('Y-m-d') }}">
                                <div class="hm-date" aria-hidden="true">
                                    <span class="hm-date-month">{{ $hDate->format('M') }}</span>
                                    <span class="hm-date-day">{{ $hDate->format('j') }}</span>
                                </div>
                                <div class="hm-item-main">
                                    <div class="hm-item-meta">
                                        <strong>{{ $hDate->format('l, F j, Y') }}</strong>
                                        <span class="hm-tag {{ $daysAway >= 0 && $daysAway <= 7 ? 'is-soon' : '' }}" data-role="relative"></span>
                                    </div>
                                    <input type="text" name="holidays[{{ $index }}][reason]"
                                        value="{{ $holiday['reason'] ?? '' }}" required maxlength="255"
                                        aria-label="Reason for {{ $hDate->format('F j, Y') }}" placeholder="Add a reason"
                                        class="hm-reason">
                                </div>
                                <button type="button" class="hm-remove" data-action="remove-holiday"
                                    aria-label="Remove holiday on {{ $hDate->format('F j, Y') }}" title="Remove">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                                <input type="hidden" name="holidays[{{ $index }}][date]" value="{{ $holiday['date'] }}">
                                <input type="hidden" name="holidays[{{ $index }}][is_closed]"
                                    value="{{ (bool) ($holiday['is_closed'] ?? true) ? 1 : 0 }}">
                            </div>
                        @endforeach
                    </div>
                    <div id="no-holidays" class="hm-empty" @if($holidayRows->isNotEmpty()) hidden @endif>
                        <div class="hm-empty-icon" aria-hidden="true">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p>No holidays scheduled</p>
                        <p>Add the dates your restaurant will be closed.</p>
                    </div>
                </div>
            </div>

            <div class="hm-footer">
                <p class="hm-footer-note" id="holiday-save-note">All changes saved.</p>
                <button type="submit" class="hm-btn hm-btn-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save holidays
                </button>
            </div>
            </fieldset>
        </form>
    </div>
</div>

<template id="holiday-item-template">
    <div class="holiday-item hm-item is-new">
        <div class="hm-date" aria-hidden="true">
            <span class="hm-date-month"></span>
            <span class="hm-date-day"></span>
        </div>
        <div class="hm-item-main">
            <div class="hm-item-meta">
                <strong data-role="full-date"></strong>
                <span class="hm-tag" data-role="relative"></span>
            </div>
            <input type="text" data-field="reason" required maxlength="255" placeholder="Add a reason" class="hm-reason">
        </div>
        <button type="button" class="hm-remove" data-action="remove-holiday" title="Remove">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </button>
        <input type="hidden" data-field="date">
        <input type="hidden" data-field="is_closed" value="1">
    </div>
</template>

<script>
(function () {
    'use strict';

    const MS_PER_DAY = 86400000;

    function parseDate(value) {
        // Accept 'Y-m-d' as well as values with a time part, e.g. '2026-11-20 00:00:00'.
        const [y, m, d] = String(value).slice(0, 10).split('-').map(Number);
        return new Date(y, m - 1, d);
    }

    function daysFromToday(value) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return Math.round((parseDate(value) - today) / MS_PER_DAY);
    }

    function relativeLabel(days) {
        if (days === 0) return 'Today';
        if (days === 1) return 'Tomorrow';
        if (days < 0) return 'Past';
        if (days < 7) return `In ${days} days`;
        const weeks = Math.round(days / 7);
        if (days < 60) return `In ${weeks} week${weeks > 1 ? 's' : ''}`;
        const months = Math.round(days / 30);
        return `In ${months} months`;
    }

    function setError(input, errorId, message) {
        const error = document.getElementById(errorId);
        error.textContent = message;
        error.classList.toggle('hidden', !message);
        input.setAttribute('aria-invalid', message ? 'true' : 'false');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('holidays-form');
        if (!form) return;

        const list = document.getElementById('holidays-list');
        const empty = document.getElementById('no-holidays');
        const template = document.getElementById('holiday-item-template');
        const dateInput = document.getElementById('new-holiday-date');
        const reasonInput = document.getElementById('new-holiday-reason');
        const addButton = document.getElementById('add-holiday-btn');
        const totalCount = document.getElementById('holiday-total-count');
        const upcomingCount = document.getElementById('holiday-upcoming-count');
        const saveNote = document.getElementById('holiday-save-note');

        function items() {
            return Array.from(list.querySelectorAll('.holiday-item'));
        }

        function markDirty() {
            saveNote.textContent = 'You have unsaved changes.';
            saveNote.classList.add('is-dirty');
        }

        function refresh() {
            const rows = items();
            rows.forEach(function (item, index) {
                item.querySelectorAll('input[name^="holidays["]').forEach(function (input) {
                    input.name = input.name.replace(/^holidays\[\d+\]/, `holidays[${index}]`);
                });
                const days = daysFromToday(item.dataset.date);
                const tag = item.querySelector('[data-role="relative"]');
                tag.textContent = relativeLabel(days);
                tag.classList.toggle('is-soon', days >= 0 && days <= 7);
                item.classList.toggle('is-past', days < 0);
            });
            const upcoming = rows.filter(function (item) {
                return daysFromToday(item.dataset.date) >= 0;
            }).length;
            totalCount.textContent = `${rows.length} ${rows.length === 1 ? 'date' : 'dates'}`;
            upcomingCount.textContent = `${upcoming} upcoming`;
            empty.hidden = rows.length > 0;
        }

        function addHoliday() {
            const date = dateInput.value;
            const reason = (reasonInput.value || '').trim();
            let dateError = !date ? 'Please select a date.'
                : !dateInput.checkValidity() ? 'Please select today or a future date.' : '';
            const reasonError = !reason ? 'Please enter a reason.'
                : Array.from(reason).length > 255 ? 'The reason must be 255 characters or fewer.' : '';

            if (!dateError && items().some(function (item) { return item.dataset.date === date; })) {
                dateError = 'This date is already on the list.';
            }

            setError(dateInput, 'holiday-date-error', dateError);
            setError(reasonInput, 'holiday-reason-error', reasonError);
            if (dateError || reasonError) {
                (dateError ? dateInput : reasonInput).focus();
                return false;
            }

            const parsed = parseDate(date);
            const fullDate = parsed.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            const shortDate = parsed.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

            const node = template.content.firstElementChild.cloneNode(true);
            node.dataset.date = date;
            node.querySelector('.hm-date-month').textContent = parsed.toLocaleDateString('en-US', { month: 'short' });
            node.querySelector('.hm-date-day').textContent = parsed.getDate();
            node.querySelector('[data-role="full-date"]').textContent = fullDate;
            const reasonField = node.querySelector('[data-field="reason"]');
            reasonField.name = 'holidays[0][reason]';
            reasonField.value = reason;
            reasonField.setAttribute('aria-label', `Reason for ${shortDate}`);
            const dateField = node.querySelector('[data-field="date"]');
            dateField.name = 'holidays[0][date]';
            dateField.value = date;
            node.querySelector('[data-field="is_closed"]').name = 'holidays[0][is_closed]';
            node.querySelector('[data-action="remove-holiday"]').setAttribute('aria-label', `Remove holiday on ${shortDate}`);

            // Keep the list in chronological order.
            const next = items().find(function (item) { return item.dataset.date > date; });
            list.insertBefore(node, next || null);

            refresh();
            markDirty();
            dateInput.value = '';
            reasonInput.value = '';
            dateInput.focus();
            node.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            return true;
        }

        addButton.addEventListener('click', addHoliday);

        [dateInput, reasonInput].forEach(function (input) {
            input.addEventListener('input', function () {
                setError(input, input === dateInput ? 'holiday-date-error' : 'holiday-reason-error', '');
            });
            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    addHoliday();
                }
            });
        });

        list.addEventListener('click', function (event) {
            const button = event.target.closest('[data-action="remove-holiday"]');
            if (!button) return;
            const item = button.closest('.holiday-item');
            const sibling = item.nextElementSibling || item.previousElementSibling;
            item.remove();
            refresh();
            markDirty();
            (sibling ? sibling.querySelector('.hm-remove') : dateInput).focus();
        });

        list.addEventListener('input', markDirty);

        form.addEventListener('submit', function (event) {
            // Include a pending holiday instead of silently discarding its fields.
            if ((dateInput.value || reasonInput.value) && !addHoliday()) {
                event.preventDefault();
            }
        });

        refresh();
    });
})();
</script>
