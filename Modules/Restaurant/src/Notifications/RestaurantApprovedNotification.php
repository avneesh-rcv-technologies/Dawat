<?php

namespace Modules\Restaurant\Notifications;

use Exception;
use Modules\Restaurant\Models\Restaurant;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Services\RestaurantOnboardingSummaryService;

class RestaurantApprovedNotification implements Notification
{
    private ?array $variables = null;

    public function __construct(protected $restaurant = null, protected $data = []) {}

    public function getEventCode(): string
    {
        return 'restaurant_approved';
    }

    public function getTemplateIdentifier(): array
    {
        $channels = [];

        try {
            $event = NotificationEvent::where('event_code', $this->getEventCode())->first();

            if (!$event) {
                return $channels;
            }

            $mappings = NotificationMapping::where('event_id', $event->id)->get();

            foreach ($mappings as $mapping) {
                $channel = NotificationChannel::find($mapping->channel_id);
                if ($channel) {
                    $channels[$channel->name] = $mapping->template_id;

                    // Add recipient flags from mapping
                    if (!empty($mapping->recipients)) {
                        $channels = array_merge($channels, $mapping->recipients);
                    }
                }
            }
        } catch (Exception $e) {
            // Log error if needed
        }

        return $channels;
    }

    public function getVariables(): array
    {
        if ($this->variables !== null) return $this->variables;

        $restaurant = null;
        $passwordInfo = $this->data['password']
            ?? $this->restaurant['password']
            ?? '(Use Forgot Password if needed)';

        if ($this->restaurant instanceof Restaurant) {
            $restaurant = $this->restaurant;
        } elseif (isset($this->data['restaurant'])) {
            $restaurant = $this->data['restaurant'];
        } elseif (isset($this->restaurant['restaurant'])) {
           
            $restaurant = $this->restaurant['restaurant'];
        }

        if (!$restaurant) {
            return [];
        }

        if (isset($this->data['data']['restaurant_password'])) {
            $passwordInfo = $this->data['data']['restaurant_password'];
        } elseif (isset($this->restaurant['data']['restaurant_password'])) {
            $passwordInfo = $this->restaurant['data']['restaurant_password'];
        }

        $admin = $restaurant->admin;
        $steps = app(RestaurantOnboardingSummaryService::class)->build(
            $restaurant,
            $this->data['pending_kyc_requests'] ?? $this->restaurant['pending_kyc_requests'] ?? []
        );

        return $this->variables = [
            "company_logo" =>  fn_get_image('company_logo', 0)['url'] ?? '',
            'restaurant_name' => $restaurant->name ?? 'Restaurant',
            'restaurant_owner_name' => $admin->name ?? 'Owner',
            'restaurant_email' => $admin->email ?? '',
            'restaurant_password' => $passwordInfo,
            'restaurant_phone' => $restaurant->phone ?? '',
            'restaurant_id' => $restaurant->id,
            'approved_date' => $restaurant->approved_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
            'approved_step_key' => OnboardingStep::REGISTRATION->value,
            'approved_step_label' => OnboardingStep::REGISTRATION->label(),
            'onboarding_steps_html' => view('restaurant::emails.partials.onboarding-summary', compact('steps'))->render(),
            'dashboard_link' => config('app.url', 'https://app.local') . '/dashboard',
            'login_url' => config('app.url', 'https://app.local') . '/admin/login',
            'app_name' => fn_get_setting('general.company.name'),
            'current_year' => date('Y'),
            'support_url' => config('app.url', 'https://app.local') . '/support',
            'privacy_url' => config('app.url', 'https://app.local') . '/privacy-policy',
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'restaurant_name' => 'Name of the restaurant',
            'restaurant_owner_name' => 'Name of the restaurant owner',
            'restaurant_email' => 'Restaurant owner email address',
            'restaurant_password' => 'Temporary password for restaurant login',
            'restaurant_phone' => 'Restaurant phone number',
            'restaurant_id' => 'Unique ID of the restaurant',
            'approved_date' => 'Date and time when restaurant was approved',
            'approved_step_key' => 'Approved onboarding step key',
            'approved_step_label' => 'Approved onboarding step label',
            'onboarding_steps_html' => 'Step-by-step onboarding statuses and submitted details',
            'dashboard_link' => 'Link to restaurant dashboard',
            'login_url' => 'Direct URL to login page',
            'app_name' => 'Application name',
            'current_year' => 'Current year',
            'support_url' => 'Support page URL',
            'privacy_url' => 'Privacy policy URL',
        ];
    }

    public function getPushVariables(): array
    {
        // The email summary can exceed the push provider's payload limit.
        return array_diff_key($this->getVariables(), ['onboarding_steps_html' => true]);
    }

    public function getAvailableRecipients(): array
    {
        return [
            'notify_restaurant' => 'Notify Restaurant',
            'notify_admin' => 'Notify Admin',
        ];
    }

    public function getRecipientTypeMapping(): array
    {
        return [
            'notify_restaurant' => 'notifiable',
            'notify_admin' => 'admin',
        ];
    }

    public function getRecipientClassMapping(): array
    {
        return [
            'notify_restaurant' => \Modules\Acl\Models\Admin::class,
            'notify_admin' => \Modules\Acl\Models\Admin::class,
        ];
    }

    public function toArray(): array
    {
        return [
            'event_code' => $this->getEventCode(),
            'template' => $this->getTemplateIdentifier(),
            'variables' => $this->getVariables(),
            'available_variables' => $this->getAvailableVariables(),
        ];
    }
}
