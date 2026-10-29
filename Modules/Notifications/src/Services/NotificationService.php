<?php

namespace Modules\Notifications\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
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
    public function trigger(string $modulename, string $eventType, $notifiable, $entity): bool
    {
        try {

            // Build notification class name
            $notificationClass = "Modules\\{$modulename}\\Notifications\\{$eventType}Notification";

            if (!class_exists($notificationClass)) {
                throw new \Exception("Notification class {$notificationClass} not found");
            }
            $notification = new $notificationClass($entity);

            // $notification = is_array($entity)
            //     ? new $notificationClass($entity['restaurant'] ?? null, $entity)
            //     : new $notificationClass($entity);


            // Dispatch notification
            $this->dispatcher->dispatch(
                $notification->getEventCode(),
                [$notifiable],
                $notification
            );

            return true;
        } catch (\Exception $e) {
            ApiExceptionHandler::handle($e);
            return false;
        }
    }
}
