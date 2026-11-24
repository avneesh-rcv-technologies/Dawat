<?php

namespace Modules\Acl\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\EmailNotification\Support\DawatEmail;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;

class CustomerNotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Create Events
        $customerCreatedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'customer_created'],
            [
                'notification_class' => \Modules\Acl\Notifications\CustomerCreatedNotification::class,
                'event_name' => 'Customer Created',
                'event_description' => 'Triggered when a new customer account is created',
            ]
        );

        $customerRegisteredEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'customer_registered'],
            [
                'notification_class' => \Modules\Acl\Notifications\CustomerRegisteredNotification::class,
                'event_name' => 'Customer Registered',
                'event_description' => 'Triggered when customer completes registration',
            ]
        );

        $customerProfileUpdatedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'customer_profile_updated'],
            [
                'notification_class' => \Modules\Acl\Notifications\CustomerProfileUpdatedNotification::class,
                'event_name' => 'Customer Profile Updated',
                'event_description' => 'Triggered when customer updates their profile',
            ]
        );

        // Create Email Templates
        $customerCreatedEmailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'customer_created', 'locale' => 'en'],
            [
                'subject' => 'Welcome to {{app_name}}!',
                'content' => $this->getCustomerCreatedEmailTemplate(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        $customerRegisteredEmailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'customer_registered', 'locale' => 'en'],
            [
                'subject' => 'Account Registration Successful',
                'content' => $this->getCustomerRegisteredEmailTemplate(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        $customerProfileUpdatedEmailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'customer_profile_updated', 'locale' => 'en'],
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
                'variables' => [
                    'app_name' => 'Application name',
                    'customer_name' => 'Customer name',
                ],
                'is_active' => true,
            ]
        );

        $customerRegisteredPushTemplate = WebPushTemplate::updateOrCreate(
            ['name' => 'customer_registered'],
            [
                'subject' => 'Registration Complete',
                'body' => 'Your account has been successfully created. Start ordering now!',
                'variables' => [
                    'customer_name' => 'Customer name',
                ],
                'is_active' => true,
            ]
        );

        $customerProfileUpdatedPushTemplate = WebPushTemplate::updateOrCreate(
            ['name' => 'customer_profile_updated'],
            [
                'subject' => 'Profile Updated',
                'body' => 'Your profile has been updated successfully.',
                'variables' => [
                    'customer_name' => 'Customer name',
                ],
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
                    'recipients' => [
                        'notify_customer' => true,
                        'notify_admin' => true,
                    ],
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
                    'recipients' => [
                        'notify_customer' => true,
                    ],
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
                    'recipients' => [
                        'notify_customer' => true,
                    ],
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
                    'recipients' => [
                        'notify_customer' => true,
                    ],
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
                    'recipients' => [
                        'notify_customer' => true,
                    ],
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
                    'recipients' => [
                        'notify_customer' => true,
                    ],
                ]
            );
        }
    }

    private function getCustomerCreatedEmailTemplate(): string
    {
        return DawatEmail::render([
            'title' => 'Welcome to Dawat',
            'preheader' => 'Your Dawat account is ready. Start exploring restaurants near you.',
            'badge' => 'WELCOME',
            'headline' => 'Welcome<br>to the table',
            'greeting' => 'Hello {{customer_name}},',
            'intro' => "Thank you for creating an account with us. We're excited to have you on board!",
            'blocks' => [
                DawatEmail::details('Account details', [
                    'Email' => '{{customer_email}}',
                    'Account created' => '{{created_date}}',
                ]),
                DawatEmail::list("What's next?", [
                    'Complete your profile information',
                    'Add your delivery addresses',
                    'Browse restaurants and place your first order',
                    'Enjoy exclusive offers and rewards',
                ]),
            ],
            'buttons' => [DawatEmail::button('Start exploring', '{{app_url}}')],
            'footer' => 'You received this email because an account was created for you on Dawat.',
        ]);
    }

    private function getCustomerRegisteredEmailTemplate(): string
    {
        return DawatEmail::render([
            'title' => 'Registration successful — Dawat',
            'preheader' => 'Your Dawat account registration is complete.',
            'badge' => 'REGISTRATION COMPLETE',
            'headline' => "You're all set",
            'dot' => true,
            'greeting' => 'Hello {{customer_name}},',
            'intro' => 'Your account registration is complete. You can now place orders and enjoy our services.',
            'blocks' => [
                DawatEmail::details('Account information', [
                    'Name' => '{{customer_name}}',
                    'Email' => '{{customer_email}}',
                    'Phone' => '{{customer_phone}}',
                ]),
            ],
            'buttons' => [DawatEmail::button('View profile', '{{profile_link}}')],
            'footer' => 'You received this email because you registered an account on Dawat.',
        ]);
    }

    private function getCustomerProfileUpdatedEmailTemplate(): string
    {
        return DawatEmail::render([
            'title' => 'Profile updated — Dawat',
            'preheader' => 'Your Dawat profile was updated on {{updated_date}}.',
            'badge' => 'PROFILE UPDATED',
            'headline' => 'Your profile<br>was updated',
            'greeting' => 'Hello {{customer_name}},',
            'intro' => 'Your profile has been updated successfully.',
            'blocks' => [
                DawatEmail::details('Update details', [
                    'Updated on' => '{{updated_date}}',
                    'Status' => 'Updated successfully',
                ]),
                DawatEmail::callout("Didn't make this change?", 'If you did not update your profile, please change your password and contact support right away.'),
            ],
            'buttons' => [DawatEmail::button('View profile', '{{profile_link}}')],
            'footer' => 'You received this email because your Dawat profile was changed.',
        ]);
    }
}
