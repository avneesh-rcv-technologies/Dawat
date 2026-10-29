<?php

namespace Modules\Twilio\Notifications;

use Exception;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;

class QueueNotification implements Notification
{
    /**
     * $data should contain at least: 'status' => 'failed'|'completed', and other context
     */
    public function __construct(protected $data) {}

    public function getEventCode(): string
    {
        // We'll use a generic code; NotificationMapping can map specific templates per channel
        return 'twilio_queue_update';
    }

    public function getTemplateIdentifier(): array
    {
        $channels = [];
        try {
            $event = NotificationEvent::where('event_code', $this->getEventCode())->first();

            if (!$event) {
                return $channels;
            }

            $mappings = NotificationMapping::where('event_id', $event->id)->get();

            foreach ($mappings as $mapping) {
                $channel = NotificationChannel::where('id', $mapping->channel_id)->first();
                $channels[$channel->name] = $mapping->template_id;
                $channels['notify_user'] = $mapping->notify_customer;
                $channels['notify_admin'] = $mapping->notify_admin;
            }
        } catch (Exception $e) {
            // ignore
        }

        return $channels;
    }

    public function getVariables(): array
    {
        // Add a human friendly status label
        $status = $this->data['status'] ?? null;
        $this->data['status_label'] = $status === 'failed' ? 'Failed' : ($status === 'completed' ? 'Completed' : ucfirst((string)$status));
        // Link to the upload progress page (reuse upload-progress route)
        $this->data['queue_url'] = isset($this->data['queue']) ? route('admin.twilio.upload-progress', $this->data['queue']->id) : null;

        return $this->data;
    }

    public function getAvailableVariables(): array
    {
        return [
            'queue_id'     => 'Queue/Upload ID',
            'status'       => "Raw status (e.g. 'failed' or 'completed')",
            'status_label' => 'Human friendly status',
            'total'        => 'Total items in queue',
            'processed'    => 'Processed count',
            'failed'       => 'Failed count',
            'queue_url'    => 'Admin URL to view queue upload details',
        ];
    }

      public function getAvailableRecipients(): array
    {
        return [
            'notify_customer' => 'Notify Customer',
            'notify_admin' => 'Notify Admin',
            'notify_vendor' => 'Notify Vendor/Restaurant',
        ];
    }

    public function getRecipientTypeMapping(): array
    {
        return [
            'notify_customer' => 'notifiable',
            'notify_admin' => 'admin',
            'notify_vendor' => 'notifiable',
        ];
    }

    public function getRecipientClassMapping(): array
    {
        return [
            'notify_customer' => \Modules\Acl\Models\Customer::class,
            'notify_admin' => \Modules\Acl\Models\Admin::class,
            'notify_vendor' => \Modules\Acl\Models\Admin::class,
        ];
    }

    public function toArray(): array
    {
        return [
            'event_code'          => $this->getEventCode(),
            'template'            => $this->getTemplateIdentifier(),
            'variables'           => $this->getVariables(),
            'available_variables' => $this->getAvailableVariables()
        ];
    }
}
