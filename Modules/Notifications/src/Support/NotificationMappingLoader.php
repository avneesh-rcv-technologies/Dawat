<?php

namespace Modules\Notifications\Support;

use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;

final class NotificationMappingLoader
{
    /**
     * Channel template IDs, recipient flags, and email provider for an event.
     *
     * @return array<string, mixed>
     */
    public static function forEventCode(string $eventCode): array
    {
        $channels = [];

        $event = NotificationEvent::query()->where('event_code', $eventCode)->first();

        if (! $event) {
            return $channels;
        }

        foreach (NotificationMapping::query()->where('event_id', $event->id)->get() as $mapping) {
            $channel = NotificationChannel::query()->find($mapping->channel_id);

            if (! $channel) {
                continue;
            }

            $channels[$channel->name] = $mapping->template_id;

            if ($channel->name === 'email' && $mapping->email_provider_id) {
                $channels['email_provider_id'] = (int) $mapping->email_provider_id;
            }

            if (! empty($mapping->recipients) && is_array($mapping->recipients)) {
                $channels = array_merge($channels, $mapping->recipients);
            }
        }

        return $channels;
    }
}
