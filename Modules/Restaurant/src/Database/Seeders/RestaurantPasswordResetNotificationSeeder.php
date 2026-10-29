<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;
use Modules\Restaurant\Notifications\RestaurantPasswordResetNotification;

class RestaurantPasswordResetNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $passwordResetEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_password_reset'],
            [
                'event_name' => 'Restaurant Password Reset',
                'event_description' => 'Triggered when a restaurant owner resets their password successfully',
                'notification_class' => RestaurantPasswordResetNotification::class,
            ]
        );

        $passwordResetEmailTpl = EmailTemplate::firstOrCreate(
            ['name' => 'restaurant_password_reset'],
            [
                'subject' => 'Your Password Has Been Updated - {{app_name}}',
                'content' => $this->tplRestaurantPasswordReset(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        $passwordResetPushTpl = WebPushTemplate::firstOrCreate(
            ['name' => 'restaurant_password_reset'],
            [
                'subject' => 'Password Updated',
                'body' => 'Your restaurant password was updated successfully on {{reset_time}}.',
                'variables' => json_encode([
                    'restaurant_name' => 'Restaurant name',
                    'reset_time' => 'Password reset timestamp',
                ]),
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
                    'recipients' => ['notify_restaurant' => true],
                ]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $passwordResetEvent->id, 'channel_id' => $webpushChannel->id],
                [
                    'template_id' => (string) $passwordResetPushTpl->id,
                    'recipients' => ['notify_restaurant' => true],
                ]
            );
        }
    }

    private function tplRestaurantPasswordReset(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Updated</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f7fa;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f4f7fa;">
  <tr><td style="padding:40px 20px;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
      <tr>
        <td style="background:linear-gradient(135deg,#0f766e 0%,#14b8a6 100%);padding:40px 30px;text-align:center;border-radius:12px 12px 0 0;">
          <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:600;">Password Updated</h1>
        </td>
      </tr>
      <tr>
        <td style="padding:40px 30px;">
          <p style="margin:0 0 10px;color:#333;font-size:16px;">Hello {{restaurant_name}},</p>
          <p style="margin:0 0 25px;color:#555;font-size:15px;">This is a confirmation that your restaurant account password was successfully updated on <strong>{{reset_time}}</strong>.</p>

          <div style="background:#ecfeff;border-left:4px solid #0f766e;padding:20px;margin-bottom:25px;border-radius:4px;">
            <p style="margin:0;color:#0f172a;font-size:14px;line-height:1.6;">
              Your account email: <strong>{{restaurant_email}}</strong><br>
              If you did not make this change, please reset your password immediately and contact support.
            </p>
          </div>

          <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr><td style="text-align:center;padding:20px 0;">
              <a href="{{login_url}}" style="background:#0f766e;color:#fff;padding:14px 32px;text-decoration:none;border-radius:50px;display:inline-block;font-weight:600;font-size:15px;">Login to Dashboard</a>
            </td></tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="background:#f8f9fa;padding:25px;text-align:center;border-radius:0 0 12px 12px;border-top:1px solid #e9ecef;">
          <p style="margin:0 0 5px;color:#666;font-size:14px;">Best regards,<br><strong>{{app_name}} Team</strong></p>
          <p style="margin:0;color:#999;font-size:12px;">&copy; {{current_year}} {{app_name}}. All rights reserved.</p>
          <p style="margin:8px 0 0;font-size:12px;"><a href="{{support_url}}" style="color:#0f766e;text-decoration:none;">Support</a> | <a href="{{privacy_url}}" style="color:#0f766e;text-decoration:none;">Privacy Policy</a></p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
    }
}
