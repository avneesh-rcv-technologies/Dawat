<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Facades\Log;
use Modules\Acl\Models\Admin;
use Modules\Notifications\Services\NotificationService;
use Modules\Restaurant\Models\Restaurant;

class RestaurantPasswordResetNotificationService
{
    public function __construct(private NotificationService $notifications) {}

    public function sendForOwner(Admin $admin): bool
    {
        try {
            $restaurant = Restaurant::where('admin_id', $admin->id)->first();
            if (!$restaurant) return false;

            $queued = $this->notifications->trigger('Restaurant', 'RestaurantPasswordReset', $admin, [
                'restaurant_name' => $restaurant->name,
                'restaurant_email' => $admin->email,
                'reset_time' => now()->format('Y-m-d H:i:s'),
            ]);
            if (!$queued) {
                Log::warning('Unable to queue restaurant password reset confirmation.', ['admin_id' => $admin->id]);
            }
        } catch (\Throwable $exception) {
            // The password was already changed; a notification error must not
            // turn a successful reset into a failed response or a second email.
            Log::warning('Restaurant password reset confirmation failed.', [
                'admin_id' => $admin->id, 'error' => $exception->getMessage(),
            ]);
        }

        return true;
    }
}
