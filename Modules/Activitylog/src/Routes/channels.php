<?php

use Illuminate\Support\Facades\Broadcast;
// userId: admin id
Broadcast::channel('activity-logs.{userId}', function ($user, $userId) {
    // Ensure the authenticated user's ID matches the requested userId for the channel
    return (int) $user->id === (int) $userId;
}, ['guards' => ['admin']]);  