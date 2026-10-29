<?php

namespace Modules\Notifications\Channels;

use Modules\Notifications\Models\Notification;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Notifications\Contracts\NotificationChannel;

class SystemChannel implements NotificationChannel
{
    // Channel implementation
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function send(array $notifiables, $notification): void
    {
        // foreach ($notifiables as $notifiable) {
        //     try {
        //         $notifiableType = is_object($notifiable) ? get_class($notifiable) : 'raw';
        //         $notifiableId = is_object($notifiable) ? ($notifiable->id ?? null) : 0;
        //         $data = method_exists($notification, 'toArray') ? $notification->toArray() : ['data' => $notification->getVariables() ?? []];

        //         Notification::create([
        //             'type' => get_class($notification),
        //             'notifiable_type' => $notifiableType,
        //             'notifiable_id' => $notifiableId,
        //             'data' => $data,
        //         ]);
        //     } catch (\Throwable $e) {

        //         ApiExceptionHandler::handle($e);
        //     }
        // }
    }

    public function getName(): string
    {
        return 'system';
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}

