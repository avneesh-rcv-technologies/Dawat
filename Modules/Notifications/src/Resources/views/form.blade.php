<form id="templateMappingForm" class="form-ajax" method="POST" action="{{route('notification.update', $currentEventId)}}">
    @csrf
    @isset($channels)
    @method('PUT')
    @endisset
    <input type="hidden" name="event_id" value="{{ $currentEventId }}">
    
    <!-- Channel Mappings Section -->
    <div class="space-y-4">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($channelMappings as $channel)
            <div class="border border-blue-100 rounded-lg p-4 bg-white">
                <h4 class="font-medium text-gray-800 mb-3">{{ $channel['name'] }}</h4>
                
                <div class="space-y-3">
                    @if(isset($availableRecipients) && count($availableRecipients) > 0)
                        @foreach ($availableRecipients as $recipientKey => $recipientLabel)
                            <div class="flex items-center">
                                <input type="checkbox" 
                                    id="{{ $recipientKey }}_{{ $channel['channel_id'] }}" 
                                    name="channels[{{ $channel['channel_id'] }}][{{ $recipientKey }}]"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" 
                                    value="1"
                                    {{ ($channel['notifications'][$recipientKey] ?? false) ? 'checked' : '' }}>
                                <label for="{{ $recipientKey }}_{{ $channel['channel_id'] }}" class="ml-2 block text-sm text-gray-700">
                                    {{ $recipientLabel }}
                                </label>
                            </div>    
                        @endforeach
                    @else
                        @foreach ($channel['notifications'] as $notificationTypeKey => $notificationType)
                            <div class="flex items-center">
                                <input type="checkbox" 
                                    id="{{ $notificationTypeKey }}_{{ $channel['channel_id'] }}" 
                                    name="channels[{{ $channel['channel_id'] }}][{{ $notificationTypeKey }}]"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" 
                                    value="1"
                                    {{ $notificationType ? 'checked' : '' }}>
                                <label for="{{ $notificationTypeKey }}_{{ $channel['channel_id'] }}" class="ml-2 block text-sm text-gray-700 capitalize">
                                    {{ str_replace('_', ' ', $notificationTypeKey) }}
                                </label>
                            </div>    
                        @endforeach
                    @endif
                    

                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Templates Section -->
    <div class="space-y-4 mt-3">
        <h3 class="text-lg font-medium text-gray-900">Template Selection</h3>
        
        <div class="grid grid-cols-1 gap-4">
            @foreach($channelMappings as $channel)
                @if(count($templates[$channel['channel_id']]) > 0)
                <div class="border border-blue-100 rounded-lg p-4 bg-white">
                    <h4 class="font-medium text-gray-800 mb-3">{{ $channel['name'] }} Template</h4>
                    
                    <select name="channels[{{ $channel['channel_id'] }}][template_id]"
                            class="input-field">
                        <option value="">-- No Template --</option>
                        @foreach($templates[$channel['channel_id']] as $template)
                        <option value="{{ $template['id'] }}"
                                {{ $channelMappings[$channel['channel_id']]['template_id'] == $template['id'] ? 'selected' : '' }}>
                            {{ $template['name'] }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Submit Button -->
    <div class="pt-4">
        <x-button type="submit"                     
            class="btn-primary" 
            label="Save" 
            icon=''
            name="button" 
        />
    </div>
</form>