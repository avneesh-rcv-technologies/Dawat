<?php

namespace Modules\WebPushNotification\Channels;

use Exception;
use Modules\Acl\Models\Admin;
use Modules\Customers\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use Modules\Notifications\Models\NotificationLog;
use Modules\Notifications\Models\Notification;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Notifications\Contracts\NotificationChannel;
use Modules\WebPushNotification\Models\WebPushSubscription;
use Modules\WebPushNotification\Models\NotificationTemplate;
use Modules\WebPushNotification\Jobs\SendPushNotificationJob;
use Modules\WebPushNotification\Services\FirebaseNotificationService;

class WebPushChannel implements NotificationChannel
{
    protected $firebaseService;

    public function __construct(protected array $config)
    {
        $this->firebaseService = new FirebaseNotificationService();
    }

    public function send(array $notifiables, $notification): void
    {
       

        $notificationMapping = $notification->getTemplateIdentifier() ?? [];
        $pushVariables = method_exists($notification, 'getPushVariables')
            ? $notification->getPushVariables()
            : $notification->getVariables();
        Log::info('Notification mapping: ', $notificationMapping);
        $template = NotificationTemplate::where('id', $notificationMapping[$this->getName()] ?? 0)
            ->first();

        // if (!$template) {
        //     throw new \Exception("Template not found: {$notificationMapping[$this->getName()]}");
        // }

        // Log::info('Template found: ' . ($template ? $template->name : 'No template'), ['notification' => $notification, 'mapping' => $notificationMapping]);

        foreach ($notifiables as $notifiable) {
            try {

                $recipients = [];

                if (!$template) {
                    $compiled = [
                        'subject' => 'Notification',
                        'content' => implode(',\\n', $pushVariables)
                    ];
                } else {
                    $compiled = $template->compile($pushVariables);
                }

                // Get available recipients and their type mappings dynamically
                $availableRecipients = [];
                $recipientTypeMapping = [];
                $recipientClassMapping = [];

                if (method_exists($notification, 'getAvailableRecipients')) {
                    $availableRecipients = $notification->getAvailableRecipients();
                    Log::info('Available recipients: ' . json_encode($availableRecipients));
                }

                if (method_exists($notification, 'getRecipientTypeMapping')) {
                    $recipientTypeMapping = $notification->getRecipientTypeMapping();
                    Log::info('Recipient type mapping: ' . json_encode($recipientTypeMapping));
                }

                if (method_exists($notification, 'getRecipientClassMapping')) {
                    $recipientClassMapping = $notification->getRecipientClassMapping();
                    Log::info('Recipient class mapping: ' . json_encode($recipientClassMapping));
                }

                // Dynamically add recipients based on configuration
                foreach ($availableRecipients as $recipientKey => $recipientLabel) {
                    if (!empty($notificationMapping[$recipientKey]) && $notificationMapping[$recipientKey]) {

                        $recipientType = $recipientTypeMapping[$recipientKey] ?? 'notifiable';
                        $subscribableClass = $recipientClassMapping[$recipientKey] ?? Admin::class;

                        if ($recipientType === 'admin') {
                            // Get admin subscriptions
                            Log::info("Fetching admin subscriptions for class: {$subscribableClass}");
                            $subscriptions = WebPushSubscription::where('subscribable_type', $subscribableClass)
                                ->whereHas('subscribable', function ($query) {
                                    $query->where('role_id', 1);
                                })
                                ->get(['token', 'device']);
                            
                            Log::info("Found admin subscriptions: " . json_encode($subscriptions));

                            foreach ($subscriptions as $subscription) {
                                $recipients[] = [
                                    'token' => $subscription->token,
                                    'device' => $subscription->device,
                                    'type' => 'notification',
                                ];
                            }
                            
                            Log::info("Admin recipients added: " . count($subscriptions));
                        } elseif ($recipientType === 'notifiable') {
                            // Get notifiable user's subscriptions
                            // Special handling for vendor notifications
                            if ($recipientKey === 'notify_vendor') {
                                // Get vendor email from notification variables
                                $email = $pushVariables['vendor_email'] ?? null;
                                Log::info("Vendor notification - using vendor email: {$email}");
                            } else {
                                // Regular notifiable (customer)
                                if (is_object($notifiable) && method_exists($notifiable, 'routeNotificationFor')) {
                                    $email = $notifiable->routeNotificationFor('mail', $notification);
                                } else if (is_array($notifiable) && isset($notifiable['email'])) {
                                    $email = $notifiable['email'];
                                } else {
                                    $email = $notifiable;
                                }
                            }

                            if (empty($email)) {
                                Log::info("No email found for {$recipientKey}, skipping");
                                continue;
                            }

                            Log::info("Fetching subscriptions for email: {$email} and class: {$subscribableClass}");
                            // Fetch tokens and devices from WebPushSubscription model
                            $subscriptions = WebPushSubscription::where('subscribable_type', $subscribableClass)
                                ->whereHas('subscribable', function ($query) use ($email) {
                                    $query->where('email', $email);
                                })
                                ->get(['token', 'device']);


                            Log::info("Found subscriptions: " . json_encode($subscriptions));
                            foreach ($subscriptions as $subscription) {

                                $recipients[] = [
                                    'token' => $subscription->token,
                                    'device' => $subscription->device,
                                    'type' => 'notification',
                                ];
                            }

                            Log::info("Subscriptions for email {$email}: " . json_encode($recipients));
                        }
                    }
                }

                Log::info('Final recipients: ' . json_encode($recipients));

                if (empty($recipients)) {
                    continue;
                }

                //  get multiple $recipients
                foreach ($recipients as $recipient) {

                    try {
                        $log = NotificationLog::create([
                            'event_code' => $notification->getEventCode(),
                            'channel_name' => $this->getName(),
                            'notifiable_type' => is_object($notifiable) ? get_class($notifiable) : 'raw',
                            'notifiable_id' => is_object($notifiable) ? ($notifiable->id ?? null) : 0,
                            'content' => $compiled['content'],
                            'status' => 'queued',
                            'sent_at' => null
                        ]);
                        
                        $variables = $pushVariables;
                        \Log::info('Notification variables: ', $variables);
                        
                        // Find which subscription this token belongs to
                        $subscription = WebPushSubscription::where('token', $recipient['token'])->first();
                        
                        // Determine correct notifiable type and ID based on subscription
                        $notifiableType = \Modules\Acl\Models\Customer::class;
                        $notifiableId = $variables['customer_id'] ?? 0;
                        
                        if ($subscription) {
                            // Use the subscription's subscribable type and ID
                            $notifiableType = $subscription->subscribable_type;
                            $notifiableId = $subscription->subscribable_id;
                            \Log::info("Notification stored for {$notifiableType} ID: {$notifiableId} (from subscription)");
                        } else {
                            \Log::warning("No subscription found for token: " . substr($recipient['token'], 0, 20) . "...");
                        }
                        
                        Notification::create([
                            'type' => get_class($notification),
                            'notifiable_type' => $notifiableType,
                            'notifiable_id' => $notifiableId,
                            'data' => $compiled['content'],
                        ]);

                        // Prepare payload data
                        // $payload = [
                        //     'type' => $recipient['type'] ?? 'test',
                        //     // 'job_id' => $recipient['job_id'] ?? null,
                        // ];
                         $payload = array_merge(
                            ['type' => $recipient['type'] ?? 'notification'],
                            $pushVariables
                        );

                        SendPushNotificationJob::dispatch(
                            $recipient['token'],
                            $recipient['device'],
                            $compiled['subject'],
                            strip_tags($compiled['content']),
                            $payload,
                            $log->id
                        )->onQueue('push_notifications');
                    } catch (\Exception $e) {

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
                    }
                }
            } catch (\Throwable $e) {
                ApiExceptionHandler::handle($e);
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
        return NotificationTemplate::select('id', 'name')
            ->where('is_active', true)
            ->get()
            ->toArray();
    }

    public function getName(): string
    {
        return 'webpush';
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
