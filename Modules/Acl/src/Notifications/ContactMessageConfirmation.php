<?php

namespace Modules\Acl\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Acl\Models\SupportMessage;

class ContactMessageConfirmation extends Notification
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
            ->subject('We received your message - ' . $this->message->reference_number)
            ->greeting('Hello ' . $this->message->name . ',')
            ->line('Thank you for contacting us. We have received your message and will get back to you as soon as possible.')
            ->line('**Reference Number:** ' . $this->message->reference_number)
            ->line('**Subject:** ' . $this->message->subject)
            ->line('**Category:** ' . $this->message->category_label)
            ->line('**Estimated Response Time:** 24-48 hours')
            ->line('Please keep this reference number for your records. You can use it to track your inquiry.')
            ->line('If you have any urgent concerns, please contact us directly.')
            ->salutation('Best regards, ' . config('app.name') . ' Support Team');
    }
}
