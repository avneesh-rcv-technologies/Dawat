<?php

namespace Modules\Notifications\Contracts;

interface NotificationChannel
{
    public function send(array $notifiables, Notification $notification): void;
    
    public function getName(): string;
    
    public function getConfig(): array;
}