<?php

namespace Modules\Website\Services;

use Modules\Acl\Models\Admin;
use Modules\Notifications\Models\Notification;
use Modules\Notifications\Models\NotificationLog;
use Modules\WebPushNotification\Jobs\SendPushNotificationJob;
use Modules\WebPushNotification\Models\WebPushSubscription;
use Modules\Website\Models\ContactQuery;

class ContactQueryAdminNotifier
{
    public function notify(ContactQuery $query): void
    {
        $data = [
            'title' => 'New contact enquiry',
            'body' => $query->first_name.' '.$query->last_name.' submitted a message through the website.',
            'contact_query_id' => (string) $query->id,
            'url' => route('website.contact-queries.show', $query),
        ];

        Admin::with('role')->where('status', true)->chunkById(100, function ($admins) use ($data) {
            foreach ($admins as $admin) {
                if ($admin->role?->guard !== 'admin' || !$admin->hasPermission('website.contact-queries.show')) {
                    continue;
                }

                try {
                    Notification::create([
                        'type' => 'contact_query_submitted',
                        'notifiable_type' => get_class($admin),
                        'notifiable_id' => $admin->id,
                        'data' => $data,
                    ]);
                } catch (\Throwable $exception) {
                    report($exception);
                    continue;
                }

                foreach (WebPushSubscription::where('subscribable_type', get_class($admin))
                    ->where('subscribable_id', $admin->id)->get()->unique('token') as $subscription) {
                    $log = null;
                    try {
                        $log = NotificationLog::create([
                            'event_code' => 'contact_query_submitted',
                            'channel_name' => 'webpush',
                            'notifiable_type' => get_class($admin),
                            'notifiable_id' => $admin->id,
                            'content' => $data,
                            'status' => 'queued',
                        ]);
                        SendPushNotificationJob::dispatch(
                            $subscription->token, $subscription->device, $data['title'], $data['body'],
                            array_merge($data, ['type' => 'contact_query_submitted']), $log->id
                        )->afterCommit();
                    } catch (\Throwable $exception) {
                        $log?->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);
                        report($exception);
                    }
                }
            }
        });
    }
}
