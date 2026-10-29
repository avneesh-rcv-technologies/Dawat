<?php

namespace Modules\Admin\Notifications;

use Exception;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class UserNotification implements Notification
{
    public function __construct(protected $data)
    {
    }

    public function getEventCode(): string
    {
        return 'create_user_account';
    }

    public function getTemplateIdentifier(): array
    {
        $channels = [];
        try {
            // Get the event first
            $event = NotificationEvent::where('event_code', $this->getEventCode())->first();

            if (!$event) {
                return $channels;
            }
            // Get the mapping using the event ID
            $mappings = NotificationMapping::where('event_id', $event->id)
                ->get();
            foreach ($mappings as $key => $mapping) {
                $channel_name = NotificationChannel::where('id', $mapping->channel_id)->first();
                $channels[$channel_name->name] = $mapping->template_id;
                
                // Merge recipients from JSON for EmailChannel compatibility
                if (!empty($mapping->recipients)) {
                    $channels = array_merge($channels, $mapping->recipients);
                }
            }
        } catch (Exception $e) {
        }

        return $channels;
    }

    public function getVariables(): array
    {
        $this->data['login_url'] = route('admin.login');
        return $this->data;
    }

    public function getAvailableVariables(): array
    {
        return [
            'name' => "User name",
            'email' => "User email",
            'phone' => "User Phone",
            'password' => "User Random password",
            'login_url' => "Login URL",
        ];
    }

    public function getAvailableRecipients(): array
    {
        return [
            'notify_admin' => 'Notify Admin',
            'notify_user_admin' => 'Notify User Admin',
        ];
    }

    public function getRecipientTypeMapping(): array
    {
        return [
            'notify_admin' => 'admin',
            'notify_user_admin' => 'notifiable'
        ];
    }

    public function getRecipientClassMapping(): array
    {
        return [
            'notify_admin' => \Modules\Acl\Models\Admin::class,
            'notify_user_admin' => \Modules\Acl\Models\Admin::class
        ];
    }

    public function toArray(): array
    {
        return [
            'event_code' => $this->getEventCode(),
            'template' => $this->getTemplateIdentifier(),
            'variables' => $this->getVariables(),
            'available_variables' => $this->getAvailableVariables()
        ];
    }
}
