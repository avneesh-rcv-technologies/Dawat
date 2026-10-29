<?php

use Illuminate\Support\Facades\Broadcast;
use Modules\Backend\Helpers\BroadcastChannelToken;

/*
 * Token-based private channels - use encrypted channel-token meta instead of raw user-id/user-type.
 * The token is cryptographically signed and cannot be tampered with.
 */
$tokenChannelCallback = fn ($user, $token) => BroadcastChannelToken::validateForUser($token, $user);

Broadcast::channel('user.{token}.report-logs', $tokenChannelCallback, ['guards' => ['admin']]);