<?php

namespace Modules\Orders\Database\Seeders;

use Modules\EmailNotification\Models\NotificationTemplate;

class OrderSnapshotNotificationSeeder extends OrderNotificationSeeder
{
    public function run(): void
    {
        $this->call(OrderWorkflowStatusSeeder::class);
        foreach ([
            'order_created_customer' => $this->getOrderCreatedEmailTemplate(),
            'order_status_updated' => $this->getOrderStatusEmailTemplate(),
            'restaurant_order_received' => (new \Modules\Restaurant\Database\Seeders\RestaurantNotificationSeeder())->tplOrderReceived(),
        ] as $name => $content) {
            $template = NotificationTemplate::firstOrNew(['name' => $name, 'locale' => 'en']);
            if (!$template->exists) {
                $template->subject = match ($name) {
                    'order_created_customer' => 'Order Confirmation - {{order_number}}',
                    'restaurant_order_received' => 'New Order #{{order_number}} Received',
                    default => 'Order {{order_number}} Status Updated',
                };
                $template->status = true;
            }
            $template->content = $content;
            $template->save();
        }
    }
}
