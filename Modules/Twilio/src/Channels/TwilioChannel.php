<?php

namespace Modules\Twilio\Channels;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Twilio\Models\SmsTemplate;
use Modules\Notifications\Models\NotificationLog;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Notifications\Contracts\NotificationChannel;

class TwilioChannel implements NotificationChannel
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send(array $notifiables, $notification): void
    {
        $notificationMapping = $notification->getTemplateIdentifier() ?? [];

        $template = SmsTemplate::where('event_code', $notification->getEventCode())
            ->where('is_active', true)
            ->first();

        if (!$template) {
            throw new \Exception("SMS template not found for event: " . $notification->getEventCode());
        }

        foreach ($notifiables as $notifiable) {
            try {
                $recipients = [];
                $compiled = $template->compile($notification->getVariables());

                if (!empty($notificationMapping['notify_user']) && $notificationMapping['notify_user']) {
                    if (is_object($notifiable) && method_exists($notifiable, 'routeNotificationFor')) {
                        $phone = $notifiable->routeNotificationFor('sms', $notification);
                        if ($phone) {
                            $recipients[] = $phone;
                        }
                    } else {
                        $recipients[] = $notifiable;
                    }
                }

                if (empty($recipients)) {
                    continue;
                }

                $log = NotificationLog::create([
                    'event_code'      => $notification->getEventCode(),
                    'channel_name'    => $this->getName(),
                    'notifiable_type' => is_object($notifiable) ? get_class($notifiable) : 'raw',
                    'notifiable_id'   => is_object($notifiable) ? ($notifiable->id ?? null) : 0,
                    'content'         => $compiled['content'],
                    'status'          => 'queued',
                    'sent_at'         => null
                ]);

                // Dispatch to queue
                dispatch(new \Modules\Twilio\Jobs\SendNotificationSmsJob(
                    $recipients,
                    $compiled['content'],
                    $log->id,
                    $this->config
                ));
            } catch (\Throwable $e) {
                ApiExceptionHandler::handle($e);
                NotificationLog::create([
                    'event_code'      => $notification->getEventCode(),
                    'channel_name'    => $this->getName(),
                    'notifiable_type' => is_object($notifiable) ? get_class($notifiable) : 'raw',
                    'notifiable_id'   => is_object($notifiable) ? ($notifiable->id ?? null) : 0,
                    'status'          => 'failed',
                    'error_message'   => $e->getMessage(),
                    'config'          => json_encode($this->config)
                ]);
            }
        }
    }

    /**
     * Method getTemplates
     *
     * @return array
     */
    public function getTemplates()
    {
        return SmsTemplate::select('id', 'name')
            ->where('is_active', true)
            ->get()
            ->toArray();
    }

    public function getName(): string
    {
        return 'sms';
    }
    public function getConfig(): array
    {
        return $this->config;
    }
}
