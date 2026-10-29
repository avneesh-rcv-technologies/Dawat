<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;

class RestaurantNotificationSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------------------------------------------------------
        // EVENTS
        // ---------------------------------------------------------------
        $onboardingEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_onboarding'],
            [
                'event_name' => 'Restaurant Onboarding',
                'event_description' => 'Triggered when restaurant submits onboarding form — notifies admin',
            ]
        );

        $onboardingConfirmationEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_onboarding_confirmation'],
            [
                'event_name' => 'Restaurant Onboarding Confirmation',
                'event_description' => 'Triggered after onboarding form submit — confirms receipt to restaurant owner',
            ]
        );

        $createdEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_created'],
            [
                'event_name' => 'Restaurant Created by Admin',
                'event_description' => 'Triggered when admin creates a restaurant — sends credentials to restaurant owner',
            ]
        );

        $approvedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_approved'],
            [
                'event_name' => 'Restaurant Approved',
                'event_description' => 'Triggered when admin approves a restaurant — notifies restaurant owner',
            ]
        );

        $rejectedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_rejected'],
            [
                'event_name' => 'Restaurant Rejected',
                'event_description' => 'Triggered when admin rejects a restaurant application — notifies restaurant owner',
            ]
        );

        $statusChangedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_status_changed'],
            [
                'event_name' => 'Restaurant Status Changed',
                'event_description' => 'Triggered when restaurant status changes — notifies restaurant owner',
            ]
        );

        $orderReceivedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_order_received'],
            [
                'event_name' => 'Restaurant Order Received',
                'event_description' => 'Triggered when restaurant receives a new order',
            ]
        );

        $emailVerificationEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_email_verification'],
            [
                'event_name' => 'Restaurant Email Verification',
                'event_description' => 'Triggered when a restaurant owner needs to verify their email address',
            ]
        );

        // ---------------------------------------------------------------
        // EMAIL TEMPLATES
        // ---------------------------------------------------------------

        // 1. Admin notified on onboarding
        $onboardingEmailTpl = EmailTemplate::updateOrCreate(
            ['name' => 'restaurant_onboarding'],
            [
                'subject' => 'New Restaurant Application - {{restaurant_name}}',
                'content' => $this->tplOnboardingAdmin(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        // 2. Restaurant owner confirmation after onboarding
        $onboardingConfirmEmailTpl = EmailTemplate::updateOrCreate(
            ['name' => 'restaurant_onboarding_confirmation'],
            [
                'subject' => 'We Received Your Application - {{restaurant_name}}',
                'content' => $this->tplOnboardingConfirmation(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        // 3. Restaurant owner receives credentials when admin creates restaurant
        $createdEmailTpl = EmailTemplate::updateOrCreate(
            ['name' => 'restaurant_created'],
            [
                'subject' => 'Welcome to {{app_name}} - Your Restaurant is Ready',
                'content' => $this->tplRestaurantCreated(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        // 4. Restaurant owner notified on approval
        $approvedEmailTpl = $this->seedApprovalEmailTemplate();

        // 5. Restaurant owner notified on rejection
        $rejectedEmailTpl = EmailTemplate::updateOrCreate(
            ['name' => 'restaurant_rejected'],
            [
                'subject' => 'Update on Your Restaurant Application - {{restaurant_name}}',
                'content' => $this->tplRestaurantRejected(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        // 6. Restaurant owner notified on status change
        $statusChangedEmailTpl = EmailTemplate::updateOrCreate(
            ['name' => 'restaurant_status_changed'],
            [
                'subject' => 'Restaurant Status Updated - {{restaurant_name}}',
                'content' => $this->tplStatusChanged(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        // 7. Restaurant owner notified on new order
        $orderReceivedEmailTpl = EmailTemplate::updateOrCreate(
            ['name' => 'restaurant_order_received'],
            [
                'subject' => '🔔 New Order #{{order_number}} Received',
                'content' => $this->tplOrderReceived(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        // 8. Restaurant email verification OTP
        $restaurantEmailVerificationEmailTpl = EmailTemplate::updateOrCreate(
            ['name' => 'restaurant_email_verification'],
            [
                'subject' => 'Verify Your Restaurant Email - {{otp_code}}',
                'content' => $this->tplRestaurantEmailVerification(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        // ---------------------------------------------------------------
        // WEB PUSH TEMPLATES
        // ---------------------------------------------------------------

        $onboardingPushTpl = WebPushTemplate::updateOrCreate(
            ['name' => 'restaurant_onboarding'],
            [
                'subject' => 'New Restaurant Application',
                'body' => '🍽️ {{restaurant_name}} from {{restaurant_city}} has submitted an onboarding application.',
                'variables' => json_encode([
                    'restaurant_name' => 'Restaurant name',
                    'restaurant_city' => 'Restaurant city',
                ]),
                'is_active' => true,
            ]
        );

        $onboardingConfirmPushTpl = WebPushTemplate::updateOrCreate(
            ['name' => 'restaurant_onboarding_confirmation'],
            [
                'subject' => 'Application Received',
                'body' => 'Your application for {{restaurant_name}} has been received. We will review it shortly.',
                'variables' => json_encode([
                    'restaurant_name' => 'Restaurant name',
                ]),
                'is_active' => true,
            ]
        );

        $createdPushTpl = WebPushTemplate::updateOrCreate(
            ['name' => 'restaurant_created'],
            [
                'subject' => 'Welcome! Your Restaurant is Ready',
                'body' => 'Your restaurant {{restaurant_name}} has been created. Check your email for login credentials.',
                'variables' => json_encode([
                    'restaurant_name' => 'Restaurant name',
                ]),
                'is_active' => true,
            ]
        );

        $approvedPushTpl = WebPushTemplate::updateOrCreate(
            ['name' => 'restaurant_approved'],
            [
                'subject' => 'Restaurant Approved 🎉',
                'body' => 'Congratulations! {{restaurant_name}} has been approved and is now live on the platform.',
                'variables' => json_encode([
                    'restaurant_name' => 'Restaurant name',
                ]),
                'is_active' => true,
            ]
        );

        $rejectedPushTpl = WebPushTemplate::updateOrCreate(
            ['name' => 'restaurant_rejected'],
            [
                'subject' => 'Application Update',
                'body' => 'Your application for {{restaurant_name}} could not be approved. Reason: {{rejection_reason}}',
                'variables' => json_encode([
                    'restaurant_name' => 'Restaurant name',
                    'rejection_reason' => 'Rejection reason',
                ]),
                'is_active' => true,
            ]
        );

        $statusChangedPushTpl = WebPushTemplate::updateOrCreate(
            ['name' => 'restaurant_status_changed'],
            [
                'subject' => 'Status Update',
                'body' => '{{restaurant_name}} status has been updated to {{restaurant_status}}.',
                'variables' => json_encode([
                    'restaurant_name' => 'Restaurant name',
                    'restaurant_status' => 'New restaurant status',
                ]),
                'is_active' => true,
            ]
        );

        $orderReceivedPushTpl = WebPushTemplate::updateOrCreate(
            ['name' => 'restaurant_order_received'],
            [
                'subject' => 'New Order Received',
                'body' => 'Order #{{order_number}} from {{customer_name}}. Total: {{order_total}}',
                'variables' => json_encode([
                    'order_number' => 'Order number',
                    'customer_name' => 'Customer name',
                    'order_total' => 'Order total amount',
                ]),
                'is_active' => true,
            ]
        );

        // ---------------------------------------------------------------
        // MAPPINGS
        // ---------------------------------------------------------------
        $emailChannel = NotificationChannel::where('name', 'email')->first();
        $webpushChannel = NotificationChannel::where('name', 'webpush')->first();

        // 1. Onboarding → notify ADMIN only
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $onboardingEvent->id, 'channel_id' => $emailChannel->id],
                ['template_id' => (string) $onboardingEmailTpl->id, 'priority' => 1,
                 'custom_config' => json_encode(['notify_admin' => true])]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $onboardingEvent->id, 'channel_id' => $webpushChannel->id],
                ['template_id' => (string) $onboardingPushTpl->id, 'priority' => 2,
                 'custom_config' => json_encode(['notify_admin' => true])]
            );
        }

        // 2. Onboarding Confirmation → notify RESTAURANT only
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $onboardingConfirmationEvent->id, 'channel_id' => $emailChannel->id],
                ['template_id' => (string) $onboardingConfirmEmailTpl->id, 'priority' => 1,
                 'custom_config' => json_encode(['notify_restaurant' => true])]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $onboardingConfirmationEvent->id, 'channel_id' => $webpushChannel->id],
                ['template_id' => (string) $onboardingConfirmPushTpl->id, 'priority' => 2,
                 'custom_config' => json_encode(['notify_restaurant' => true])]
            );
        }

        // 3. Restaurant Created → notify RESTAURANT only (credentials email)
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $createdEvent->id, 'channel_id' => $emailChannel->id],
                ['template_id' => (string) $createdEmailTpl->id, 'priority' => 1,
                 'custom_config' => json_encode(['notify_restaurant' => true])]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $createdEvent->id, 'channel_id' => $webpushChannel->id],
                ['template_id' => (string) $createdPushTpl->id, 'priority' => 2,
                 'custom_config' => json_encode(['notify_restaurant' => true])]
            );
        }

        // 4. Approved → notify RESTAURANT only
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $approvedEvent->id, 'channel_id' => $emailChannel->id],
                ['template_id' => (string) $approvedEmailTpl->id, 'priority' => 1,
                 'custom_config' => json_encode(['notify_restaurant' => true])]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $approvedEvent->id, 'channel_id' => $webpushChannel->id],
                ['template_id' => (string) $approvedPushTpl->id, 'priority' => 2,
                 'custom_config' => json_encode(['notify_restaurant' => true])]
            );
        }

        // 5. Rejected → notify RESTAURANT only
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $rejectedEvent->id, 'channel_id' => $emailChannel->id],
                ['template_id' => (string) $rejectedEmailTpl->id, 'priority' => 1,
                 'custom_config' => json_encode(['notify_restaurant' => true])]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $rejectedEvent->id, 'channel_id' => $webpushChannel->id],
                ['template_id' => (string) $rejectedPushTpl->id, 'priority' => 2,
                 'custom_config' => json_encode(['notify_restaurant' => true])]
            );
        }

        // 6. Status Changed → notify RESTAURANT only
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $statusChangedEvent->id, 'channel_id' => $emailChannel->id],
                ['template_id' => (string) $statusChangedEmailTpl->id, 'priority' => 1,
                 'custom_config' => json_encode(['notify_restaurant' => true])]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $statusChangedEvent->id, 'channel_id' => $webpushChannel->id],
                ['template_id' => (string) $statusChangedPushTpl->id, 'priority' => 2,
                 'custom_config' => json_encode(['notify_restaurant' => true])]
            );
        }

        // 7. Order Received → notify RESTAURANT only
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $orderReceivedEvent->id, 'channel_id' => $emailChannel->id],
                ['template_id' => (string) $orderReceivedEmailTpl->id, 'priority' => 1,
                 'custom_config' => json_encode(['notify_restaurant' => true])]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $orderReceivedEvent->id, 'channel_id' => $webpushChannel->id],
                ['template_id' => (string) $orderReceivedPushTpl->id, 'priority' => 2,
                 'custom_config' => json_encode(['notify_restaurant' => true])]
            );
        }

        // 8. Restaurant Email Verification → notify RESTAURANT only
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $emailVerificationEvent->id, 'channel_id' => $emailChannel->id],
                ['template_id' => (string) $restaurantEmailVerificationEmailTpl->id, 'priority' => 1,
                 'custom_config' => json_encode(['notify_restaurant' => true])]
            );
        }
    }

    // ---------------------------------------------------------------
    // EMAIL TEMPLATE METHODS
    // Variables used exactly match what each Notification class exposes
    // ---------------------------------------------------------------

    /**
     * To: Admin
     * Variables: company_logo, restaurant_name, restaurant_owner_name, restaurant_email,
     *            restaurant_password, restaurant_phone, restaurant_city, restaurant_address,
     *            restaurant_id, submitted_date, review_link, login_url, app_name, current_year,
     *            support_url, privacy_url
     */
    private function tplOnboardingAdmin(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>New Restaurant Application</title></head>
<body style="margin:0;padding:0;background-color:#f4f7fa;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f4f7fa;">
  <tr><td style="padding:40px 20px;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
      <tr>
        <td style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:40px 30px;text-align:center;border-radius:12px 12px 0 0;">
          <img src="{{company_logo}}" alt="{{app_name}}" style="max-width:180px;height:auto;margin-bottom:20px;" />
          <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:600;">🍽️ New Restaurant Application</h1>
        </td>
      </tr>
      <tr>
        <td style="padding:40px 30px;">
          <p style="margin:0 0 10px;color:#333;font-size:16px;">Hello Admin,</p>
          <p style="margin:0 0 25px;color:#555;font-size:15px;">A new restaurant has submitted an onboarding application and requires your review.</p>

          <div style="background:#f8f9fa;border-left:4px solid #667eea;padding:20px;margin-bottom:25px;border-radius:4px;">
            <h2 style="margin:0 0 15px;color:#667eea;font-size:18px;">Restaurant Details</h2>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
              <tr><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#666;font-size:14px;width:40%;"><strong>Name:</strong></td><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#333;font-size:14px;">{{restaurant_name}}</td></tr>
              <tr><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#666;font-size:14px;"><strong>Owner:</strong></td><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#333;font-size:14px;">{{restaurant_owner_name}}</td></tr>
              <tr><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#666;font-size:14px;"><strong>Email:</strong></td><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#333;font-size:14px;">{{restaurant_email}}</td></tr>
              <tr><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#666;font-size:14px;"><strong>Phone:</strong></td><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#333;font-size:14px;">{{restaurant_phone}}</td></tr>
              <tr><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#666;font-size:14px;"><strong>City:</strong></td><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#333;font-size:14px;">{{restaurant_city}}</td></tr>
              <tr><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#666;font-size:14px;"><strong>Address:</strong></td><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#333;font-size:14px;">{{restaurant_address}}</td></tr>
              <tr><td style="padding:8px 0;color:#666;font-size:14px;"><strong>Submitted:</strong></td><td style="padding:8px 0;color:#333;font-size:14px;">{{submitted_date}}</td></tr>
            </table>
          </div>

          <div style="background:#e8f5e9;border-left:4px solid #4caf50;padding:20px;margin-bottom:25px;border-radius:4px;">
            <h3 style="margin:0 0 12px;color:#2e7d32;font-size:16px;">🔐 Submitted Credentials (for reference)</h3>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
              <tr><td style="padding:6px 0;color:#2e7d32;font-size:14px;width:30%;"><strong>Email:</strong></td><td style="padding:6px;color:#333;font-size:14px;font-family:'Courier New',monospace;background:#fff;border-radius:4px;">{{restaurant_email}}</td></tr>
              <tr><td style="padding:6px 0;color:#2e7d32;font-size:14px;"><strong>Password:</strong></td><td style="padding:6px;color:#333;font-size:14px;font-family:'Courier New',monospace;background:#fff;border-radius:4px;">{{restaurant_password}}</td></tr>
            </table>
          </div>

          <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr><td style="text-align:center;padding:20px 0;">
              <a href="{{review_link}}" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;padding:14px 36px;text-decoration:none;border-radius:50px;display:inline-block;font-weight:600;font-size:15px;">Review Application</a>
            </td></tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="background:#f8f9fa;padding:25px;text-align:center;border-radius:0 0 12px 12px;border-top:1px solid #e9ecef;">
          <p style="margin:0;color:#999;font-size:12px;">&copy; {{current_year}} {{app_name}}. All rights reserved.</p>
          <p style="margin:8px 0 0;font-size:12px;"><a href="{{support_url}}" style="color:#667eea;text-decoration:none;">Support</a> | <a href="{{privacy_url}}" style="color:#667eea;text-decoration:none;">Privacy Policy</a></p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
    }

    /**
     * To: Restaurant owner (after submitting onboarding form)
     * Variables: company_logo, restaurant_name, restaurant_owner_name, restaurant_id,
     *            submitted_date, dashboard_link, app_name, current_year, support_url, privacy_url
     */
    private function tplOnboardingConfirmation(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Application Received</title></head>
<body style="margin:0;padding:0;background-color:#f4f7fa;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f4f7fa;">
  <tr><td style="padding:40px 20px;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
      <tr>
        <td style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:40px 30px;text-align:center;border-radius:12px 12px 0 0;">
          <img src="{{company_logo}}" alt="{{app_name}}" style="max-width:180px;height:auto;margin:0 auto 20px;display:block;" />
          <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:600;">Application Received ✓</h1>
        </td>
      </tr>
      <tr>
        <td style="padding:40px 30px;">
          <p style="margin:0 0 10px;color:#333;font-size:16px;">Dear {{restaurant_owner_name}},</p>
          <p style="margin:0 0 25px;color:#555;font-size:15px;">Thank you for submitting your restaurant application. We have received it and our team will review it shortly.</p>

          <div style="background:#f8f9fa;border-left:4px solid #667eea;padding:20px;margin-bottom:25px;border-radius:4px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
              <tr><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#666;font-size:14px;width:40%;"><strong>Restaurant:</strong></td><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#333;font-size:14px;">{{restaurant_name}}</td></tr>
              <tr><td style="padding:8px 0;color:#666;font-size:14px;"><strong>Submitted:</strong></td><td style="padding:8px 0;color:#333;font-size:14px;">{{submitted_date}}</td></tr>
            </table>
          </div>

          <div style="background:#e8f5e9;border-left:4px solid #4caf50;padding:20px;margin-bottom:25px;border-radius:4px;">
            <h3 style="margin:0 0 12px;color:#2e7d32;font-size:16px;">📋 What happens next?</h3>
            <ul style="margin:0;padding-left:20px;color:#555;font-size:14px;line-height:1.8;">
              <li>Our team reviews your application within <strong>2–3 business days</strong></li>
              <li>You will receive an email with the outcome</li>
              <li>If approved, you will receive your login credentials</li>
            </ul>
          </div>

          <p style="margin:0;color:#777;font-size:13px;text-align:center;">Questions? Contact us at <a href="{{support_url}}" style="color:#667eea;text-decoration:none;">our support page</a>.</p>
        </td>
      </tr>
      <tr>
        <td style="background:#f8f9fa;padding:25px;text-align:center;border-radius:0 0 12px 12px;border-top:1px solid #e9ecef;">
          <p style="margin:0 0 5px;color:#666;font-size:14px;">Best regards,<br><strong>{{app_name}} Team</strong></p>
          <p style="margin:0;color:#999;font-size:12px;">&copy; {{current_year}} {{app_name}}. All rights reserved.</p>
          <p style="margin:8px 0 0;font-size:12px;"><a href="{{support_url}}" style="color:#667eea;text-decoration:none;">Support</a> | <a href="{{privacy_url}}" style="color:#667eea;text-decoration:none;">Privacy Policy</a></p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
    }

    /**
     * To: Restaurant owner (when admin creates restaurant)
     * Variables: company_logo, restaurant_name, restaurant_owner_name, restaurant_email,
     *            restaurant_password, restaurant_phone, restaurant_id, created_date,
     *            dashboard_link, login_url, app_name, current_year, support_url, privacy_url
     */
    private function tplRestaurantCreated(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Welcome to the Platform</title></head>
<body style="margin:0;padding:0;background-color:#f4f7fa;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f4f7fa;">
  <tr><td style="padding:40px 20px;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
      <tr>
        <td style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:40px 30px;text-align:center;border-radius:12px 12px 0 0;">
          <img src="{{company_logo}}" alt="{{app_name}}" style="max-width:180px;height:auto;margin:0 auto 20px;display:block;" />
          <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:600;">Welcome to {{app_name}}! 🎉</h1>
          <p style="margin:8px 0 0;color:#ffffff;font-size:15px;opacity:0.9;">Your restaurant account is ready</p>
        </td>
      </tr>
      <tr>
        <td style="padding:40px 30px;">
          <p style="margin:0 0 10px;color:#333;font-size:16px;">Dear {{restaurant_owner_name}},</p>
          <p style="margin:0 0 25px;color:#555;font-size:15px;">Your restaurant <strong>{{restaurant_name}}</strong> has been set up on {{app_name}}. Use the credentials below to log in and complete your setup.</p>

          <div style="background:#fff3cd;border-left:4px solid #ffc107;padding:20px;margin-bottom:25px;border-radius:4px;">
            <h3 style="margin:0 0 12px;color:#856404;font-size:16px;">🔐 Your Login Credentials</h3>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
              <tr><td style="padding:8px 0;color:#856404;font-size:14px;width:30%;"><strong>Email:</strong></td><td style="padding:6px 8px;color:#333;font-size:14px;font-family:'Courier New',monospace;background:#fff;border-radius:4px;">{{restaurant_email}}</td></tr>
              <tr><td style="padding:8px 0;color:#856404;font-size:14px;"><strong>Password:</strong></td><td style="padding:6px 8px;color:#333;font-size:14px;font-family:'Courier New',monospace;background:#fff;border-radius:4px;margin-top:4px;display:block;">{{restaurant_password}}</td></tr>
              <tr><td style="padding:8px 0;color:#856404;font-size:14px;"><strong>Login URL:</strong></td><td style="padding:8px 0;font-size:14px;"><a href="{{login_url}}" style="color:#667eea;text-decoration:none;font-weight:600;">{{login_url}}</a></td></tr>
            </table>
            <p style="margin:12px 0 0;color:#856404;font-size:12px;">⚠️ Please change your password after your first login.</p>
          </div>

          <div style="background:#e8f5e9;border-left:4px solid #4caf50;padding:20px;margin-bottom:25px;border-radius:4px;">
            <h3 style="margin:0 0 12px;color:#2e7d32;font-size:16px;">🚀 Get started</h3>
            <ul style="margin:0;padding-left:20px;color:#555;font-size:14px;line-height:1.8;">
              <li>Add your menu items and categories</li>
              <li>Configure delivery zones and working hours</li>
              <li>Upload your restaurant photos</li>
              <li>Start accepting orders!</li>
            </ul>
          </div>

          <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr><td style="text-align:center;padding:20px 0;">
              <a href="{{login_url}}" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;padding:14px 36px;text-decoration:none;border-radius:50px;display:inline-block;font-weight:600;font-size:15px;">Login to Dashboard</a>
            </td></tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="background:#f8f9fa;padding:25px;text-align:center;border-radius:0 0 12px 12px;border-top:1px solid #e9ecef;">
          <p style="margin:0 0 5px;color:#666;font-size:14px;">Best regards,<br><strong>{{app_name}} Team</strong></p>
          <p style="margin:0;color:#999;font-size:12px;">&copy; {{current_year}} {{app_name}}. All rights reserved.</p>
          <p style="margin:8px 0 0;font-size:12px;"><a href="{{support_url}}" style="color:#667eea;text-decoration:none;">Support</a> | <a href="{{privacy_url}}" style="color:#667eea;text-decoration:none;">Privacy Policy</a></p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
    }

    /**
     * To: Restaurant owner (when admin approves)
     * Variables: company_logo, restaurant_name, restaurant_owner_name, restaurant_email,
     *            restaurant_password, restaurant_phone, restaurant_id, approved_date,
     *            dashboard_link, login_url, app_name, current_year, support_url, privacy_url
     */
    public function seedApprovalEmailTemplate(): EmailTemplate
    {
        return EmailTemplate::updateOrCreate(['name' => 'restaurant_approved'], [
            'subject' => 'Restrarunt Approved & Fill Onboarding details',
            'content' => $this->tplRestaurantApproved(),
            'status' => true,
            'locale' => 'en',
        ]);
    }

    private function tplRestaurantApproved(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Restaurant Approved</title></head>
<body style="margin:0;padding:0;background-color:#f4f7fa;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f4f7fa;">
  <tr><td style="padding:40px 20px;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
      <tr>
        <td style="background:linear-gradient(135deg,#11998e 0%,#38ef7d 100%);padding:40px 30px;text-align:center;border-radius:12px 12px 0 0;">
          <img src="{{company_logo}}" alt="{{app_name}}" style="max-width:180px;height:auto;margin:0 auto 20px;display:block;" />
          <h1 style="margin:0;color:#ffffff;font-size:28px;font-weight:600;">🎉 Congratulations!</h1>
          <p style="margin:8px 0 0;color:#ffffff;font-size:15px;opacity:0.95;">{{approved_step_label}} Approved</p>
        </td>
      </tr>
      <tr>
        <td style="padding:40px 30px;">
          <p style="margin:0 0 10px;color:#333;font-size:16px;">Dear {{restaurant_owner_name}},</p>
          <p style="margin:0 0 25px;color:#555;font-size:15px;">The <strong>{{approved_step_label}}</strong> step for <strong>{{restaurant_name}}</strong> has been approved. Review your submitted details and the remaining onboarding steps below.</p>

          <div style="background:#e8f5e9;border-left:4px solid #4caf50;padding:20px;margin-bottom:25px;border-radius:4px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
              <tr><td style="padding:8px 0;border-bottom:1px solid #c8e6c9;color:#2e7d32;font-size:14px;width:40%;"><strong>Restaurant:</strong></td><td style="padding:8px 0;border-bottom:1px solid #c8e6c9;color:#333;font-size:14px;">{{restaurant_name}}</td></tr>
              <tr><td style="padding:8px 0;border-bottom:1px solid #c8e6c9;color:#2e7d32;font-size:14px;"><strong>Status:</strong></td><td style="padding:8px 0;border-bottom:1px solid #c8e6c9;color:#4caf50;font-size:14px;"><strong>{{approved_step_label}} approved</strong></td></tr>
              <tr><td style="padding:8px 0;color:#2e7d32;font-size:14px;"><strong>Approval Date:</strong></td><td style="padding:8px 0;color:#333;font-size:14px;">{{approved_date}}</td></tr>
            </table>
          </div>

          {{onboarding_steps_html}}

          <div style="background:#fff3cd;border-left:4px solid #ffc107;padding:20px;margin-bottom:25px;border-radius:4px;">
            <h3 style="margin:0 0 12px;color:#856404;font-size:16px;">🔐 Login Details</h3>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
              <tr><td style="padding:6px 0;color:#856404;font-size:14px;width:30%;"><strong>Email:</strong></td><td style="padding:6px 8px;color:#333;font-size:14px;font-family:'Courier New',monospace;background:#fff;border-radius:4px;">{{restaurant_email}}</td></tr>
              <tr><td style="padding:6px 0;color:#856404;font-size:14px;"><strong>Password:</strong></td><td style="padding:6px 8px;color:#333;font-size:14px;font-family:'Courier New',monospace;background:#fff;border-radius:4px;">{{restaurant_password}}</td></tr>
              <tr><td style="padding:6px 0;color:#856404;font-size:14px;"><strong>Login URL:</strong></td><td style="padding:6px 0;font-size:14px;"><a href="{{login_url}}" style="color:#667eea;font-weight:600;text-decoration:none;">{{login_url}}</a></td></tr>
            </table>
          </div>

          <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr><td style="text-align:center;padding:20px 0;">
              <a href="{{login_url}}" style="background:linear-gradient(135deg,#11998e 0%,#38ef7d 100%);color:#fff;padding:14px 36px;text-decoration:none;border-radius:50px;display:inline-block;font-weight:600;font-size:15px;">Login to Dashboard</a>
            </td></tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="background:#f8f9fa;padding:25px;text-align:center;border-radius:0 0 12px 12px;border-top:1px solid #e9ecef;">
          <p style="margin:0 0 5px;color:#666;font-size:14px;">Best regards,<br><strong>{{app_name}} Team</strong></p>
          <p style="margin:0;color:#999;font-size:12px;">&copy; {{current_year}} {{app_name}}. All rights reserved.</p>
          <p style="margin:8px 0 0;font-size:12px;"><a href="{{support_url}}" style="color:#667eea;text-decoration:none;">Support</a> | <a href="{{privacy_url}}" style="color:#667eea;text-decoration:none;">Privacy Policy</a></p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
    }

    /**
     * To: Restaurant owner (when admin rejects)
     * Variables: company_logo, restaurant_name, restaurant_owner_name, restaurant_email,
     *            restaurant_id, rejected_date, rejection_reason, reapply_link, app_name
     * Note: RestaurantRejectedNotification does not expose current_year/support_url/privacy_url
     *       so those are not used here.
     */
    private function tplRestaurantRejected(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Application Update</title></head>
<body style="margin:0;padding:0;background-color:#f4f7fa;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f4f7fa;">
  <tr><td style="padding:40px 20px;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
      <tr>
        <td style="background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%);padding:40px 30px;text-align:center;border-radius:12px 12px 0 0;">
          <img src="{{company_logo}}" alt="{{app_name}}" style="max-width:180px;height:auto;margin-bottom:20px;" />
          <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:600;">Application Update</h1>
        </td>
      </tr>
      <tr>
        <td style="padding:40px 30px;">
          <p style="margin:0 0 10px;color:#333;font-size:16px;">Dear {{restaurant_owner_name}},</p>
          <p style="margin:0 0 25px;color:#555;font-size:15px;">Thank you for applying to partner with {{app_name}}. After careful review we are unable to approve <strong>{{restaurant_name}}</strong> at this time.</p>

          <div style="background:#fff3cd;border-left:4px solid #ffc107;padding:20px;margin-bottom:25px;border-radius:4px;">
            <h3 style="margin:0 0 12px;color:#856404;font-size:16px;">📋 Review Details</h3>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
              <tr><td style="padding:8px 0;border-bottom:1px solid #f5e6a8;color:#856404;font-size:14px;width:40%;"><strong>Restaurant:</strong></td><td style="padding:8px 0;border-bottom:1px solid #f5e6a8;color:#333;font-size:14px;">{{restaurant_name}}</td></tr>
              <tr><td style="padding:8px 0;border-bottom:1px solid #f5e6a8;color:#856404;font-size:14px;"><strong>Reviewed on:</strong></td><td style="padding:8px 0;border-bottom:1px solid #f5e6a8;color:#333;font-size:14px;">{{rejected_date}}</td></tr>
              <tr><td style="padding:8px 0;color:#856404;font-size:14px;" colspan="2"><strong>Reason:</strong></td></tr>
              <tr><td style="padding:8px 0;color:#333;font-size:14px;line-height:1.6;" colspan="2">{{rejection_reason}}</td></tr>
            </table>
          </div>

          <div style="background:#e7f3ff;border-left:4px solid #2196F3;padding:20px;margin-bottom:25px;border-radius:4px;">
            <h3 style="margin:0 0 8px;color:#1976d2;font-size:16px;">💡 You can reapply</h3>
            <p style="margin:0;color:#555;font-size:14px;line-height:1.6;">Address the points above and submit a new application. We are here to help you succeed.</p>
          </div>

          <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr><td style="text-align:center;padding:20px 0;">
              <a href="{{reapply_link}}" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;padding:14px 36px;text-decoration:none;border-radius:50px;display:inline-block;font-weight:600;font-size:15px;">Reapply Now</a>
            </td></tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="background:#f8f9fa;padding:25px;text-align:center;border-radius:0 0 12px 12px;border-top:1px solid #e9ecef;">
          <p style="margin:0 0 5px;color:#666;font-size:14px;">Best regards,<br><strong>{{app_name}} Team</strong></p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
    }

    /**
     * To: Restaurant owner (on status change)
     * Variables: company_logo, restaurant_name, restaurant_owner_name, restaurant_email,
     *            restaurant_id, restaurant_status, updated_date, dashboard_link, app_name
     * Note: RestaurantStatusChangedNotification does not expose current_year/support_url/privacy_url.
     */
    private function tplStatusChanged(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Status Update</title></head>
<body style="margin:0;padding:0;background-color:#f4f7fa;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f4f7fa;">
  <tr><td style="padding:40px 20px;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
      <tr>
        <td style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:40px 30px;text-align:center;border-radius:12px 12px 0 0;">
          <img src="{{company_logo}}" alt="{{app_name}}" style="max-width:180px;height:auto;margin-bottom:20px;" />
          <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:600;">🔔 Status Update</h1>
        </td>
      </tr>
      <tr>
        <td style="padding:40px 30px;">
          <p style="margin:0 0 10px;color:#333;font-size:16px;">Dear {{restaurant_owner_name}},</p>
          <p style="margin:0 0 25px;color:#555;font-size:15px;">Your restaurant status has been updated.</p>

          <div style="background:#f8f9fa;border-left:4px solid #667eea;padding:20px;margin-bottom:25px;border-radius:4px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
              <tr><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#666;font-size:14px;width:40%;"><strong>Restaurant:</strong></td><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#333;font-size:14px;">{{restaurant_name}}</td></tr>
              <tr><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#666;font-size:14px;"><strong>New Status:</strong></td><td style="padding:8px 0;border-bottom:1px solid #e9ecef;font-size:14px;"><strong style="color:#667eea;">{{restaurant_status}}</strong></td></tr>
              <tr><td style="padding:8px 0;color:#666;font-size:14px;"><strong>Updated At:</strong></td><td style="padding:8px 0;color:#333;font-size:14px;">{{updated_date}}</td></tr>
            </table>
          </div>

          <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr><td style="text-align:center;padding:20px 0;">
              <a href="{{dashboard_link}}" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;padding:14px 36px;text-decoration:none;border-radius:50px;display:inline-block;font-weight:600;font-size:15px;">View Dashboard</a>
            </td></tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="background:#f8f9fa;padding:25px;text-align:center;border-radius:0 0 12px 12px;border-top:1px solid #e9ecef;">
          <p style="margin:0 0 5px;color:#666;font-size:14px;">Best regards,<br><strong>{{app_name}} Team</strong></p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
    }

    /**
     * To: Restaurant owner (on new order)
     * Variables: restaurant_name, restaurant_owner_name, restaurant_email, restaurant_id,
     *            order_number, order_id, customer_name, order_total, delivery_address, order_link, app_name
     * Note: RestaurantOrderReceivedNotification does not expose items/accept_order_link/order_time.
     */
    private function tplOrderReceived(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>New Order Received</title></head>
<body style="margin:0;padding:0;background-color:#f4f7fa;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f4f7fa;">
  <tr><td style="padding:40px 20px;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
      <tr>
        <td style="background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%);padding:40px 30px;text-align:center;border-radius:12px 12px 0 0;">
          <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:600;">🔔 New Order Alert</h1>
          <p style="margin:8px 0 0;color:#ffffff;font-size:15px;opacity:0.95;">You have a new order!</p>
        </td>
      </tr>
      <tr>
        <td style="padding:40px 30px;">
          <p style="margin:0 0 10px;color:#333;font-size:16px;">Dear {{restaurant_owner_name}},</p>
          <p style="margin:0 0 25px;color:#555;font-size:15px;">A new order has been placed at <strong>{{restaurant_name}}</strong>. Please prepare it as soon as possible.</p>

          <div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:20px;margin-bottom:25px;border-radius:8px;text-align:center;">
            <p style="margin:0 0 4px;color:#fff;font-size:13px;opacity:0.9;">Order Number</p>
            <p style="margin:0 0 12px;color:#fff;font-size:26px;font-weight:700;">#{{order_number}}</p>
            <p style="margin:0 0 4px;color:#fff;font-size:13px;opacity:0.9;">Total Amount</p>
            <p style="margin:0;color:#fff;font-size:28px;font-weight:700;">{{order_total}}</p>
          </div>

          <div style="background:#f8f9fa;border-left:4px solid #667eea;padding:20px;margin-bottom:25px;border-radius:4px;">
            <h3 style="margin:0 0 12px;color:#667eea;font-size:16px;">Order Details</h3>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
              <tr><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#666;font-size:14px;width:40%;"><strong>Customer:</strong></td><td style="padding:8px 0;border-bottom:1px solid #e9ecef;color:#333;font-size:14px;">{{customer_name}}</td></tr>
              <tr><td style="padding:8px 0;color:#666;font-size:14px;"><strong>Delivery Address:</strong></td><td style="padding:8px 0;color:#333;font-size:14px;">{{delivery_address}}</td></tr>
            </table>
          </div>

          <div style="background:#fff3cd;border-left:4px solid #ffc107;padding:15px;margin-bottom:25px;border-radius:4px;">
            <p style="margin:0;color:#856404;font-size:14px;">⚡ <strong>Action Required:</strong> Please acknowledge and start preparing this order immediately.</p>
          </div>

          <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr><td style="text-align:center;padding:20px 0;">
              <a href="{{order_link}}" style="background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%);color:#fff;padding:14px 36px;text-decoration:none;border-radius:50px;display:inline-block;font-weight:600;font-size:15px;">View Order Details</a>
            </td></tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="background:#f8f9fa;padding:25px;text-align:center;border-radius:0 0 12px 12px;border-top:1px solid #e9ecef;">
          <p style="margin:0 0 5px;color:#666;font-size:14px;">Best regards,<br><strong>{{app_name}} Team</strong></p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
    }

    private function tplRestaurantEmailVerification(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Verify Your Restaurant Email</title></head>
<body style="margin:0;padding:0;background-color:#f4f7fa;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f4f7fa;">
  <tr><td style="padding:40px 20px;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
      <tr>
        <td style="background:linear-gradient(135deg,#19a974 0%,#0d7c5f 100%);padding:40px 30px;text-align:center;border-radius:12px 12px 0 0;">
          <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:600;">✅ Verify Your Email</h1>
        </td>
      </tr>
      <tr>
        <td style="padding:40px 30px;">
          <p style="margin:0 0 10px;color:#333;font-size:16px;">Hello {{restaurant_name}},</p>
          <p style="margin:0 0 25px;color:#555;font-size:15px;">Use the code below to verify your restaurant email address and complete your account setup.</p>

          <div style="background:#f0fdf4;border-left:4px solid #16a34a;padding:20px;margin-bottom:25px;border-radius:4px;text-align:center;">
            <p style="margin:0 0 8px;color:#166534;font-size:13px;letter-spacing:1px;text-transform:uppercase;font-weight:700;">Verification Code</p>
            <p style="margin:0;color:#166534;font-size:34px;font-weight:800;letter-spacing:5px;">{{otp_code}}</p>
          </div>

          <p style="margin:0 0 12px;color:#555;font-size:15px;">This code expires in <strong>{{expires_minutes}} minutes</strong>.</p>
          <p style="margin:0;color:#555;font-size:15px;">{{security_warning}}</p>
        </td>
      </tr>
      <tr>
        <td style="background:#f8f9fa;padding:25px;text-align:center;border-radius:0 0 12px 12px;border-top:1px solid #e9ecef;">
          <p style="margin:0 0 5px;color:#666;font-size:14px;">Best regards,<br><strong>{{app_name}} Team</strong></p>
          <p style="margin:8px 0 0;color:#999;font-size:12px;">Need help? Contact us at {{support_email}}</p>
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
