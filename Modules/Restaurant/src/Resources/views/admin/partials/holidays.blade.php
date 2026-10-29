@if ($holidays->count() > 0)
<div class="space-y-4">
    @php
    $currentDate = now();
    $upcomingHolidays = $holidays->filter(function ($holiday) use ($currentDate) {
        return \Carbon\Carbon::parse($holiday->date)->gte($currentDate->startOfDay());
    });
    $pastHolidays = $holidays->filter(function ($holiday) use ($currentDate) {
        return \Carbon\Carbon::parse($holiday->date)->lt($currentDate->startOfDay());
    });
    @endphp

    @if ($upcomingHolidays->count() > 0)
    <div>
        <h4 class="text-sm font-medium text-gray-700 mb-3">Upcoming Holidays ({{ $upcomingHolidays->count() }})</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($upcomingHolidays as $holiday)
            @php
            $holidayDate = \Carbon\Carbon::parse($holiday->date);
            $isToday = $holidayDate->isToday();
            $isTomorrow = $holidayDate->isTomorrow();
            $daysUntil = $currentDate->diffInDays($holidayDate, false);
            @endphp
            <div class="p-4 bg-white rounded-lg border {{ $isToday ? 'ring-2 ring-red-500' : 'border-gray-200' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-1">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">{{ $holidayDate->format('M j, Y') }}</span>
                        </div>
                        @if ($holiday->reason)
                        <p class="text-xs text-gray-600 mb-2">{{ $holiday->reason }}</p>
                        @endif
                        <div class="flex items-center space-x-2">
                            @if ($isToday)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Today</span>
                            @elseif($isTomorrow)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Tomorrow</span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $daysUntil }} {{ $daysUntil === 1 ? 'day' : 'days' }}
                            </span>
                            @endif
                            <span class="text-xs text-gray-500">{{ $holidayDate->format('l') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if ($pastHolidays->count() > 0)
    <div>
        <h4 class="text-sm font-medium text-gray-700 mb-3">Past Holidays ({{ $pastHolidays->count() }})</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach ($pastHolidays->take(8) as $holiday)
            @php
            $holidayDate = \Carbon\Carbon::parse($holiday->date);
            @endphp
            <div class="p-3 bg-white rounded-lg border border-gray-200 opacity-75">
                <div class="flex items-center space-x-2 mb-1">
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-xs font-medium text-gray-700">{{ $holidayDate->format('M j, Y') }}</span>
                </div>
                @if ($holiday->reason)
                <p class="text-xs text-gray-500">{{ $holiday->reason }}</p>
                @endif
            </div>
            @endforeach
            @if ($pastHolidays->count() > 8)
            <div class="p-3 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                <span class="text-xs text-gray-500">+{{ $pastHolidays->count() - 8 }} more</span>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@else
<div class="text-center py-8">
    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
    </svg>
    <h4 class="text-sm font-medium text-gray-900 mb-1">No Holidays Scheduled</h4>
    <p class="text-sm text-gray-500">This restaurant has no scheduled holidays</p>
</div>
@endif
