<?php

namespace Modules\Restaurant\Listeners;

use Exception;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Notifications\RestaurantOnboardingNotification;
use Modules\Restaurant\Notifications\RestaurantOnboardingConfirmationNotification;
use Modules\Restaurant\Notifications\RestaurantApprovedNotification;
use Modules\Restaurant\Notifications\RestaurantRejectedNotification;
use Modules\Restaurant\Notifications\RestaurantCreatedNotification;
use Modules\Restaurant\Notifications\RestaurantStatusChangedNotification;
use Modules\Notifications\Contracts\Notification;

class RestaurantNotificationHandler
{
    /**
     * Get the notification class for the given event type
     */
    public static function getNotificationClass(string $eventType, $entity): ?Notification
    {
        $notificationClass = match (strtolower($eventType)) {
            'onboarding' => RestaurantOnboardingNotification::class,
            'onboardingconfirmation', 'onboarding_confirmation' => RestaurantOnboardingConfirmationNotification::class,
            'approved' => RestaurantApprovedNotification::class,
            'rejected' => RestaurantRejectedNotification::class,
            'created' => RestaurantCreatedNotification::class,
            'statuschanged', 'status_changed' => RestaurantStatusChangedNotification::class,
            default => null,
        };

        if (!$notificationClass) {
            return null;
        }

        try {
            return new $notificationClass($entity);
        } catch (Exception $e) {
            \Log::error('Failed to instantiate restaurant notification', [
                'event_type' => $eventType,
                'class' => $notificationClass,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Handle restaurant onboarding notification
     */
    public function handleOnboarding($notifiable, Restaurant $restaurant, $notification)
    {
        // Log activity
        if (function_exists('fn_log_activity')) {
            fn_log_activity([
                'activity' => 'Restaurant onboarding notification sent',
                'description' => "Notification sent for restaurant onboarding: {$restaurant->name}",
                'subject_type' => Restaurant::class,
                'subject_id' => $restaurant->id,
                'causer_type' => get_class($notifiable),
                'causer_id' => $notifiable->id,
            ]);
        }
    }

    /**
     * Handle restaurant approved notification
     */
    public function handleApproved($notifiable, Restaurant $restaurant, $notification)
    {
        // Log activity
        if (function_exists('fn_log_activity')) {
            fn_log_activity([
                'activity' => 'Restaurant approved notification sent',
                'description' => "Approval notification sent to {$notifiable->name} for restaurant: {$restaurant->name}",
                'subject_type' => Restaurant::class,
                'subject_id' => $restaurant->id,
                'causer_type' => get_class($notifiable),
                'causer_id' => $notifiable->id,
            ]);
        }
    }

    /**
     * Handle restaurant rejected notification
     */
    public function handleRejected($notifiable, Restaurant $restaurant, $notification)
    {
        // Log activity
        if (function_exists('fn_log_activity')) {
            fn_log_activity([
                'activity' => 'Restaurant rejected notification sent',
                'description' => "Rejection notification sent to {$notifiable->name} for restaurant: {$restaurant->name}",
                'subject_type' => Restaurant::class,
                'subject_id' => $restaurant->id,
                'causer_type' => get_class($notifiable),
                'causer_id' => $notifiable->id,
            ]);
        }
    }

    /**
     * Handle restaurant created notification
     */
    public function handleCreated($notifiable, Restaurant $restaurant, $notification)
    {
        // Log activity
        if (function_exists('fn_log_activity')) {
            fn_log_activity([
                'activity' => 'Restaurant created notification sent',
                'description' => "Creation notification sent for restaurant: {$restaurant->name}",
                'subject_type' => Restaurant::class,
                'subject_id' => $restaurant->id,
                'causer_type' => get_class($notifiable),
                'causer_id' => $notifiable->id,
            ]);
        }
    }

    /**
     * Handle restaurant status changed notification
     */
    public function handleStatusChanged($notifiable, Restaurant $restaurant, $notification)
    {
        // Log activity
        if (function_exists('fn_log_activity')) {
            fn_log_activity([
                'activity' => 'Restaurant status changed notification sent',
                'description' => "Status change notification sent for restaurant: {$restaurant->name}",
                'subject_type' => Restaurant::class,
                'subject_id' => $restaurant->id,
                'causer_type' => get_class($notifiable),
                'causer_id' => $notifiable->id,
            ]);
        }
    }
}
