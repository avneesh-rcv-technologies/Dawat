<?php

namespace Modules\Notifications\Services;

use Exception;
use Log;
use Modules\Notifications\Contracts\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;

class NotificationDispatcher
{
    public function __construct(
        protected NotificationChannelManager $channelManager
    ) {
    }

    public function dispatch(string $eventCode, $notifiables, $notification): void
    {

        $event = NotificationEvent::where('event_code', $eventCode)->first();
        if (!$event) {
            return;
        }

        try {
            $mappings = $event->activeMappings()
                ->get();
            foreach ($mappings as $mapping) {

                $channel = $this->channelManager->getChannel($mapping->channel->name);
                if (!$channel || !$channel['is_active']) {
                    continue;
                }

                $handler = app($channel['handler'], ['config' => $channel['config']]);
                $handler->send($notifiables, $notification);
            }
        } catch (Exception $e) {
            throw new \Exception('Failed to dispatch.', 0, $e);
        }
    }
}
