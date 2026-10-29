<?php

namespace Modules\Acl\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Acl\Models\SupportMessage;

class ContactMessageReceived extends Notification
{
    use Queueable;

    protected SupportMessage $message;

    public function __construct(SupportMessage $message)
    {
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Support] ' . $this->message->category_label . ' - ' . $this->message->subject)
            ->greeting('New Support Message Received')
            ->line('**Reference:** ' . $this->message->reference_number)
            ->line('**From:** ' . $this->message->name . ' (' . $this->message->email . ')')
            ->line('**Category:** ' . $this->message->category_label)
            ->line('**Subject:** ' . $this->message->subject)
            ->line('**Message:**')
            ->line($this->message->message)
            ->when($this->message->customer_id, function ($mail) {
                return $mail->line('**Customer ID:** ' . $this->message->customer_id);
            })
            ->when($this->message->order_id, function ($mail) {
                return $mail->line('**Related Order:** #' . $this->message->order_id);
            })
            ->line('**Submitted:** ' . $this->message->created_at->format('Y-m-d H:i:s'));
    }
}
