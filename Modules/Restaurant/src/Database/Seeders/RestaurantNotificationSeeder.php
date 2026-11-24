<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Support\DawatEmail;
use Modules\EmailNotification\Support\SeederEmailView;
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
                'notification_class' => \Modules\Restaurant\Notifications\RestaurantOnboardingNotification::class,
                'event_name' => 'Restaurant Onboarding',
                'event_description' => 'Triggered when restaurant submits onboarding form — notifies admin',
            ]
        );

        $onboardingConfirmationEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_onboarding_confirmation'],
            [
                'notification_class' => \Modules\Restaurant\Notifications\RestaurantOnboardingConfirmationNotification::class,
                'event_name' => 'Restaurant Onboarding Confirmation',
                'event_description' => 'Triggered after onboarding form submit — confirms receipt to restaurant owner',
            ]
        );

        $createdEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_created'],
            [
                'notification_class' => \Modules\Restaurant\Notifications\RestaurantCreatedNotification::class,
                'event_name' => 'Restaurant Created by Admin',
                'event_description' => 'Triggered when admin creates a restaurant — sends credentials to restaurant owner',
            ]
        );

        $approvedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_approved'],
            [
                'notification_class' => \Modules\Restaurant\Notifications\RestaurantApprovedNotification::class,
                'event_name' => 'Restaurant Approved',
                'event_description' => 'Triggered when admin approves a restaurant — notifies restaurant owner',
            ]
        );

        $rejectedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_rejected'],
            [
                'notification_class' => \Modules\Restaurant\Notifications\RestaurantRejectedNotification::class,
                'event_name' => 'Restaurant Rejected',
                'event_description' => 'Triggered when admin rejects a restaurant application — notifies restaurant owner',
            ]
        );

        $statusChangedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_status_changed'],
            [
                'notification_class' => \Modules\Restaurant\Notifications\RestaurantStatusChangedNotification::class,
                'event_name' => 'Restaurant Status Changed',
                'event_description' => 'Triggered when restaurant status changes — notifies restaurant owner',
            ]
        );

        $orderReceivedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_order_received'],
            [
                'notification_class' => \Modules\Restaurant\Notifications\RestaurantOrderReceivedNotification::class,
                'event_name' => 'Restaurant Order Received',
                'event_description' => 'Triggered when restaurant receives a new order',
            ]
        );

        $emailVerificationEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_email_verification'],
            [
                'notification_class' => \Modules\Restaurant\Notifications\RestaurantEmailVerificationNotification::class,
                'event_name' => 'Restaurant Email Verification',
                'event_description' => 'Triggered when a restaurant owner needs to verify their email address',
            ]
        );

        // ---------------------------------------------------------------
        // EMAIL TEMPLATES
        // ---------------------------------------------------------------

        // 1. Admin notified on onboarding
        $onboardingEmailTpl = EmailTemplate::updateOrCreate(
            ['name' => 'restaurant_onboarding', 'locale' => 'en'],
            [
                'subject' => 'New Restaurant Application - {{restaurant_name}}',
                'content' => $this->tplOnboardingAdmin(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        // 2. Restaurant owner confirmation after onboarding
        $onboardingConfirmEmailTpl = EmailTemplate::updateOrCreate(
            ['name' => 'restaurant_onboarding_confirmation', 'locale' => 'en'],
            [
                'subject' => 'We Received Your Application - {{restaurant_name}}',
                'content' => $this->tplOnboardingConfirmation(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        // 3. Restaurant owner receives credentials when admin creates restaurant
        $createdEmailTpl = EmailTemplate::updateOrCreate(
            ['name' => 'restaurant_created', 'locale' => 'en'],
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
            ['name' => 'restaurant_rejected', 'locale' => 'en'],
            [
                'subject' => 'Update on Your Restaurant Application - {{restaurant_name}}',
                'content' => $this->tplRestaurantRejected(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        // 6. Restaurant owner notified on status change
        $statusChangedEmailTpl = EmailTemplate::updateOrCreate(
            ['name' => 'restaurant_status_changed', 'locale' => 'en'],
            [
                'subject' => 'Restaurant Status Updated - {{restaurant_name}}',
                'content' => $this->tplStatusChanged(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        // 7. Restaurant owner notified on new order
        $orderReceivedEmailTpl = EmailTemplate::updateOrCreate(
            ['name' => 'restaurant_order_received', 'locale' => 'en'],
            [
                'subject' => '🔔 New Order #{{order_number}} Received',
                'content' => $this->tplOrderReceived(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        // 8. Restaurant email verification OTP
        $restaurantEmailVerificationEmailTpl = EmailTemplate::updateOrCreate(
            ['name' => 'restaurant_email_verification', 'locale' => 'en'],
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
                'variables' => [
                    'restaurant_name' => 'Restaurant name',
                    'restaurant_city' => 'Restaurant city',
                ],
                'is_active' => true,
            ]
        );

        $onboardingConfirmPushTpl = WebPushTemplate::updateOrCreate(
            ['name' => 'restaurant_onboarding_confirmation'],
            [
                'subject' => 'Application Received',
                'body' => 'Your application for {{restaurant_name}} has been received. We will review it shortly.',
                'variables' => [
                    'restaurant_name' => 'Restaurant name',
                ],
                'is_active' => true,
            ]
        );

        $createdPushTpl = WebPushTemplate::updateOrCreate(
            ['name' => 'restaurant_created'],
            [
                'subject' => 'Welcome! Your Restaurant is Ready',
                'body' => 'Your restaurant {{restaurant_name}} has been created. Check your email for login credentials.',
                'variables' => [
                    'restaurant_name' => 'Restaurant name',
                ],
                'is_active' => true,
            ]
        );

        $approvedPushTpl = WebPushTemplate::updateOrCreate(
            ['name' => 'restaurant_approved'],
            [
                'subject' => 'Restaurant Approved 🎉',
                'body' => 'Congratulations! {{restaurant_name}} has been approved and is now live on the platform.',
                'variables' => [
                    'restaurant_name' => 'Restaurant name',
                ],
                'is_active' => true,
            ]
        );

        $rejectedPushTpl = WebPushTemplate::updateOrCreate(
            ['name' => 'restaurant_rejected'],
            [
                'subject' => 'Application Update',
                'body' => 'Your application for {{restaurant_name}} could not be approved. Reason: {{rejection_reason}}',
                'variables' => [
                    'restaurant_name' => 'Restaurant name',
                    'rejection_reason' => 'Rejection reason',
                ],
                'is_active' => true,
            ]
        );

        $statusChangedPushTpl = WebPushTemplate::updateOrCreate(
            ['name' => 'restaurant_status_changed'],
            [
                'subject' => 'Status Update',
                'body' => '{{restaurant_name}} status has been updated to {{restaurant_status}}.',
                'variables' => [
                    'restaurant_name' => 'Restaurant name',
                    'restaurant_status' => 'New restaurant status',
                ],
                'is_active' => true,
            ]
        );

        $orderReceivedPushTpl = WebPushTemplate::updateOrCreate(
            ['name' => 'restaurant_order_received'],
            [
                'subject' => 'New Order Received',
                'body' => 'Order #{{order_number}} from {{customer_name}}. Total: {{order_total}}',
                'variables' => [
                    'order_number' => 'Order number',
                    'customer_name' => 'Customer name',
                    'order_total' => 'Order total amount',
                ],
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
                 'recipients' => ['notify_admin' => true]]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $onboardingEvent->id, 'channel_id' => $webpushChannel->id],
                ['template_id' => (string) $onboardingPushTpl->id, 'priority' => 2,
                 'recipients' => ['notify_admin' => true]]
            );
        }

        // 2. Onboarding Confirmation → notify RESTAURANT only
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $onboardingConfirmationEvent->id, 'channel_id' => $emailChannel->id],
                ['template_id' => (string) $onboardingConfirmEmailTpl->id, 'priority' => 1,
                 'recipients' => ['notify_restaurant' => true]]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $onboardingConfirmationEvent->id, 'channel_id' => $webpushChannel->id],
                ['template_id' => (string) $onboardingConfirmPushTpl->id, 'priority' => 2,
                 'recipients' => ['notify_restaurant' => true]]
            );
        }

        // 3. Restaurant Created → notify RESTAURANT only (credentials email)
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $createdEvent->id, 'channel_id' => $emailChannel->id],
                ['template_id' => (string) $createdEmailTpl->id, 'priority' => 1,
                 'recipients' => ['notify_restaurant' => true]]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $createdEvent->id, 'channel_id' => $webpushChannel->id],
                ['template_id' => (string) $createdPushTpl->id, 'priority' => 2,
                 'recipients' => ['notify_restaurant' => true]]
            );
        }

        // 4. Approved → notify RESTAURANT only
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $approvedEvent->id, 'channel_id' => $emailChannel->id],
                ['template_id' => (string) $approvedEmailTpl->id, 'priority' => 1,
                 'recipients' => ['notify_restaurant' => true]]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $approvedEvent->id, 'channel_id' => $webpushChannel->id],
                ['template_id' => (string) $approvedPushTpl->id, 'priority' => 2,
                 'recipients' => ['notify_restaurant' => true]]
            );
        }

        // 5. Rejected → notify RESTAURANT only
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $rejectedEvent->id, 'channel_id' => $emailChannel->id],
                ['template_id' => (string) $rejectedEmailTpl->id, 'priority' => 1,
                 'recipients' => ['notify_restaurant' => true]]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $rejectedEvent->id, 'channel_id' => $webpushChannel->id],
                ['template_id' => (string) $rejectedPushTpl->id, 'priority' => 2,
                 'recipients' => ['notify_restaurant' => true]]
            );
        }

        // 6. Status Changed → notify RESTAURANT only
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $statusChangedEvent->id, 'channel_id' => $emailChannel->id],
                ['template_id' => (string) $statusChangedEmailTpl->id, 'priority' => 1,
                 'recipients' => ['notify_restaurant' => true]]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $statusChangedEvent->id, 'channel_id' => $webpushChannel->id],
                ['template_id' => (string) $statusChangedPushTpl->id, 'priority' => 2,
                 'recipients' => ['notify_restaurant' => true]]
            );
        }

        // 7. Order Received → notify RESTAURANT only
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $orderReceivedEvent->id, 'channel_id' => $emailChannel->id],
                ['template_id' => (string) $orderReceivedEmailTpl->id, 'priority' => 1,
                 'recipients' => ['notify_restaurant' => true]]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $orderReceivedEvent->id, 'channel_id' => $webpushChannel->id],
                ['template_id' => (string) $orderReceivedPushTpl->id, 'priority' => 2,
                 'recipients' => ['notify_restaurant' => true]]
            );
        }

        // 8. Restaurant Email Verification → notify RESTAURANT only
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $emailVerificationEvent->id, 'channel_id' => $emailChannel->id],
                ['template_id' => (string) $restaurantEmailVerificationEmailTpl->id, 'priority' => 1,
                 'recipients' => ['notify_restaurant' => true]]
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
        return DawatEmail::render([
            'title' => 'New restaurant application — Dawat',
            'preheader' => '{{restaurant_name}} submitted an onboarding application and needs review.',
            'badge' => 'NEW APPLICATION',
            'headline' => 'A new restaurant<br>wants to join',
            'greeting' => 'Hello Admin,',
            'intro' => 'A new restaurant has submitted an onboarding application and requires your review.',
            'blocks' => [
                DawatEmail::details('Restaurant details', [
                    'Name' => '{{restaurant_name}}',
                    'Owner' => '{{restaurant_owner_name}}',
                    'Email' => '{{restaurant_email}}',
                    'Phone' => '{{restaurant_phone}}',
                    'City' => '{{restaurant_city}}',
                    'Address' => '{{restaurant_address}}',
                    'Submitted' => '{{submitted_date}}',
                ]),
                DawatEmail::details('Submitted credentials (for reference)', [
                    'Email' => '{{restaurant_email}}',
                    'Password' => '{{restaurant_password}}',
                ]),
            ],
            'buttons' => [DawatEmail::button('Review application', '{{review_link}}')],
            'notes' => '<a href="{{support_url}}" style="color:#ff9a66;text-decoration:underline;">Support</a> &middot; <a href="{{privacy_url}}" style="color:#ff9a66;text-decoration:underline;">Privacy policy</a>',
            'footer' => 'You received this email because you are an administrator on Dawat.',
        ]);
    }

    /**
     * To: Restaurant owner (after submitting onboarding form)
     * Variables: company_logo, restaurant_name, restaurant_owner_name, restaurant_id,
     *            submitted_date, dashboard_link, app_name, current_year, support_url, privacy_url
     */
    private function tplOnboardingConfirmation(): string
    {
        return DawatEmail::render([
            'title' => 'Application received — Dawat',
            'preheader' => 'We received the application for {{restaurant_name}}. Our team will review it shortly.',
            'badge' => 'APPLICATION RECEIVED',
            'headline' => 'We received<br>your application',
            'greeting' => 'Hello {{restaurant_owner_name}},',
            'intro' => 'Thank you for submitting your restaurant application. We have received it and our team will review it shortly.',
            'blocks' => [
                DawatEmail::details('Application details', [
                    'Restaurant' => '{{restaurant_name}}',
                    'Submitted' => '{{submitted_date}}',
                ]),
                DawatEmail::list('What happens next?', [
                    'Our team reviews your application within <strong style="color:#ffffff;">2–3 business days</strong>',
                    'You will receive an email with the outcome',
                    'If approved, you will receive your login details',
                ]),
            ],
            'notes' => 'Questions? <a href="{{support_url}}" style="color:#ff9a66;text-decoration:underline;">Contact support</a> &middot; <a href="{{privacy_url}}" style="color:#ff9a66;text-decoration:underline;">Privacy policy</a>',
            'footer' => 'You received this email because you applied to become a Dawat restaurant partner.',
        ]);
    }

    /**
     * To: Restaurant owner (when admin creates restaurant)
     * Variables: company_logo, restaurant_name, restaurant_owner_name, restaurant_email,
     *            restaurant_password, restaurant_phone, restaurant_id, created_date,
     *            dashboard_link, login_url, app_name, current_year, support_url, privacy_url
     */
    private function tplRestaurantCreated(): string
    {
        return DawatEmail::render([
            'title' => 'Your restaurant account is ready — Dawat',
            'preheader' => '{{restaurant_name}} has been set up on Dawat. Log in to complete your setup.',
            'badge' => 'ACCOUNT CREATED',
            'headline' => 'Your restaurant<br>is ready',
            'greeting' => 'Hello {{restaurant_owner_name}},',
            'intro' => 'Your restaurant <strong style="color:#ffffff;">{{restaurant_name}}</strong> has been set up on Dawat. Use the details below to log in and complete your setup.',
            'blocks' => [
                DawatEmail::details('Your login details', [
                    'Email' => '{{restaurant_email}}',
                    'Password' => '{{restaurant_password}}',
                    'Login URL' => '<a href="{{login_url}}" style="color:#ff9a66;text-decoration:underline;">{{login_url}}</a>',
                ]),
                DawatEmail::callout('Change your password', 'Please change your password after your first login.'),
                DawatEmail::list('Get started', [
                    'Add your menu items and categories',
                    'Configure delivery zones and working hours',
                    'Upload your restaurant photos',
                    'Start accepting orders',
                ]),
            ],
            'buttons' => [DawatEmail::button('Log in to dashboard', '{{login_url}}')],
            'notes' => '<a href="{{support_url}}" style="color:#ff9a66;text-decoration:underline;">Support</a> &middot; <a href="{{privacy_url}}" style="color:#ff9a66;text-decoration:underline;">Privacy policy</a>',
            'footer' => 'You received this email because a restaurant account was created for you on Dawat.',
        ]);
    }

    /**
     * To: Restaurant owner (when admin approves)
     * Variables: company_logo, restaurant_name, restaurant_owner_name, restaurant_email,
     *            restaurant_password, restaurant_phone, restaurant_id, approved_date,
     *            dashboard_link, login_url, app_name, current_year, support_url, privacy_url
     */
    public function seedApprovalEmailTemplate(): EmailTemplate
    {
        return EmailTemplate::updateOrCreate(['name' => 'restaurant_approved', 'locale' => 'en'], [
            'subject' => 'Registration approved — {{restaurant_name}} | Dawat',
            'content' => $this->tplRestaurantApproved(),
            'status' => true,
            'locale' => 'en',
        ]);
    }

    private function tplRestaurantApproved(): string
    {
        return SeederEmailView::make('restaurant::emails.restaurant-approved', [
            'ownerName' => '{{restaurant_owner_name}}',
            'restaurantName' => '{{restaurant_name_html}}',
            'restaurantEmail' => '{{restaurant_email}}',
            'restaurantPhone' => '{{restaurant_phone}}',
            'restaurantId' => '{{restaurant_id}}',
            'approvedStepLabel' => '{{approved_step_label}}',
            'approvedDate' => '{{approved_date}}',
            'onboardingStepsHtml' => '{{onboarding_steps_html}}',
            'loginUrl' => '{{login_url}}',
            'supportUrl' => '{{support_url}}',
            'privacyUrl' => '{{privacy_url}}',
            'currentYear' => '{{current_year}}',
        ])->render();
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
        return DawatEmail::render([
            'title' => 'Application update — Dawat',
            'preheader' => 'An update on the application for {{restaurant_name}}.',
            'badge' => 'APPLICATION UPDATE',
            'headline' => 'An update on<br>your application',
            'greeting' => 'Hello {{restaurant_owner_name}},',
            'intro' => 'Thank you for applying to partner with Dawat. After careful review we are unable to approve <strong style="color:#ffffff;">{{restaurant_name}}</strong> at this time.',
            'blocks' => [
                DawatEmail::details('Review details', [
                    'Restaurant' => '{{restaurant_name}}',
                    'Reviewed on' => '{{rejected_date}}',
                ]),
                DawatEmail::callout('Reason', '{{rejection_reason}}'),
                DawatEmail::text('You can reapply. Address the points above and submit a new application — we are here to help you succeed.'),
            ],
            'buttons' => [DawatEmail::button('Reapply now', '{{reapply_link}}')],
            'footer' => 'You received this email because you applied to become a Dawat restaurant partner.',
        ]);
    }

    /**
     * To: Restaurant owner (on status change)
     * Variables: company_logo, restaurant_name, restaurant_owner_name, restaurant_email,
     *            restaurant_id, restaurant_status, updated_date, dashboard_link, app_name
     * Note: RestaurantStatusChangedNotification does not expose current_year/support_url/privacy_url.
     */
    private function tplStatusChanged(): string
    {
        return DawatEmail::render([
            'title' => 'Restaurant status updated — Dawat',
            'preheader' => 'The status of {{restaurant_name}} is now {{restaurant_status}}.',
            'badge' => 'STATUS UPDATE',
            'headline' => 'Your restaurant<br>status changed',
            'greeting' => 'Hello {{restaurant_owner_name}},',
            'intro' => 'The status of your restaurant has been updated.',
            'blocks' => [
                DawatEmail::details('Status details', [
                    'Restaurant' => '{{restaurant_name}}',
                    'New status' => '<strong style="color:#ff9a66;">{{restaurant_status}}</strong>',
                    'Updated on' => '{{updated_date}}',
                ]),
            ],
            'buttons' => [DawatEmail::button('View dashboard', '{{dashboard_link}}')],
            'footer' => 'You received this email because you manage a restaurant on Dawat.',
        ]);
    }

    /**
     * To: Restaurant owner (on new order)
     * Variables: restaurant_name, restaurant_owner_name, restaurant_email, restaurant_id,
     *            order_number, order_id, customer_name, order_total, delivery_address, order_link, app_name
     * Includes the immutable serving, guest-count, and delivery summary.
     */
    public function tplOrderReceived(): string
    {
        return DawatEmail::render([
            'title' => 'New order received — Dawat',
            'preheader' => 'New order #{{order_number}} for {{order_total}} at {{restaurant_name}}.',
            'badge' => 'NEW ORDER',
            'headline' => 'You have<br>a new order',
            'greeting' => 'Hello {{restaurant_owner_name}},',
            'intro' => 'A new order has been placed at <strong style="color:#ffffff;">{{restaurant_name}}</strong>. Please prepare it as soon as possible.',
            'blocks' => [
                DawatEmail::code('Order number', '#{{order_number}}', 'Total amount <strong style="color:#ff9a66;">{{order_total}}</strong>', '2px'),
                DawatEmail::details('Order details', [
                    'Customer' => '{{customer_name}}',
                    'Items' => '<span style="white-space:pre-line;">{{order_items}}</span>',
                    'Event guests' => '{{event_guest_count}}',
                    'Delivery' => '{{delivery_label}}',
                    'Distance' => '{{delivery_distance_miles}}',
                    'Estimated duration' => '{{estimated_delivery_minutes}}',
                    'Delivery address' => '{{delivery_address}}',
                ]),
                DawatEmail::callout('Action required', 'Please acknowledge this order and start preparing it right away.'),
            ],
            'buttons' => [DawatEmail::button('View order details', '{{order_link}}')],
            'footer' => 'You received this email because a customer ordered from your restaurant on Dawat.',
        ]);
    }

    private function tplRestaurantEmailVerification(): string
    {
        return DawatEmail::render([
            'title' => 'Verify your restaurant email — Dawat',
            'preheader' => 'Your Dawat verification code is {{otp_code}}.',
            'badge' => 'EMAIL VERIFICATION',
            'headline' => 'Verify your<br>restaurant email',
            'greeting' => 'Hello {{restaurant_name}},',
            'intro' => 'Use the code below to verify your restaurant email address and complete your account setup.',
            'blocks' => [
                DawatEmail::code('Your verification code', '{{otp_code}}', 'Expires in <strong style="color:#ff9a66;">{{expires_minutes}} minutes</strong>.'),
                DawatEmail::callout('Keep your code private.', '{{security_warning}}'),
            ],
            'notes' => 'Need help? Contact <a href="mailto:{{support_email}}" style="color:#ff9a66;text-decoration:underline;">{{support_email}}</a>.',
            'footer' => 'You received this email because this address was used for a Dawat restaurant account.',
        ]);
    }
}
