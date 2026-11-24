<?php

namespace Modules\Notifications\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Modules\Notifications\Contracts\Notification;
use Illuminate\Support\Facades\Validator;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;

class NotificationController extends Controller
{

    /**
     * Discover all Notification classes from modules
     */
    protected function discoverNotificationClasses()
    {
        $notificationClasses = [];
        $modulesPath = base_path('Modules');

        if (!File::exists($modulesPath)) {
            return $notificationClasses;
        }

        // Scan all module directories
        $modules = File::directories($modulesPath);

        foreach ($modules as $modulePath) {
            $notificationsPath = $modulePath . '/src/Notifications';

            if (File::exists($notificationsPath)) {
                $files = File::files($notificationsPath);

                foreach ($files as $file) {
                    if ($file->getExtension() === 'php') {
                        $moduleName = basename($modulePath);
                        $className = $file->getBasename('.php');
                        $fullClassName = "Modules\\{$moduleName}\\Notifications\\{$className}";

                        try {
                            // Autoloading itself can throw, so keep it inside the per-file boundary.
                            if (!class_exists($fullClassName)) {
                                continue;
                            }
                            $reflection = new \ReflectionClass($fullClassName);
                            if (!$reflection->isInstantiable() || !$reflection->implementsInterface(Notification::class)) {
                                continue;
                            }
                            $instance = $reflection->newInstanceWithoutConstructor();
                            $eventCode = $instance->getEventCode();
                            if (trim($eventCode) === '') {
                                continue;
                            }
                            $notificationClasses[] = [
                                'class' => $fullClassName,
                                'event_code' => $eventCode,
                                'event_name' => ucwords(str_replace('_', ' ', $eventCode)),
                                'module' => $moduleName,
                            ];
                        } catch (\Throwable $e) {
                            Log::warning('Skipping invalid notification definition.', [
                                'notification_class' => $fullClassName, 'error' => $e->getMessage(),
                            ]);
                            continue;
                        }
                    }
                }
            }
        }

        return $notificationClasses;
    }

    protected function availableRecipientsFor(?string $className): array
    {
        $fallback = ['notify_admin' => 'Notify Admin', 'notify_user_admin' => 'Notify Admin User'];
        if (empty($className)) {
            return $fallback;
        }
        try {
            $reflection = new \ReflectionClass($className);
            if (!$reflection->isInstantiable() || !$reflection->implementsInterface(Notification::class)) {
                return $fallback;
            }
            return $reflection->newInstanceWithoutConstructor()->getAvailableRecipients();
        } catch (\Throwable $e) {
            Log::warning('Using fallback notification recipients.', [
                'notification_class' => $className, 'error' => $e->getMessage(),
            ]);
            return $fallback;
        }
    }

    protected function templatesForChannel(?string $className, ?string $channelName = null): array
    {
        // Template selection is reference data; it must not initialize mail/Firebase clients.
        if ($channelName === 'email') {
            return \Modules\EmailNotification\Models\NotificationTemplate::where('status', true)
                ->orderBy('name')->get(['id', 'name'])->toArray();
        }
        if ($channelName === 'webpush') {
            return \Modules\WebPushNotification\Models\NotificationTemplate::where('is_active', true)
                ->orderBy('name')->get(['id', 'name'])->toArray();
        }
        if (empty($className)) {
            return [];
        }
        try {
            $reflection = new \ReflectionClass($className);
            if (!$reflection->isInstantiable()) {
                return [];
            }
            $channel = app($className, ['config' => []]);
            return method_exists($channel, 'getTemplates') ? $channel->getTemplates() : [];
        } catch (\Throwable $e) {
            Log::warning('Skipping unavailable notification channel templates.', [
                'channel_class' => $className, 'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Sync discovered notification classes with database
     */
    protected function syncNotificationEvents()
    {
        $discoveredClasses = $this->discoverNotificationClasses();

        foreach ($discoveredClasses as $classInfo) {
            NotificationEvent::updateOrCreate(
                ['event_code' => $classInfo['event_code']],
                [
                    'event_name' => $classInfo['event_name'],
                    'notification_class' => $classInfo['class'],
                    'event_description' => "Auto-discovered from {$classInfo['module']} module"
                ]
            );
        }
    }

    public function index()
    {
        // Auto-sync discovered notification events
        $this->syncNotificationEvents();

        // Get all active events with their mappings and related channel info
        $events = NotificationEvent::with([
            'mappings' => function ($query) {
                $query->with('channel');
            }
        ])->get();

        // Get all active channels
        $channels = NotificationChannel::where('status', true)->get();

        // Prepare the mapping data structure with available recipients
        $mappings = [];
        $availableRecipients = [];

        foreach ($events as $event) {
            $availableRecipients[$event->id] = $this->availableRecipientsFor($event->notification_class);

            foreach ($event->mappings as $mapping) {
                // Channel mappings
                if ($mapping->channel_id) {
                    $mappings[$event->id]['channels'][$mapping->channel_id] = $mapping;
                }

                // Recipient mappings - get dynamically from JSON or fallback to columns
                if (isset($availableRecipients[$event->id])) {
                    foreach ($availableRecipients[$event->id] as $recipientKey => $recipientLabel) {
                        $mappings[$event->id]['recipients'][$recipientKey] = $mapping->getRecipient($recipientKey);
                    }
                }

                // Template mappings
                if ($mapping->template_id) {
                    $mappings[$event->id]['templates']['template'] = $mapping->template_id;
                }
            }
        }

        return view('notifications::index', [
            'events' => $events,
            'channels' => $channels,
            'mappings' => $mappings,
            'availableRecipients' => $availableRecipients
        ]);
    }

    public function show($id)
    {
        try {
            // Get the specific event
            $event = NotificationEvent::findOrFail($id);
            $mappings = NotificationMapping::where('event_id', $id)->get();

            $availableRecipients = $this->availableRecipientsFor($event->notification_class);

            $channelMappings = [];
            $templates = [];

            foreach ($mappings as $key => $mapping) {
                $channel_id = $mapping->channel_id;
                $templates[$channel_id] = [];

                // Get all channels with their mappings for this event
                $channels = NotificationChannel::where('id', $mapping->channel_id)->get();

                foreach ($channels as $channel) {
                    $templates[$channel_id] = array_merge($templates[$channel_id], $this->templatesForChannel($channel->channel_class, $channel->name));
                }

                // Organize mappings by channel with dynamic recipients
                foreach ($channels as $channel) {
                    $notifications = [];
                    foreach ($availableRecipients as $recipientKey => $recipientLabel) {
                        $notifications[$recipientKey] = $mapping->getRecipient($recipientKey);
                    }

                    $channelMappings[$channel->id] = [
                        'name' => $channel->name,
                        'template_id' => $mapping->template_id ?? null,
                        'channel_id' => $mapping->channel_id ?? null,
                        'channel_class' => $channel->channel_class,
                        'notifications' => $notifications
                    ];
                }
            }

            return view('notifications::form', [
                'channels' => $channels ?? [],
                'templates' => $templates,
                'channelMappings' => $channelMappings,
                'currentEventId' => $id,
                'availableRecipients' => $availableRecipients
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'errors' => 'Failed to load notification settings: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validate request - accept any fields dynamically
            $validator = Validator::make($request->all(), [
                'event_id' => 'required|exists:notification_events,id',
                'channels' => 'required|array',
                'channels.*' => 'array',
                'channels.*.template_id' => 'sometimes|nullable'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ]);
            }

            $event = NotificationEvent::findOrFail($request['event_id']);
            $channels = $request->input('channels');

            foreach ($channels as $channelId => $mappingData) {
                // Only update if mapping already exists (don't create new ones)
                $existingMapping = NotificationMapping::where('event_id', $event->id)
                    ->where('channel_id', $channelId)
                    ->first();

                if (!$existingMapping) {
                    continue; // Skip if mapping doesn't exist
                }

                // Extract all notify_* fields dynamically
                $recipients = [];
                foreach ($mappingData as $key => $value) {
                    if (strpos($key, 'notify_') === 0) {
                        $recipients[$key] = (bool) $value;
                    }
                }

                $updateData = [
                    'template_id' => $mappingData['template_id'] ?? null,
                    'recipients' => $recipients
                ];

                // Update only
                $existingMapping->update($updateData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Notification settings updated successfully',
                'redirect_url' => route('notification.index')
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'errors' => 'Failed to update settings: ' . $e->getMessage()
            ]);
        }
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'notifications' => 'required|array',
            'notifications.*' => 'array',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['notifications'] as $eventId => $settings) {
                // Get all existing channel IDs for this event
                $existingChannelIds = NotificationMapping::where('event_id', $eventId)
                    ->pluck('channel_id')
                    ->toArray();

                $submittedChannelIds = [];

                if (isset($settings['channels'])) {
                    foreach ($settings['channels'] as $channelId => $channelData) {
                        // Only process if the channel is enabled
                        if (isset($channelData['enabled']) && $channelData['enabled']) {
                            // Get recipient settings (now from the recipients array)
                            $notifyAdmin = $settings['recipients']['admin'] ?? false;

                            // Update or create the mapping
                            NotificationMapping::updateOrCreate(
                                [
                                    'event_id' => $eventId,
                                    'channel_id' => $channelId,
                                ],
                                [
                                    'notify_admin' => (bool) $notifyAdmin
                                ]
                            );
                            $submittedChannelIds[] = $channelId;
                        }
                    }
                }

                // Delete mappings for channels that weren't submitted or were unchecked
                $channelsToDelete = array_diff($existingChannelIds, $submittedChannelIds);
                if (!empty($channelsToDelete)) {
                    NotificationMapping::where('event_id', $eventId)
                        ->whereIn('channel_id', $channelsToDelete)
                        ->delete();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Notification settings updated successfully',
                'redirect_url' => route('notification.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'errors' => 'Failed to update notification settings: ' . $e->getMessage(),
            ]);
        }
    }
}
