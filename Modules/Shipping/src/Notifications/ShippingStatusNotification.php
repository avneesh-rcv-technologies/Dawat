<?php

namespace Modules\Shipping\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Shipping\Models\Shipping;

class ShippingStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Shipping $shipping;
    protected string $previousStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Shipping $shipping, string $previousStatus)
    {
        $this->shipping = $shipping;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusMessage = $this->getStatusMessage();
        
        return (new MailMessage)
            ->subject('Shipping Status Update - Order #' . $this->shipping->order_id)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($statusMessage)
            ->line('Tracking Number: ' . $this->shipping->tracking_number)
            ->when($this->shipping->estimated_delivery_at, function ($mail) {
                return $mail->line('Estimated Delivery: ' . $this->shipping->estimated_delivery_at->format('M d, Y H:i'));
            })
            ->action('Track Your Order', url('/track/' . $this->shipping->tracking_number))
            ->line('Thank you for your order!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'shipping_id' => $this->shipping->id,
            'order_id' => $this->shipping->order_id,
            'tracking_number' => $this->shipping->tracking_number,
            'status' => $this->shipping->status->value,
            'previous_status' => $this->previousStatus,
            'message' => $this->getStatusMessage(),
        ];
    }

    /**
     * Get status-specific message.
     */
    protected function getStatusMessage(): string
    {
        return match($this->shipping->status->value) {
            'pending' => 'Your order is being prepared.',
            'processing' => 'Your order is being processed and will be ready soon.',
            'shipped' => 'Your order has been shipped and is on its way!',
            'out_for_delivery' => 'Your order is out for delivery and will arrive soon.',
            'delivered' => 'Your order has been successfully delivered. Enjoy your meal!',
            'cancelled' => 'Your order has been cancelled. If you have any questions, please contact us.',
            'returned' => 'Your order has been returned. Please contact us for assistance.',
            'ready_for_pickup' => 'Your order is ready for pickup! Please come to our location.',
            'picked_up' => 'Your order has been picked up. Thank you for choosing us!',
            default => 'Your order status has been updated.',
        };
    }
}