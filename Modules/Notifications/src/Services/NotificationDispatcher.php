<?php

namespace Modules\Notifications\Services;

use Illuminate\Support\Facades\Log;
use Modules\Notifications\Models\NotificationEvent;

class NotificationDispatcher
{
    public function __construct(
        protected NotificationChannelManager $channelManager
    ) {
    }

    public function dispatch(string $eventCode, $notifiables, $notification, ?array $channels = null): bool
    {
        try {
            $event = NotificationEvent::where('event_code', $eventCode)->first();
            if (!$event) {
                return false;
            }
            $mappings = $event->activeMappings()
                ->when($channels !== null, fn ($query) => $query->whereHas('channel', fn ($query) => $query->whereIn('name', $channels)))
                ->get();
            $sent = false;
            $failed = false;
            foreach ($mappings as $mapping) {
                try {
                    $channel = $this->channelManager->getChannel($mapping->channel->name);
                    if (!$channel || !$channel['is_active']) {
                        continue;
                    }
                    $handler = app($channel['handler'], ['config' => $channel['config']]);
                    $handler->send($notifiables, $notification);
                    $sent = true;
                } catch (\Throwable $e) {
                    $failed = true;
                    Log::warning('Notification channel delivery failed.', [
                        'event_code' => $eventCode,
                        'mapping_id' => $mapping->id,
                        'exception_type' => get_class($e),
                    ]);
                }
            }
            return $sent && !$failed;
        } catch (\Throwable $e) {
            Log::warning('Notification dispatch could not be completed.', [
                'event_code' => $eventCode,
                'exception_type' => get_class($e),
            ]);
            return false;
        }
    }
}
