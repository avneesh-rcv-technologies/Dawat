<?php

namespace Modules\WebPushNotification\Http\Controllers;

use Artisan;
use Exception;
use Google\Service\Monitoring\Custom;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Modules\Acl\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Kreait\Firebase\Contract\Messaging;
use Illuminate\Support\Facades\Validator;
use Modules\Technicians\Models\Technician;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Factory as FirebaseFactory;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\WebPushNotification\Models\WebPushSubscription;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Modules\Acl\Models\Customer;
use Modules\WebPushNotification\Services\FirebaseNotificationService;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $config = $this->getFirebaseConfig();
        return view('webpushnotification::firebase-config', compact('config'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used for settings
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Not used for settings
        abort(404);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Not used for settings
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Not used for settings
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id = null)
    {
        // ✅ VALIDATION
        $validated = $request->validate([
            // ---------------- Web ----------------
            'api_key' => 'required|string',
            'auth_domain' => 'required|string',
            'project_id' => 'required|string',
            'storage_bucket' => 'nullable|string',
            'messaging_sender_id' => 'nullable|string',
            'app_id' => 'required|string',
            'measurement_id' => 'nullable|string',
            'vapid_key' => 'required|string',
            'service_account_json' => 'nullable|string',

            // ---------------- iOS ----------------
            'ios_app_id' => 'nullable|string',
            'ios_api_key' => 'nullable|string',
            'ios_bundle_id' => 'nullable|string',
            'ios_messaging_sender_id' => 'nullable|string',
        ]);

        // -----------------------------------------------------
        // 🔵 SAVE WEB SETTINGS
        // -----------------------------------------------------
        fn_update_setting('firebase_api_key', $validated['api_key']);
        fn_update_setting('firebase_auth_domain', $validated['auth_domain']);
        fn_update_setting('firebase_project_id', $validated['project_id']);
        fn_update_setting('firebase_storage_bucket', $validated['storage_bucket'] ?? '');
        fn_update_setting('firebase_messaging_sender_id', $validated['messaging_sender_id'] ?? '');
        fn_update_setting('firebase_app_id', $validated['app_id']);
        fn_update_setting('firebase_measurement_id', $validated['measurement_id'] ?? '');
        fn_update_setting('firebase_vapid_key', $validated['vapid_key']);
        fn_update_setting('firebase_service_account_json', $validated['service_account_json'] ?? '');

        // -----------------------------------------------------
        // 🔴 SAVE IOS SETTINGS
        // -----------------------------------------------------
        fn_update_setting('ios_app_id', $validated['ios_app_id'] ?? '');
        fn_update_setting('ios_api_key', $validated['ios_api_key'] ?? '');
        fn_update_setting('ios_bundle_id', $validated['ios_bundle_id'] ?? '');
        fn_update_setting('ios_messaging_sender_id', $validated['ios_messaging_sender_id'] ?? '');

        // -----------------------------------------------------
        // 🔥 REGENERATE SERVICE WORKER JS FOR WEB PUSH
        // -----------------------------------------------------
        $swContent = $this->generateServiceWorkerContent();
        File::put(public_path('firebase-messaging-sw.js'), $swContent);

        // -----------------------------------------------------
        // 🔁 CLEAR CONFIG CACHE
        // -----------------------------------------------------
        \Artisan::call('config:clear');

        return redirect()->route('firebase.index')
            ->with('success', 'Firebase configuration updated successfully!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Not used for settings
        abort(404);
    }

    /**
     * Get Firebase configuration from settings
     */
    private function getFirebaseConfig(): array
    {
        return [
            'api_key' => fn_get_setting('firebase_api_key', ''),
            'auth_domain' => fn_get_setting('firebase_auth_domain', ''),
            'project_id' => fn_get_setting('firebase_project_id', ''),
            'storage_bucket' => fn_get_setting('firebase_storage_bucket', ''),
            'messaging_sender_id' => fn_get_setting('firebase_messaging_sender_id', ''),
            'app_id' => fn_get_setting('firebase_app_id', ''),
            'measurement_id' => fn_get_setting('firebase_measurement_id', ''),
            'vapid_key' => fn_get_setting('firebase_vapid_key', ''),
        ];
    }

    /**
     * Generate default firebase service worker content using settings.
     */
    private function generateServiceWorkerContent(): string
    {
        $cfg = $this->getFirebaseConfig();

        // Safely escape single quotes in settings to avoid breaking JS string literals
        $escape = function ($v) {
            return str_replace("'", "\\'", $v ?? '');
        };

        $apiKey = $escape($cfg['api_key']);
        $authDomain = $escape($cfg['auth_domain']);
        $projectId = $escape($cfg['project_id']);
        $storageBucket = $escape($cfg['storage_bucket']);
        $messagingSenderId = $escape($cfg['messaging_sender_id']);
        $appId = $escape($cfg['app_id']);
        $measurementId = $escape($cfg['measurement_id']);

        $content = <<<JS
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

// The service worker cannot access page-scoped JS variables directly.
// We initialize the Firebase SDK here using values from site settings.

try {
    firebase.initializeApp({
        apiKey: '{$apiKey}',
        authDomain: '{$authDomain}',
        projectId: '{$projectId}',
        storageBucket: '{$storageBucket}',
        messagingSenderId: '{$messagingSenderId}',
        appId: '{$appId}',
        measurementId: '{$measurementId}'
    });

    const messaging = firebase.messaging();

    messaging.onBackgroundMessage((payload) => {
      console.log('[firebase-messaging-sw.js] Received background message', payload);
      const notificationTitle = payload.notification?.title || payload.data?.title || 'Notification';
      const notificationOptions = {
        body: payload.notification?.body || payload.data?.body || '',
        icon: fn_get_setting('general.company.favicon', 0)['url'] || '/public/favicon.ico',
      };

      self.registration.showNotification(notificationTitle, notificationOptions);
    });
} catch (e) {
    console.error('Failed to initialize Firebase in service worker:', e);
}
JS;

        return $content;
    }

    /**
     * Handle web push subscription.
     */
    public function subscribe(Request $request)
    {

        $validated = $request->validate([
            'fcm_token' => 'required|string',
            'device' => 'required|string|in:web,android,ios',
        ]);

        try {
            $subscribableType = null;
            $subscribableId = null;
            if (Auth::guard('customer')->check()) {
                $subscribableType = Customer::class;
                $subscribableId = Auth::guard('customer')->id();
            } elseif (Auth::guard('admin')->check()) {
                $subscribableType = Admin::class;
                $subscribableId = Auth::guard('admin')->id();
            } else {
                return response()->json([
                    'success' => false,
                    'errors' => 'Unauthorized. Please login first.',
                ], 401);
            }



            // Update or create subscription by user + device
            WebPushSubscription::updateOrCreate(
                [
                    'subscribable_type' => $subscribableType,
                    'subscribable_id' => $subscribableId,
                    'device_id' => $request->header('User-Agent'), // unique per device/browser
                ],
                [
                    'token' => $validated['fcm_token'],
                    'device' => $validated['device'],
                    'browser' => $request->header('User-Agent'),
                    'ip_address' => $request->ip(),
                    'last_active_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Successfully subscribed to push notifications.',
            ]);
        } catch (\Exception $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'success' => false,
                'errors' => 'Failed to subscribe to push notifications.',
            ]);
        }
    }


    /**
     * Unsubscribe from push notifications.
     */
    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        try {
            WebPushSubscription::where('admin_id', Auth::guard(\Illuminate\Support\Facades\Auth::getDefaultDriver())->id())
                ->where('token', $validated['token'])
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Successfully unsubscribed from push notifications.',
            ]);
        } catch (Exception $e) {
            Log::error('WebPush unsubscribe error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to unsubscribe from push notifications.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send a test notification to the current user
     */


    public function sendTestNotification(Request $request)
    {
        try {
            // $token = $request->token;
            // $device = $request->device; // 'web', 'android', 'ios'

            // $tokens = WebPushSubscription::select('token', 'device')->where('subscribable_id', Auth::guard(\Illuminate\Support\Facades\Auth::getDefaultDriver())->id())->get();
            $token = "eIJGWpMbLi3y-0Rhs4lVJe:APA91bH9j5bckXZKlfPMojp3NhIjTSzoFfi5u9WODbXMC-ioS_gakndtn9p5QfrODhTu9J6LapSyYWEzg0U_tccaBStwy8Yjc6yuwS8TiT11oP0hccNiQ-Q";
            $device = "web";

            // foreach ($tokens as $t) {
            //     $token = $t->token;
            //     $device = $t->device; // Assuming web for this example, adjust as needed

            //     if (!$token || !$device) {
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'Token and device type are required.',
            //         ], 422);
            //     }

            //     $service = new FirebaseNotificationService();

            //     $title = 'Test Notification';
            //     $body = 'This is a test notification from ' . config('app.name');

            //     $payload = [
            //         'type' => 'test',
            //     ];

            //     $response = $service->sendNotification(
            //         token: $token,
            //         device: $device,
            //         title: $title,
            //         body: $body,
            //         data: $payload
            //     );
            // }

            $service = new FirebaseNotificationService();

            $title = 'Test Notification';
            $body = 'This is a test notification from ' . config('app.name');

            $payload = [
                'type' => 'test',
            ];

            $response = $service->sendNotification(
                token: $token,
                device: $device,
                title: $title,
                body: $body,
                data: $payload
            );

            return response()->json([
                'success' => true,
                'message' => 'Test notification sent successfully',
                'response' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
