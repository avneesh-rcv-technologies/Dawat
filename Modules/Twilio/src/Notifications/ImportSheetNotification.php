<?php

namespace Modules\Twilio\Notifications;

use Exception;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;

class ImportSheetNotification implements Notification
{
    public function __construct(protected $data) {}

    public function getEventCode(): string
    {
        // match the event_code defined in NotificationEvent table
        return 'twilio_import_sheet';
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
            // swallow exceptions to avoid breaking the caller
        }

        return $channels;
    }

    public function getVariables(): array
    {
        // Link to the upload progress page (routes use 'upload-progress')
        $this->data['import_url'] = isset($this->data['upload']) ? route('admin.twilio.upload-progress', $this->data['upload']->id) : null;
        return $this->data;
    }

    public function getAvailableVariables(): array
    {
        return [
            'upload_id'  => 'Upload ID',
            'file_name'  => 'Uploaded file name',
            'total'      => 'Total rows in sheet',
            'success'    => 'Number of successful rows',
            'failed'     => 'Number of failed rows',
            'import_url' => 'Admin URL to view upload progress',
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
