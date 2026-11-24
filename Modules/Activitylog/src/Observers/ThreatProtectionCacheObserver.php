<?php

namespace Modules\Activitylog\Observers;

use Modules\Activitylog\Models\AllowedIp;
use Modules\Activitylog\Models\BlockedIp;
use Modules\Activitylog\Support\ThreatProtectionCache;

class ThreatProtectionCacheObserver
{
    public function saved(BlockedIp|AllowedIp $model): void
    {
        $this->forgetFor($model);
    }

    public function deleted(BlockedIp|AllowedIp $model): void
    {
        $this->forgetFor($model);
    }

    protected function forgetFor(BlockedIp|AllowedIp $model): void
    {
        if ($model instanceof BlockedIp) {
            ThreatProtectionCache::forgetBlockedIps();

            return;
        }

        ThreatProtectionCache::forgetAllowedAdminIps();
    }
}
