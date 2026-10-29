<?php

namespace Modules\EmailNotification\Channels;

use Exception;
use Illuminate\Support\Facades\Mail;
use Modules\Notifications\Models\NotificationLog;
use Modules\Notifications\Models\Notification;
use Modules\EmailNotification\Mail\NotificationEmail;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Notifications\Contracts\NotificationChannel;
use Modules\EmailNotification\Models\NotificationTemplate;

class EmailChannel implements NotificationChannel
{
    public function __construct(protected array $config) {}

    public function send(array $notifiables, $notification): void
    {

        $notificationMapping = $notification->getTemplateIdentifier() ?? [];

        $template = NotificationTemplate::where('id', $notificationMapping[$this->getName()] ?? 0)
            ->first();

            
        foreach ($notifiables as $notifiable) {
            try {
                $recipient = [];

                // Log::info("EmailChannel varaible", [$notification->getVariables()]);
                if (!$template) {
                    $compiled = [
                        'subject' => 'Notification',
                        'content' => implode(',\\n', $notification->getVariables())
                    ];
                } else {
                    $compiled = $template->compile($notification->getVariables());
                }

                $attachment = method_exists($notification, 'getEmailAttachments')
                    ? $notification->getEmailAttachments()
                    : ($notification->getVariables()['attachments'] ?? null);

                if(!empty($notification->getAvailableRecipients())) {
                    $recipientTypes = $notification->getRecipientTypeMapping();
                    foreach ($notification->getAvailableRecipients() as $key => $type) {
                        if (!empty($notificationMapping[$key]) && $notificationMapping[$key]) {
                            $typeKey = $recipientTypes[$key] ?? null;
                            
                            if ($typeKey === 'admin') {
                                // Send to super admin email (role_id = 1)
                                $superAdmin = \Modules\Acl\Models\Admin::where('role_id', 1)->first();
                                if ($superAdmin && $superAdmin->email) {
                                    $recipient[] = $superAdmin->email;
                                }
                            } 
                            elseif ($typeKey === 'notifiable') {
                                // Special handling for vendor notifications
                                if ($key === 'notify_vendor') {
                                    // Get vendor email from notification variables
                                    $vendorEmail = $notification->getVariables()['vendor_email'] ?? null;
                                    if ($vendorEmail) {
                                        $recipient[] = $vendorEmail;
                                    }
                                } else {
                                    // Regular notifiable (customer)
                                    if (is_object($notifiable) && method_exists($notifiable, 'routeNotificationFor')) {
                                        $recipient[] = $notifiable->routeNotificationFor('mail', $notification);
                                    } else {
                                        $recipient[] = $notifiable;
                                    }
                                }
                            }
                        }
                    }
                }

                if (empty($recipient)) {
                    throw new Exception("No recipients found for email notification.");
                    continue;
                }

                $log = NotificationLog::create([
                    'event_code' => $notification->getEventCode(),
                    'channel_name' => $this->getName(),
                    'notifiable_type' => is_object($notifiable) ? get_class($notifiable) : 'raw',
                    'notifiable_id' => is_object($notifiable) ? ($notifiable->id ?? null) : 0,
                    'content' => $compiled['content'],
                    'status' => 'queued',
                    'sent_at' => null
                ]);

                // Store in notifications table for app developers
                Notification::create([
                    'type' => get_class($notification),
                    'notifiable_type' => is_object($notifiable) ? get_class($notifiable) : 'raw',
                    'notifiable_id' => is_object($notifiable) ? ($notifiable->id ?? null) : 0,
                    'data' => $compiled['content'], // Just the readable content
                ]);

                $this->dispatchToQueue($recipient, $compiled['subject'], $compiled['content'], $log->id, $attachment);
            } catch (\Throwable $e) {
                ApiExceptionHandler::handle($e);
                NotificationLog::create([
                    'event_code' => $notification->getEventCode(),
                    'channel_name' => $this->getName(),
                    'notifiable_type' => is_object($notifiable) ? get_class($notifiable) : 'raw',
                    'notifiable_id' => is_object($notifiable) ? ($notifiable->id ?? null) : 0,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'config' => json_encode($this->config)
                ]);
                if ($notification instanceof \Modules\Acl\Notifications\AdminPasswordResetNotification) {
                    throw $e;
                }
            }
        }
    }

    protected function dispatchToQueue($recipient, $subject, $content, $logId, $attachment)
    {
        try {   

            $mailable = new NotificationEmail($subject, $content, $logId, $attachment);
            Mail::mailer(fn_get_setting('general.mail.driver'))->to($recipient)->queue($mailable);
            
        } catch (\Throwable $e) {
            
            ApiExceptionHandler::handle($e);
            NotificationLog::where('id', $logId)
                ->update([
                    'status' => 'failed',
                    'error_message' => 'Queue dispatch failed: ' . $e->getMessage()
                ]);

            throw $e;
        }
    }

    /**
     * Method getTemplates
     *
     * @return array
     */
    public function getTemplates()
    {
        return NotificationTemplate::select('id', 'name')
            ->where('status', true)
            ->get()
            ->toArray();
    }

    public function getName(): string
    {
        return 'email';
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    protected function addRecipient(): array
    {
        return $this->normalizeRecipients(fn_get_setting('general.company.mail'));
    }

    protected function normalizeRecipients($value): array
    {
        if (is_string($value)) {
            return array_filter(array_map('trim', explode(',', $value)));
        }
        if (is_array($value)) {
            return array_filter(array_map('trim', $value));
        }
        return [];
    }
}
