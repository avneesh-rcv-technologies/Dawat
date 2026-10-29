<?php

namespace Modules\WebPushNotification\Services;

use Exception;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Exception\MessagingException;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class FirebaseNotificationService
{
    private $messaging;

    public function __construct()
    {
        try {
            $serviceAccount = $this->getServiceAccountFromEnv();
            $this->messaging = (new Factory)->withServiceAccount($serviceAccount)->createMessaging();
        } catch (Exception $e) {
            ApiExceptionHandler::handle($e);
            throw $e;
        }
    }

    /**
     * Send a notification to a specific device token
     */
    /**
     * Send FCM Notification to iOS, Android & Web
     */
    public function sendNotification(
        string $token,
        string $device,
        string $title,
        string $body,
        array $data = [],
        array $options = []
    ) {
        try {
            Log::info("FCM preparing to send", [
                'device' => $device,
                'token' => $token,
                'title' => $title,
                'data' => $data,
            ]);
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData(array_merge([
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'timestamp' => now()->toDateTimeString(),
                ], $data));


            // ------------------------------------------------------
            // 🔵 PLATFORM SPECIFIC CONFIGURATION
            // ------------------------------------------------------

            switch ($device) {

                /**
                 * 📱 ANDROID
                 */
                case 'android':
                    $androidConfig = AndroidConfig::fromArray([
                        'ttl' => '3600s',
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'channel_id' => 'high_importance_channel',
                        ],
                    ]);
                    $message = $message->withAndroidConfig($androidConfig);
                    break;

                /**
                 * 🍎 IOS
                 */
                case 'ios':
                    $apnsConfig = ApnsConfig::fromArray([
                        'headers' => [
                            'apns-priority' => '10',
                        ],
                        'payload' => [
                            'aps' => [
                                'alert' => [
                                    'title' => $title,
                                    'body' => $body,
                                ],
                                'sound' => 'default',
                                'badge' => 1,
                            ],
                        ],
                    ]);
                    $message = $message->withApnsConfig($apnsConfig);
                    break;

                /**
                 * 💻 WEB (PWA)
                 */
                case 'web':
                    $webPushConfig = WebPushConfig::fromArray([
                        'fcm_options' => [
                            'link' => $options['click_action'] ?? '/',
                        ],
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                            'icon' => $options['icon'] ?? '/icon.png',
                        ],
                    ]);
                    $message = $message->withWebPushConfig($webPushConfig);
                    break;
            }


            // ------------------------------------------------------
            // 🚀 SEND
            // ------------------------------------------------------
            Log::info("FCM sending", [
                'device' => $device,
                'token' => $token,
                'title' => $title,
                'data' => $data,
            ]);

            return $this->messaging->send($message);

        } catch (\Throwable $e) {
            Log::error("FCM ERROR", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }


    /**
     * Send notification to multiple tokens
     */
    public function sendMulticast(array $tokens, string $title, string $body, array $data = [], array $options = [])
    {
        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData(array_merge([
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'timestamp' => now()->toDateTimeString(),
                ], $data));

            if (!empty($options['webPush'])) {
                $message = $message->withWebPushConfig($options['webPush']);
            }

            $response = $this->messaging->sendMulticast($message, $tokens);

            return $response;
        } catch (MessagingException $e) {
            throw $e;
        }
    }

    /**
     * Send a topic notification
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = [], array $options = [])
    {
        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($notification)
                ->withData(array_merge([
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'timestamp' => now()->toDateTimeString(),
                ], $data));

            if (!empty($options['webPush'])) {
                $message = $message->withWebPushConfig($options['webPush']);
            }

            $response = $this->messaging->send($message);


            return $response;
        } catch (MessagingException $e) {

            throw $e;
        }
    }

    /**
     * Get Firebase service account configuration
     */
    private function getServiceAccountFromEnv(): array
    {
        $projectId = fn_get_setting('firebase_project_id');

        // Try to get service account JSON from settings
        $jsonEnv = fn_get_setting('firebase_service_account_json', '') ?: env('FIREBASE_SERVICE_ACCOUNT_JSON');
        if (!empty($jsonEnv)) {
            // Try base64 decode first
            $decoded = @base64_decode($jsonEnv, true);
            $maybeJson = $decoded !== false && json_decode($decoded, true) ? $decoded : $jsonEnv;
            $data = json_decode($maybeJson, true);
            if (is_array($data)) {
                return $data;
            }
        }

        // Fall back to individual env vars
        return [
            'type' => 'service_account',
            'project_id' => $projectId,
            'private_key_id' => env('FIREBASE_PRIVATE_KEY_ID'),
            'private_key' => preg_replace("/\\\n/", "\n", env('FIREBASE_PRIVATE_KEY')),
            'client_email' => env('FIREBASE_CLIENT_EMAIL'),
            'client_id' => env('FIREBASE_CLIENT_ID'),
            'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
            'token_uri' => 'https://oauth2.googleapis.com/token',
            'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
            'client_x509_cert_url' => env('FIREBASE_CLIENT_CERT_URL')
        ];
    }
}