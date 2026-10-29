<?php

namespace Modules\Acl\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;

class CustomerNotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Create Events
        $customerCreatedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'customer_created'],
            [
                'event_name' => 'Customer Created',
                'event_description' => 'Triggered when a new customer account is created',
            ]
        );

        $customerRegisteredEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'customer_registered'],
            [
                'event_name' => 'Customer Registered',
                'event_description' => 'Triggered when customer completes registration',
            ]
        );

        $customerProfileUpdatedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'customer_profile_updated'],
            [
                'event_name' => 'Customer Profile Updated',
                'event_description' => 'Triggered when customer updates their profile',
            ]
        );

        // Create Email Templates
        $customerCreatedEmailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'customer_created'],
            [
                'subject' => 'Welcome to {{app_name}}!',
                'content' => $this->getCustomerCreatedEmailTemplate(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        $customerRegisteredEmailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'customer_registered'],
            [
                'subject' => 'Account Registration Successful',
                'content' => $this->getCustomerRegisteredEmailTemplate(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        $customerProfileUpdatedEmailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'customer_profile_updated'],
            [
                'subject' => 'Profile Update Confirmation',
                'content' => $this->getCustomerProfileUpdatedEmailTemplate(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        // Create WebPush Templates
        $customerCreatedPushTemplate = WebPushTemplate::updateOrCreate(
            ['name' => 'customer_created'],
            [
                'subject' => 'Welcome!',
                'body' => 'Welcome to {{app_name}}, {{customer_name}}! Start exploring now.',
                'variables' => json_encode([
                    'app_name' => 'Application name',
                    'customer_name' => 'Customer name',
                ]),
                'is_active' => true,
            ]
        );

        $customerRegisteredPushTemplate = WebPushTemplate::updateOrCreate(
            ['name' => 'customer_registered'],
            [
                'subject' => 'Registration Complete',
                'body' => 'Your account has been successfully created. Start ordering now!',
                'variables' => json_encode([
                    'customer_name' => 'Customer name',
                ]),
                'is_active' => true,
            ]
        );

        $customerProfileUpdatedPushTemplate = WebPushTemplate::updateOrCreate(
            ['name' => 'customer_profile_updated'],
            [
                'subject' => 'Profile Updated',
                'body' => 'Your profile has been updated successfully.',
                'variables' => json_encode([
                    'customer_name' => 'Customer name',
                ]),
                'is_active' => true,
            ]
        );

        // Get Channels
        $emailChannel = NotificationChannel::where('name', 'email')->first();
        $webpushChannel = NotificationChannel::where('name', 'webpush')->first();

        // Create Mappings for Customer Created
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $customerCreatedEvent->id,
                    'channel_id' => $emailChannel->id,
                ],
                [
                    'template_id' => (string)$customerCreatedEmailTemplate->id,
                    'priority' => 1,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                        'notify_admin' => true,
                    ]),
                ]
            );
        }

        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $customerCreatedEvent->id,
                    'channel_id' => $webpushChannel->id,
                ],
                [
                    'template_id' => (string)$customerCreatedPushTemplate->id,
                    'priority' => 2,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }

        // Create Mappings for Customer Registered
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $customerRegisteredEvent->id,
                    'channel_id' => $emailChannel->id,
                ],
                [
                    'template_id' => (string)$customerRegisteredEmailTemplate->id,
                    'priority' => 1,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }

        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $customerRegisteredEvent->id,
                    'channel_id' => $webpushChannel->id,
                ],
                [
                    'template_id' => (string)$customerRegisteredPushTemplate->id,
                    'priority' => 2,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }

        // Create Mappings for Customer Profile Updated
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $customerProfileUpdatedEvent->id,
                    'channel_id' => $emailChannel->id,
                ],
                [
                    'template_id' => (string)$customerProfileUpdatedEmailTemplate->id,
                    'priority' => 1,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }

        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $customerProfileUpdatedEvent->id,
                    'channel_id' => $webpushChannel->id,
                ],
                [
                    'template_id' => (string)$customerProfileUpdatedPushTemplate->id,
                    'priority' => 2,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }
    }

    private function getCustomerCreatedEmailTemplate(): string
    {
        return <<<'HTML'
<html>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #007bff;">Welcome to {{app_name}}!</h2>
        <p>Dear {{customer_name}},</p>
        <p>Thank you for creating an account with us. We're excited to have you on board!</p>
        
        <h3 style="color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px;">Account Details</h3>
        <table style="width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px;"><strong>Email:</strong></td>
                <td style="padding: 8px;">{{customer_email}}</td>
            </tr>
            <tr style="background-color: #f9f9f9;">
                <td style="padding: 8px;"><strong>Account Created:</strong></td>
                <td style="padding: 8px;">{{created_date}}</td>
            </tr>
        </table>

        <h3 style="color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px;">What's Next?</h3>
        <ul style="line-height: 1.8;">
            <li>Complete your profile information</li>
            <li>Add delivery addresses</li>
            <li>Browse restaurants and place your first order</li>
            <li>Enjoy exclusive offers and rewards</li>
        </ul>

        <div style="margin-top: 30px; text-align: center;">
            <a href="{{app_url}}" style="background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Start Exploring</a>
        </div>
        
        <p style="margin-top: 30px; color: #666; font-size: 12px;">
            Best regards,<br>
            {{app_name}} Team
        </p>
    </div>
</body>
</html>
HTML;
    }

    private function getCustomerRegisteredEmailTemplate(): string
    {
        return <<<'HTML'
<html>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #007bff;">Registration Successful!</h2>
        <p>Dear {{customer_name}},</p>
        <p>Your account registration is now complete. You can now place orders and enjoy our services.</p>
        
        <h3 style="color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px;">Account Information</h3>
        <table style="width: 100%; margin: 20px 0; border-collapse: collapse;">
            <tr style="background-color: #f9f9f9;">
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><strong>Name:</strong></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">{{customer_name}}</td>
            </tr>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><strong>Email:</strong></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">{{customer_email}}</td>
            </tr>
            <tr style="background-color: #f9f9f9;">
                <td style="padding: 12px;"><strong>Phone:</strong></td>
                <td style="padding: 12px;">{{customer_phone}}</td>
            </tr>
        </table>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="{{profile_link}}" style="background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">View Profile</a>
        </div>
        
        <p style="margin-top: 30px; color: #666; font-size: 12px;">
            Best regards,<br>
            {{app_name}} Team
        </p>
    </div>
</body>
</html>
HTML;
    }

    private function getCustomerProfileUpdatedEmailTemplate(): string
    {
        return <<<'HTML'
<html>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #007bff;">Profile Update Confirmation</h2>
        <p>Dear {{customer_name}},</p>
        <p>Your profile has been successfully updated.</p>
        
        <h3 style="color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px;">Updated Information</h3>
        <table style="width: 100%; margin: 20px 0; border-collapse: collapse;">
            <tr style="background-color: #f9f9f9;">
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><strong>Update Time:</strong></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">{{updated_date}}</td>
            </tr>
            <tr>
                <td style="padding: 12px;"><strong>Status:</strong></td>
                <td style="padding: 12px; color: #28a745; font-weight: bold;">Updated Successfully</td>
            </tr>
        </table>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="{{profile_link}}" style="background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">View Profile</a>
        </div>
        
        <p style="margin-top: 30px; color: #666; font-size: 12px;">
            Best regards,<br>
            {{app_name}} Team
        </p>
    </div>
</body>
</html>
HTML;
    }
}
