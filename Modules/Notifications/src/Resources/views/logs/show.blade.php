<table class="w-full border border-gray-200 rounded-lg">
        <tbody>
            <tr class="border-b">
                <td class="font-semibold p-2 w-40">ID</td>
                <td class="p-2">{{ $log->id }}</td>
            </tr>
            <tr class="border-b">
                <td class="font-semibold p-2">Event Code</td>
                <td class="p-2">{{ $log->event_code }}</td>
            </tr>
            <tr class="border-b">
                <td class="font-semibold p-2">Channel</td>
                <td class="p-2">{{ $log->channel_name }}</td>
            </tr>
            <tr class="border-b">
                <td class="font-semibold p-2">Notifiable</td>
                <td class="p-2">{{ $log->notifiable_type }} (ID: {{ $log->notifiable_id }})</td>
            </tr>
            <tr class="border-b">
                <td class="font-semibold p-2">Status</td>
                <td class="p-2">
                    <span class="px-2 py-1 rounded text-white 
                        {{ $log->status === 'sent' ? 'bg-green-500' : 'bg-red-500' }}">
                        {{ ucfirst($log->status) }}
                    </span>
                </td>
            </tr>
            @if($log->error_message)
            <tr class="border-b">
                <td class="font-semibold p-2">Error</td>
                <td class="p-2 text-red-600">{{ $log->error_message }}</td>
            </tr>
            @endif
            <tr class="border-b">
                <td class="font-semibold p-2">Sent At</td>
                <td class="p-2">{{ $log->sent_at }}</td>
            </tr>
            <tr class="border-b">
                <td class="font-semibold p-2">Created At</td>
                <td class="p-2">{{ $log->created_at }}</td>
            </tr>
        </tbody>
    </table>

    <div class="mt-6">
        <h3 class="text-lg font-semibold mb-2">Email Content</h3>
        <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
            @if(is_array($log->content) || is_object($log->content))
                {!! json_encode($log->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
            @else
                {!! $log->content !!}
            @endif
        </div>
    </div>