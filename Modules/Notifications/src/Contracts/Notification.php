<?php

namespace Modules\Notifications\Contracts;

interface Notification
{
    // Get the notification event code
    public function getEventCode(): string;

    // Get the variables for template replacement
    public function getVariables(): array;

    // Get the template identifier
    public function getTemplateIdentifier(): array;

    // Get available recipients for this notification
    public function getAvailableRecipients(): array;

    // Get recipient type mapping (admin, notifiable, etc.)
    public function getRecipientTypeMapping(): array;

    // Get recipient class mapping (which Model class for each recipient)
    public function getRecipientClassMapping(): array;

    // public function getAvailableRecipients(): array;

    // Convert notification to array
    public function toArray(): array;
}
