<?php

namespace Modules\Rating\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RatingWorkflowNotification extends Notification implements \Modules\Notifications\Contracts\Notification
{
    use Queueable;

    public function __construct(private string $title, private string $message, private array $data = []) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array
    {
        return ['title' => $this->title, 'message' => $this->message] + $this->data;
    }

    public function getEventCode(): string { return 'rating_workflow'; }
    public function getVariables(): array { return ['title' => $this->title, 'message' => $this->message] + $this->data; }
    public function getTemplateIdentifier(): array
    {
        $event = \Modules\Notifications\Models\NotificationEvent::where('event_code', $this->getEventCode())->first();
        $result = [];
        foreach ($event?->mappings ?? [] as $mapping) {
            $channel = $mapping->channel;
            if ($channel) $result[$channel->name] = $mapping->template_id;
        }
        return $result;
    }
    public function getAvailableRecipients(): array { return []; }
    public function getRecipientTypeMapping(): array { return []; }
    public function getRecipientClassMapping(): array { return []; }
    public function toArray(): array { return $this->getVariables(); }
}
