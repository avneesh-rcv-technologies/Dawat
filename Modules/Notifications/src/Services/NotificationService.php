<?php

namespace Modules\Notifications\Services;

use Illuminate\Support\Facades\Log;
use Modules\Notifications\Services\NotificationDispatcher;

class NotificationService
{
    protected $dispatcher;

    public function __construct(NotificationDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Trigger a notification
     *
     * @param string $eventType (e.g., "OrderCreated")
     * @param mixed $notifiable (The recipient)
     * @param mixed $entity (The related entity)
     * @return bool
     */
    public function trigger(string $modulename, string $eventType, $notifiable, $entity, ?array $channels = null): bool
    {
        try {

            // Build notification class name
            $notificationClass = "Modules\\{$modulename}\\Notifications\\{$eventType}Notification";

            if (!class_exists($notificationClass)) {
                Log::warning('Notification class is unavailable.', ['notification_class' => $notificationClass]);
                return false;
            }
            $notification = new $notificationClass($entity);

            // $notification = is_array($entity)
            //     ? new $notificationClass($entity['restaurant'] ?? null, $entity)
            //     : new $notificationClass($entity);


            // Dispatch notification
            return $this->dispatcher->dispatch(
                $notification->getEventCode(),
                [$notifiable],
                $notification,
                $channels
            );

        } catch (\Throwable $e) {
            Log::warning('Notification could not be sent.', [
                'module' => $modulename, 'event_type' => $eventType,
                'exception_type' => get_class($e),
            ]);
            return false;
        }
    }
}
