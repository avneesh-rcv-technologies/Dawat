<?php

namespace Modules\Acl\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\EmailNotification\Support\DawatEmail;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;

class CustomerPasswordResetNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $passwordResetEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'customer_password_reset'],
            [
                'notification_class' => \Modules\Acl\Notifications\CustomerPasswordResetNotification::class,
                'event_name' => 'Customer Password Reset',
                'event_description' => 'Triggered when a customer resets their password successfully',
            ]
        );

        $passwordResetEmailTpl = EmailTemplate::updateOrCreate(
            ['name' => 'customer_password_reset', 'locale' => 'en'],
            [
                'subject' => 'Your Password Has Been Updated - {{app_name}}',
                'content' => $this->tplCustomerPasswordReset(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        $passwordResetPushTpl = WebPushTemplate::updateOrCreate(
            ['name' => 'customer_password_reset'],
            [
                'subject' => 'Password Updated',
                'body' => 'Your customer password was updated successfully on {{reset_time}}.',
                'variables' => [
                    'customer_name' => 'Customer name',
                    'reset_time' => 'Password reset timestamp',
                ],
                'is_active' => true,
            ]
        );
        $emailChannel = NotificationChannel::where('name', 'email')->first();
        $webpushChannel = NotificationChannel::where('name', 'webpush')->first();
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $passwordResetEvent->id, 'channel_id' => $emailChannel->id],
                [
                    'template_id' => (string) $passwordResetEmailTpl->id,
                    'recipients' => ['notify_customer' => true],
                ]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $passwordResetEvent->id, 'channel_id' => $webpushChannel->id],
                [
                    'template_id' => (string) $passwordResetPushTpl->id,
                    'recipients' => ['notify_customer' => true],
                ]
            );
        }
    }

    private function tplCustomerPasswordReset(): string
    {
        return DawatEmail::render([
            'title' => 'Password updated — Dawat',
            'preheader' => 'Your Dawat account password was updated on {{reset_time}}.',
            'badge' => 'SECURITY NOTICE',
            'headline' => 'Your password<br>was updated',
            'greeting' => 'Hello {{customer_name}},',
            'intro' => 'This confirms that the password for your Dawat account was updated successfully.',
            'blocks' => [
                DawatEmail::details('Account details', [
                    'Account email' => '{{customer_email}}',
                    'Updated on' => '{{reset_time}}',
                ]),
                DawatEmail::callout("Didn't make this change?", 'Reset your password immediately and contact support so we can secure your account.'),
            ],
            'buttons' => [DawatEmail::button('Log in', '{{login_url}}')],
            'notes' => 'Need help? <a href="{{support_url}}" style="color:#ff9a66;text-decoration:underline;">Contact support</a> &middot; <a href="{{privacy_url}}" style="color:#ff9a66;text-decoration:underline;">Privacy policy</a>',
            'footer' => 'You received this email because the password on your Dawat account was changed.',
        ]);
    }
}
