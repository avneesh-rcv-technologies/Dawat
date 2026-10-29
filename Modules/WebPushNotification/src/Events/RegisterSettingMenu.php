<?php

namespace Modules\WebPushNotification\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Core\Events\AbstractSettingMenuEvent;

class RegisterSettingMenu extends AbstractSettingMenuEvent
{
    use SerializesModels;

    public function __construct()
    {
        parent::__construct('webpush');
    }
}
