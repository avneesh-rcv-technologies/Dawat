@extends('backend::layouts.app')

@section('title', 'Notification Channels')

@section('styles')
    <style>
        .disabled-checkbox {
            opacity: 0.5;
            pointer-events: none;
        }
    </style>
@endsection

@section('breadcrumbs')
    @php
        $title = 'Notification Channels';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'System Settings', 'url' => route('settings.index')],
            ['label' => $title, 'url' => null],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
    <form class="form-ajax" method="POST" action="{{ route('notification.store') }}">
        @csrf
        @method('POST')

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 " id="notificationTable">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 ">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Event
                        </th>
                        @foreach ($channels as $channel)
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ $channel->name }}
                            </th>
                        @endforeach

                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Templates Mapping
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white border-b divide-gray-100">
                    @foreach ($events as $event)
                        @php
                            $eventMappings = $mappings[$event->id] ?? [];
                            $channelMappings = $eventMappings['channels'] ?? [];
                            $recipientMappings = $eventMappings['recipients'] ?? [];
                            $templates = $eventMappings['templates'] ?? [];

                            // Check if any channel is selected for this event
                            $hasEnabledChannel = false;
                            foreach ($channels as $channel) {
                                if ($channelMappings[$channel->id] ?? false) {
                                    $hasEnabledChannel = true;
                                    break;
                                }
                            }
                        @endphp
                        <tr class="" data-event-id="{{ $event->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $event->event_name }}</div>
                                <div class="text-sm text-gray-500">{{ $event->event_code }}</div>
                            </td>

                            @foreach ($channels as $channel)
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="hidden"
                                        name="notifications[{{ $event->id }}][channels][{{ $channel->id }}][enabled]"
                                        value="0">
                                    <input type="checkbox"
                                        name="notifications[{{ $event->id }}][channels][{{ $channel->id }}][enabled]"
                                        @if ($channelMappings[$channel->id] ?? false) checked @endif value="1"
                                        class="channel-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                        data-event-id="{{ $event->id }}">
                                </td>
                            @endforeach

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($hasEnabledChannel)
                                    <div class="text-sm font-medium text-gray-900">
                                        <x-modal buttonText="Add Mapping" modalTitle="Templates Mapping"
                                            id="notification_template_mapping"
                                            ajaxUrl="{{ route('notification.show', $event->id) }}" color="btn-cancel"
                                            modalSize="2xl" />
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end mt-6">
            <x-button type="submit" class="btn-primary" label="Save" icon='' name="button" />
        </div>
    </form>

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('notificationTable');

            table.addEventListener('change', function(e) {
                if (e.target.classList.contains('channel-checkbox')) {
                    const eventId = e.target.dataset.eventId;
                    const row = document.querySelector(`tr[data-event-id="${eventId}"]`);
                    const channelCheckboxes = row.querySelectorAll('.channel-checkbox');
                    const adminCheckbox = row.querySelector('.admin-checkbox');

                    // Check if any channel is selected
                    let hasEnabledChannel = false;
                    channelCheckboxes.forEach(checkbox => {
                        if (checkbox.checked) {
                            hasEnabledChannel = true;
                        }
                    });

                    // Enable/disable recipient checkboxes
                    adminCheckbox.disabled = !hasEnabledChannel;

                    // Toggle disabled class
                    if (hasEnabledChannel) {
                        adminCheckbox.classList.remove('disabled-checkbox');
                    } else {
                        adminCheckbox.classList.add('disabled-checkbox');
                        // Uncheck recipients if no channels selected
                        adminCheckbox.checked = false;
                    }
                }
            });

        });
    </script>
@endsection
@endsection
